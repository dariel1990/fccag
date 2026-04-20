<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { details } from '@/actions/App/Http/Controllers/Si/SiMemberController';
import { Badge } from '@/components/ui/badge';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/components/ui/sheet';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableEmpty,
} from '@/components/ui/table';

type Member = { id: number; name: string };

const props = defineProps<{
    open: boolean;
    member: Member | null;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

type MemberDetail = {
    id: number;
    name: string;
    sex: string;
    ph_id: string | null;
    address: string | null;
    status: string;
    status_label: string;
    status_color: string;
    enrolled_at: string;
    exited_at: string | null;
    caregiver: { id: number; name: string } | null;
};

type CategoryScore = {
    id: number;
    name: string;
    weight: number;
    score: number;
    weighted_score: number;
};

type Assessment = { value: string; label: string; color: string };

type Category = { id: number; name: string };

type ActivityRecord = {
    id: number;
    title: string;
    conducted_at: string;
    category_id: number;
    category: string | null;
    speaker: string | null;
    topic: string | null;
    status: string | null;
    status_label: string | null;
    status_color: string | null;
    remarks: string | null;
    recommendation: string | null;
};

type DetailData = {
    member: MemberDetail;
    category_scores: CategoryScore[];
    overall_percentage: number;
    star_rating: number;
    spiritual_assessment: Assessment;
    activity_assessment: Assessment;
    activities: ActivityRecord[];
    categories: Category[];
};

const data = ref<DetailData | null>(null);
const loading = ref(false);
const selectedCategoryId = ref<number | null>(null);

watch(
    () => props.member,
    async (member) => {
        if (!member) return;
        loading.value = true;
        selectedCategoryId.value = null;
        data.value = null;
        try {
            const res = await fetch(details(member.id).url);
            data.value = await res.json();
        } finally {
            loading.value = false;
        }
    },
);

const filteredActivities = computed(() =>
    selectedCategoryId.value === null
        ? (data.value?.activities ?? [])
        : (data.value?.activities ?? []).filter(
              (a) => a.category_id === selectedCategoryId.value,
          ),
);

function formatPct(val: number) {
    return (val * 100).toFixed(2) + '%';
}

function stars(rating: number) {
    return '⭐'.repeat(rating) + '☆'.repeat(5 - rating);
}

const statusColorClass: Record<string, string> = {
    green: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    red: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    yellow: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
    blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    orange: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
    gray: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
};

function statusClass(color: string | null): string {
    return (
        (statusColorClass[color ?? ''] ?? statusColorClass['gray']) +
        ' rounded-full px-2 py-0.5 text-xs font-medium'
    );
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent
            class="flex w-full flex-col overflow-hidden p-6 sm:!max-w-[calc(100vw-16rem)]"
        >
            <SheetHeader>
                <SheetTitle>{{ member?.name }}</SheetTitle>
                <SheetDescription
                    >Member Information & Attendance History</SheetDescription
                >
            </SheetHeader>

            <div
                v-if="loading"
                class="flex flex-1 items-center justify-center text-muted-foreground"
            >
                Loading...
            </div>

            <div
                v-else-if="data"
                class="flex-1 space-y-6 overflow-y-auto pt-2 pr-1"
            >
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Member Info -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border"
                    >
                        <h3 class="mb-4 font-semibold">Member Information</h3>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">PH ID</dt>
                                <dd class="font-medium">
                                    {{ data.member.ph_id || '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Sex</dt>
                                <dd>{{ data.member.sex }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Status</dt>
                                <dd>
                                    <Badge
                                        :variant="
                                            data.member.status === 'active'
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{ data.member.status_label }}
                                    </Badge>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Caregiver</dt>
                                <dd class="font-medium">
                                    {{ data.member.caregiver?.name || '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Address</dt>
                                <dd class="text-right">
                                    {{ data.member.address || '—' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Enrolled</dt>
                                <dd>{{ data.member.enrolled_at }}</dd>
                            </div>
                            <div
                                v-if="data.member.exited_at"
                                class="flex justify-between"
                            >
                                <dt class="text-muted-foreground">Exited</dt>
                                <dd>{{ data.member.exited_at }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Overall Score Card -->
                    <div
                        class="rounded-xl border border-sidebar-border/70 p-5 dark:border-sidebar-border"
                    >
                        <h3 class="mb-4 font-semibold">Overall Assessment</h3>
                        <div class="space-y-4">
                            <div class="text-center">
                                <div class="text-4xl font-bold">
                                    {{ formatPct(data.overall_percentage) }}
                                </div>
                                <div class="mt-1 text-2xl">
                                    {{ stars(data.star_rating) }}
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div
                                    class="flex items-center justify-between rounded-lg bg-muted/40 px-3 py-2"
                                >
                                    <span class="text-muted-foreground"
                                        >Spiritual Assessment</span
                                    >
                                    <Badge variant="outline">{{
                                        data.spiritual_assessment.label
                                    }}</Badge>
                                </div>
                                <div
                                    class="flex items-center justify-between rounded-lg bg-muted/40 px-3 py-2"
                                >
                                    <span class="text-muted-foreground"
                                        >Activity Assessment</span
                                    >
                                    <Badge variant="outline">{{
                                        data.activity_assessment.label
                                    }}</Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Score Breakdown by Category -->
                <div
                    class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h3 class="font-semibold">
                            Score Breakdown by Category
                        </h3>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <div
                                v-for="cat in data.category_scores"
                                :key="cat.id"
                                class="flex items-center gap-4"
                            >
                                <div class="w-40 shrink-0 text-sm font-medium">
                                    {{ cat.name }}
                                </div>
                                <div class="flex-1">
                                    <div
                                        class="h-2 overflow-hidden rounded-full bg-muted"
                                    >
                                        <div
                                            class="h-full rounded-full bg-primary transition-all"
                                            :style="{
                                                width: `${cat.score * 100}%`,
                                            }"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="w-16 text-right text-sm text-muted-foreground"
                                >
                                    {{ formatPct(cat.score) }}
                                </div>
                                <div
                                    class="w-20 text-right text-xs text-muted-foreground"
                                >
                                    × {{ (cat.weight * 100).toFixed(0) }}%
                                    weight
                                </div>
                                <div
                                    class="w-16 text-right text-sm font-semibold"
                                >
                                    {{ formatPct(cat.weighted_score) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Attendance History -->
                <div
                    class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <div
                        class="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <h3 class="font-semibold">
                            Activity Attendance History
                        </h3>
                    </div>

                    <!-- Category filter pills -->
                    <div
                        class="flex flex-wrap gap-2 border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
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
                                >({{ data.activities.length }})</span
                            >
                        </button>
                        <button
                            v-for="cat in data.categories"
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
                                    data.activities.filter(
                                        (a) => a.category_id === cat.id,
                                    ).length
                                }})
                            </span>
                        </button>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-8 text-center">#</TableHead>
                                <TableHead>Activity</TableHead>
                                <TableHead>Category</TableHead>
                                <TableHead>Date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Remarks</TableHead>
                                <TableHead>Recommendation</TableHead>
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
                                v-for="(activity, idx) in filteredActivities"
                                :key="activity.id"
                            >
                                <TableCell
                                    class="text-center text-muted-foreground"
                                    >{{ idx + 1 }}</TableCell
                                >
                                <TableCell class="font-medium">
                                    {{ activity.title }}
                                    <div
                                        v-if="activity.topic"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ activity.topic }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span
                                        v-if="activity.category"
                                        class="rounded-full border border-sidebar-border/70 px-2 py-0.5 text-xs dark:border-sidebar-border"
                                    >
                                        {{ activity.category }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{
                                    activity.conducted_at
                                }}</TableCell>
                                <TableCell>
                                    <span
                                        v-if="activity.status_label"
                                        :class="
                                            statusClass(activity.status_color)
                                        "
                                    >
                                        {{ activity.status_label }}
                                    </span>
                                    <span v-else class="text-muted-foreground"
                                        >—</span
                                    >
                                </TableCell>
                                <TableCell
                                    class="max-w-48 text-sm break-words whitespace-normal text-muted-foreground"
                                >
                                    {{ activity.remarks || '—' }}
                                </TableCell>
                                <TableCell
                                    class="max-w-48 text-sm break-words whitespace-normal text-muted-foreground"
                                >
                                    {{ activity.recommendation || '—' }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>
