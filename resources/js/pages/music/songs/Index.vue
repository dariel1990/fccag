<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy as destroySong,
    index as songsIndex,
    show as showSong,
} from '@/actions/App/Http/Controllers/Music/SongController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import SongFormDialog from '@/components/music/SongFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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

type Song = {
    id: number;
    title: string;
    artist: string | null;
    original_key: string;
    tempo: number | null;
    time_signature: string | null;
    is_active: boolean;
};

const props = defineProps<{
    songs: Song[];
    filters: { search?: string; key?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Songs',
        href: songsIndex().url,
    },
];

const formDialogOpen = ref(false);
const formSong = ref<Song | null>(null);

const deleteDialogOpen = ref(false);
const deleteTarget = ref<Song | null>(null);

const search = ref(props.filters.search ?? '');

function openCreateDialog(): void {
    formSong.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(song: Song): void {
    formSong.value = song;
    formDialogOpen.value = true;
}

function openDeleteDialog(song: Song): void {
    deleteTarget.value = song;
    deleteDialogOpen.value = true;
}

function handleFormSaved(): void {
    router.reload();
}

function handleDeleteConfirm(): void {
    if (!deleteTarget.value) { return; }

    router.delete(destroySong(deleteTarget.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteTarget.value = null;
        },
    });
}

function applyFilters(): void {
    router.get(
        songsIndex().url,
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Songs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <Heading
                    title="Songs"
                    description="Manage your chord sheets and song library"
                    no-margin
                />
                <Button class="w-full sm:w-auto sm:shrink-0" @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Song
                </Button>
            </div>

            <Input
                v-model="search"
                class="w-full sm:max-w-xs"
                placeholder="Search songs..."
                @keyup.enter="applyFilters"
            />


            <!-- Mobile card list -->
            <div class="space-y-2 md:hidden">
                <div v-if="songs.length === 0" class="text-muted-foreground rounded-xl border px-4 py-8 text-center text-sm">
                    No songs found.
                </div>
                <div
                    v-for="song in songs"
                    :key="song.id"
                    class="flex items-center gap-3 rounded-xl border px-3 py-3"
                >
                    <div class="min-w-0 flex-1">
                        <a :href="showSong(song.id).url" class="truncate font-medium hover:underline">{{ song.title }}</a>
                        <div class="text-muted-foreground mt-0.5 flex flex-wrap items-center gap-2 text-xs">
                            <span>{{ song.artist || '—' }}</span>
                            <Badge variant="outline" class="text-xs">{{ song.original_key }}</Badge>
                            <Badge :variant="song.is_active ? 'default' : 'secondary'" class="text-xs">
                                {{ song.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditDialog(song)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDeleteDialog(song)">
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
                            <TableHead>Artist</TableHead>
                            <TableHead>Key</TableHead>
                            <TableHead>Tempo</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="songs.length === 0" colspan="6">
                            No songs found.
                        </TableEmpty>
                        <TableRow v-for="song in songs" :key="song.id">
                            <TableCell class="font-medium">
                                <a :href="showSong(song.id).url" class="hover:underline">{{ song.title }}</a>
                            </TableCell>
                            <TableCell class="text-muted-foreground">{{ song.artist || '—' }}</TableCell>
                            <TableCell>
                                <Badge variant="outline">{{ song.original_key }}</Badge>
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ song.tempo ? `${song.tempo} BPM` : '—' }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="song.is_active ? 'default' : 'secondary'">
                                    {{ song.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEditDialog(song)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openDeleteDialog(song)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <SongFormDialog
                v-model:open="formDialogOpen"
                :song="formSong"
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
