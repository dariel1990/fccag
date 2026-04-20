<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { index as membersIndex } from '@/actions/App/Http/Controllers/Si/SiMemberController';
import { index } from '@/actions/App/Http/Controllers/Si/SiReportController';
import Heading from '@/components/Heading.vue';
import SiMemberReportDialog from '@/components/si/SiMemberReportDialog.vue';
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

type Category = { id: number; name: string; weight: number };

type AssessmentOption = { value: string; label: string; color: string };

type MemberReport = {
    id: number;
    name: string;
    status: string;
    caregiver: string | null;
    category_scores: Record<number, number>;
    overall_percentage: number;
    star_rating: number;
    spiritual_assessments: AssessmentOption[];
    activity_assessments: AssessmentOption[];
    spiritual_assessment: { label: string; color: string };
    activity_assessment: { label: string; color: string };
};

const props = defineProps<{
    categories: Category[];
    members: MemberReport[];
    filters: { from_month: string | null; to_month: string | null };
    spiritualAssessmentOptions: AssessmentOption[];
    activityAssessmentOptions: AssessmentOption[];
}>();

const fromMonth = ref(props.filters.from_month ?? '');
const toMonth = ref(props.filters.to_month ?? '');

watch([fromMonth, toMonth], ([from, to]) => {
    router.get(
        index().url,
        { from_month: from || undefined, to_month: to || undefined },
        { preserveScroll: true, replace: true },
    );
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'SI Report', href: index().url },
];

function formatPct(val: number) {
    return (val * 100).toFixed(2) + '%';
}

function stars(rating: number) {
    return '⭐'.repeat(rating) + '☆'.repeat(5 - rating);
}

const colorClass: Record<string, string> = {
    green: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    red: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    orange: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
    amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
    purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
    teal: 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
    rose: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
    gray: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
};

function assessmentClass(color: string): string {
    return (
        (colorClass[color] ?? colorClass['gray']) +
        ' rounded-full px-2 py-0.5 text-xs font-medium'
    );
}

const reportDialogOpen = ref(false);
const selectedMember = ref<MemberReport | null>(null);

function openMemberReport(member: MemberReport) {
    selectedMember.value = member;
    reportDialogOpen.value = true;
}
</script>

<template>
    <Head title="SI Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="SI Attendance Report"
                    description="Overall performance across all active members"
                />
                <div class="flex shrink-0 items-center gap-2">
                    <div class="flex items-center gap-2 text-sm">
                        <label class="text-muted-foreground">From</label>
                        <input
                            v-model="fromMonth"
                            type="month"
                            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm shadow-sm focus:ring-1 focus:ring-ring focus:outline-none"
                        />
                        <label class="text-muted-foreground">To</label>
                        <input
                            v-model="toMonth"
                            type="month"
                            class="rounded-md border border-input bg-background px-3 py-1.5 text-sm shadow-sm focus:ring-1 focus:ring-ring focus:outline-none"
                        />
                        <button
                            v-if="fromMonth || toMonth"
                            class="text-xs text-muted-foreground underline hover:text-foreground"
                            @click="
                                fromMonth = '';
                                toMonth = '';
                            "
                        >
                            Clear
                        </button>
                    </div>
                    <Button variant="outline" as-child>
                        <Link :href="membersIndex().url">View Members</Link>
                    </Button>
                </div>
            </div>

            <!-- Category Weights Summary -->
            <div class="flex flex-wrap gap-3">
                <div
                    v-for="cat in categories"
                    :key="cat.id"
                    class="rounded-lg border border-sidebar-border/70 px-4 py-2 text-sm dark:border-sidebar-border"
                >
                    <span class="font-medium">{{ cat.name }}</span>
                    <span class="ml-2 text-muted-foreground"
                        >{{ (cat.weight * 100).toFixed(0) }}%</span
                    >
                </div>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-8">#</TableHead>
                            <TableHead>Member</TableHead>
                            <TableHead>Caregiver</TableHead>
                            <TableHead
                                v-for="cat in categories"
                                :key="cat.id"
                                class="text-center"
                            >
                                {{ cat.name }}
                            </TableHead>
                            <TableHead class="text-center">Overall %</TableHead>
                            <TableHead class="text-center">Rating</TableHead>
                            <TableHead class="text-center">Spiritual</TableHead>
                            <TableHead class="text-center">Activity</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="members.length === 0"
                            :colspan="6 + categories.length"
                        >
                            No members enrolled yet.
                        </TableEmpty>
                        <TableRow
                            v-for="(member, idx) in members"
                            :key="member.id"
                            :class="{ 'opacity-60': member.status === 'exit' }"
                            class="cursor-pointer"
                            @click="openMemberReport(member)"
                        >
                            <TableCell
                                class="text-center text-muted-foreground"
                                >{{ idx + 1 }}</TableCell
                            >
                            <TableCell class="font-medium">{{
                                member.name
                            }}</TableCell>
                            <TableCell class="text-muted-foreground">{{
                                member.caregiver || '—'
                            }}</TableCell>
                            <TableCell
                                v-for="cat in categories"
                                :key="cat.id"
                                class="text-center text-sm"
                            >
                                {{
                                    formatPct(
                                        (member.category_scores[cat.id] ?? 0) *
                                            cat.weight,
                                    )
                                }}
                            </TableCell>
                            <TableCell class="text-center font-semibold">
                                {{ formatPct(member.overall_percentage) }}
                            </TableCell>
                            <TableCell class="text-center text-base">
                                {{ stars(member.star_rating) }}
                            </TableCell>
                            <TableCell class="text-center">
                                <div
                                    class="flex flex-wrap justify-center gap-1"
                                >
                                    <span
                                        v-for="a in member.spiritual_assessments"
                                        :key="a.value"
                                        :class="assessmentClass(a.color)"
                                    >
                                        {{ a.label }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-center">
                                <div
                                    class="flex flex-wrap justify-center gap-1"
                                >
                                    <span
                                        v-for="a in member.activity_assessments"
                                        :key="a.value"
                                        :class="assessmentClass(a.color)"
                                    >
                                        {{ a.label }}
                                    </span>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
        <SiMemberReportDialog
            v-model:open="reportDialogOpen"
            :member="selectedMember"
            :categories="categories"
            :spiritual-assessment-options="spiritualAssessmentOptions"
            :activity-assessment-options="activityAssessmentOptions"
            @saved="router.reload()"
        />
    </AppLayout>
</template>
