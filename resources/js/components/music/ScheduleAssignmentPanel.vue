<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { X } from 'lucide-vue-next';
import {
    update as updateSchedule,
    updateAssignments,
} from '@/actions/App/Http/Controllers/Music/ScheduleController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

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

type AssignmentDraft = {
    schedule_role_id: number;
    music_member_id: number | null;
    notes: string;
};

const NONE_VALUE = '__none__';
const STATUSES = ['active', 'cancelled', 'none'] as const;
const TEAM_ORDER = ['band', 'media', 'worship'] as const;

const props = defineProps<{
    schedule: ScheduleRow | null;
    roles: ScheduleRole[];
    members: MusicMember[];
}>();

const emit = defineEmits<{
    saved: [];
    close: [];
}>();

/**
 * Status/notes editing strategy:
 * UpdateScheduleAssignmentsRequest only accepts `assignments`. To persist
 * status/notes changes we issue a separate PATCH to the schedule update endpoint,
 * which requires service_type_id + service_date too. We chain: meta update first
 * (only if changed), then assignments. Both succeed = saved; either errors = surface.
 */
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});
const initialStatus = ref<string>('active');
const initialNotes = ref<string>('');
const status = ref<string>('active');
const scheduleNotes = ref<string>('');
const assignments = ref<AssignmentDraft[]>([]);

const orderedRoles = computed(() => {
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

const draftIndexByRoleId = computed(() => {
    const map = new Map<number, number>();
    assignments.value.forEach((d, i) => map.set(d.schedule_role_id, i));
    return map;
});

const teamSections = computed(() => {
    const sections: { team: string; roles: ScheduleRole[] }[] = [];
    const grouped = new Map<string, ScheduleRole[]>();
    for (const role of orderedRoles.value) {
        if (!grouped.has(role.team)) {
            grouped.set(role.team, []);
        }
        grouped.get(role.team)!.push(role);
    }
    for (const team of TEAM_ORDER) {
        if (grouped.has(team)) {
            sections.push({ team, roles: grouped.get(team)! });
        }
    }
    for (const [team, roles] of grouped.entries()) {
        if (!TEAM_ORDER.includes(team as typeof TEAM_ORDER[number])) {
            sections.push({ team, roles });
        }
    }
    return sections;
});

watch(
    () => props.schedule,
    (schedule) => {
        errors.value = {};
        if (!schedule) {
            status.value = 'active';
            scheduleNotes.value = '';
            assignments.value = [];
            initialStatus.value = 'active';
            initialNotes.value = '';
            return;
        }
        status.value = schedule.status;
        scheduleNotes.value = schedule.notes ?? '';
        initialStatus.value = schedule.status;
        initialNotes.value = schedule.notes ?? '';
        assignments.value = orderedRoles.value.map((role) => {
            const existing = schedule.assignments.find(
                (a) => a.schedule_role_id === role.id
            );
            return {
                schedule_role_id: role.id,
                music_member_id: existing?.music_member_id ?? null,
                notes: existing?.notes ?? '',
            };
        });
    },
    { immediate: true }
);

function draftFor(roleId: number): AssignmentDraft | null {
    const idx = draftIndexByRoleId.value.get(roleId);
    return idx === undefined ? null : assignments.value[idx];
}

function memberSelectValue(draft: AssignmentDraft): string {
    return draft.music_member_id === null ? NONE_VALUE : String(draft.music_member_id);
}

function setMember(draft: AssignmentDraft, value: string): void {
    draft.music_member_id = value === NONE_VALUE ? null : Number(value);
}

function formattedDate(): string {
    if (!props.schedule) {
        return '';
    }
    const [y, m, d] = props.schedule.service_date.slice(0, 10).split('-').map(Number);
    const dt = new Date(Date.UTC(y, m - 1, d));
    return dt.toLocaleDateString(undefined, {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        timeZone: 'UTC',
    });
}

function handleClose(): void {
    emit('close');
}

function submitAssignments(): void {
    if (!props.schedule) {
        return;
    }
    const payload = {
        assignments: assignments.value.map((a) => ({
            schedule_role_id: a.schedule_role_id,
            music_member_id: a.music_member_id,
            notes: a.notes ? a.notes : null,
        })),
    };
    router.patch(updateAssignments(props.schedule.id).url, payload, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('saved');
        },
        onError: (errs: Record<string, string>) => {
            errors.value = { ...errors.value, ...errs };
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}

function submit(): void {
    if (!props.schedule) {
        return;
    }
    isProcessing.value = true;
    errors.value = {};

    const metaChanged =
        status.value !== initialStatus.value || scheduleNotes.value !== initialNotes.value;

    if (!metaChanged) {
        submitAssignments();
        return;
    }

    router.patch(
        updateSchedule(props.schedule.id).url,
        {
            service_type_id: props.schedule.service_type_id,
            service_date: props.schedule.service_date.slice(0, 10),
            status: status.value,
            notes: scheduleNotes.value ? scheduleNotes.value : null,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                submitAssignments();
            },
            onError: (errs: Record<string, string>) => {
                errors.value = errs;
                isProcessing.value = false;
            },
        }
    );
}
</script>

<template>
    <div class="rounded-xl border border-sidebar-border/70 bg-background dark:border-sidebar-border">
        <div v-if="!schedule" class="flex flex-col items-center justify-center gap-2 p-8 text-center">
            <p class="text-sm font-medium text-foreground">Select a day to view or edit assignments</p>
            <p class="text-xs text-muted-foreground">
                Click any day on the calendar. If no schedule exists yet, generate the month first.
            </p>
        </div>

        <form v-else class="flex flex-col" @submit.prevent="submit">
            <div class="flex items-start justify-between gap-2 border-b px-4 py-3">
                <div>
                    <h2 class="text-sm font-semibold">Assignments</h2>
                    <p class="text-xs text-muted-foreground">{{ formattedDate() }}</p>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="h-7 w-7"
                    aria-label="Close"
                    @click="handleClose"
                >
                    <X class="h-4 w-4" />
                </Button>
            </div>

            <div class="space-y-5 px-4 py-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Status</Label>
                        <Select v-model="status">
                            <SelectTrigger>
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="s in STATUSES" :key="s" :value="s">
                                    {{ s }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.status" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Notes</Label>
                        <Input v-model="scheduleNotes" placeholder="Optional service notes" />
                        <InputError :message="errors.notes" />
                    </div>
                </div>

                <div
                    v-for="section in teamSections"
                    :key="section.team"
                    class="space-y-3"
                >
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {{ section.team }}
                    </h3>
                    <div
                        v-for="role in section.roles"
                        :key="role.id"
                        class="space-y-2 rounded-md border p-3"
                    >
                        <Label class="text-sm font-medium">{{ role.name }}</Label>
                        <template v-if="draftFor(role.id)">
                            <Select
                                :model-value="memberSelectValue(draftFor(role.id)!)"
                                @update:model-value="(v) => setMember(draftFor(role.id)!, String(v))"
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="— None —" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="NONE_VALUE">— None —</SelectItem>
                                    <SelectItem
                                        v-for="m in members"
                                        :key="m.id"
                                        :value="String(m.id)"
                                    >
                                        {{ m.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors[`assignments.${draftIndexByRoleId.get(role.id)}.music_member_id`]" />
                            <Textarea
                                v-model="draftFor(role.id)!.notes"
                                rows="1"
                                placeholder='e.g. "Gideon/Emman"'
                                class="min-h-9"
                            />
                            <InputError :message="errors[`assignments.${draftIndexByRoleId.get(role.id)}.notes`]" />
                        </template>
                    </div>
                </div>
                <p v-if="assignments.length === 0" class="text-sm text-muted-foreground">
                    No roles configured yet.
                </p>
                <InputError :message="errors.assignments" />
            </div>

            <div class="flex items-center justify-end gap-2 border-t px-4 py-3">
                <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                <Button type="submit" :disabled="isProcessing">
                    {{ isProcessing ? 'Saving…' : 'Save' }}
                </Button>
            </div>
        </form>
    </div>
</template>
