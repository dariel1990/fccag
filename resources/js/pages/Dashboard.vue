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
import { show as showSetlist } from '@/actions/App/Http/Controllers/Music/SetlistController';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
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
    musicStats?: {
        total_songs: number | null;
        total_setlists: number | null;
        upcoming_setlists: number | null;
    } | null;
    recentSetlists?: Array<{
        id: number;
        title: string;
        service_date: string;
        status: string;
        songs_count: number;
        theme: string | null;
    }> | null;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: dashboard().url }];

const pieChartData = computed(() => ({
    labels: props.spiritualLevelDistribution?.labels || [],
    datasets: [{
        data: props.spiritualLevelDistribution?.data || [],
        backgroundColor: props.spiritualLevelDistribution?.colors || [],
        borderWidth: 0,
    }],
}));

const pieChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
};

const lineChartData = computed(() => ({
    labels: props.attendanceTrend?.labels || [],
    datasets: [{
        label: 'Attendance Rate (%)',
        data: props.attendanceTrend?.data || [],
        borderColor: '#1e3a6e',
        backgroundColor: 'rgba(30, 58, 110, 0.1)',
        tension: 0.4,
        fill: true,
    }],
}));

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            max: 100,
            ticks: { callback: (value: number) => `${value}%` },
        },
    },
    plugins: { legend: { display: false } },
};

const barChartData = computed(() => ({
    labels: props.activityTypeStats?.map((s) => s.name) || [],
    datasets: [{
        label: 'Activities',
        data: props.activityTypeStats?.map((s) => s.activity_count) || [],
        backgroundColor: '#1e3a6e',
    }],
}));

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
};

const siMemberStatusChartData = computed(() => ({
    labels: props.siMemberStatusBreakdown?.labels || [],
    datasets: [{
        data: props.siMemberStatusBreakdown?.data || [],
        backgroundColor: props.siMemberStatusBreakdown?.colors || [],
        borderWidth: 0,
    }],
}));

const hasAnySiPermission = computed(
    () => can('si_members', 'read') || can('si_activities', 'read'),
);

const hasAnyMusicPermission = computed(
    () => can('songs', 'read') || can('setlists', 'read'),
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:gap-6 md:p-6">

            <!-- Empty state -->
            <div
                v-if="!can('participants', 'read') && !can('activities', 'read') && !can('activity_types', 'read') && !hasAnySiPermission && !hasAnyMusicPermission"
                class="flex flex-1 flex-col items-center justify-center gap-3 rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
            >
                <p class="text-lg font-semibold">No data available</p>
                <p class="text-sm text-muted-foreground">
                    You don't have permission to view any dashboard data.
                    Contact your superadmin to request access.
                </p>
            </div>

            <!-- Stats Cards -->
            <div
                v-if="can('participants', 'read') || can('activities', 'read')"
                class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4"
            >
                <div
                    v-if="can('participants', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">Total People of God</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ stats?.total_participants ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="can('activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">
                        Activities This Quarter
                        <span class="block text-xs opacity-70">{{ currentQuarter }}</span>
                    </p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ stats?.total_activities_this_quarter ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="can('activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">Activities This Month</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ stats?.activities_recorded_this_month ?? '—' }}
                    </p>
                </div>
            </div>

            <!-- Charts Row -->
            <div
                v-if="can('participants', 'read') || can('activities', 'read')"
                class="grid gap-4 md:grid-cols-2"
            >
                <div
                    v-if="can('participants', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
                >
                    <h3 class="mb-3 text-base font-semibold md:mb-4 md:text-lg">Spiritual Level Distribution</h3>
                    <div class="h-52 md:h-64">
                        <Pie :data="pieChartData" :options="pieChartOptions" />
                    </div>
                </div>

                <div
                    v-if="can('activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
                >
                    <h3 class="mb-3 text-base font-semibold md:mb-4 md:text-lg">Attendance Trend (Last 6 Months)</h3>
                    <div class="h-52 md:h-64">
                        <Line :data="lineChartData" :options="lineChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Activity Type Stats -->
            <div
                v-if="can('activity_types', 'read')"
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
            >
                <h3 class="mb-3 text-base font-semibold md:mb-4 md:text-lg">Activity Types This Quarter</h3>
                <div class="h-52 md:h-64">
                    <Bar :data="barChartData" :options="barChartOptions" />
                </div>
            </div>

            <!-- Recent Activities -->
            <div
                v-if="can('activities', 'read')"
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div class="p-4 pb-0 md:p-6 md:pb-0">
                    <h3 class="text-base font-semibold md:text-lg">Recent Activities</h3>
                    <p class="text-sm text-muted-foreground">Latest activity sessions with attendance</p>
                </div>

                <!-- Mobile: card list -->
                <div class="divide-y p-2 sm:hidden">
                    <div
                        v-for="activity in recentActivities"
                        :key="activity.id"
                        class="flex flex-col gap-1 px-2 py-3"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <Link
                                :href="showActivity(activity.id).url"
                                class="flex-1 truncate font-medium hover:underline"
                            >
                                {{ activity.title }}
                            </Link>
                            <span class="shrink-0 text-xs text-muted-foreground">
                                {{ activity.activity_date }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-muted-foreground">
                            <span>{{ activity.activity_type }}</span>
                            <span v-if="activity.attendance_count > 0">
                                {{ activity.present_count }}/{{ activity.attendance_count }} present
                            </span>
                            <span v-else>Not recorded</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden overflow-x-auto p-2 sm:block">
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
                            <TableRow v-for="activity in recentActivities" :key="activity.id">
                                <TableCell>{{ activity.activity_date }}</TableCell>
                                <TableCell class="font-medium">
                                    <Link :href="showActivity(activity.id).url" class="hover:underline">
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
                <span class="text-xs font-semibold tracking-wider text-muted-foreground uppercase md:text-sm">SI Program</span>
                <div class="h-px flex-1 bg-border" />
            </div>

            <!-- SI Stats Cards -->
            <div
                v-if="hasAnySiPermission"
                class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4"
            >
                <div
                    v-if="can('si_members', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">Active SI Members</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ siStats?.total_active_members ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="can('si_activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">SI Activities This Month</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ siStats?.total_activities_this_month ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="can('si_activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">SI Attendance Rate</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ siStats?.attendance_rate_this_month ?? '—' }}
                        <span v-if="siStats?.attendance_rate_this_month != null" class="text-lg">%</span>
                    </p>
                </div>
            </div>

            <!-- SI Charts + Recent Activities -->
            <div v-if="hasAnySiPermission" class="grid gap-4 md:grid-cols-2">
                <div
                    v-if="can('si_members', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border md:p-6"
                >
                    <h3 class="mb-3 text-base font-semibold md:mb-4 md:text-lg">SI Member Status Breakdown</h3>
                    <div class="h-52 md:h-64">
                        <Pie :data="siMemberStatusChartData" :options="pieChartOptions" />
                    </div>
                </div>

                <div
                    v-if="can('si_activities', 'read')"
                    class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div class="p-4 pb-0 md:p-6 md:pb-0">
                        <h3 class="text-base font-semibold md:text-lg">Recent SI Activities</h3>
                        <p class="text-sm text-muted-foreground">Latest SI sessions with attendance</p>
                    </div>

                    <!-- Mobile: card list -->
                    <div class="divide-y p-2 sm:hidden">
                        <div
                            v-for="activity in siRecentActivities"
                            :key="activity.id"
                            class="flex flex-col gap-1 px-2 py-3"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <Link
                                    :href="showSiActivity(activity.id).url"
                                    class="flex-1 truncate font-medium hover:underline"
                                >
                                    {{ activity.title }}
                                </Link>
                                <span class="shrink-0 text-xs text-muted-foreground">
                                    {{ activity.conducted_at }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-muted-foreground">
                                <span>{{ activity.category }}</span>
                                <span v-if="activity.total_count > 0">
                                    {{ activity.present_count }}/{{ activity.total_count }} present
                                </span>
                                <span v-else>Not recorded</span>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop: table -->
                    <div class="hidden overflow-x-auto p-2 sm:block">
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
                                <TableRow v-for="activity in siRecentActivities" :key="activity.id">
                                    <TableCell>{{ activity.conducted_at }}</TableCell>
                                    <TableCell class="font-medium">
                                        <Link :href="showSiActivity(activity.id).url" class="hover:underline">
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

            <!-- Music Section Divider -->
            <div v-if="hasAnyMusicPermission" class="flex items-center gap-3">
                <div class="h-px flex-1 bg-border" />
                <span class="text-xs font-semibold tracking-wider text-muted-foreground uppercase md:text-sm">Music</span>
                <div class="h-px flex-1 bg-border" />
            </div>

            <!-- Music Stats Cards -->
            <div
                v-if="hasAnyMusicPermission"
                class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4"
            >
                <div
                    v-if="can('songs', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">Active Songs</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ musicStats?.total_songs ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="can('setlists', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">Total Setlists</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ musicStats?.total_setlists ?? '—' }}
                    </p>
                </div>
                <div
                    v-if="can('setlists', 'read')"
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <p class="text-xs text-muted-foreground md:text-sm">Upcoming Services</p>
                    <p class="mt-1 text-2xl font-bold md:mt-2 md:text-3xl">
                        {{ musicStats?.upcoming_setlists ?? '—' }}
                    </p>
                </div>
            </div>

            <!-- Recent Setlists -->
            <div
                v-if="can('setlists', 'read')"
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div class="p-4 pb-0 md:p-6 md:pb-0">
                    <h3 class="text-base font-semibold md:text-lg">Recent Setlists</h3>
                    <p class="text-sm text-muted-foreground">Latest worship service setlists</p>
                </div>

                <!-- Mobile: card list -->
                <div class="divide-y p-2 sm:hidden">
                    <div
                        v-for="setlist in recentSetlists"
                        :key="setlist.id"
                        class="flex flex-col gap-1 px-2 py-3"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <Link
                                :href="showSetlist(setlist.id).url"
                                class="flex-1 truncate font-medium hover:underline"
                            >
                                {{ setlist.title }}
                            </Link>
                            <span class="shrink-0 text-xs text-muted-foreground">
                                {{ setlist.service_date }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-muted-foreground">
                            <span>{{ setlist.songs_count }} song{{ setlist.songs_count !== 1 ? 's' : '' }}</span>
                            <span
                                class="capitalize rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': setlist.status === 'published',
                                    'bg-muted text-muted-foreground': setlist.status === 'draft',
                                    'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': setlist.status === 'archived',
                                }"
                            >{{ setlist.status }}</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden overflow-x-auto p-2 sm:block">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Title</TableHead>
                                <TableHead>Theme</TableHead>
                                <TableHead class="text-center">Songs</TableHead>
                                <TableHead class="text-center">Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="setlist in recentSetlists" :key="setlist.id">
                                <TableCell>{{ setlist.service_date }}</TableCell>
                                <TableCell class="font-medium">
                                    <Link :href="showSetlist(setlist.id).url" class="hover:underline">
                                        {{ setlist.title }}
                                    </Link>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{ setlist.theme ?? '—' }}</TableCell>
                                <TableCell class="text-center">{{ setlist.songs_count }}</TableCell>
                                <TableCell class="text-center">
                                    <span
                                        class="capitalize rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': setlist.status === 'published',
                                            'bg-muted text-muted-foreground': setlist.status === 'draft',
                                            'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': setlist.status === 'archived',
                                        }"
                                    >{{ setlist.status }}</span>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
