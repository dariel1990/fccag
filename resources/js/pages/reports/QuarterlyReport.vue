<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { quarterlyReport } from '@/actions/App/Http/Controllers/ReportController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableEmpty,
} from '@/components/ui/table';
import { useIsMobile, useApiBaseUrl } from '@/composables/useDataSource';
import AppLayout from '@/layouts/AppLayout.vue';
import MobileLayout from '@/layouts/mobile/MobileLayout.vue';
import { type BreadcrumbItem } from '@/types';

type ReportEntry = {
    participant_id: number;
    full_name: string;
    cell_group: string | null;
    ministry: string | null;
    total_activities: number;
    attended: number;
    percentage: number;
    spiritual_level: string;
    spiritual_level_color: string;
};

type Summary = {
    total_participants: number;
    spiritually_mature: number;
    growing: number;
    developing: number;
    needs_guidance: number;
    average_attendance: number;
};

type Props = {
    report?: ReportEntry[];
    summary?: Summary;
    filters?: {
        year: number;
        quarter: number;
    };
    availableYears?: number[];
};

const props = defineProps<Props>();

const isMobile = useIsMobile();
const apiBaseUrl = useApiBaseUrl();

const report = ref<ReportEntry[]>(props.report || []);
const summary = ref<Summary>(
    props.summary || {
        total_participants: 0,
        spiritually_mature: 0,
        growing: 0,
        developing: 0,
        needs_guidance: 0,
        average_attendance: 0,
    },
);
const filters = ref(
    props.filters || {
        year: new Date().getFullYear(),
        quarter: Math.ceil((new Date().getMonth() + 1) / 3),
    },
);
const availableYears = ref<number[]>(props.availableYears || []);
const isLoading = ref(false);

async function fetchReport(year: number, quarter: number) {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    isLoading.value = true;
    try {
        const response = await fetch(
            `${apiBaseUrl}/api/reports/quarterly?year=${year}&quarter=${quarter}`,
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            },
        );

        if (response.ok) {
            const data = await response.json();
            report.value = data.report;
            summary.value = data.summary;
            filters.value = data.filters;
            availableYears.value = data.available_years;
        }
    } catch {
        // Silently fail
    } finally {
        isLoading.value = false;
    }
}

onMounted(async () => {
    if (isMobile) {
        await fetchReport(filters.value.year, filters.value.quarter);
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Quarterly Report',
        href: quarterlyReport().url,
    },
];

const quarterLabels: Record<number, string> = {
    1: 'Q1 (Jan - Mar)',
    2: 'Q2 (Apr - Jun)',
    3: 'Q3 (Jul - Sep)',
    4: 'Q4 (Oct - Dec)',
};

function applyFilters(year: number, quarter: number) {
    if (isMobile) {
        fetchReport(year, quarter);
    } else {
        router.get(
            quarterlyReport().url,
            { year, quarter },
            { preserveState: true },
        );
    }
}

function badgeVariant(
    color: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (color) {
        case 'green':
            return 'default';
        case 'blue':
            return 'outline';
        case 'yellow':
            return 'secondary';
        case 'red':
            return 'destructive';
        default:
            return 'secondary';
    }
}

const Layout = isMobile ? MobileLayout : AppLayout;
</script>

<template>
    <Head
        :title="`Quarterly Report - ${quarterLabels[filters.quarter]} ${filters.year}`"
    />

    <component :is="Layout" :breadcrumbs="isMobile ? undefined : breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6"
            :class="isMobile ? 'pb-4' : 'p-4'"
        >
            <div class="flex items-center justify-between">
                <Heading
                    title="Quarterly Report"
                    description="Spiritual level assessment based on attendance"
                />
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium">Year:</label>
                    <select
                        :value="filters.year"
                        @change="
                            applyFilters(
                                Number(
                                    ($event.target as HTMLSelectElement).value,
                                ),
                                filters.quarter,
                            )
                        "
                        class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <option
                            v-for="year in availableYears"
                            :key="year"
                            :value="year"
                        >
                            {{ year }}
                        </option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium">Quarter:</label>
                    <select
                        :value="filters.quarter"
                        @change="
                            applyFilters(
                                filters.year,
                                Number(
                                    ($event.target as HTMLSelectElement).value,
                                ),
                            )
                        "
                        class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <option
                            v-for="(label, q) in quarterLabels"
                            :key="q"
                            :value="q"
                        >
                            {{ label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Loading state -->
            <div v-if="isLoading" class="flex items-center justify-center py-8">
                <div class="text-muted-foreground">Loading report...</div>
            </div>

            <template v-else>
                <!-- Summary Cards -->
                <div
                    class="grid gap-4"
                    :class="
                        isMobile
                            ? 'grid-cols-2'
                            : 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6'
                    "
                >
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <p class="text-sm text-muted-foreground">
                            Total People of God
                        </p>
                        <p class="text-2xl font-bold">
                            {{ summary.total_participants }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <p class="text-sm text-muted-foreground">
                            Avg. Attendance
                        </p>
                        <p class="text-2xl font-bold">
                            {{ summary.average_attendance }}%
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <p class="text-sm text-muted-foreground">
                            Spiritually Mature
                        </p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ summary.spiritually_mature }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <p class="text-sm text-muted-foreground">Growing</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ summary.growing }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <p class="text-sm text-muted-foreground">Developing</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ summary.developing }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <p class="text-sm text-muted-foreground">
                            Needs Guidance
                        </p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ summary.needs_guidance }}
                        </p>
                    </div>
                </div>

                <!-- Report Table -->
                <div
                    class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead v-if="!isMobile">
                                    Cell Group
                                </TableHead>
                                <TableHead v-if="!isMobile">
                                    Ministry
                                </TableHead>
                                <TableHead class="text-center">
                                    Attended
                                </TableHead>
                                <TableHead class="text-center">%</TableHead>
                                <TableHead class="text-center">
                                    Level
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty
                                v-if="report.length === 0"
                                :colspan="isMobile ? 4 : 7"
                            >
                                No data available for this quarter.
                            </TableEmpty>
                            <TableRow
                                v-for="entry in report"
                                :key="entry.participant_id"
                            >
                                <TableCell class="font-medium">
                                    <div>{{ entry.full_name }}</div>
                                    <div
                                        v-if="isMobile"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ entry.cell_group || '—' }}
                                    </div>
                                </TableCell>
                                <TableCell
                                    v-if="!isMobile"
                                    class="text-muted-foreground"
                                >
                                    {{ entry.cell_group || '—' }}
                                </TableCell>
                                <TableCell
                                    v-if="!isMobile"
                                    class="text-muted-foreground"
                                >
                                    {{ entry.ministry || '—' }}
                                </TableCell>
                                <TableCell class="text-center">
                                    {{ entry.attended }}/{{
                                        entry.total_activities
                                    }}
                                </TableCell>
                                <TableCell class="text-center">
                                    {{ entry.percentage }}%
                                </TableCell>
                                <TableCell class="text-center">
                                    <Badge
                                        :variant="
                                            badgeVariant(
                                                entry.spiritual_level_color,
                                            )
                                        "
                                    >
                                        {{ entry.spiritual_level }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </template>
        </div>
    </component>
</template>
