<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy,
    index,
} from '@/actions/App/Http/Controllers/ClassificationController';
import ClassificationFormDialog from '@/components/classifications/ClassificationFormDialog.vue';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
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

type Classification = {
    id: number;
    name: string;
    code: string;
    description: string | null;
    people_count: number;
};

type Props = {
    classifications: Classification[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Classifications',
        href: index().url,
    },
];

const formDialogOpen = ref(false);
const formClassification = ref<Classification | null>(null);

const deleteDialogOpen = ref(false);
const deleteClassification = ref<Classification | null>(null);

function openCreateDialog() {
    formClassification.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(classification: Classification) {
    formClassification.value = classification;
    formDialogOpen.value = true;
}

function openDeleteDialog(classification: Classification) {
    deleteClassification.value = classification;
    deleteDialogOpen.value = true;
}

function handleFormSaved() {
    router.reload();
}

function handleDeleteConfirm() {
    if (!deleteClassification.value) return;

    router.delete(destroy(deleteClassification.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteClassification.value = null;
        },
    });
}
</script>

<template>
    <Head title="Classifications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Classifications"
                    description="Manage member classifications"
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
                            <TableHead>Code</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead class="text-center">People</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="props.classifications.length === 0"
                            :colspan="5"
                        >
                            No classifications found.
                        </TableEmpty>
                        <TableRow
                            v-for="classification in props.classifications"
                            :key="classification.id"
                        >
                            <TableCell class="font-medium">
                                {{ classification.name }}
                            </TableCell>
                            <TableCell>
                                <span class="font-mono text-sm">{{
                                    classification.code
                                }}</span>
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ classification.description || '—' }}
                            </TableCell>
                            <TableCell class="text-center">
                                {{ classification.people_count }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(classification)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="
                                            openDeleteDialog(classification)
                                        "
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

            <ClassificationFormDialog
                v-model:open="formDialogOpen"
                :classification="formClassification"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteClassification?.name"
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
