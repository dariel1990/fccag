<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import ChordDisplay from '@/components/music/ChordDisplay.vue';
import KeyTransposer from '@/components/music/KeyTransposer.vue';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';

type Song = {
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
};

const props = defineProps<{
    open: boolean;
    song: Song;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const displayKey = ref(props.song.original_key);
const mode = ref<'chord' | 'nashville'>('chord');

watch(() => props.song.original_key, (key) => {
    displayKey.value = key;
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        displayKey.value = props.song.original_key;
        mode.value = 'chord';
    }
});

const embedUrl = computed(() => {
    if (!props.song.video_link) { return null; }
    const match = props.song.video_link.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    return match ? `https://www.youtube.com/embed/${match[1]}` : null;
});
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="flex h-dvh max-w-2xl flex-col overflow-hidden rounded-none p-0 sm:h-[96vh] sm:rounded-lg">
            <DialogHeader class="shrink-0 border-b px-6 pt-6 pb-4">
                <DialogTitle class="text-xl leading-tight">{{ song.title }}</DialogTitle>
                <DialogDescription class="sr-only">Song details for {{ song.title }}</DialogDescription>
                <div class="space-y-0.5">
                    <p v-if="song.artist" class="text-muted-foreground text-sm">{{ song.artist }}</p>
                    <p v-if="song.composer && song.composer !== song.artist" class="text-muted-foreground text-xs">
                        Composer: {{ song.composer }}
                    </p>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <Badge variant="outline">{{ displayKey }}</Badge>
                    <Badge v-if="song.tempo" variant="outline">{{ song.tempo }} BPM</Badge>
                    <Badge v-if="song.time_signature" variant="outline">{{ song.time_signature }}</Badge>
                </div>
            </DialogHeader>

            <!-- Toolbar -->
            <div class="shrink-0 flex flex-wrap items-center justify-between gap-2 border-b px-6 py-2">
                <KeyTransposer v-model="displayKey" :original-key="song.original_key" />

                <div class="bg-muted inline-flex rounded-md p-0.5">
                    <button
                        type="button"
                        :class="[
                            'rounded px-3 py-1 text-xs font-medium transition-colors',
                            mode === 'chord'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        ]"
                        @click="mode = 'chord'"
                    >
                        Chords
                    </button>
                    <button
                        type="button"
                        :class="[
                            'rounded px-3 py-1 text-xs font-medium transition-colors',
                            mode === 'nashville'
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground',
                        ]"
                        @click="mode = 'nashville'"
                    >
                        Nashville
                    </button>
                </div>
            </div>

            <!-- Scrollable content -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                <!-- Chord chart -->
                <div class="rounded-lg border p-4 text-sm">
                    <ChordDisplay
                        :key="displayKey + mode"
                        :lyrics="song.lyrics_with_chords"
                        :original-key="song.original_key"
                        :display-key="displayKey"
                        :mode="mode"
                    />
                </div>

                <!-- Embedded video -->
                <div v-if="embedUrl" class="overflow-hidden rounded-lg border">
                    <div class="relative aspect-video">
                        <iframe
                            :src="embedUrl"
                            class="absolute inset-0 h-full w-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        />
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="song.notes" class="text-muted-foreground rounded-lg border p-4 text-sm">
                    <p class="font-medium text-foreground mb-1">Notes</p>
                    <p>{{ song.notes }}</p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
