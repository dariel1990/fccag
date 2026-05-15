<script setup lang="ts">
import { computed } from 'vue';
import { buildWeeks, todayKey, WEEKDAY_LABELS } from '@/components/music/monthGrid';

type ServiceType = {
    id: number;
    name: string;
    day_of_week: number | null;
    color: string | null;
    sort_order: number;
};

type ScheduleRole = {
    id: number;
    team: string;
    name: string;
    sort_order: number;
};

type MusicMember = {
    id: number;
    name: string;
    user_id: number | null;
};

type Assignment = {
    id: number;
    schedule_role_id: number;
    music_member_id: number | null;
    notes: string | null;
};

type ScheduleRow = {
    id: number;
    service_type_id: number;
    service_date: string;
    status: string;
    notes: string | null;
    assignments: Assignment[];
};

const props = defineProps<{
    year: number;
    month: number;
    schedules: ScheduleRow[];
    serviceTypes: ServiceType[];
    roles: ScheduleRole[];
    members: MusicMember[];
    selectedDate?: string | null;
}>();

const emit = defineEmits<{
    'select-day': [payload: { date: string; schedules: ScheduleRow[]; selectedScheduleId?: number }];
}>();

const FALLBACK_CHIP = 'bg-muted-foreground/15 text-foreground ring-muted-foreground/20 hover:bg-muted-foreground/25';

const weeks = computed(() => buildWeeks(props.year, props.month));

const today = todayKey();

const serviceTypeMap = computed(() => {
    const map = new Map<number, ServiceType>();
    for (const st of props.serviceTypes) {
        map.set(st.id, st);
    }
    return map;
});

const schedulesByDate = computed(() => {
    const map = new Map<string, ScheduleRow[]>();
    const sorted = [...props.schedules].sort((a, b) => {
        const stA = serviceTypeMap.value.get(a.service_type_id)?.sort_order ?? 0;
        const stB = serviceTypeMap.value.get(b.service_type_id)?.sort_order ?? 0;
        return stA - stB;
    });
    for (const s of sorted) {
        const key = s.service_date.slice(0, 10);
        if (!map.has(key)) {
            map.set(key, []);
        }
        map.get(key)!.push(s);
    }
    return map;
});

function schedulesForDate(date: string): ScheduleRow[] {
    return schedulesByDate.value.get(date) ?? [];
}

function chipClass(schedule: ScheduleRow): string {
    const st = serviceTypeMap.value.get(schedule.service_type_id);
    if (st?.color) {
        return 'ring-1 ring-black/10 hover:opacity-90';
    }
    return FALLBACK_CHIP;
}

function chipStyle(schedule: ScheduleRow): Record<string, string> {
    const st = serviceTypeMap.value.get(schedule.service_type_id);
    if (st?.color) {
        return { backgroundColor: st.color, color: '#111827' };
    }
    return {};
}

function chipLabel(schedule: ScheduleRow): string {
    const st = serviceTypeMap.value.get(schedule.service_type_id);
    return st?.name ?? 'Service';
}

function handleCellClick(date: string): void {
    emit('select-day', { date, schedules: schedulesForDate(date) });
}

function handleChipClick(schedule: ScheduleRow, event: MouseEvent): void {
    event.stopPropagation();
    const date = schedule.service_date.slice(0, 10);
    emit('select-day', {
        date,
        schedules: schedulesForDate(date),
        selectedScheduleId: schedule.id,
    });
}
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <div class="min-w-[720px]">
            <div class="grid grid-cols-7 bg-muted/50">
                <div
                    v-for="label in WEEKDAY_LABELS"
                    :key="label"
                    class="border-b px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                >
                    {{ label }}
                </div>
            </div>
            <div
                v-for="(week, wIdx) in weeks"
                :key="wIdx"
                class="grid grid-cols-7"
            >
                <button
                    v-for="cell in week"
                    :key="cell.date"
                    type="button"
                    :class="[
                        'flex min-h-[110px] flex-col gap-1 border-b border-r px-2 py-2 text-left transition last:border-r-0',
                        cell.inMonth ? 'bg-background hover:bg-accent/30' : 'bg-muted/20 text-muted-foreground/60',
                        cell.date === today ? 'ring-2 ring-inset ring-primary' : '',
                        cell.date === selectedDate ? 'bg-accent/60 ring-2 ring-inset ring-ring' : '',
                        'cursor-pointer',
                    ]"
                    @click="handleCellClick(cell.date)"
                >
                    <span
                        :class="[
                            'text-xs font-semibold',
                            cell.inMonth ? 'text-foreground' : 'text-muted-foreground/60',
                        ]"
                    >
                        {{ cell.day }}
                    </span>
                    <div class="flex flex-col gap-1">
                        <button
                            v-for="schedule in schedulesForDate(cell.date)"
                            :key="schedule.id"
                            type="button"
                            :class="[
                                'truncate rounded-md px-2 py-1 text-left text-[11px] font-medium transition',
                                chipClass(schedule),
                            ]"
                            :style="chipStyle(schedule)"
                            :title="chipLabel(schedule)"
                            @click="(e) => handleChipClick(schedule, e)"
                        >
                            {{ chipLabel(schedule) }}
                            <span
                                v-if="schedule.status !== 'active'"
                                class="ml-1 text-[9px] uppercase tracking-wide opacity-70"
                            >
                                · {{ schedule.status }}
                            </span>
                        </button>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>
