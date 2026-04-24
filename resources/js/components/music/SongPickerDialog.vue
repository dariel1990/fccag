<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { store as storeSetlistSong } from '@/actions/App/Http/Controllers/Music/SetlistSongController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';

type AvailableSong = {
    id: number;
    title: string;
    artist: string | null;
    original_key: string;
};

const props = defineProps<{
    open: boolean;
    setlistId: number;
    existingSongIds: number[];
    songs: AvailableSong[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    added: [];
}>();

const search = ref('');
const addingId = ref<number | null>(null);

const filteredSongs = computed(() => {
    const query = search.value.toLowerCase().trim();
    return props.songs.filter((song) => {
        if (props.existingSongIds.includes(song.id)) {
            return false;
        }
        if (!query) {
            return true;
        }
        return song.title.toLowerCase().includes(query) || (song.artist ?? '').toLowerCase().includes(query);
    });
});

function addSong(song: AvailableSong): void {
    addingId.value = song.id;

    router.post(
        storeSetlistSong(props.setlistId).url,
        { song_id: song.id },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('added');
            },
            onFinish: () => {
                addingId.value = null;
            },
        },
    );
}

function handleClose(): void {
    search.value = '';
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="flex h-dvh max-w-lg flex-col overflow-hidden rounded-none p-0 sm:h-[96vh] sm:rounded-lg">
            <DialogHeader class="shrink-0 border-b px-6 pt-6 pb-4">
                <DialogTitle>Add Song to Setlist</DialogTitle>
                <DialogDescription>Search and select a song to add to this setlist.</DialogDescription>
            </DialogHeader>

            <div class="shrink-0 px-6 pt-4">
                <Input v-model="search" placeholder="Search by title or artist..." autofocus />
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-3">
                <div class="rounded-md border">
                    <div v-if="filteredSongs.length === 0" class="text-muted-foreground px-4 py-6 text-center text-sm">
                        No songs available.
                    </div>
                    <button
                        v-for="song in filteredSongs"
                        :key="song.id"
                        type="button"
                        class="hover:bg-accent flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition-colors"
                        :disabled="addingId === song.id"
                        @click="addSong(song)"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="truncate font-medium">{{ song.title }}</div>
                            <div class="text-muted-foreground truncate text-sm">{{ song.artist || '—' }}</div>
                        </div>
                        <Badge variant="outline">{{ song.original_key }}</Badge>
                    </button>
                </div>
            </div>

            <div class="shrink-0 border-t px-6 py-4 flex justify-end">
                <Button variant="outline" @click="handleClose">Close</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
