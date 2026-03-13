<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import {
    create,
    destroy,
    edit,
    index,
} from '@/actions/App/Http/Controllers/PastorController';
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

type Pastor = {
    id: number;
    first_name: string;
    last_name: string;
    title: string | null;
    role: string | null;
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

function deletePastor(pastor: Pastor) {
    if (confirm(`Are you sure you want to delete "${fullName(pastor)}"?`)) {
        router.delete(destroy(pastor.id).url);
    }
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
                                        as-child
                                    >
                                        <Link :href="edit(pastor.id).url">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deletePastor(pastor)"
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
