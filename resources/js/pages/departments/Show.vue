<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, UserCheck, Users } from 'lucide-vue-next';
import Multiselect from '@vueform/multiselect';
import {
    edit,
    index,
} from '@/actions/App/Http/Controllers/DepartmentController';
import {
    store as storeOfficer,
    update as updateOfficer,
    destroy as destroyOfficer,
} from '@/actions/App/Http/Controllers/DepartmentOfficerController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Person = { id: number; full_name: string };

type Officer = {
    id: number;
    role: string;
    started_at: string;
    ended_at: string | null;
    person: Person;
};

type Member = { id: number; full_name: string; is_active: boolean };

type Department = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    photo_url: string | null;
    officers: Officer[];
    members: Member[];
};

type Participant = { id: number; first_name: string; last_name: string; full_name?: string };

type Props = {
    department: Department;
    participants: Participant[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Departments', href: index().url },
    { title: props.department.name },
];

// Derived participant options for Multiselect
const participantOptions = computed(() =>
    props.participants.map((p) => ({
        id: p.id,
        name: `${p.last_name}, ${p.first_name}`,
    })),
);

// Current vs past split
const currentOfficers = computed(() =>
    props.department.officers.filter((o) => o.ended_at === null),
);
const pastOfficers = computed(() =>
    props.department.officers.filter((o) => o.ended_at !== null),
);

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// ── Add / Edit Dialog ──────────────────────────────────────────────
const officerDialogOpen = ref(false);
const editingOfficer = ref<Officer | null>(null);

const form = useForm({
    person_id: null as number | null,
    role: '',
    started_at: '',
    ended_at: '',
});

function openAddDialog() {
    editingOfficer.value = null;
    form.reset();
    form.started_at = new Date().toISOString().split('T')[0];
    officerDialogOpen.value = true;
}

function openEditDialog(officer: Officer) {
    editingOfficer.value = officer;
    form.person_id = officer.person.id;
    form.role = officer.role;
    form.started_at = officer.started_at;
    form.ended_at = officer.ended_at ?? '';
    officerDialogOpen.value = true;
}

function closeOfficerDialog() {
    officerDialogOpen.value = false;
    editingOfficer.value = null;
    form.clearErrors();
}

function submitOfficer() {
    if (editingOfficer.value) {
        form.put(updateOfficer({ department: props.department.id, officer: editingOfficer.value.id }).url, {
            onSuccess: () => closeOfficerDialog(),
        });
    } else {
        form.post(storeOfficer(props.department.id).url, {
            onSuccess: () => closeOfficerDialog(),
        });
    }
}

// ── Delete ─────────────────────────────────────────────────────────
const deleteDialogOpen = ref(false);
const deletingOfficer = ref<Officer | null>(null);

function openDeleteDialog(officer: Officer) {
    deletingOfficer.value = officer;
    deleteDialogOpen.value = true;
}

function confirmDelete() {
    if (!deletingOfficer.value) return;
    router.delete(destroyOfficer({ department: props.department.id, officer: deletingOfficer.value.id }).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deletingOfficer.value = null;
        },
    });
}

// ── Members ────────────────────────────────────────────────────────
const membersDialogOpen = ref(false);
</script>

<template>
    <Head :title="props.department.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">

            <!-- Department Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <img
                        v-if="props.department.photo_url"
                        :src="props.department.photo_url"
                        :alt="props.department.name"
                        class="h-16 w-16 rounded-xl object-cover"
                    />
                    <div v-else class="h-16 w-16 rounded-xl bg-muted" />
                    <div>
                        <Heading
                            :title="props.department.name"
                            :description="props.department.description ?? undefined"
                        />
                        <Badge
                            class="mt-1"
                            :variant="props.department.is_active ? 'default' : 'secondary'"
                        >
                            {{ props.department.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </div>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="edit(props.department.id).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
            </div>

            <!-- Current Officers -->
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Current Officers</h2>
                    <Button size="sm" @click="openAddDialog">
                        <Plus class="mr-1.5 h-4 w-4" />
                        Add Officer
                    </Button>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>Since</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty v-if="currentOfficers.length === 0" :colspan="4">
                                No current officers assigned.
                            </TableEmpty>
                            <TableRow v-for="officer in currentOfficers" :key="officer.id">
                                <TableCell class="font-medium">
                                    <div class="flex items-center gap-2">
                                        <UserCheck class="h-4 w-4 text-primary" />
                                        {{ officer.person.full_name }}
                                    </div>
                                </TableCell>
                                <TableCell>{{ officer.role }}</TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ formatDate(officer.started_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="openEditDialog(officer)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="openDeleteDialog(officer)"
                                        >
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <!-- Past Officers -->
            <div v-if="pastOfficers.length > 0">
                <h2 class="mb-3 text-lg font-semibold text-muted-foreground">Past Officers</h2>
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead>From</TableHead>
                                <TableHead>Until</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="officer in pastOfficers" :key="officer.id">
                                <TableCell class="font-medium text-muted-foreground">
                                    {{ officer.person.full_name }}
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{ officer.role }}</TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ formatDate(officer.started_at) }}
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ officer.ended_at ? formatDate(officer.ended_at) : '—' }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="openEditDialog(officer)"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="openDeleteDialog(officer)"
                                        >
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <!-- Members -->
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">
                        Members
                        <span class="ml-1 text-sm font-normal text-muted-foreground">
                            ({{ props.department.members.length }})
                        </span>
                    </h2>
                    <Button
                        v-if="props.department.members.length > 0"
                        size="sm"
                        variant="outline"
                        @click="membersDialogOpen = true"
                    >
                        <Users class="mr-1.5 h-4 w-4" />
                        View All
                    </Button>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead class="text-center">Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty v-if="props.department.members.length === 0" :colspan="2">
                                No members assigned to this department yet.
                            </TableEmpty>
                            <TableRow
                                v-for="member in props.department.members.slice(0, 5)"
                                :key="member.id"
                            >
                                <TableCell class="font-medium">{{ member.full_name }}</TableCell>
                                <TableCell class="text-center">
                                    <Badge :variant="member.is_active ? 'default' : 'secondary'">
                                        {{ member.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.department.members.length > 5" class="hover:bg-transparent">
                                <TableCell :colspan="2" class="text-center text-sm text-muted-foreground">
                                    and {{ props.department.members.length - 5 }} more…
                                    <button class="ml-1 underline" @click="membersDialogOpen = true">View all</button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

        </div>
    </AppLayout>

    <!-- Add / Edit Officer Dialog -->
    <Dialog :open="officerDialogOpen" @update:open="closeOfficerDialog">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>
                    {{ editingOfficer ? 'Edit Officer' : 'Add Officer' }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        editingOfficer
                            ? 'Update the officer\'s role or tenure dates.'
                            : 'Assign a person as an officer of this department.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitOfficer" class="space-y-4">
                <div class="space-y-1.5">
                    <Label>Person</Label>
                    <Multiselect
                        v-model="form.person_id"
                        :options="participantOptions"
                        label="name"
                        value-prop="id"
                        placeholder="Search by name..."
                        :searchable="true"
                        :can-clear="false"
                    />
                    <InputError :message="form.errors.person_id" />
                </div>

                <div class="space-y-1.5">
                    <Label for="role">Role</Label>
                    <Input
                        id="role"
                        v-model="form.role"
                        placeholder="e.g. President, Secretary"
                        required
                    />
                    <InputError :message="form.errors.role" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <Label for="started_at">Started</Label>
                        <Input
                            id="started_at"
                            v-model="form.started_at"
                            type="date"
                            required
                        />
                        <InputError :message="form.errors.started_at" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="ended_at">
                            Ended
                            <span class="text-muted-foreground text-xs font-normal">(leave blank if current)</span>
                        </Label>
                        <Input
                            id="ended_at"
                            v-model="form.ended_at"
                            type="date"
                        />
                        <InputError :message="form.errors.ended_at" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeOfficerDialog">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ editingOfficer ? 'Update' : 'Add Officer' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
        <DialogContent class="max-w-sm">
            <DialogHeader>
                <DialogTitle>Remove Officer</DialogTitle>
                <DialogDescription>
                    Remove <strong>{{ deletingOfficer?.person.full_name }}</strong>
                    ({{ deletingOfficer?.role }}) from this department?
                    This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="deleteDialogOpen = false">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Remove</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Members Dialog -->
    <Dialog :open="membersDialogOpen" @update:open="membersDialogOpen = $event">
        <DialogContent class="max-h-[80vh] max-w-lg overflow-y-auto">
            <DialogHeader>
                <DialogTitle>
                    {{ props.department.name }} — Members ({{ props.department.members.length }})
                </DialogTitle>
                <DialogDescription>
                    All people assigned to this department.
                </DialogDescription>
            </DialogHeader>
            <div class="rounded-lg border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>#</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="(member, idx) in props.department.members"
                            :key="member.id"
                        >
                            <TableCell class="text-muted-foreground">{{ idx + 1 }}</TableCell>
                            <TableCell class="font-medium">{{ member.full_name }}</TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="member.is_active ? 'default' : 'secondary'">
                                    {{ member.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </DialogContent>
    </Dialog>
</template>
