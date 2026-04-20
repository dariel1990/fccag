<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import {
    index,
    destroy,
} from '@/actions/App/Http/Controllers/Si/SiActivityController';
import Heading from '@/components/Heading.vue';
import SiActivityFormDialog from '@/components/si/SiActivityFormDialog.vue';
import SiAttendanceSheet from '@/components/si/SiAttendanceSheet.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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

type Category = { id: number; name: string };
type Member = { id: number; name: string };
type ChurchActivity = { id: number; title: string; activity_date: string };
type AttendanceStatus = { value: string; label: string; color: string };

type MemberAttendance = {
    id: number;
    name: string;
    ph_id: string | null;
    attendance: { status: string; remarks: string | null } | null;
};

type SiActivity = {
    id: number;
    si_activity_category_id: number;
    activity_id: number | null;
    title: string;
    category: string | null;
    speaker: string | null;
    topic: string | null;
    memory_verse: string | null;
    conducted_at: string;
    assigned_members_count: number;
    member_ids: number[];
    members: MemberAttendance[];
};

const props = defineProps<{
    activities: SiActivity[];
    categories: Category[];
    members: Member[];
    churchActivities: ChurchActivity[];
    attendanceStatuses: AttendanceStatus[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'SI Activities', href: index().url },
];

const selectedCategoryId = ref<number | null>(null);

const filteredActivities = computed(() =>
    selectedCategoryId.value === null
        ? props.activities
        : props.activities.filter(
              (a) => a.si_activity_category_id === selectedCategoryId.value,
          ),
);

const formDialogOpen = ref(false);
const formActivity = ref<SiActivity | null>(null);

function openCreateDialog() {
    formActivity.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(activity: SiActivity) {
    formActivity.value = activity;
    formDialogOpen.value = true;
}

function deleteActivity(activity: SiActivity) {
    if (confirm(`Delete "${activity.title}"?`)) {
        router.delete(destroy(activity.id).url);
    }
}

const attendanceSheetOpen = ref(false);
const attendanceActivity = ref<SiActivity | null>(null);

function openAttendanceSheet(activity: SiActivity) {
    attendanceActivity.value = activity;
    attendanceSheetOpen.value = true;
}

const page = usePage<{ flash: { openActivityId: number | null } }>();

onMounted(() => {
    const id = page.props.flash?.openActivityId;
    if (id) {
        const activity = props.activities.find((a) => a.id === id);
        if (activity) {
            openAttendanceSheet(activity);
        }
    }
});
</script>

<template>
    <Head title="SI Activities" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="SI Activities"
                    description="Manage SI program activities and attendance"
                />
                <Button @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Activity
                </Button>
            </div>

            <!-- Category filter pills -->
            <div class="flex flex-wrap gap-2">
                <button
                    class="rounded-full border px-3 py-1 text-sm transition-colors"
                    :class="
                        selectedCategoryId === null
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-sidebar-border/70 text-muted-foreground hover:border-primary hover:text-foreground dark:border-sidebar-border'
                    "
                    @click="selectedCategoryId = null"
                >
                    All
                    <span class="ml-1 text-xs opacity-70"
                        >({{ activities.length }})</span
                    >
                </button>
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    class="rounded-full border px-3 py-1 text-sm transition-colors"
                    :class="
                        selectedCategoryId === cat.id
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-sidebar-border/70 text-muted-foreground hover:border-primary hover:text-foreground dark:border-sidebar-border'
                    "
                    @click="selectedCategoryId = cat.id"
                >
                    {{ cat.name }}
                    <span class="ml-1 text-xs opacity-70">
                        ({{
                            activities.filter(
                                (a) => a.si_activity_category_id === cat.id,
                            ).length
                        }})
                    </span>
                </button>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Speaker</TableHead>
                            <TableHead>Topic</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead class="text-center">Members</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="filteredActivities.length === 0"
                            :colspan="7"
                        >
                            No activities found.
                        </TableEmpty>
                        <TableRow
                            v-for="activity in filteredActivities"
                            :key="activity.id"
                        >
                            <TableCell class="font-medium">{{
                                activity.title
                            }}</TableCell>
                            <TableCell>
                                <Badge
                                    v-if="activity.category"
                                    variant="outline"
                                    >{{ activity.category }}</Badge
                                >
                                <span v-else class="text-muted-foreground"
                                    >—</span
                                >
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{
                                activity.speaker || '—'
                            }}</TableCell>
                            <TableCell class="text-muted-foreground">{{
                                activity.topic || '—'
                            }}</TableCell>
                            <TableCell class="text-muted-foreground">{{
                                activity.conducted_at
                            }}</TableCell>
                            <TableCell class="text-center">{{
                                activity.assigned_members_count
                            }}</TableCell>
                            <TableCell class="text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openAttendanceSheet(activity)"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(activity)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteActivity(activity)"
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
        </div>

        <SiActivityFormDialog
            v-model:open="formDialogOpen"
            :activity="formActivity"
            :categories="props.categories"
            :members="props.members"
            :church-activities="props.churchActivities"
            @saved="router.reload()"
        />

        <SiAttendanceSheet
            v-model:open="attendanceSheetOpen"
            :activity="attendanceActivity"
            :attendance-statuses="props.attendanceStatuses"
            @saved="router.reload()"
        />
    </AppLayout>
</template>
