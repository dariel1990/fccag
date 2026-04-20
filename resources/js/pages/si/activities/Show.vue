<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { Save, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';
import { index } from '@/actions/App/Http/Controllers/Si/SiActivityController';
import { store as storeAttendance } from '@/actions/App/Http/Controllers/Si/SiAttendanceController';
import Heading from '@/components/Heading.vue';
import SiActivityFormDialog from '@/components/si/SiActivityFormDialog.vue';
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

type Category = { id: number; name: string };
type Member = { id: number; name: string };
type ChurchActivity = { id: number; title: string; activity_date: string };

type SiActivityData = {
    id: number;
    si_activity_category_id: number;
    activity_id: number | null;
    title: string;
    category: string | null;
    speaker: string | null;
    topic: string | null;
    memory_verse: string | null;
    conducted_at: string;
    member_ids: number[];
};

type MemberAttendance = {
    id: number;
    name: string;
    ph_id: string | null;
    caregiver: string | null;
    attendance: { status: string; remarks: string | null; recommendation: string | null } | null;
};

type AttendanceStatus = { value: string; label: string; color: string };

const props = defineProps<{
    siActivity: SiActivityData;
    members: MemberAttendance[];
    attendanceStatuses: AttendanceStatus[];
    categories: Category[];
    activeMembers: Member[];
    churchActivities: ChurchActivity[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'SI Activities', href: index().url },
    { title: props.siActivity.title },
];

const attendances = ref(
    props.members.map((m) => ({
        si_member_id: m.id,
        status: m.attendance?.status ?? 'absent',
        remarks: m.attendance?.remarks ?? '',
        recommendation: m.attendance?.recommendation ?? '',
    })),
);

const saving = ref(false);
const editDialogOpen = ref(false);

function saveAttendance() {
    saving.value = true;
    router.post(
        storeAttendance(props.siActivity.id).url,
        { attendances: attendances.value },
        {
            preserveScroll: true,
            onFinish: () => (saving.value = false),
        },
    );
}

const presentCount = () => attendances.value.filter((a) => a.status === 'present').length;
</script>

<template>
    <Head :title="`Attendance - ${siActivity.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="flex items-start justify-between">
                <Heading
                    :title="siActivity.title"
                    :description="`${siActivity.category ?? ''} · ${siActivity.conducted_at}`"
                />
                <Button variant="outline" @click="editDialogOpen = true">
                    <Pencil class="mr-2 h-4 w-4" />
                    Edit
                </Button>
            </div>

            <!-- Activity Meta -->
            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                <div v-if="siActivity.speaker" class="rounded-lg bg-muted/40 p-3">
                    <div class="text-xs text-muted-foreground">Speaker</div>
                    <div class="font-medium">{{ siActivity.speaker }}</div>
                </div>
                <div v-if="siActivity.topic" class="rounded-lg bg-muted/40 p-3">
                    <div class="text-xs text-muted-foreground">Topic</div>
                    <div class="font-medium">{{ siActivity.topic }}</div>
                </div>
                <div v-if="siActivity.memory_verse" class="col-span-2 rounded-lg bg-muted/40 p-3">
                    <div class="text-xs text-muted-foreground">Memory Verse</div>
                    <div class="font-medium">{{ siActivity.memory_verse }}</div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                    <div class="text-sm text-muted-foreground">
                        {{ presentCount() }}/{{ members.length }} present
                    </div>
                    <Button size="sm" :disabled="saving" @click="saveAttendance">
                        <Save class="mr-2 h-4 w-4" />
                        {{ saving ? 'Saving...' : 'Save Attendance' }}
                    </Button>
                </div>

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
                        <TableEmpty v-if="members.length === 0" :colspan="7">
                            No members assigned to this activity.
                        </TableEmpty>
                        <TableRow v-for="(member, idx) in members" :key="member.id">
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
        </div>

        <SiActivityFormDialog
            v-model:open="editDialogOpen"
            :activity="siActivity"
            :categories="categories"
            :members="activeMembers"
            :church-activities="churchActivities"
            @saved="router.reload()"
        />
    </AppLayout>
</template>
