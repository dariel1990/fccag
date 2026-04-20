<script setup lang="ts">
import Multiselect from '@vueform/multiselect';
import { CheckCircle2, Save } from 'lucide-vue-next';
import { ref, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { store as storeAttendance } from '@/actions/App/Http/Controllers/Si/SiAttendanceController';
import { Button } from '@/components/ui/button';
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

type MemberAttendance = {
    id: number;
    name: string;
    ph_id: string | null;
    caregiver: string | null;
    attendance: { status: string; remarks: string | null; recommendation: string | null } | null;
};

type AttendanceStatus = { value: string; label: string; color: string };

type SiActivity = {
    id: number;
    title: string;
    category: string | null;
    speaker: string | null;
    topic: string | null;
    memory_verse: string | null;
    conducted_at: string;
    members: MemberAttendance[];
};

const props = defineProps<{
    open: boolean;
    activity: SiActivity | null;
    attendanceStatuses: AttendanceStatus[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const attendances = ref<{ si_member_id: number; status: string; remarks: string; recommendation: string }[]>([]);
const saving = ref(false);
const saved = ref(false);

watch(
    () => props.activity,
    (activity) => {
        if (activity) {
            saved.value = false;
            attendances.value = activity.members.map((m) => ({
                si_member_id: m.id,
                status: m.attendance?.status ?? 'absent',
                remarks: m.attendance?.remarks ?? '',
                recommendation: m.attendance?.recommendation ?? '',
            }));
            nextTick(() => {
                document.querySelectorAll<HTMLTextAreaElement>('textarea[data-attendance]').forEach((t) => {
                    t.style.height = 'auto';
                    t.style.height = t.scrollHeight + 'px';
                });
            });
        }
    },
    { immediate: true },
);

const countByStatus = (status: string) => attendances.value.filter((a) => a.status === status).length;
const presentCount = () => countByStatus('present');

const summaryItems = [
    { label: 'Present', status: 'present', color: 'text-green-600 dark:text-green-400' },
    { label: 'Absent', status: 'absent', color: 'text-red-600 dark:text-red-400' },
    { label: 'Child Sick', status: 'child_sick', color: 'text-yellow-600 dark:text-yellow-400' },
    { label: 'Child under Medication', status: 'child_under_medication', color: 'text-orange-600 dark:text-orange-400' },
];

function saveAttendance() {
    if (!props.activity) {
        return;
    }

    saving.value = true;
    router.post(
        storeAttendance(props.activity.id).url,
        { attendances: attendances.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                saved.value = true;
                emit('saved');
            },
            onFinish: () => (saving.value = false),
        },
    );
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex w-full flex-col gap-0 overflow-y-auto sm:max-w-[calc(100vw-16rem)]">
            <SheetHeader class="border-b pb-4">
                <SheetTitle>{{ activity?.title }}</SheetTitle>
                <SheetDescription>
                    {{ activity?.category ?? '' }} · {{ activity?.conducted_at }}
                </SheetDescription>
            </SheetHeader>

            <template v-if="activity">
                <!-- Meta -->
                <div v-if="activity.speaker || activity.topic || activity.memory_verse" class="grid grid-cols-2 gap-3 border-b p-4 text-sm">
                    <div v-if="activity.speaker" class="rounded-lg bg-muted/40 p-3">
                        <div class="text-xs text-muted-foreground">Speaker</div>
                        <div class="font-medium">{{ activity.speaker }}</div>
                    </div>
                    <div v-if="activity.topic" class="rounded-lg bg-muted/40 p-3">
                        <div class="text-xs text-muted-foreground">Topic</div>
                        <div class="font-medium">{{ activity.topic }}</div>
                    </div>
                    <div v-if="activity.memory_verse" class="col-span-2 rounded-lg bg-muted/40 p-3">
                        <div class="text-xs text-muted-foreground">Memory Verse</div>
                        <div class="font-medium">{{ activity.memory_verse }}</div>
                    </div>
                </div>

                <!-- Success alert -->
                <div
                    v-if="saved"
                    class="mx-4 mt-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300"
                >
                    <CheckCircle2 class="h-4 w-4 shrink-0" />
                    Attendance saved successfully.
                </div>

                <!-- Summary -->
                <div class="grid grid-cols-2 gap-2 border-t p-4 sm:grid-cols-4">
                    <div
                        v-for="item in summaryItems"
                        :key="item.status"
                        class="rounded-lg bg-muted/40 p-3 text-center"
                    >
                        <div :class="['text-2xl font-bold', item.color]">
                            {{ countByStatus(item.status) }}
                        </div>
                        <div class="mt-0.5 text-xs text-muted-foreground">{{ item.label }}</div>
                    </div>
                </div>

                <!-- Attendance -->
                <div class="flex items-center justify-between border-t p-4">
                    <span class="text-sm text-muted-foreground">
                        {{ presentCount() }}/{{ activity.members.length }} present
                    </span>
                    <Button size="sm" :disabled="saving" @click="saveAttendance">
                        <Save class="mr-2 h-4 w-4" />
                        {{ saving ? 'Saving...' : 'Save Attendance' }}
                    </Button>
                </div>

                <div class="border-t">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-10 text-center">#</TableHead>
                                <TableHead>Member</TableHead>
                                <TableHead>Caregiver</TableHead>
                                <TableHead>PH ID</TableHead>
                                <TableHead class="w-44">Status</TableHead>
                                <TableHead>Remarks</TableHead>
                                <TableHead>Recommendation</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty v-if="activity.members.length === 0" :colspan="7">
                                No members assigned to this activity.
                            </TableEmpty>
                            <TableRow v-for="(member, idx) in activity.members" :key="member.id">
                                <TableCell class="text-center text-muted-foreground">{{ idx + 1 }}</TableCell>
                                <TableCell class="font-medium">{{ member.name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ member.caregiver || '—' }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ member.ph_id || '—' }}</TableCell>
                                <TableCell>
                                    <Multiselect
                                        v-if="attendances[idx]"
                                        v-model="attendances[idx].status"
                                        :options="attendanceStatuses.map((s) => ({ value: s.value, label: s.label }))"
                                        label="label"
                                        value-prop="value"
                                        :can-clear="false"
                                    />
                                </TableCell>
                                <TableCell>
                                    <textarea
                                        v-if="attendances[idx]"
                                        v-model="attendances[idx].remarks"
                                        data-attendance
                                        rows="1"
                                        class="w-full resize-none overflow-hidden rounded border border-input bg-background px-2 py-1 text-sm leading-relaxed"
                                        placeholder="Optional remarks"
                                        @input="(e) => { const t = e.target as HTMLTextAreaElement; t.style.height = 'auto'; t.style.height = t.scrollHeight + 'px'; }"
                                    />
                                </TableCell>
                                <TableCell>
                                    <textarea
                                        v-if="attendances[idx]"
                                        v-model="attendances[idx].recommendation"
                                        data-attendance
                                        rows="1"
                                        class="w-full resize-none overflow-hidden rounded border border-input bg-background px-2 py-1 text-sm leading-relaxed"
                                        placeholder="Optional recommendation"
                                        @input="(e) => { const t = e.target as HTMLTextAreaElement; t.style.height = 'auto'; t.style.height = t.scrollHeight + 'px'; }"
                                    />
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </template>
        </SheetContent>
    </Sheet>
</template>
