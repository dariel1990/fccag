<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import {
    destroy as destroyScheduleRole,
    index as scheduleRolesIndex,
} from '@/actions/App/Http/Controllers/Music/ScheduleRoleController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import ScheduleRoleFormDialog from '@/components/music/ScheduleRoleFormDialog.vue';
import { Badge } from '@/components/ui/badge';
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

type ScheduleRole = {
    id: number;
    team: string;
    name: string;
    sort_order: number;
    assignments_count: number;
};

defineProps<{
    roles: ScheduleRole[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Schedule Roles',
        href: scheduleRolesIndex().url,
    },
];

const formDialogOpen = ref(false);
const formModel = ref<ScheduleRole | null>(null);

const deleteDialogOpen = ref(false);
const deleteTarget = ref<ScheduleRole | null>(null);

const deleteDescription = computed(() => {
    if (!deleteTarget.value) { return undefined; }
    const r = deleteTarget.value;
    if (r.assignments_count > 0) {
        return `Warning: "${r.name}" has ${r.assignments_count} existing assignment(s). Deleting it will remove this role from all schedules. Are you sure?`;
    }
    return `Are you sure you want to delete "${r.name}"?`;
});

function openCreateDialog(): void {
    formModel.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(role: ScheduleRole): void {
    formModel.value = role;
    formDialogOpen.value = true;
}

function openDeleteDialog(role: ScheduleRole): void {
    deleteTarget.value = role;
    deleteDialogOpen.value = true;
}

function handleFormSaved(): void {
    router.reload();
}

function handleDeleteConfirm(): void {
    if (!deleteTarget.value) { return; }

    router.delete(destroyScheduleRole(deleteTarget.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteTarget.value = null;
        },
    });
}

function teamLabel(team: string): string {
    return team.charAt(0).toUpperCase() + team.slice(1);
}
</script>

<template>
    <Head title="Schedule Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <Heading
                    title="Schedule Roles"
                    description="Manage roles available in band, media, and worship schedules"
                    no-margin
                />
                <Button class="w-full sm:w-auto sm:shrink-0" @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Role
                </Button>
            </div>

            <!-- Mobile card list -->
            <div class="space-y-2 md:hidden">
                <div
                    v-if="roles.length === 0"
                    class="text-muted-foreground rounded-xl border px-4 py-8 text-center text-sm"
                >
                    No roles found.
                </div>
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="flex items-center gap-3 rounded-xl border px-3 py-3"
                >
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium">{{ role.name }}</div>
                        <div class="text-muted-foreground mt-0.5 flex flex-wrap items-center gap-2 text-xs">
                            <Badge variant="outline" class="text-xs">{{ teamLabel(role.team) }}</Badge>
                            <span>· #{{ role.sort_order }}</span>
                            <span>· {{ role.assignments_count }} assignment(s)</span>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditDialog(role)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDeleteDialog(role)">
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
                            <TableHead>Team</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Sort order</TableHead>
                            <TableHead class="text-center">Assignments</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="roles.length === 0" colspan="5">
                            No roles found.
                        </TableEmpty>
                        <TableRow v-for="role in roles" :key="role.id">
                            <TableCell>
                                <Badge variant="outline">{{ teamLabel(role.team) }}</Badge>
                            </TableCell>
                            <TableCell class="font-medium">{{ role.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ role.sort_order }}</TableCell>
                            <TableCell class="text-center text-muted-foreground">
                                {{ role.assignments_count }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEditDialog(role)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openDeleteDialog(role)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <ScheduleRoleFormDialog
                v-model:open="formDialogOpen"
                :model="formModel"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteTarget?.name"
                :description="deleteDescription"
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
