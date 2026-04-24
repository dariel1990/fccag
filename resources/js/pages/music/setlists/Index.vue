<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy as destroySetlist,
    index as setlistsIndex,
    show as showSetlist,
} from '@/actions/App/Http/Controllers/Music/SetlistController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import SetlistFormDialog from '@/components/music/SetlistFormDialog.vue';
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

type Setlist = {
    id: number;
    title: string;
    service_date: string;
    theme: string | null;
    status: string;
    songs_count: number;
};

const props = defineProps<{
    setlists: Setlist[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Setlists',
        href: setlistsIndex().url,
    },
];

const formDialogOpen = ref(false);
const formSetlist = ref<Setlist | null>(null);

const deleteDialogOpen = ref(false);
const deleteTarget = ref<Setlist | null>(null);

function openCreateDialog(): void {
    formSetlist.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(setlist: Setlist): void {
    formSetlist.value = setlist;
    formDialogOpen.value = true;
}

function openDeleteDialog(setlist: Setlist): void {
    deleteTarget.value = setlist;
    deleteDialogOpen.value = true;
}

function handleFormSaved(): void {
    router.reload();
}

function handleDeleteConfirm(): void {
    if (!deleteTarget.value) {
        return;
    }

    router.delete(destroySetlist(deleteTarget.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteTarget.value = null;
        },
    });
}

function statusVariant(status: string): 'default' | 'secondary' | 'outline' {
    if (status === 'published') {
        return 'default';
    }
    if (status === 'archived') {
        return 'outline';
    }
    return 'secondary';
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Setlists" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <Heading
                    title="Setlists"
                    description="Plan and manage your worship service setlists"
                    no-margin
                />
                <Button class="w-full sm:w-auto sm:shrink-0" @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    New Setlist
                </Button>
            </div>

            <!-- Mobile card list -->
            <div class="space-y-2 md:hidden">
                <div v-if="setlists.length === 0" class="text-muted-foreground rounded-xl border px-4 py-8 text-center text-sm">
                    No setlists found.
                </div>
                <div
                    v-for="setlist in setlists"
                    :key="setlist.id"
                    class="flex items-center gap-3 rounded-xl border px-3 py-3"
                >
                    <div class="min-w-0 flex-1">
                        <a :href="showSetlist(setlist.id).url" class="truncate font-medium hover:underline">{{ setlist.title }}</a>
                        <div class="text-muted-foreground mt-0.5 flex flex-wrap items-center gap-2 text-xs">
                            <span>{{ formatDate(setlist.service_date) }}</span>
                            <span v-if="setlist.theme">· {{ setlist.theme }}</span>
                            <Badge :variant="statusVariant(setlist.status)" class="capitalize text-xs">{{ setlist.status }}</Badge>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditDialog(setlist)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDeleteDialog(setlist)">
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden rounded-xl border border-sidebar-border/70 md:block dark:border-sidebar-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Title</TableHead>
                            <TableHead>Service Date</TableHead>
                            <TableHead>Theme</TableHead>
                            <TableHead class="text-center">Songs</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="setlists.length === 0" colspan="6">
                            No setlists found.
                        </TableEmpty>
                        <TableRow v-for="setlist in setlists" :key="setlist.id">
                            <TableCell class="font-medium">
                                <a :href="showSetlist(setlist.id).url" class="hover:underline">{{ setlist.title }}</a>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ formatDate(setlist.service_date) }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ setlist.theme || '—' }}</TableCell>
                            <TableCell class="text-center text-muted-foreground">{{ setlist.songs_count }}</TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="statusVariant(setlist.status)" class="capitalize">{{ setlist.status }}</Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEditDialog(setlist)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openDeleteDialog(setlist)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <SetlistFormDialog
                v-model:open="formDialogOpen"
                :setlist="formSetlist"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteTarget?.title"
                :description="
                    deleteTarget
                        ? `Are you sure you want to delete &quot;${deleteTarget.title}&quot;?`
                        : undefined
                "
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
