<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, ClipboardCheck, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import {
    index,
    create,
    show,
    edit,
    destroy,
} from '@/actions/App/Http/Controllers/ActivityController';
import Heading from '@/components/Heading.vue';
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
import {
    useDataSource,
    useIsMobile,
    useApiBaseUrl,
} from '@/composables/useDataSource';
import AppLayout from '@/layouts/AppLayout.vue';
import MobileLayout from '@/layouts/mobile/MobileLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Activity = {
    id: number;
    title: string;
    activity_type: string;
    activity_date: string;
    attendances_count: number;
    present_count: number;
};

type Props = {
    activities?: Activity[];
    year?: number;
    month?: number;
};

const props = defineProps<Props>();

const isMobile = useIsMobile();
const apiBaseUrl = useApiBaseUrl();
const { delete: deleteRequest } = useDataSource();

const now = new Date();
const currentYear = ref(props.year ?? now.getFullYear());
const currentMonth = ref(props.month ?? (now.getMonth() + 1));

const activities = ref<Activity[]>(props.activities || []);
const isLoading = ref(false);

const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const monthLabel = computed(() => `${MONTH_NAMES[currentMonth.value - 1]} ${currentYear.value}`);

function prevMonth() {
    let m = currentMonth.value - 1;
    let y = currentYear.value;
    if (m < 1) { m = 12; y--; }
    navigate(y, m);
}

function nextMonth() {
    let m = currentMonth.value + 1;
    let y = currentYear.value;
    if (m > 12) { m = 1; y++; }
    navigate(y, m);
}

function navigate(year: number, month: number) {
    if (isMobile) {
        fetchActivities(year, month);
    } else {
        router.get(index().url, { year, month }, { preserveScroll: true });
    }
    currentYear.value = year;
    currentMonth.value = month;
}

async function fetchActivities(year: number, month: number) {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        window.location.href = '/mobile/login';
        return;
    }

    isLoading.value = true;
    try {
        const response = await fetch(
            `${apiBaseUrl}/api/activities?year=${year}&month=${month}`,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            },
        );

        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
            window.location.href = '/mobile/login';
            return;
        }

        if (response.ok) {
            const data = await response.json();
            activities.value = data.data.map(
                (a: { activity_type: { name: string } }) => ({
                    ...a,
                    activity_type: a.activity_type?.name || a.activity_type,
                }),
            );
        }
    } catch {
        // Silently fail
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    if (isMobile) {
        fetchActivities(currentYear.value, currentMonth.value);
    }
});

// Group activities by Mon–Sun week
function toLocalDateStr(d: Date): string {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function getWeekMonday(dateStr: string): string {
    const d = new Date(dateStr + 'T00:00:00');
    const day = d.getDay(); // 0=Sun, 1=Mon, …, 6=Sat
    const diff = day === 0 ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    return toLocalDateStr(d); // use local parts to avoid UTC shift
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
        const sunday = toLocalDateStr(sundayDate); // use local parts to avoid UTC shift

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

function deleteActivity(activity: Activity) {
    if (
        confirm(
            `Are you sure you want to delete "${activity.title}"? This will also delete all attendance records for this activity.`,
        )
    ) {
        if (isMobile) {
            deleteRequest(`/activities/${activity.id}`).then(() => {
                activities.value = activities.value.filter(
                    (a) => a.id !== activity.id,
                );
            });
        } else {
            router.delete(destroy(activity.id).url);
        }
    }
}

const Layout = isMobile ? MobileLayout : AppLayout;
</script>

<template>
    <Head title="Activities" />

    <component :is="Layout" :breadcrumbs="isMobile ? undefined : breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4"
            :class="isMobile ? 'pb-4' : 'p-4'"
        >
            <div class="flex items-center justify-between">
                <Heading
                    title="Activities"
                    description="Manage church activity sessions"
                />
                <Button as-child>
                    <Link :href="create().url">
                        <Plus class="mr-2 h-4 w-4" />
                        <span v-if="!isMobile">Add Activity</span>
                        <span v-else>Add</span>
                    </Link>
                </Button>
            </div>

            <!-- Month navigation -->
            <div class="flex items-center justify-between">
                <Button variant="outline" size="sm" @click="prevMonth">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <span class="text-sm font-semibold">{{ monthLabel }}</span>
                <Button variant="outline" size="sm" @click="nextMonth">
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>

            <!-- Loading state -->
            <div v-if="isLoading" class="flex items-center justify-center py-8">
                <div class="text-muted-foreground">Loading...</div>
            </div>

            <!-- Empty state -->
            <div
                v-else-if="weekGroups.length === 0"
                class="rounded-xl border border-sidebar-border/70 py-12 text-center text-sm text-muted-foreground dark:border-sidebar-border"
            >
                No activities for {{ monthLabel }}.
            </div>

            <!-- Weekly groups — single table so column widths are consistent -->
            <div
                v-else
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="!isMobile">Date</TableHead>
                            <TableHead>Title</TableHead>
                            <TableHead v-if="!isMobile">Type</TableHead>
                            <TableHead class="text-center">Attendance</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="group in weekGroups" :key="group.weekStart">
                            <!-- Week separator row -->
                            <TableRow class="hover:bg-transparent">
                                <TableCell
                                    :colspan="isMobile ? 3 : 5"
                                    class="bg-muted/40 py-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                >
                                    {{ group.label }}
                                </TableCell>
                            </TableRow>
                            <!-- Activity rows -->
                            <TableRow
                                v-for="activity in group.activities"
                                :key="activity.id"
                            >
                                <TableCell v-if="!isMobile" class="text-muted-foreground">
                                    {{ activity.activity_date }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    <div>{{ activity.title }}</div>
                                    <div
                                        v-if="isMobile"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ activity.activity_type }} &middot;
                                        {{ activity.activity_date }}
                                    </div>
                                </TableCell>
                                <TableCell v-if="!isMobile" class="text-muted-foreground">
                                    {{ activity.activity_type }}
                                </TableCell>
                                <TableCell class="text-center">
                                    <span v-if="activity.attendances_count > 0" class="text-sm">
                                        {{ activity.present_count }}/{{ activity.attendances_count }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="show(activity.id).url">
                                                <ClipboardCheck class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="edit(activity.id).url">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="deleteActivity(activity)"
                                        >
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>
        </div>
    </component>
</template>
