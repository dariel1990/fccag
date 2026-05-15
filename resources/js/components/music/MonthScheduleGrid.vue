<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { CalendarDays, CalendarPlus, Table as TableIcon } from 'lucide-vue-next';
import {
    index as schedulesIndex,
    generateMonth,
} from '@/actions/App/Http/Controllers/Music/ScheduleController';
import MonthCalendarView from '@/components/music/MonthCalendarView.vue';
import ScheduleAssignmentDialog from '@/components/music/ScheduleAssignmentDialog.vue';
import ScheduleAssignmentPanel from '@/components/music/ScheduleAssignmentPanel.vue';
import { buildWeeks, MONTH_NAMES, type WeekCell } from '@/components/music/monthGrid';
import { Button } from '@/components/ui/button';

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
    role?: ScheduleRole;
    member?: MusicMember | null;
};

type ScheduleRow = {
    id: number;
    service_type_id: number;
    service_date: string;
    status: string;
    notes: string | null;
    assignments: Assignment[];
};

type GridCell = { date: string | null; inMonth: boolean; schedule: ScheduleRow | null };
type ViewMode = 'grid' | 'calendar';

const TEAM_ORDER = ['band', 'media', 'worship'] as const;

const VIEW_MODE_STORAGE_KEY = 'music.schedules.viewMode';

const props = defineProps<{
    year: number;
    month: number;
    schedules: ScheduleRow[];
    serviceTypes: ServiceType[];
    roles: ScheduleRole[];
    members: MusicMember[];
}>();

function readInitialViewMode(): ViewMode {
    if (typeof window === 'undefined') {
        return 'grid';
    }
    const stored = window.localStorage.getItem(VIEW_MODE_STORAGE_KEY);
    return stored === 'calendar' ? 'calendar' : 'grid';
}

const monthValue = ref(formatMonthInput(props.year, props.month));
const dialogOpen = ref(false);
const dialogSchedule = ref<ScheduleRow | null>(null);
const generating = ref(false);
const viewMode = ref<ViewMode>(readInitialViewMode());
const selectedDate = ref<string | null>(null);
const selectedSchedule = ref<ScheduleRow | null>(null);
const panelRef = ref<HTMLElement | null>(null);

watch(viewMode, (mode) => {
    if (typeof window === 'undefined') {
        return;
    }
    window.localStorage.setItem(VIEW_MODE_STORAGE_KEY, mode);
});

const sortedServiceTypes = computed(() =>
    [...props.serviceTypes].sort((a, b) => a.sort_order - b.sort_order)
);

const sortedRoles = computed(() => {
    const teamRank = (team: string): number => {
        const idx = TEAM_ORDER.indexOf(team as typeof TEAM_ORDER[number]);
        return idx === -1 ? TEAM_ORDER.length : idx;
    };
    return [...props.roles].sort((a, b) => {
        const t = teamRank(a.team) - teamRank(b.team);
        if (t !== 0) {
            return t;
        }
        return a.sort_order - b.sort_order;
    });
});

const rolesByTeam = computed(() => {
    const map = new Map<string, ScheduleRole[]>();
    for (const role of sortedRoles.value) {
        if (!map.has(role.team)) {
            map.set(role.team, []);
        }
        map.get(role.team)!.push(role);
    }
    return map;
});

const monthTitle = computed(() => `${MONTH_NAMES[props.month - 1]} ${props.year}`);

const weeks = computed(() => buildWeeks(props.year, props.month));

const schedulesByKey = computed(() => {
    const map = new Map<string, ScheduleRow>();
    for (const s of props.schedules) {
        const dateKey = s.service_date.slice(0, 10);
        map.set(`${s.service_type_id}::${dateKey}`, s);
    }
    return map;
});

const grid = computed<GridCell[][][]>(() =>
    weeks.value.map((week) =>
        sortedServiceTypes.value.map((st) => [resolveCell(st, week)])
    )
);

function formatMonthInput(year: number, month: number): string {
    return `${year}-${String(month).padStart(2, '0')}`;
}

function resolveCell(serviceType: ServiceType, week: WeekCell[]): GridCell {
    if (serviceType.day_of_week === null) {
        return { date: null, inMonth: false, schedule: null };
    }
    const cell = week.find((c) => c.dayOfWeek === serviceType.day_of_week);
    if (!cell || !cell.inMonth) {
        return { date: cell?.date ?? null, inMonth: false, schedule: null };
    }
    const key = `${serviceType.id}::${cell.date}`;
    return {
        date: cell.date,
        inMonth: true,
        schedule: schedulesByKey.value.get(key) ?? null,
    };
}

function navigate(): void {
    const [yStr, mStr] = monthValue.value.split('-');
    const y = Number(yStr);
    const m = Number(mStr);
    if (!y || !m) {
        return;
    }
    router.get(
        schedulesIndex().url,
        { year: y, month: m },
        { preserveState: false }
    );
}

function handleGenerate(): void {
    generating.value = true;
    router.post(
        generateMonth().url,
        { year: props.year, month: props.month },
        {
            preserveScroll: true,
            onFinish: () => {
                generating.value = false;
            },
        }
    );
}

function openDialog(schedule: ScheduleRow | null): void {
    if (!schedule) {
        return;
    }
    dialogSchedule.value = schedule;
    dialogOpen.value = true;
}

function handleDialogSaved(): void {
    dialogOpen.value = false;
    router.reload({ only: ['schedules'] });
}

function handleSelectDay(payload: { date: string; schedules: ScheduleRow[]; selectedScheduleId?: number }): void {
    selectedDate.value = payload.date;
    if (payload.schedules.length === 0) {
        selectedSchedule.value = null;
    } else if (payload.selectedScheduleId !== undefined) {
        selectedSchedule.value =
            payload.schedules.find((s) => s.id === payload.selectedScheduleId) ?? payload.schedules[0];
    } else {
        const sorted = [...payload.schedules].sort((a, b) => {
            const stA = props.serviceTypes.find((st) => st.id === a.service_type_id)?.sort_order ?? 0;
            const stB = props.serviceTypes.find((st) => st.id === b.service_type_id)?.sort_order ?? 0;
            return stA - stB;
        });
        selectedSchedule.value = sorted[0];
    }
    nextTick(() => {
        if (typeof window !== 'undefined' && window.matchMedia('(max-width: 1023px)').matches) {
            panelRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

function handlePanelSaved(): void {
    const previousId = selectedSchedule.value?.id ?? null;
    router.reload({
        only: ['schedules'],
        onSuccess: () => {
            if (previousId === null) {
                return;
            }
            const refreshed = props.schedules.find((s) => s.id === previousId) ?? null;
            selectedSchedule.value = refreshed;
        },
    });
}

function handlePanelClose(): void {
    selectedDate.value = null;
    selectedSchedule.value = null;
}

function formatChipDate(dateStr: string): string {
    const [, , dd] = dateStr.split('-');
    return String(Number(dd));
}

function memberLabel(assignment: Assignment): string {
    if (assignment.member?.name) {
        return assignment.member.name;
    }
    if (assignment.notes) {
        return assignment.notes;
    }
    return '—';
}

function assignmentForRole(schedule: ScheduleRow, roleId: number): Assignment | null {
    return schedule.assignments.find((a) => a.schedule_role_id === roleId) ?? null;
}

function teamsForCell(): readonly string[] {
    return TEAM_ORDER.filter((t) => (rolesByTeam.value.get(t)?.length ?? 0) > 0);
}
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Schedules</h1>
                <p class="text-muted-foreground text-sm">{{ monthTitle }}</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="inline-flex rounded-md border border-input bg-background p-0.5">
                    <Button
                        type="button"
                        size="sm"
                        :variant="viewMode === 'grid' ? 'default' : 'ghost'"
                        class="h-8 px-3"
                        @click="viewMode = 'grid'"
                    >
                        <TableIcon class="mr-1.5 h-4 w-4" />
                        Grid
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        :variant="viewMode === 'calendar' ? 'default' : 'ghost'"
                        class="h-8 px-3"
                        @click="viewMode = 'calendar'"
                    >
                        <CalendarDays class="mr-1.5 h-4 w-4" />
                        Calendar
                    </Button>
                </div>
                <input
                    v-model="monthValue"
                    type="month"
                    class="border-input bg-background focus-visible:ring-ring rounded-md border px-3 py-1.5 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1"
                    @change="navigate"
                />
                <Button :disabled="generating" @click="handleGenerate">
                    <CalendarPlus class="mr-2 h-4 w-4" />
                    {{ generating ? 'Generating…' : 'Generate month' }}
                </Button>
            </div>
        </div>

        <div v-if="viewMode === 'calendar'" class="flex flex-col gap-4 lg:grid lg:grid-cols-3 lg:items-start">
            <div class="lg:col-span-2">
                <MonthCalendarView
                    :year="year"
                    :month="month"
                    :schedules="schedules"
                    :service-types="serviceTypes"
                    :roles="roles"
                    :members="members"
                    :selected-date="selectedDate"
                    @select-day="handleSelectDay"
                />
            </div>
            <div ref="panelRef" class="lg:col-span-1 lg:sticky lg:top-4">
                <ScheduleAssignmentPanel
                    :schedule="selectedSchedule"
                    :roles="sortedRoles"
                    :members="members"
                    @saved="handlePanelSaved"
                    @close="handlePanelClose"
                />
            </div>
        </div>

        <div
            v-else
            class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <table class="w-full min-w-[720px] border-collapse">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="border-b px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Week
                        </th>
                        <th
                            v-for="st in sortedServiceTypes"
                            :key="st.id"
                            class="border-b px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            {{ st.name }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(week, wIdx) in weeks"
                        :key="wIdx"
                        :class="wIdx % 2 === 0 ? 'bg-background' : 'bg-muted/20'"
                    >
                        <td class="border-b px-3 py-3 align-top text-xs font-medium text-muted-foreground">
                            Week {{ wIdx + 1 }}
                        </td>
                        <td
                            v-for="(cellWrap, cIdx) in grid[wIdx]"
                            :key="cIdx"
                            class="border-b px-3 py-3 align-top"
                        >
                            <template v-for="cell in cellWrap" :key="cell.date ?? 'empty'">
                                <button
                                    v-if="cell.schedule"
                                    type="button"
                                    class="flex w-full flex-col gap-2 rounded-md border border-transparent p-2 text-left transition hover:border-input hover:bg-accent/50"
                                    @click="openDialog(cell.schedule)"
                                >
                                    <span
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary ring-1 ring-primary/30"
                                    >
                                        {{ formatChipDate(cell.schedule.service_date) }}
                                    </span>
                                    <div class="flex w-full flex-col gap-2">
                                        <div
                                            v-for="team in teamsForCell()"
                                            :key="team"
                                            class="space-y-0.5"
                                        >
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                                                {{ team }}
                                            </p>
                                            <ul class="space-y-0.5 text-xs">
                                                <li
                                                    v-for="role in rolesByTeam.get(team) ?? []"
                                                    :key="role.id"
                                                    class="flex items-baseline justify-between gap-2"
                                                >
                                                    <span class="text-muted-foreground">{{ role.name }}</span>
                                                    <span
                                                        :class="[
                                                            'truncate text-right font-medium',
                                                            assignmentForRole(cell.schedule, role.id) ? 'text-foreground' : 'text-muted-foreground/60',
                                                        ]"
                                                    >
                                                        {{ assignmentForRole(cell.schedule, role.id) ? memberLabel(assignmentForRole(cell.schedule, role.id)!) : '—' }}
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <span
                                        v-if="cell.schedule.status !== 'active'"
                                        class="text-[10px] uppercase tracking-wide text-muted-foreground"
                                    >
                                        {{ cell.schedule.status }}
                                    </span>
                                </button>
                                <div
                                    v-else-if="cell.inMonth && cell.date"
                                    class="flex flex-col gap-2 rounded-md border border-dashed border-muted-foreground/30 p-2 text-xs text-muted-foreground"
                                >
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-muted text-xs font-bold text-muted-foreground">
                                        {{ formatChipDate(cell.date) }}
                                    </span>
                                    <span class="italic">No schedule yet.</span>
                                </div>
                                <div v-else class="text-xs text-muted-foreground/40">—</div>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ScheduleAssignmentDialog
            v-model:open="dialogOpen"
            :schedule="dialogSchedule"
            :roles="sortedRoles"
            :members="members"
            @saved="handleDialogSaved"
        />
    </div>
</template>
