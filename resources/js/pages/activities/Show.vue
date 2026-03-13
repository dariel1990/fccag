<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Save } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { ref, onMounted } from 'vue';
import { index } from '@/actions/App/Http/Controllers/ActivityController';
import { store as storeAttendance } from '@/actions/App/Http/Controllers/AttendanceController';
import Heading from '@/components/Heading.vue';
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
import { useIsMobile, useApiBaseUrl } from '@/composables/useDataSource';
import AppLayout from '@/layouts/AppLayout.vue';
import MobileLayout from '@/layouts/mobile/MobileLayout.vue';
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

const isMobile = useIsMobile();
const apiBaseUrl = useApiBaseUrl();

const participants = ref<ParticipantAttendance[]>(props.participants || []);
const isLoading = ref(false);

onMounted(async () => {
    if (isMobile) {
        const token = localStorage.getItem('auth_token');
        if (!token) return;

        isLoading.value = true;
        try {
            const response = await fetch(
                `${apiBaseUrl}/api/activities/${props.activity.id}/attendance`,
                {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: 'application/json',
                    },
                },
            );

            if (response.ok) {
                const data = await response.json();
                participants.value = data.participants.map(
                    (p: {
                        id: number;
                        full_name: string;
                        attendance: {
                            is_present: boolean;
                            remarks: string | null;
                        } | null;
                    }) => ({
                        id: p.id,
                        full_name: p.full_name,
                        is_present: p.attendance?.is_present ?? false,
                        remarks: p.attendance?.remarks ?? null,
                    }),
                );
            }
        } catch {
            // Silently fail
        } finally {
            isLoading.value = false;
        }
    }
});

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

// Re-initialize attendances when participants load for mobile
const initAttendances = () => {
    attendances.value = participants.value.map((p) => ({
        person_id: p.id,
        is_present: p.is_present,
        remarks: p.remarks ?? '',
    }));
};

// Watch for participants loading in mobile mode
if (isMobile) {
    const unwatch = ref<ReturnType<typeof setInterval> | null>(null);
    onMounted(() => {
        unwatch.value = setInterval(() => {
            if (
                participants.value.length > 0 &&
                attendances.value.length === 0
            ) {
                initAttendances();
                if (unwatch.value) clearInterval(unwatch.value);
            }
        }, 100);
        setTimeout(() => {
            if (unwatch.value) clearInterval(unwatch.value);
        }, 5000);
    });
}

const saving = ref(false);

function toggleAll(present: boolean) {
    attendances.value.forEach((a) => (a.is_present = present));
}

async function saveAttendance() {
    saving.value = true;

    if (isMobile) {
        const token = localStorage.getItem('auth_token');
        try {
            await fetch(
                `${apiBaseUrl}/api/activities/${props.activity.id}/attendance`,
                {
                    method: 'POST',
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ attendances: attendances.value }),
                },
            );
        } finally {
            saving.value = false;
        }
    } else {
        router.post(
            storeAttendance(props.activity.id).url,
            { attendances: attendances.value },
            {
                preserveScroll: true,
                onFinish: () => (saving.value = false),
            },
        );
    }
}

const presentCount = () => attendances.value.filter((a) => a.is_present).length;

const Layout = isMobile ? MobileLayout : AppLayout;
</script>

<template>
    <Head :title="`Attendance - ${activity.title}`" />

    <component :is="Layout" :breadcrumbs="isMobile ? undefined : breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-6"
            :class="isMobile ? 'pb-4' : 'p-4'"
        >
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

            <!-- Department filter notice -->
            <div v-if="activity.departments?.length" class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                <span>Filtered to:</span>
                <Badge v-for="dept in activity.departments" :key="dept" variant="outline">{{ dept }}</Badge>
            </div>

            <!-- Loading state for mobile -->
            <div v-if="isLoading" class="flex items-center justify-center py-8">
                <div class="text-muted-foreground">Loading attendance...</div>
            </div>

            <div
                v-else
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div
                    class="flex items-center border-b border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    :class="isMobile ? 'flex-col gap-3' : 'justify-between'"
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
                                No active members found in the assigned departments.
                            </span>
                            <span v-else>
                                No active participants found. Add participants first.
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
    </component>
</template>
