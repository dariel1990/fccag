<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy,
    index,
} from '@/actions/App/Http/Controllers/MinistryController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import MinistryFormDialog from '@/components/ministries/MinistryFormDialog.vue';
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

type Ministry = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    people_count: number;
};

type Props = {
    ministries: Ministry[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ministries',
        href: index().url,
    },
];

const formDialogOpen = ref(false);
const formMinistry = ref<Ministry | null>(null);

const deleteDialogOpen = ref(false);
const deleteMinistry = ref<Ministry | null>(null);

function openCreateDialog() {
    formMinistry.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(ministry: Ministry) {
    formMinistry.value = ministry;
    formDialogOpen.value = true;
}

function openDeleteDialog(ministry: Ministry) {
    deleteMinistry.value = ministry;
    deleteDialogOpen.value = true;
}

function handleFormSaved() {
    router.reload();
}

function handleDeleteConfirm() {
    if (!deleteMinistry.value) return;

    router.delete(destroy(deleteMinistry.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteMinistry.value = null;
        },
    });
}
</script>

<template>
    <Head title="Ministries" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Ministries"
                    description="Manage church ministries"
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
                            <TableHead>Description</TableHead>
                            <TableHead class="text-center">Members</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="props.ministries.length === 0"
                            :colspan="5"
                        >
                            No ministries found.
                        </TableEmpty>
                        <TableRow
                            v-for="ministry in props.ministries"
                            :key="ministry.id"
                        >
                            <TableCell class="font-medium">
                                {{ ministry.name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ ministry.description || '—' }}
                            </TableCell>
                            <TableCell class="text-center">
                                {{ ministry.people_count }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge
                                    :variant="
                                        ministry.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        ministry.is_active
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
                                        @click="openEditDialog(ministry)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openDeleteDialog(ministry)"
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

            <MinistryFormDialog
                v-model:open="formDialogOpen"
                :ministry="formMinistry"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteMinistry?.name"
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
