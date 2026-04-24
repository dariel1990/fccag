<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Play } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import {
    index as setlistsIndex,
    live as setlistLive,
    show as showSetlist,
    update as updateSetlist,
} from '@/actions/App/Http/Controllers/Music/SetlistController';
import { reorder as reorderSetlistSongs } from '@/actions/App/Http/Controllers/Music/SetlistSongController';
import Heading from '@/components/Heading.vue';
import SetlistFormDialog from '@/components/music/SetlistFormDialog.vue';
import SetlistSongRow from '@/components/music/SetlistSongRow.vue';
import SongPickerDialog from '@/components/music/SongPickerDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type SetlistSong = {
    id: number;
    title: string;
    artist: string | null;
    original_key: string;
    pivot_order: number;
    pivot_key_override: string | null;
    pivot_notes: string | null;
};

type AvailableSong = {
    id: number;
    title: string;
    artist: string | null;
    original_key: string;
};

type Setlist = {
    id: number;
    title: string;
    service_date: string;
    theme: string | null;
    notes: string | null;
    status: string;
    songs: SetlistSong[];
};

const props = defineProps<{
    setlist: Setlist;
    availableSongs: AvailableSong[];
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Setlists', href: setlistsIndex().url },
    { title: props.setlist.title, href: showSetlist(props.setlist.id).url },
]);

const editDialogOpen = ref(false);
const songPickerOpen = ref(false);

const localSongs = ref([...props.setlist.songs].sort((a, b) => a.pivot_order - b.pivot_order));
watch(() => props.setlist, (setlist) => {
    if (dragFromIndex.value === null) {
        localSongs.value = [...setlist.songs].sort((a, b) => a.pivot_order - b.pivot_order);
    }
}, { deep: true });

const existingSongIds = computed(() => props.setlist.songs.map((s) => s.id));

const dragFromIndex = ref<number | null>(null);

function handleDragStart(index: number): void {
    dragFromIndex.value = index;
}

function handleDragEnter(index: number): void {
    if (dragFromIndex.value === null || dragFromIndex.value === index) {
        return;
    }
    const songs = [...localSongs.value];
    const [moved] = songs.splice(dragFromIndex.value, 1);
    songs.splice(index, 0, moved);
    localSongs.value = songs;
    dragFromIndex.value = index;
}

function handleDragEnd(): void {
    dragFromIndex.value = null;
    const songs = localSongs.value.map((song, i) => ({ id: song.id, order: i }));
    router.patch(
        reorderSetlistSongs(props.setlist.id).url,
        { songs },
        { preserveScroll: true },
    );
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
        month: 'long',
        day: 'numeric',
    });
}

function handleSaved(): void {
    router.reload();
}

function handleSongAdded(): void {
    router.reload({ only: ['setlist'] });
}

function handleSongRemoved(): void {
    router.reload({ only: ['setlist'] });
}

function handleSongEdited(): void {
    router.reload({ only: ['setlist'] });
}
</script>

<template>
    <Head :title="setlist.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <Heading :title="setlist.title" no-margin />
                        <Badge :variant="statusVariant(setlist.status)" class="capitalize">
                            {{ setlist.status }}
                        </Badge>
                    </div>
                    <div class="text-muted-foreground flex flex-wrap items-center gap-2 text-sm">
                        <span>{{ formatDate(setlist.service_date) }}</span>
                        <span v-if="setlist.theme">· {{ setlist.theme }}</span>
                    </div>
                </div>

                <div class="flex gap-2 sm:shrink-0">
                    <Button variant="outline" size="sm" class="flex-1 sm:flex-none" @click="editDialogOpen = true">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Button>
                    <Link :href="setlistLive(setlist.id).url" class="flex-1 sm:flex-none">
                        <Button size="sm" class="w-full">
                            <Play class="mr-2 h-4 w-4" />
                            Start Service
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="setlist.notes" class="bg-muted rounded-md px-4 py-3 text-sm">
                {{ setlist.notes }}
            </div>

            <!-- Songs section -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">
                        Songs
                        <span class="text-muted-foreground ml-1 text-sm font-normal">({{ setlist.songs.length }})</span>
                    </h2>
                    <Button variant="outline" size="sm" @click="songPickerOpen = true">
                        <Pencil class="mr-2 h-3.5 w-3.5" />
                        Add Song
                    </Button>
                </div>

                <div v-if="localSongs.length === 0" class="text-muted-foreground rounded-md border px-4 py-8 text-center text-sm">
                    No songs added yet. Click "Add Song" to get started.
                </div>

                <div v-else class="space-y-2">
                    <SetlistSongRow
                        v-for="(song, index) in localSongs"
                        :key="song.id"
                        :song="song"
                        :setlist-id="setlist.id"
                        :index="index"
                        :dragging="dragFromIndex === index"
                        @removed="handleSongRemoved"
                        @edited="handleSongEdited"
                        @dragstart="handleDragStart"
                        @dragenter="handleDragEnter"
                        @dragend="handleDragEnd"
                    />
                </div>
            </div>
        </div>

        <SetlistFormDialog
            v-model:open="editDialogOpen"
            :setlist="setlist"
            @saved="handleSaved"
        />

        <SongPickerDialog
            v-model:open="songPickerOpen"
            :setlist-id="setlist.id"
            :existing-song-ids="existingSongIds"
            :songs="availableSongs"
            @added="handleSongAdded"
        />
    </AppLayout>
</template>
