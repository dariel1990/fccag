<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Pencil,
    Trash2,
    ClipboardCheck,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
    destroy,
    index,
    show,
} from '@/actions/App/Http/Controllers/ActivityController';
import ActivityFormDialog from '@/components/activities/ActivityFormDialog.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Activity = {
    id: number;
    activity_type_id: number;
    title: string;
    description: string | null;
    activity_type: string;
    activity_date: string;
    attendances_count: number;
    present_count: number;
};

type ActivityType = { id: number; name: string };

type Props = {
    activities?: Activity[];
    activityTypes: ActivityType[];
    year?: number;
    month?: number;
};

const props = defineProps<Props>();

const now = new Date();
const currentYear = ref(props.year ?? now.getFullYear());
const currentMonth = ref(props.month ?? now.getMonth() + 1);

const activities = ref<Activity[]>(props.activities || []);

const MONTH_NAMES = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
];

const monthLabel = computed(
    () => `${MONTH_NAMES[currentMonth.value - 1]} ${currentYear.value}`,
);

function prevMonth() {
    let m = currentMonth.value - 1;
    let y = currentYear.value;
    if (m < 1) {
        m = 12;
        y--;
    }
    navigate(y, m);
}

function nextMonth() {
    let m = currentMonth.value + 1;
    let y = currentYear.value;
    if (m > 12) {
        m = 1;
        y++;
    }
    navigate(y, m);
}

function navigate(year: number, month: number) {
    router.get(index().url, { year, month }, { preserveScroll: true });
    currentYear.value = year;
    currentMonth.value = month;
}

function toLocalDateStr(d: Date): string {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function getWeekMonday(dateStr: string): string {
    const d = new Date(dateStr + 'T00:00:00');
    const day = d.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    return toLocalDateStr(d);
}

function formatShortDate(dateStr: string): string {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
}

type WeekGroup = {
    weekStart: string;
    weekEnd: string;
    label: string;
    activities: Activity[];
};

const weekGroups = computed((): WeekGroup[] => {
    const map = new Map<string, Activity[]>();

    for (const activity of activities.value) {
        const monday = getWeekMonday(activity.activity_date);
        if (!map.has(monday)) {
            map.set(monday, []);
        }
        map.get(monday)!.push(activity);
    }

    const sorted = [...map.entries()].sort(([a], [b]) => a.localeCompare(b));

    return sorted.map(([monday, acts], idx) => {
        const sundayDate = new Date(monday + 'T00:00:00');
        sundayDate.setDate(sundayDate.getDate() + 6);
        const sunday = toLocalDateStr(sundayDate);

        return {
            weekStart: monday,
            weekEnd: sunday,
            label: `Week ${idx + 1} · ${formatShortDate(monday)} – ${formatShortDate(sunday)}`,
            activities: acts,
        };
    });
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Activities', href: index().url },
];

const formDialogOpen = ref(false);
const formActivity = ref<Activity | null>(null);

const deleteDialogOpen = ref(false);
const deleteActivity = ref<Activity | null>(null);

function openCreateDialog() {
    formActivity.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(activity: Activity) {
    formActivity.value = activity;
    formDialogOpen.value = true;
}

function openDeleteDialog(activity: Activity) {
    deleteActivity.value = activity;
    deleteDialogOpen.value = true;
}

function handleFormSaved() {
    router.reload();
}

function handleDeleteConfirm() {
    if (!deleteActivity.value) return;

    router.delete(destroy(deleteActivity.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteActivity.value = null;
        },
    });
}
</script>

<template>
    <Head title="Activities" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Activities"
                    description="Manage church activity sessions"
                />
                <Button @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Activity
                </Button>
            </div>

            <div class="flex items-center justify-between">
                <Button variant="outline" size="sm" @click="prevMonth">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <span class="text-sm font-semibold">{{ monthLabel }}</span>
                <Button variant="outline" size="sm" @click="nextMonth">
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>

            <div
                v-if="weekGroups.length === 0"
                class="rounded-xl border border-sidebar-border/70 py-12 text-center text-sm text-muted-foreground dark:border-sidebar-border"
            >
                No activities for {{ monthLabel }}.
            </div>

            <div
                v-else
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Date</TableHead>
                            <TableHead>Title</TableHead>
                            <TableHead>Type</TableHead>
                            <TableHead class="text-center"
                                >Attendance</TableHead
                            >
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template
                            v-for="group in weekGroups"
                            :key="group.weekStart"
                        >
                            <TableRow class="hover:bg-transparent">
                                <TableCell
                                    colspan="5"
                                    class="bg-muted/40 py-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    {{ group.label }}
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-for="activity in group.activities"
                                :key="activity.id"
                            >
                                <TableCell class="text-muted-foreground">
                                    {{ activity.activity_date }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ activity.title }}
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ activity.activity_type }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <span
                                        v-if="activity.attendances_count > 0"
                                        class="text-sm"
                                    >
                                        {{ activity.present_count }}/{{
                                            activity.attendances_count
                                        }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                        >—</span
                                    >
                                </TableCell>
                                <TableCell class="text-right">
                                    <div
                                        class="flex items-center justify-end gap-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            as-child
                                        >
                                            <Link :href="show(activity.id).url">
                                                <ClipboardCheck
                                                    class="h-4 w-4"
                                                />
                                            </Link>
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
                                            @click="openDeleteDialog(activity)"
                                        >
                                            <Trash2
                                                class="h-4 w-4 text-destructive"
                                            />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <ActivityFormDialog
                v-model:open="formDialogOpen"
                :activity="formActivity"
                :activity-types="props.activityTypes"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteActivity?.title"
                :description="
                    deleteActivity
                        ? `Are you sure you want to delete &quot;${deleteActivity.title}&quot;? This will also delete all attendance records for this activity.`
                        : undefined
                "
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
