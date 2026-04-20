<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy,
    index,
    show,
} from '@/actions/App/Http/Controllers/DepartmentController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import DepartmentFormDialog from '@/components/departments/DepartmentFormDialog.vue';
import Heading from '@/components/Heading.vue';
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

type Department = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    officers_count: number;
    members_count: number;
    photo_url: string | null;
};

type Props = {
    departments: Department[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Departments',
        href: index().url,
    },
];

const formDialogOpen = ref(false);
const formDepartment = ref<Department | null>(null);

const deleteDialogOpen = ref(false);
const deleteDepartment = ref<Department | null>(null);

function openCreateDialog() {
    formDepartment.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(department: Department) {
    formDepartment.value = department;
    formDialogOpen.value = true;
}

function openDeleteDialog(department: Department) {
    deleteDepartment.value = department;
    deleteDialogOpen.value = true;
}

function handleFormSaved() {
    router.reload();
}

function handleDeleteConfirm() {
    if (!deleteDepartment.value) return;

    router.delete(destroy(deleteDepartment.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteDepartment.value = null;
        },
    });
}
</script>

<template>
    <Head title="Departments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Departments"
                    description="Manage church departments"
                />
                <Button @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add
                </Button>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-12">Logo</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead class="text-center">Members</TableHead>
                            <TableHead class="text-center">Officers</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="props.departments.length === 0"
                            :colspan="6"
                        >
                            No departments found.
                        </TableEmpty>
                        <TableRow
                            v-for="department in props.departments"
                            :key="department.id"
                        >
                            <TableCell>
                                <img
                                    v-if="department.photo_url"
                                    :src="department.photo_url"
                                    :alt="department.name"
                                    class="h-8 w-8 rounded-full object-cover"
                                />
                                <div
                                    v-else
                                    class="bg-muted h-8 w-8 rounded-full"
                                />
                            </TableCell>
                            <TableCell class="font-medium">
                                <Link
                                    :href="show(department.id).url"
                                    class="hover:underline"
                                >
                                    {{ department.name }}
                                </Link>
                            </TableCell>
                            <TableCell class="text-center">
                                {{ department.members_count }}
                            </TableCell>
                            <TableCell class="text-center">
                                {{ department.officers_count }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge
                                    :variant="
                                        department.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        department.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(department)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openDeleteDialog(department)"
                                    >
                                        <Trash2
                                            class="h-4 w-4 text-destructive"
                                        />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <DepartmentFormDialog
                v-model:open="formDialogOpen"
                :department="formDepartment"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteDepartment?.name"
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
