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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

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

type Props = {
    stats?: {
        total_participants: number;
        total_activities_this_quarter: number;
        activities_recorded_this_month: number;
    };
    spiritualLevelDistribution?: {
        labels: string[];
        data: number[];
        colors: string[];
    };
    attendanceTrend?: {
        labels: string[];
        data: number[];
    };
    recentActivities?: Activity[];
    activityTypeStats?: Array<{
        name: string;
        activity_count: number;
        average_attendance: number;
    }>;
    currentQuarter?: string;
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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        Total People of God
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ stats?.total_participants }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        Activities This Quarter ({{ currentQuarter }})
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ stats?.total_activities_this_quarter }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        Activities This Month
                    </p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ stats?.activities_recorded_this_month }}
                    </p>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-4 md:grid-cols-2">
                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-4 text-lg font-semibold">
                        Spiritual Level Distribution
                    </h3>
                    <div class="h-64">
                        <Pie
                            :data="pieChartData"
                            :options="pieChartOptions"
                        />
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <h3 class="mb-4 text-lg font-semibold">
                        Attendance Trend (Last 6 Months)
                    </h3>
                    <div class="h-64">
                        <Line
                            :data="lineChartData"
                            :options="lineChartOptions"
                        />
                    </div>
                </div>
            </div>

            <!-- Activity Type Stats -->
            <div
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h3 class="mb-4 text-lg font-semibold">
                    Activity Types This Quarter
                </h3>
                <div class="h-64">
                    <Bar :data="barChartData" :options="barChartOptions" />
                </div>
            </div>

            <!-- Recent Activities -->
            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Recent Activities</h3>
                    <p class="text-sm text-muted-foreground">
                        Latest activity sessions with attendance
                    </p>
                </div>
                <div class="p-2">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Activity</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead class="text-center"
                                    >Attendance</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="activity in recentActivities"
                                :key="activity.id"
                            >
                                <TableCell>{{
                                    activity.activity_date
                                }}</TableCell>
                                <TableCell class="font-medium">
                                    <Link
                                        :href="
                                            showActivity(activity.id).url
                                        "
                                        class="hover:underline"
                                    >
                                        {{ activity.title }}
                                    </Link>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{
                                    activity.activity_type
                                }}</TableCell>
                                <TableCell class="text-center">
                                    <span
                                        v-if="activity.attendance_count > 0"
                                        class="text-sm"
                                    >
                                        {{ activity.present_count }}/{{
                                            activity.attendance_count
                                        }}
                                        present
                                    </span>
                                    <span
                                        v-else
                                        class="text-sm text-muted-foreground"
                                        >Not recorded</span
                                    >
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
