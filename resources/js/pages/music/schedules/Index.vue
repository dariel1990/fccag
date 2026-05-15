<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import MonthScheduleGrid from '@/components/music/MonthScheduleGrid.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types/navigation';

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
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Music', href: '#' },
    { title: 'Schedules', href: '' },
];
</script>

<template>
    <Head title="Schedules" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <MonthScheduleGrid v-bind="props" />
    </AppLayout>
</template>
