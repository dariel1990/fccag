<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Check, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import {
    index,
    edit,
} from '@/actions/App/Http/Controllers/ParticipantController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
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

type Participant = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    gender: string;
    birthday: string | null;
    contact_number: string | null;
    address: string | null;
    cell_group: string | null;
    ministry: string | null;
    date_joined: string;
    is_active: boolean;
};

type AttendanceRecord = {
    id: number;
    title: string;
    activity_type: string;
    activity_date: string;
    is_present: boolean;
    remarks: string | null;
};

type Props = {
    participant?: Participant;
    attendanceHistory?: AttendanceRecord[];
};

const props = defineProps<Props>();

const participantData = ref<Participant | null>(props.participant || null);
const attendanceData = ref<AttendanceRecord[]>(
    props.attendanceHistory || [],
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'People of God',
        href: index().url,
    },
    {
        title: participantData.value?.full_name || 'Details',
    },
];

const currentParticipant = computed(
    () => participantData.value || props.participant,
);
const currentAttendance = computed(
    () => attendanceData.value || props.attendanceHistory || [],
);
</script>

<template>
    <Head :title="currentParticipant?.full_name || 'Loading...'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div
                v-if="currentParticipant"
                class="flex items-center justify-between"
            >
                <Heading
                    :title="currentParticipant.full_name"
                    description="Participant profile and attendance history"
                />
                <Button as-child>
                    <Link :href="edit(currentParticipant.id).url">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                </Button>
            </div>

            <!-- Profile Card -->
            <div
                v-if="currentParticipant"
                class="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <h3 class="mb-4 text-lg font-semibold">Personal Information</h3>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div>
                        <p class="text-sm text-muted-foreground">Gender</p>
                        <p class="capitalize">
                            {{ currentParticipant.gender }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Birthday</p>
                        <p>{{ currentParticipant.birthday || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Contact</p>
                        <p>{{ currentParticipant.contact_number || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Address</p>
                        <p>{{ currentParticipant.address || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Cell Group</p>
                        <p>{{ currentParticipant.cell_group || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Ministry</p>
                        <p>{{ currentParticipant.ministry || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Date Joined</p>
                        <p>{{ currentParticipant.date_joined }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Status</p>
                        <Badge
                            :variant="
                                currentParticipant.is_active
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{
                                currentParticipant.is_active
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </Badge>
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div class="p-6 pb-0">
                    <h3 class="text-lg font-semibold">Attendance History</h3>
                    <p class="text-sm text-muted-foreground">
                        All recorded attendance for this participant
                    </p>
                </div>
                <div class="p-2">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Activity Type</TableHead>
                                <TableHead>Activity</TableHead>
                                <TableHead class="text-center"
                                    >Present</TableHead
                                >
                                <TableHead>Remarks</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty
                                v-if="currentAttendance.length === 0"
                                :colspan="5"
                            >
                                No attendance records yet.
                            </TableEmpty>
                            <TableRow
                                v-for="record in currentAttendance"
                                :key="record.id"
                            >
                                <TableCell>{{
                                    record.activity_date
                                }}</TableCell>
                                <TableCell>{{
                                    record.activity_type
                                }}</TableCell>
                                <TableCell>{{ record.title }}</TableCell>
                                <TableCell class="text-center">
                                    <Check
                                        v-if="record.is_present"
                                        class="mx-auto h-4 w-4 text-green-600"
                                    />
                                    <X
                                        v-else
                                        class="mx-auto h-4 w-4 text-red-500"
                                    />
                                </TableCell>
                                <TableCell class="text-muted-foreground">{{
                                    record.remarks || '—'
                                }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
