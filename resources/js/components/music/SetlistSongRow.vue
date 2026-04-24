<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { GripVertical, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { destroy as destroySetlistSong } from '@/actions/App/Http/Controllers/Music/SetlistSongController';
import SongDetailModal from '@/components/music/SongDetailModal.vue';
import SongFormDialog from '@/components/music/SongFormDialog.vue';
import { Button } from '@/components/ui/button';

type SetlistSong = {
    id: number;
    title: string;
    artist: string | null;
    composer: string | null;
    original_key: string;
    tempo: number | null;
    time_signature: string | null;
    lyrics_with_chords: string;
    video_link: string | null;
    notes: string | null;
    is_active: boolean;
    pivot_order: number;
    pivot_key_override: string | null;
    pivot_notes: string | null;
};

const props = defineProps<{
    song: SetlistSong;
    setlistId: number;
    index: number;
    dragging?: boolean;
}>();

const emit = defineEmits<{
    removed: [];
    edited: [];
    dragstart: [index: number];
    dragenter: [index: number];
    dragend: [];
}>();

const isRemoving = ref(false);
const editOpen = ref(false);
const detailOpen = ref(false);

function handleTouchStart(event: TouchEvent): void {
    event.preventDefault();
    emit('dragstart', props.index);
}

function handleTouchMove(event: TouchEvent): void {
    event.preventDefault();
    const touch = event.touches[0];
    const target = document.elementFromPoint(touch.clientX, touch.clientY);
    const row = target?.closest('[data-song-index]');
    if (row) {
        const idx = parseInt(row.getAttribute('data-song-index') ?? '-1');
        if (idx >= 0) {
            emit('dragenter', idx);
        }
    }
}

function handleTouchEnd(): void {
    emit('dragend');
}

function removeSong(): void {
    isRemoving.value = true;

    router.delete(
        destroySetlistSong({ setlist: props.setlistId, song: props.song.id }).url,
        {
            preserveScroll: true,
            onSuccess: () => emit('removed'),
            onFinish: () => { isRemoving.value = false; },
        },
    );
}

function handleEdited(): void {
    emit('edited');
}
</script>

<template>
    <div
        draggable="true"
        :data-song-index="index"
        class="flex items-center gap-2 rounded-md border px-3 py-3 transition-opacity sm:py-2"
        :class="{ 'opacity-40': dragging }"
        @dragstart="emit('dragstart', index)"
        @dragenter.prevent="emit('dragenter', index)"
        @dragover.prevent
        @dragend="emit('dragend')"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
    >
        <!-- Drag handle + index -->
        <GripVertical class="text-muted-foreground h-4 w-4 shrink-0 cursor-grab" />
        <span class="text-muted-foreground w-5 shrink-0 text-center text-xs">{{ index + 1 }}</span>

        <!-- Song info -->
        <div class="min-w-0 flex-1">
            <button
                type="button"
                class="w-full truncate text-left text-sm font-semibold transition-colors hover:text-primary hover:underline sm:text-base"
                @click="detailOpen = true"
            >
                {{ song.title }}
            </button>
            <div class="text-muted-foreground truncate text-xs sm:text-sm">{{ song.artist || '—' }}</div>
        </div>

        <!-- Key + actions -->
        <div class="flex shrink-0 items-center gap-1">
            <span class="bg-muted text-muted-foreground rounded px-1.5 py-0.5 text-xs font-medium">
                {{ song.original_key }}
            </span>
            <Button variant="ghost" size="icon" class="h-8 w-8 sm:h-7 sm:w-7" @click="editOpen = true">
                <Pencil class="h-3.5 w-3.5" />
            </Button>
            <Button variant="ghost" size="icon" class="h-8 w-8 sm:h-7 sm:w-7" :disabled="isRemoving" @click="removeSong">
                <Trash2 class="text-destructive h-3.5 w-3.5" />
            </Button>
        </div>
    </div>

    <SongDetailModal
        :open="detailOpen"
        :song="song"
        @update:open="detailOpen = $event"
    />

    <SongFormDialog
        :open="editOpen"
        :song="song"
        @update:open="editOpen = $event"
        @saved="handleEdited"
    />
</template>
