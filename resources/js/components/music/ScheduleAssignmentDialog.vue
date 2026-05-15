<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    update as updateSchedule,
    updateAssignments,
} from '@/actions/App/Http/Controllers/Music/ScheduleController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
    open: boolean;
    schedule: ScheduleRow | null;
    roles: ScheduleRole[];
    members: MusicMember[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

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
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }
        errors.value = {};
        if (!props.schedule) {
            status.value = 'active';
            scheduleNotes.value = '';
            assignments.value = [];
            initialStatus.value = 'active';
            initialNotes.value = '';
            return;
        }
        status.value = props.schedule.status;
        scheduleNotes.value = props.schedule.notes ?? '';
        initialStatus.value = props.schedule.status;
        initialNotes.value = props.schedule.notes ?? '';
        assignments.value = orderedRoles.value.map((role) => {
            const existing = props.schedule!.assignments.find(
                (a) => a.schedule_role_id === role.id
            );
            return {
                schedule_role_id: role.id,
                music_member_id: existing?.music_member_id ?? null,
                notes: existing?.notes ?? '',
            };
        });
    }
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
    emit('update:open', false);
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
            handleClose();
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
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="flex h-dvh max-w-2xl flex-col overflow-hidden rounded-none p-0 sm:h-[90vh] sm:rounded-lg">
            <DialogHeader class="shrink-0 px-6 pt-6">
                <DialogTitle>Edit assignments — {{ formattedDate() }}</DialogTitle>
                <DialogDescription class="sr-only">
                    Assign team members to roles for this service date.
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-1 flex-col overflow-hidden" @submit.prevent="submit">
                <div class="flex-1 space-y-5 overflow-y-auto px-6 py-4">
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
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                            {{ section.team }}
                        </h3>
                        <div
                            v-for="role in section.roles"
                            :key="role.id"
                            class="grid grid-cols-1 gap-2 rounded-md border p-3 sm:grid-cols-12 sm:items-center"
                        >
                            <Label class="sm:col-span-3 text-sm font-medium">{{ role.name }}</Label>
                            <template v-if="draftFor(role.id)">
                                <div class="sm:col-span-5">
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
                                </div>
                                <div class="sm:col-span-4">
                                    <Textarea
                                        v-model="draftFor(role.id)!.notes"
                                        rows="1"
                                        placeholder='e.g. "Gideon/Emman"'
                                        class="min-h-9"
                                    />
                                    <InputError :message="errors[`assignments.${draftIndexByRoleId.get(role.id)}.notes`]" />
                                </div>
                            </template>
                        </div>
                    </div>
                    <p v-if="assignments.length === 0" class="text-sm text-muted-foreground">
                        No roles configured yet.
                    </p>
                    <InputError :message="errors.assignments" />
                </div>

                <DialogFooter class="shrink-0 border-t px-6 py-4">
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing || !schedule">
                        {{ isProcessing ? 'Saving…' : 'Save' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
