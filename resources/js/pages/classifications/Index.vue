<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import {
    create,
    destroy,
    edit,
    index,
} from '@/actions/App/Http/Controllers/ClassificationController';
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

function deleteClassification(classification: Classification) {
    if (
        confirm(
            `Are you sure you want to delete "${classification.name}"?`,
        )
    ) {
        router.delete(destroy(classification.id).url);
    }
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
                <Button as-child>
                    <Link :href="create().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Add
                    </Link>
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
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                edit(classification.id).url
                                            "
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="
                                            deleteClassification(classification)
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
        </div>
    </AppLayout>
</template>
