<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { ref } from 'vue';
import { index } from '@/actions/App/Http/Controllers/ActivityController';
import { store as storeAttendance } from '@/actions/App/Http/Controllers/AttendanceController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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

type Activity = {
    id: number;
    title: string;
    description: string | null;
    activity_type: string;
    activity_date: string;
    departments: string[];
};

type ParticipantAttendance = {
    id: number;
    full_name: string;
    is_present: boolean;
    remarks: string | null;
};

type Props = {
    activity: Activity;
    participants?: ParticipantAttendance[];
};

const props = defineProps<Props>();

const participants = ref<ParticipantAttendance[]>(props.participants || []);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activities',
        href: index().url,
    },
    {
        title: props.activity.title,
    },
];

const attendances = ref(
    props.participants
        ? props.participants.map((p) => ({
              person_id: p.id,
              is_present: p.is_present,
              remarks: p.remarks ?? '',
          }))
        : [],
);

const saving = ref(false);

function toggleAll(present: boolean) {
    attendances.value.forEach((a) => (a.is_present = present));
}

function saveAttendance() {
    saving.value = true;
    router.post(
        storeAttendance(props.activity.id).url,
        { attendances: attendances.value },
        {
            preserveScroll: true,
            onFinish: () => (saving.value = false),
        },
    );
}

const presentCount = () => attendances.value.filter((a) => a.is_present).length;
</script>

<template>
    <Head :title="`Attendance - ${activity.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    :title="activity.title"
                    :description="`${activity.activity_type} &middot; ${activity.activity_date}`"
                />
            </div>

            <div
                v-if="activity.description"
                class="text-sm text-muted-foreground"
            >
                {{ activity.description }}
            </div>

            <div
                v-if="activity.departments?.length"
                class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
            >
                <span>Filtered to:</span>
                <Badge
                    v-for="dept in activity.departments"
                    :key="dept"
                    variant="outline"
                    >{{ dept }}</Badge
                >
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div
                    class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="text-sm text-muted-foreground">
                        {{ presentCount() }}/{{ participants.length }} present
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="toggleAll(true)"
                        >
                            All Present
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="toggleAll(false)"
                        >
                            All Absent
                        </Button>
                        <Button
                            size="sm"
                            :disabled="saving"
                            @click="saveAttendance"
                        >
                            <Save class="mr-2 h-4 w-4" />
                            {{ saving ? 'Saving...' : 'Save' }}
                        </Button>
                    </div>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-16 text-center">#</TableHead>
                            <TableHead>Participant</TableHead>
                            <TableHead class="w-24 text-center">
                                Present
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="participants.length === 0"
                            :colspan="3"
                        >
                            <span v-if="activity.departments?.length">
                                No active members found in the assigned
                                departments.
                            </span>
                            <span v-else>
                                No active participants found. Add participants
                                first.
                            </span>
                        </TableEmpty>
                        <TableRow
                            v-for="(participant, idx) in participants"
                            :key="participant.id"
                        >
                            <TableCell
                                class="text-center text-muted-foreground"
                            >
                                {{ idx + 1 }}
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ participant.full_name }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Checkbox
                                    v-if="attendances[idx]"
                                    :model-value="attendances[idx].is_present"
                                    @update:model-value="
                                        attendances[idx].is_present =
                                            $event as boolean
                                    "
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
