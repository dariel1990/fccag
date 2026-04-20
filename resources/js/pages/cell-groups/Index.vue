<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy,
    index,
} from '@/actions/App/Http/Controllers/CellGroupController';
import CellGroupFormDialog from '@/components/cell-groups/CellGroupFormDialog.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
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

type CellGroup = {
    id: number;
    name: string;
    leader: string | null;
    description: string | null;
    is_active: boolean;
    people_count: number;
};

type Props = {
    cellGroups: CellGroup[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cell Groups',
        href: index().url,
    },
];

const formDialogOpen = ref(false);
const formCellGroup = ref<CellGroup | null>(null);

const deleteDialogOpen = ref(false);
const deleteCellGroup = ref<CellGroup | null>(null);

function openCreateDialog() {
    formCellGroup.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(cellGroup: CellGroup) {
    formCellGroup.value = cellGroup;
    formDialogOpen.value = true;
}

function openDeleteDialog(cellGroup: CellGroup) {
    deleteCellGroup.value = cellGroup;
    deleteDialogOpen.value = true;
}

function handleFormSaved() {
    router.reload();
}

function handleDeleteConfirm() {
    if (!deleteCellGroup.value) return;

    router.delete(destroy(deleteCellGroup.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteCellGroup.value = null;
        },
    });
}
</script>

<template>
    <Head title="Cell Groups" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Cell Groups"
                    description="Manage church cell groups"
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
                            <TableHead>Name</TableHead>
                            <TableHead>Leader</TableHead>
                            <TableHead class="text-center">People</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="props.cellGroups.length === 0"
                            :colspan="5"
                        >
                            No cell groups found.
                        </TableEmpty>
                        <TableRow
                            v-for="cellGroup in props.cellGroups"
                            :key="cellGroup.id"
                        >
                            <TableCell class="font-medium">
                                {{ cellGroup.name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ cellGroup.leader || '—' }}
                            </TableCell>
                            <TableCell class="text-center">
                                {{ cellGroup.people_count }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge
                                    :variant="
                                        cellGroup.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        cellGroup.is_active
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
                                        @click="openEditDialog(cellGroup)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openDeleteDialog(cellGroup)"
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

            <CellGroupFormDialog
                v-model:open="formDialogOpen"
                :cell-group="formCellGroup"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteCellGroup?.name"
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
