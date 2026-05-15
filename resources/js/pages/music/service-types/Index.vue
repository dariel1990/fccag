<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy as destroyServiceType,
    index as serviceTypesIndex,
} from '@/actions/App/Http/Controllers/Music/ServiceTypeController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import ServiceTypeFormDialog from '@/components/music/ServiceTypeFormDialog.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const DAY_LABELS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

type ServiceType = {
    id: number;
    name: string;
    day_of_week: number | null;
    color: string | null;
    sort_order: number;
};

defineProps<{
    serviceTypes: ServiceType[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Service Types',
        href: serviceTypesIndex().url,
    },
];

const formDialogOpen = ref(false);
const formModel = ref<ServiceType | null>(null);

const deleteDialogOpen = ref(false);
const deleteTarget = ref<ServiceType | null>(null);

function openCreateDialog(): void {
    formModel.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(serviceType: ServiceType): void {
    formModel.value = serviceType;
    formDialogOpen.value = true;
}

function openDeleteDialog(serviceType: ServiceType): void {
    deleteTarget.value = serviceType;
    deleteDialogOpen.value = true;
}

function handleFormSaved(): void {
    router.reload();
}

function handleDeleteConfirm(): void {
    if (!deleteTarget.value) { return; }

    router.delete(destroyServiceType(deleteTarget.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteTarget.value = null;
        },
    });
}

function dayLabel(day: number | null): string {
    return day === null ? 'Any day' : (DAY_LABELS[day] ?? '—');
}
</script>

<template>
    <Head title="Service Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <Heading
                    title="Service Types"
                    description="Manage the service types used in schedules"
                    no-margin
                />
                <Button class="w-full sm:w-auto sm:shrink-0" @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Service Type
                </Button>
            </div>

            <!-- Mobile card list -->
            <div class="space-y-2 md:hidden">
                <div
                    v-if="serviceTypes.length === 0"
                    class="text-muted-foreground rounded-xl border px-4 py-8 text-center text-sm"
                >
                    No service types found.
                </div>
                <div
                    v-for="serviceType in serviceTypes"
                    :key="serviceType.id"
                    class="flex items-center gap-3 rounded-xl border px-3 py-3"
                >
                    <span
                        class="inline-block h-4 w-4 shrink-0 rounded-full border"
                        :style="{ backgroundColor: serviceType.color ?? 'transparent' }"
                    />
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium">{{ serviceType.name }}</div>
                        <div class="text-muted-foreground mt-0.5 flex flex-wrap items-center gap-2 text-xs">
                            <span>{{ dayLabel(serviceType.day_of_week) }}</span>
                            <span>· #{{ serviceType.sort_order }}</span>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditDialog(serviceType)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDeleteDialog(serviceType)">
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden rounded-xl border border-sidebar-border/70 md:block dark:border-sidebar-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Day of week</TableHead>
                            <TableHead>Color</TableHead>
                            <TableHead>Sort order</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="serviceTypes.length === 0" colspan="5">
                            No service types found.
                        </TableEmpty>
                        <TableRow v-for="serviceType in serviceTypes" :key="serviceType.id">
                            <TableCell class="font-medium">{{ serviceType.name }}</TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ dayLabel(serviceType.day_of_week) }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-block h-4 w-4 rounded-full border"
                                        :style="{ backgroundColor: serviceType.color ?? 'transparent' }"
                                    />
                                    <span class="text-muted-foreground text-xs">{{ serviceType.color || '—' }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ serviceType.sort_order }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEditDialog(serviceType)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openDeleteDialog(serviceType)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <ServiceTypeFormDialog
                v-model:open="formDialogOpen"
                :model="formModel"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteTarget?.name"
                :description="
                    deleteTarget
                        ? `Are you sure you want to delete &quot;${deleteTarget.name}&quot;? Schedules using this service type will be deleted too.`
                        : undefined
                "
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
