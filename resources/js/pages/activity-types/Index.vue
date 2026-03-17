<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    index,
    create,
    edit,
    destroy,
} from '@/actions/App/Http/Controllers/ActivityTypeController';
import Heading from '@/components/Heading.vue';
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

type ActivityType = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    activities_count: number;
    departments: { id: number; name: string }[];
};

type Props = {
    activityTypes?: ActivityType[];
};

const props = defineProps<Props>();

const activityTypes = ref<ActivityType[]>(props.activityTypes || []);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activity Types',
        href: index().url,
    },
];

function deleteActivityType(activityType: ActivityType) {
    if (
        confirm(
            `Are you sure you want to delete "${activityType.name}"? This will also delete all associated activities and attendance records.`,
        )
    ) {
        router.delete(destroy(activityType.id).url);
    }
}
</script>

<template>
    <Head title="Activity Types" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Activity Types"
                    description="Manage the types of church activities"
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
                            <TableHead>Departments</TableHead>
                            <TableHead class="text-center"
                                >Activities</TableHead
                            >
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty
                            v-if="activityTypes.length === 0"
                            colspan="6"
                        >
                            No activity types found.
                        </TableEmpty>
                        <TableRow
                            v-for="activityType in activityTypes"
                            :key="activityType.id"
                        >
                            <TableCell class="font-medium">
                                {{ activityType.name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ activityType.description || '—' }}
                            </TableCell>
                            <TableCell>
                                <span
                                    v-if="activityType.departments?.length"
                                    class="flex flex-wrap gap-1"
                                >
                                    <Badge
                                        v-for="dept in activityType.departments"
                                        :key="dept.id"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        {{ dept.name }}
                                    </Badge>
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                    >All members</span
                                >
                            </TableCell>
                            <TableCell class="text-center">
                                {{ activityType.activities_count || 0 }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge
                                    :variant="
                                        activityType.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        activityType.is_active
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
                                        <Link :href="edit(activityType.id).url">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="
                                            deleteActivityType(activityType)
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
