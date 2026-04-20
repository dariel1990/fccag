<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { index, destroy } from '@/actions/App/Http/Controllers/Si/SiActivityCategoryController';
import Heading from '@/components/Heading.vue';
import SiActivityCategoryFormDialog from '@/components/si/SiActivityCategoryFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableEmpty,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Category = {
    id: number;
    name: string;
    weight: number;
    is_active: boolean;
    si_activities_count: number;
};

defineProps<{ categories: Category[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'SI Activity Categories', href: index().url }];

const formDialogOpen = ref(false);
const formCategory = ref<Category | null>(null);

function openCreateDialog() {
    formCategory.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(category: Category) {
    formCategory.value = category;
    formDialogOpen.value = true;
}

function deleteCategory(category: Category) {
    if (confirm(`Delete "${category.name}"? This will remove all associated SI activities.`)) {
        router.delete(destroy(category.id).url);
    }
}
</script>

<template>
    <Head title="SI Activity Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading title="SI Activity Categories" description="Manage scoring categories and their weights" />
                <Button @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Category
                </Button>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead class="text-center">Weight</TableHead>
                            <TableHead class="text-center">Activities</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="categories.length === 0" :colspan="5">
                            No categories yet.
                        </TableEmpty>
                        <TableRow v-for="category in categories" :key="category.id">
                            <TableCell class="font-medium">{{ category.name }}</TableCell>
                            <TableCell class="text-center">{{ (category.weight * 100).toFixed(2) }}%</TableCell>
                            <TableCell class="text-center">{{ category.si_activities_count }}</TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="category.is_active ? 'default' : 'secondary'">
                                    {{ category.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEditDialog(category)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="deleteCategory(category)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <SiActivityCategoryFormDialog
            v-model:open="formDialogOpen"
            :category="formCategory"
            @saved="router.reload()"
        />
    </AppLayout>
</template>
