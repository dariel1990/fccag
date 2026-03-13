<script setup lang="ts">
import { ref, watch } from 'vue';
import { Check, X, TrendingUp } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Skeleton } from '@/components/ui/skeleton';
import { useIsMobile, useApiBaseUrl } from '@/composables/useDataSource';

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

const props = defineProps<{
    open: boolean;
    participantId: number | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isMobile = useIsMobile();
const apiBaseUrl = useApiBaseUrl();

const participantData = ref<Participant | null>(null);
const attendanceData = ref<AttendanceRecord[]>([]);
const isLoading = ref(false);

async function fetchParticipant(id: number) {
    isLoading.value = true;
    try {
        let response: Response;

        if (isMobile) {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '/mobile/login';
                return;
            }
            response = await fetch(`${apiBaseUrl}/api/participants/${id}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            });
            if (response.status === 401) {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('auth_user');
                window.location.href = '/mobile/login';
                return;
            }
        } else {
            response = await fetch(`/participants/${id}`, {
                credentials: 'include',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        }

        if (response.ok) {
            const data = await response.json();
            participantData.value = data.data;
            attendanceData.value = data.attendance_history || [];
        }
    } catch {
        // Silently fail
    } finally {
        isLoading.value = false;
    }
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen && props.participantId) {
            fetchParticipant(props.participantId);
        } else {
            participantData.value = null;
            attendanceData.value = [];
        }
    },
);

watch(
    () => props.participantId,
    (newId) => {
        if (props.open && newId) {
            fetchParticipant(newId);
        }
    },
);

const attendanceStats = {
    get total() {
        return attendanceData.value.length;
    },
    get present() {
        return attendanceData.value.filter((r) => r.is_present).length;
    },
    get absent() {
        return this.total - this.present;
    },
    get percentage() {
        return this.total > 0
            ? Math.round((this.present / this.total) * 100)
            : 0;
    },
};

function handleClose() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent
            class="max-h-[90vh] w-[95vw] max-w-md overflow-y-auto sm:max-w-2xl"
        >
            <DialogHeader class="pb-3">
                <DialogTitle class="text-lg">
                    {{
                        isLoading
                            ? 'Loading...'
                            : participantData?.full_name ||
                              'Participant Details'
                    }}
                </DialogTitle>
                <DialogDescription class="text-sm">
                    View participant profile and attendance history
                </DialogDescription>
            </DialogHeader>

            <!-- Loading State -->
            <div v-if="isLoading" class="space-y-3">
                <div class="space-y-1.5">
                    <Skeleton class="h-4 w-40" />
                    <Skeleton class="h-3 w-20" />
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <Skeleton class="h-14 sm:h-16" />
                    <Skeleton class="h-14 sm:h-16" />
                    <Skeleton class="h-14 sm:h-16" />
                </div>
                <Skeleton class="h-24 sm:h-32" />
            </div>

            <!-- Content -->
            <div v-else-if="participantData" class="space-y-4">
                <!-- Attendance Stats -->
                <div class="grid grid-cols-3 gap-3">
                    <Card class="bg-primary/5">
                        <CardContent class="flex flex-col items-center justify-center p-3">
                            <TrendingUp class="mb-1 h-4 w-4 text-primary" />
                            <p class="text-lg font-bold">
                                {{ attendanceStats.percentage }}%
                            </p>
                            <p class="text-xs text-muted-foreground">Attendance</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="flex flex-col items-center justify-center p-3">
                            <Check class="mb-1 h-4 w-4 text-green-600" />
                            <p class="text-lg font-bold">
                                {{ attendanceStats.present }}
                            </p>
                            <p class="text-xs text-muted-foreground">Present</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="flex flex-col items-center justify-center p-3">
                            <X class="mb-1 h-4 w-4 text-red-500" />
                            <p class="text-lg font-bold">
                                {{ attendanceStats.absent }}
                            </p>
                            <p class="text-xs text-muted-foreground">Absent</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Personal Information -->
                <div class="rounded-lg border border-border p-4">
                    <h3 class="mb-3 text-sm font-semibold">Personal Information</h3>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <p class="text-xs text-muted-foreground">Gender</p>
                            <p class="capitalize">{{ participantData.gender }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Birthday</p>
                            <p>{{ participantData.birthday || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Contact</p>
                            <p class="truncate">{{ participantData.contact_number || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Address</p>
                            <p class="truncate">{{ participantData.address || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Cell Group</p>
                            <p class="truncate">{{ participantData.cell_group || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Ministry</p>
                            <p class="truncate">{{ participantData.ministry || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Date Joined</p>
                            <p>{{ participantData.date_joined }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Status</p>
                            <Badge
                                :variant="participantData.is_active ? 'default' : 'secondary'"
                            >
                                {{ participantData.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Attendance History -->
                <div class="rounded-lg border border-border p-4">
                    <h3 class="mb-3 text-sm font-semibold">Attendance History</h3>
                    <div
                        v-if="attendanceData.length === 0"
                        class="py-4 text-center text-sm text-muted-foreground"
                    >
                        No attendance records yet.
                    </div>
                    <div v-else class="max-h-48 space-y-2 overflow-y-auto">
                        <div
                            v-for="record in attendanceData"
                            :key="record.id"
                            class="flex items-center justify-between rounded border border-border p-2.5"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {{ record.title }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ record.activity_date }} • {{ record.activity_type }}
                                </p>
                            </div>
                            <Badge
                                :variant="record.is_present ? 'default' : 'destructive'"
                                class="ml-2 shrink-0"
                            >
                                {{ record.is_present ? 'Present' : 'Absent' }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
