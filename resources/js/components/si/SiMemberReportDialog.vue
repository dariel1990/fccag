<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Save } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Bar } from 'vue-chartjs';
import { updateAssessments } from '@/actions/App/Http/Controllers/Si/SiMemberController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

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
    open: boolean;
    member: MemberReport | null;
    categories: Category[];
    spiritualAssessmentOptions: AssessmentOption[];
    activityAssessmentOptions: AssessmentOption[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const spiritualValues = ref<string[]>([]);
const activityValues = ref<string[]>([]);
const saving = ref(false);

watch(
    () => props.member,
    (member) => {
        if (member) {
            spiritualValues.value = member.spiritual_assessments.map((a) => a.value);
            activityValues.value = member.activity_assessments.map((a) => a.value);
        }
    },
    { immediate: true },
);

function saveAssessments() {
    if (!props.member) return;
    saving.value = true;
    router.patch(
        updateAssessments(props.member.id).url,
        { spiritual_assessments: spiritualValues.value, activity_assessments: activityValues.value },
        {
            preserveScroll: true,
            onSuccess: () => { emit('saved'); emit('update:open', false); },
            onFinish: () => (saving.value = false),
        },
    );
}

function formatPct(val: number) {
    return (val * 100).toFixed(2) + '%';
}

function stars(rating: number) {
    return '⭐'.repeat(rating) + '☆'.repeat(5 - rating);
}



const chartData = computed(() => {
    if (!props.member) return { labels: [], datasets: [] };

    const labels = [...props.categories.map((c) => c.name), 'Overall'];
    const data = [
        ...props.categories.map((c) =>
            +((props.member!.category_scores[c.id] ?? 0) * c.weight * 100).toFixed(2),
        ),
        +(props.member.overall_percentage * 100).toFixed(2),
    ];
    const backgroundColors = [
        ...props.categories.map((_, i) => categoryColors[i % categoryColors.length]),
        '#94a3b8',
    ];

    return {
        labels,
        datasets: [{ label: 'Score (%)', data, backgroundColor: backgroundColors, borderRadius: 6, borderSkipped: false }],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            min: 0,
            max: 100,
            ticks: { callback: (v: number | string) => v + '%' },
            grid: { color: 'hsl(var(--border) / 0.5)' },
        },
        x: { grid: { display: false } },
    },
    plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx: { parsed: { y: number } }) => ctx.parsed.y.toFixed(2) + '%' } },
    },
};

// Stacked horizontal bar — each category's weighted contribution to the overall score
const categoryColors = [
    '#6366f1', // indigo
    '#22c55e', // green
    '#f59e0b', // amber
    '#ec4899', // pink
    '#14b8a6', // teal
    '#f97316', // orange
    '#8b5cf6', // violet
    '#0ea5e9', // sky
];

const stackedData = computed(() => {
    if (!props.member) return { labels: [], datasets: [] };
    return {
        labels: ['Overall'],
        datasets: props.categories.map((c, i) => ({
            label: c.name,
            data: [+((props.member!.category_scores[c.id] ?? 0) * c.weight * 100).toFixed(2)],
            backgroundColor: categoryColors[i % categoryColors.length],
            borderRadius: i === 0 ? { topLeft: 4, bottomLeft: 4 } : i === props.categories.length - 1 ? { topRight: 4, bottomRight: 4 } : 0,
            borderSkipped: false,
        })),
    };
});

const stackedOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y' as const,
    scales: {
        x: {
            stacked: true,
            min: 0,
            max: 100,
            ticks: { callback: (v: number | string) => v + '%' },
            grid: { color: 'hsl(var(--border) / 0.5)' },
        },
        y: { stacked: true, grid: { display: false } },
    },
    plugins: {
        legend: { display: true, position: 'bottom' as const },
        tooltip: { callbacks: { label: (ctx: { dataset: { label: string }; parsed: { x: number } }) => `${ctx.dataset.label}: ${ctx.parsed.x.toFixed(2)}%` } },
    },
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="flex max-h-[90vh] w-full flex-col overflow-hidden sm:!max-w-5xl">
            <DialogHeader>
                <DialogTitle class="text-xl">{{ member?.name }}</DialogTitle>
                <DialogDescription class="text-sm">
                    {{ member?.caregiver ? `Caregiver: ${member.caregiver}` : 'No caregiver assigned' }}
                </DialogDescription>
            </DialogHeader>

            <template v-if="member">
                <div class="flex-1 overflow-y-auto pr-1">
                    <!-- Star rating + overall -->
                    <div class="flex items-center justify-between rounded-xl bg-muted/40 px-6 py-4">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Overall Score</div>
                            <div class="mt-1 text-4xl font-bold">{{ formatPct(member.overall_percentage) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl tracking-widest">{{ stars(member.star_rating) }}</div>
                            <div class="mt-1 text-sm text-muted-foreground">{{ member.star_rating }}/5 stars</div>
                        </div>
                    </div>

                    <!-- Category score breakdown -->
                    <div class="mt-4 grid gap-3" :style="`grid-template-columns: repeat(${categories.length}, minmax(0, 1fr))`">
                        <div v-for="cat in categories" :key="cat.id" class="rounded-lg bg-muted/40 p-4">
                            <div class="text-sm text-muted-foreground">
                                {{ cat.name }}
                                <span class="ml-1 opacity-60">(×{{ (cat.weight * 100).toFixed(0) }}%)</span>
                            </div>
                            <div class="mt-1 text-lg font-semibold">
                                {{ formatPct((member.category_scores[cat.id] ?? 0) * cat.weight) }}
                            </div>
                        </div>
                    </div>

                    <!-- Bar chart -->
                    <div class="mt-4 h-64">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>

                    <!-- Stacked horizontal bar -->
                    <div class="mt-4">
                        <p class="mb-2 text-xs font-medium uppercase tracking-wide text-muted-foreground">Stacked — Weighted Contributions</p>
                        <div class="h-28">
                            <Bar :data="stackedData" :options="stackedOptions" />
                        </div>
                    </div>

                    <!-- Assessment editors -->
                    <div class="mt-4 space-y-4 border-t pt-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-muted-foreground">Spiritual Assessment</label>
                            <Multiselect
                                v-model="spiritualValues"
                                :options="spiritualAssessmentOptions.map((o) => ({ value: o.value, label: o.label }))"
                                label="label"
                                value-prop="value"
                                mode="tags"
                                :close-on-select="false"
                                placeholder="Select assessments..."
                            />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-muted-foreground">Activity Assessment</label>
                            <Multiselect
                                v-model="activityValues"
                                :options="activityAssessmentOptions.map((o) => ({ value: o.value, label: o.label }))"
                                label="label"
                                value-prop="value"
                                mode="tags"
                                :close-on-select="false"
                                placeholder="Select assessments..."
                            />
                        </div>

                        <Button class="w-full" size="lg" :disabled="saving" @click="saveAssessments">
                            <Save class="mr-2 h-5 w-5" />
                            {{ saving ? 'Saving...' : 'Save Assessments' }}
                        </Button>
                    </div>
                </div>
            </template>
        </DialogContent>
    </Dialog>
</template>
