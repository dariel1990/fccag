<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import {
    create,
    destroy,
    edit,
    index,
} from '@/actions/App/Http/Controllers/MinistryController';
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

function deleteMinistry(ministry: Ministry) {
    if (confirm(`Are you sure you want to delete "${ministry.name}"?`)) {
        router.delete(destroy(ministry.id).url);
    }
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
                                        as-child
                                    >
                                        <Link :href="edit(ministry.id).url">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteMinistry(ministry)"
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
