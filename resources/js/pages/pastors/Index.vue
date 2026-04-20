<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy,
    index,
} from '@/actions/App/Http/Controllers/PastorController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import PastorFormDialog from '@/components/pastors/PastorFormDialog.vue';
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

type Pastor = {
    id: number;
    first_name: string;
    last_name: string;
    title: string | null;
    role: string | null;
    bio: string | null;
    contact_number: string | null;
    email: string | null;
    date_started: string | null;
    is_active: boolean;
    photo_url: string | null;
};

type Props = {
    pastors: Pastor[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pastors',
        href: index().url,
    },
];

function fullName(pastor: Pastor): string {
    const parts = [pastor.title, pastor.first_name, pastor.last_name].filter(
        Boolean,
    );
    return parts.join(' ');
}

const formDialogOpen = ref(false);
const formPastor = ref<Pastor | null>(null);

const deleteDialogOpen = ref(false);
const deletePastor = ref<Pastor | null>(null);

function openCreateDialog() {
    formPastor.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(pastor: Pastor) {
    formPastor.value = pastor;
    formDialogOpen.value = true;
}

function openDeleteDialog(pastor: Pastor) {
    deletePastor.value = pastor;
    deleteDialogOpen.value = true;
}

function handleFormSaved() {
    router.reload();
}

function handleDeleteConfirm() {
    if (!deletePastor.value) return;

    router.delete(destroy(deletePastor.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deletePastor.value = null;
        },
    });
}
</script>

<template>
    <Head title="Pastors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Pastors"
                    description="Manage church pastors and leaders"
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
                            <TableHead class="w-12">Photo</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="props.pastors.length === 0"
                            :colspan="5"
                        >
                            No pastors found.
                        </TableEmpty>
                        <TableRow
                            v-for="pastor in props.pastors"
                            :key="pastor.id"
                        >
                            <TableCell>
                                <img
                                    v-if="pastor.photo_url"
                                    :src="pastor.photo_url"
                                    :alt="fullName(pastor)"
                                    class="h-8 w-8 rounded-full object-cover"
                                />
                                <div
                                    v-else
                                    class="bg-muted h-8 w-8 rounded-full"
                                />
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ fullName(pastor) }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ pastor.role || '—' }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge
                                    :variant="
                                        pastor.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        pastor.is_active ? 'Active' : 'Inactive'
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
                                        @click="openEditDialog(pastor)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openDeleteDialog(pastor)"
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

            <PastorFormDialog
                v-model:open="formDialogOpen"
                :pastor="formPastor"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="
                    deletePastor ? fullName(deletePastor) : undefined
                "
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
