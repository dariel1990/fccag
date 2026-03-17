<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    Search,
    X,
    Filter,
    Eye,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
    index,
    destroy,
} from '@/actions/App/Http/Controllers/ParticipantController';
import Heading from '@/components/Heading.vue';
import DeleteConfirmDialog from '@/components/participants/DeleteConfirmDialog.vue';
import ParticipantFormDialog from '@/components/participants/ParticipantFormDialog.vue';
import ParticipantViewDialog from '@/components/participants/ParticipantViewDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableEmpty,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Participant = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    gender: string;
    birthday: string | null;
    contact_number: string | null;
    cell_group_id: number | null;
    cell_group: string | null;
    classification_id: number | null;
    classification: string | null;
    department_id: number | null;
    department: string | null;
    ministry_ids: number[];
    ministries: string[];
    date_joined: string;
    is_active: boolean;
};

type Props = {
    participants?: Participant[];
    cellGroups: { id: number; name: string }[];
    classifications: { id: number; name: string; code: string }[];
    ministries: { id: number; name: string }[];
    departments: { id: number; name: string }[];
};

const props = defineProps<Props>();

const participants = ref<Participant[]>(props.participants || []);

// Filter options
const filterOptions = ref({
    cell_groups: props.cellGroups ?? [] as { id: number; name: string }[],
    classifications: props.classifications ?? [] as { id: number; name: string }[],
    ministries: props.ministries ?? [] as { id: number; name: string }[],
    departments: props.departments ?? [] as { id: number; name: string }[],
});

// Default classification to "Member"
const memberClassificationId = props.classifications?.find(
    (c) => c.name.toLowerCase() === 'member',
)?.id ?? null;

const searchQuery = ref('');
const selectedBirthMonth = ref('');
const selectedCellGroup = ref('');
const selectedClassification = ref(memberClassificationId ? String(memberClassificationId) : '');
const selectedMinistry = ref('');
const selectedDepartment = ref('');
const showFilters = ref(false);

const MONTHS = [
    { value: '1', label: 'January' }, { value: '2', label: 'February' },
    { value: '3', label: 'March' }, { value: '4', label: 'April' },
    { value: '5', label: 'May' }, { value: '6', label: 'June' },
    { value: '7', label: 'July' }, { value: '8', label: 'August' },
    { value: '9', label: 'September' }, { value: '10', label: 'October' },
    { value: '11', label: 'November' }, { value: '12', label: 'December' },
];

const activeFilterCount = computed(() =>
    (searchQuery.value ? 1 : 0) +
    (selectedBirthMonth.value ? 1 : 0) +
    (selectedCellGroup.value ? 1 : 0) +
    (selectedClassification.value ? 1 : 0) +
    (selectedMinistry.value ? 1 : 0) +
    (selectedDepartment.value ? 1 : 0),
);

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

// Client-side filtering
const filteredParticipants = computed(() => {
    return participants.value.filter((p) => {
        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            if (!p.full_name.toLowerCase().includes(q)) return false;
        }
        if (selectedBirthMonth.value && p.birthday) {
            const bMonth = p.birthday.split('-')[1]?.replace(/^0/, '');
            if (bMonth !== selectedBirthMonth.value) return false;
        } else if (selectedBirthMonth.value && !p.birthday) {
            return false;
        }
        if (selectedCellGroup.value && p.cell_group_id !== Number(selectedCellGroup.value)) return false;
        if (selectedClassification.value && p.classification_id !== Number(selectedClassification.value)) return false;
        if (selectedMinistry.value && !p.ministry_ids?.includes(Number(selectedMinistry.value))) return false;
        if (selectedDepartment.value && p.department_id !== Number(selectedDepartment.value)) return false;
        return true;
    });
});

function clearFilters() {
    searchQuery.value = '';
    selectedBirthMonth.value = '';
    selectedCellGroup.value = '';
    selectedClassification.value = '';
    selectedMinistry.value = '';
    selectedDepartment.value = '';
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'People of God',
        href: index().url,
    },
];

// Dialog state
const viewDialogOpen = ref(false);
const viewParticipantId = ref<number | null>(null);

const formDialogOpen = ref(false);
const formParticipant = ref<Participant | null>(null);

const deleteDialogOpen = ref(false);
const deleteParticipant = ref<Participant | null>(null);

function openViewDialog(id: number) {
    viewParticipantId.value = id;
    viewDialogOpen.value = true;
}

function openCreateDialog() {
    formParticipant.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(participant: Participant) {
    formParticipant.value = participant;
    formDialogOpen.value = true;
}

function openDeleteDialog(participant: Participant) {
    deleteParticipant.value = participant;
    deleteDialogOpen.value = true;
}

function handleFormSaved() {
    router.reload();
}

function handleDeleteConfirm() {
    if (!deleteParticipant.value) return;

    router.delete(destroy(deleteParticipant.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteParticipant.value = null;
        },
    });
}
</script>

<template>
    <Head title="People of God" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 p-4"
        >
            <div class="flex items-center justify-between">
                <Heading
                    title="People of God"
                    description="Manage people of God and new members"
                />
                <Button @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Person
                </Button>
            </div>

            <!-- Search & Filters -->
            <div class="space-y-3">
                <!-- Search Bar -->
                <div class="relative">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search by name..."
                        class="pr-10 pl-9"
                    />
                    <button
                        v-if="searchQuery"
                        class="absolute top-1/2 right-3 -translate-y-1/2"
                        @click="searchQuery = ''"
                    >
                        <X class="h-4 w-4 text-muted-foreground" />
                    </button>
                </div>

                <!-- Filter Toggle -->
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        class="flex-1 justify-start gap-1 sm:flex-none"
                        @click="showFilters = !showFilters"
                    >
                        <Filter class="h-4 w-4" />
                        Filters
                        <Badge v-if="activeFilterCount > 0" variant="secondary" class="ml-1 px-1.5 py-0">
                            {{ activeFilterCount }}
                        </Badge>
                    </Button>
                    <Button v-if="hasActiveFilters" variant="ghost" size="sm" class="text-muted-foreground" @click="clearFilters">
                        Clear all
                    </Button>
                </div>

                <!-- Filter Panel -->
                <div
                    v-if="showFilters"
                    class="grid grid-cols-2 gap-2 rounded-lg border border-sidebar-border/70 p-3 dark:border-sidebar-border sm:grid-cols-3 lg:grid-cols-5"
                >
                    <!-- Birth Month -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Birth Month</label>
                        <select
                            v-model="selectedBirthMonth"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm"
                        >
                            <option value="">All Months</option>
                            <option v-for="m in MONTHS" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                    </div>

                    <!-- Cell Group -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Cell Group</label>
                        <select
                            v-model="selectedCellGroup"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm"
                        >
                            <option value="">All Groups</option>
                            <option v-for="cg in filterOptions.cell_groups" :key="cg.id" :value="String(cg.id)">{{ cg.name }}</option>
                        </select>
                    </div>

                    <!-- Classification -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Classification</label>
                        <select
                            v-model="selectedClassification"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm"
                        >
                            <option value="">All</option>
                            <option v-for="c in filterOptions.classifications" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Ministry -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Ministry</label>
                        <select
                            v-model="selectedMinistry"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm"
                        >
                            <option value="">All Ministries</option>
                            <option v-for="m in filterOptions.ministries" :key="m.id" :value="String(m.id)">{{ m.name }}</option>
                        </select>
                    </div>

                    <!-- Department -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Department</label>
                        <select
                            v-model="selectedDepartment"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1.5 text-sm"
                        >
                            <option value="">All Departments</option>
                            <option v-for="d in filterOptions.departments" :key="d.id" :value="String(d.id)">{{ d.name }}</option>
                        </select>
                    </div>
                </div>

                <!-- Results Count -->
                <div class="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        {{ filteredParticipants.length }}
                        {{ filteredParticipants.length !== 1 ? 'people' : 'person' }}
                        <span v-if="hasActiveFilters"> found</span>
                    </span>
                </div>
            </div>

            <!-- DESKTOP TABLE VIEW -->
            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Gender</TableHead>
                            <TableHead>Contact</TableHead>
                            <TableHead>Cell Group</TableHead>
                            <TableHead>Ministry</TableHead>
                            <TableHead>Date Joined</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="filteredParticipants.length === 0"
                            :colspan="8"
                        >
                            No people of God found.
                        </TableEmpty>
                        <TableRow
                            v-for="participant in filteredParticipants"
                            :key="participant.id"
                        >
                            <TableCell class="font-medium">
                                {{ participant.full_name }}
                            </TableCell>
                            <TableCell class="capitalize">
                                {{ participant.gender }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ participant.contact_number || '—' }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ participant.cell_group || '—' }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ participant.ministries?.join(', ') || '—' }}
                            </TableCell>
                            <TableCell>
                                {{ participant.date_joined }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge
                                    :variant="
                                        participant.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        participant.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openViewDialog(participant.id)"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(participant)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openDeleteDialog(participant)"
                                    >
                                        <Trash2
                                            class="h-4 w-4 text-destructive"
                                        />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Dialogs -->
            <ParticipantViewDialog
                v-model:open="viewDialogOpen"
                :participant-id="viewParticipantId"
            />

            <ParticipantFormDialog
                v-model:open="formDialogOpen"
                :participant="formParticipant"
                :cell-groups="props.cellGroups"
                :classifications="props.classifications"
                :ministries="props.ministries"
                :departments="props.departments"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteParticipant?.full_name"
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
