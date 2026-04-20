<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import {
    Chart as ChartJS,
    ArcElement,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { computed } from 'vue';
import { Pie, Bar, Line } from 'vue-chartjs';
import { show as showActivity } from '@/actions/App/Http/Controllers/ActivityController';
import { show as showSiActivity } from '@/actions/App/Http/Controllers/Si/SiActivityController';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePermissions } from '@/composables/usePermissions';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const { can } = usePermissions();

ChartJS.register(
    ArcElement,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    Title,
    Tooltip,
    Legend,
);

type Activity = {
    id: number;
    title: string;
    activity_type: string;
    activity_date: string;
    attendance_count: number;
    present_count: number;
};

type SiActivity = {
    id: number;
    title: string;
    category: string;
    conducted_at: string;
    present_count: number;
    total_count: number;
};

type ChartData = { labels: string[]; data: number[]; colors?: string[] };

type Props = {
    stats?: {
        total_participants: number | null;
        total_activities_this_quarter: number | null;
        activities_recorded_this_month: number | null;
    };
    spiritualLevelDistribution?: ChartData | null;
    attendanceTrend?: { labels: string[]; data: number[] } | null;
    recentActivities?: Activity[] | null;
    activityTypeStats?: Array<{
        name: string;
        activity_count: number;
        average_attendance: number;
    }> | null;
    currentQuarter?: string;
    siStats?: {
        total_active_members: number | null;
        total_activities_this_month: number | null;
        attendance_rate_this_month: number | null;
    } | null;
    siRecentActivities?: SiActivity[] | null;
    siMemberStatusBreakdown?: ChartData | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const pieChartData = computed(() => ({
    labels: props.spiritualLevelDistribution?.labels || [],
    datasets: [
        {
            data: props.spiritualLevelDistribution?.data || [],
            backgroundColor: props.spiritualLevelDistribution?.colors || [],
            borderWidth: 0,
        },
    ],
}));

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom' as const,
        },
    },
};

const lineChartData = computed(() => ({
    labels: props.attendanceTrend?.labels || [],
    datasets: [
        {
            label: 'Attendance Rate (%)',
            data: props.attendanceTrend?.data || [],
            borderColor: '#1e3a6e',
            backgroundColor: 'rgba(30, 58, 110, 0.1)',
            tension: 0.4,
            fill: true,
        },
    ],
}));

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            max: 100,
            ticks: {
                callback: (value: number) => `${value}%`,
            },
        },
    },
    plugins: {
        legend: {
            display: false,
        },
    },
};

const barChartData = computed(() => ({
    labels: props.activityTypeStats?.map((s) => s.name) || [],
    datasets: [
        {
            label: 'Activities',
            data: props.activityTypeStats?.map((s) => s.activity_count) || [],
            backgroundColor: '#1e3a6e',
        },
    ],
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
};

const siMemberStatusChartData = computed(() => ({
    labels: props.siMemberStatusBreakdown?.labels || [],
    datasets: [
        {
            data: props.siMemberStatusBreakdown?.data || [],
            backgroundColor: props.siMemberStatusBreakdown?.colors || [],
            borderWidth: 0,
        },
    ],
}));

const hasAnySiPermission = computed(
    () => can('si_members', 'read') || can('si_activities', 'read'),
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">

            <!-- Empty state -->
            <div
                v-if="!can('participants', 'read') && !can('activities', 'read') && !can('activity_types', 'read') && !hasAnySiPermission"
                class="flex flex-1 flex-col items-center justify-center gap-3 rounded-xl border border-sidebar-border/70 p-12 text-center dark:border-sidebar-border"
            >
                <p class="text-lg font-semibold">No data available</p>
                <p class="text-sm text-muted-foreground">You don't have permission to view any dashboard data. Contact your superadmin to request access.</p>
            </div>

            <!-- Stats Cards -->
            <div
                v-if="can('participants', 'read') || can('activities', 'read')"
                class="grid auto-rows-min gap-4 md:grid-cols-3"
            >
                <div
                    v-if="can('participants', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">Total People of God</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats?.total_participants }}</p>
                </div>
                <div
                    v-if="can('activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        Activities This Quarter ({{ currentQuarter }})
                    </p>
                    <p class="mt-2 text-3xl font-bold">{{ stats?.total_activities_this_quarter }}</p>
                </div>
                <div
                    v-if="can('activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">Activities This Month</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats?.activities_recorded_this_month }}</p>
                </div>
            </div>

            <!-- Charts Row -->
            <div
                v-if="can('participants', 'read') || can('activities', 'read')"
                class="grid gap-4 md:grid-cols-2"
            >
                <div
                    v-if="can('participants', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-4 text-lg font-semibold">Spiritual Level Distribution</h3>
                    <div class="h-64">
                        <Pie :data="pieChartData" :options="pieChartOptions" />
                    </div>
                </div>

                <div
                    v-if="can('activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-4 text-lg font-semibold">Attendance Trend (Last 6 Months)</h3>
                    <div class="h-64">
                        <Line :data="lineChartData" :options="lineChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Activity Type Stats -->
            <div
                v-if="can('activity_types', 'read')"
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h3 class="mb-4 text-lg font-semibold">Activity Types This Quarter</h3>
                <div class="h-64">
                    <Bar :data="barChartData" :options="barChartOptions" />
                </div>
            </div>

            <!-- Recent Activities -->
            <div
                v-if="can('activities', 'read')"
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Recent Activities</h3>
                    <p class="text-sm text-muted-foreground">Latest activity sessions with attendance</p>
                </div>
                <div class="p-2">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Activity</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead class="text-center">Attendance</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="activity in recentActivities"
                                :key="activity.id"
                            >
                                <TableCell>{{ activity.activity_date }}</TableCell>
                                <TableCell class="font-medium">
                                    <Link
                                        :href="showActivity(activity.id).url"
                                        class="hover:underline"
                                    >
                                        {{ activity.title }}
                                    </Link>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{ activity.activity_type }}</TableCell>
                                <TableCell class="text-center">
                                    <span v-if="activity.attendance_count > 0" class="text-sm">
                                        {{ activity.present_count }}/{{ activity.attendance_count }} present
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground">Not recorded</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
            <!-- SI Section Divider -->
            <div v-if="hasAnySiPermission" class="flex items-center gap-3">
                <div class="h-px flex-1 bg-border" />
                <span class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">SI Program</span>
                <div class="h-px flex-1 bg-border" />
            </div>

            <!-- SI Stats Cards -->
            <div v-if="hasAnySiPermission" class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    v-if="can('si_members', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">Active SI Members</p>
                    <p class="mt-2 text-3xl font-bold">{{ siStats?.total_active_members }}</p>
                </div>
                <div
                    v-if="can('si_activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">SI Activities This Month</p>
                    <p class="mt-2 text-3xl font-bold">{{ siStats?.total_activities_this_month }}</p>
                </div>
                <div
                    v-if="can('si_activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">SI Attendance Rate This Month</p>
                    <p class="mt-2 text-3xl font-bold">{{ siStats?.attendance_rate_this_month }}%</p>
                </div>
            </div>

            <!-- SI Charts Row -->
            <div v-if="hasAnySiPermission" class="grid gap-4 md:grid-cols-2">
                <div
                    v-if="can('si_members', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-4 text-lg font-semibold">SI Member Status Breakdown</h3>
                    <div class="h-64">
                        <Pie :data="siMemberStatusChartData" :options="pieChartOptions" />
                    </div>
                </div>

                <!-- SI Recent Activities -->
                <div
                    v-if="can('si_activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div class="p-6 pb-0">
                        <h3 class="text-lg font-semibold">Recent SI Activities</h3>
                        <p class="text-sm text-muted-foreground">Latest SI sessions with attendance</p>
                    </div>
                    <div class="p-2">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead class="text-center">Attendance</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="activity in siRecentActivities"
                                    :key="activity.id"
                                >
                                    <TableCell>{{ activity.conducted_at }}</TableCell>
                                    <TableCell class="font-medium">
                                        <Link
                                            :href="showSiActivity(activity.id).url"
                                            class="hover:underline"
                                        >
                                            {{ activity.title }}
                                        </Link>
                                    </TableCell>
                                    <TableCell class="text-muted-foreground">{{ activity.category }}</TableCell>
                                    <TableCell class="text-center">
                                        <span v-if="activity.total_count > 0" class="text-sm">
                                            {{ activity.present_count }}/{{ activity.total_count }} present
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">Not recorded</span>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
