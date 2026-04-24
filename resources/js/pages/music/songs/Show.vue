<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { index as songsIndex } from '@/actions/App/Http/Controllers/Music/SongController';
import ChordDisplay from '@/components/music/ChordDisplay.vue';
import KeyTransposer from '@/components/music/KeyTransposer.vue';
import SongFormDialog from '@/components/music/SongFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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

const props = defineProps<{ song: Song }>();

const displayKey = ref(props.song.original_key);
const mode = ref<'chord' | 'nashville'>('chord');
const editDialogOpen = ref(false);

const embedUrl = computed(() => {
    if (!props.song.video_link) { return null; }
    const url = props.song.video_link;
    const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    if (youtubeMatch) {
        return `https://www.youtube.com/embed/${youtubeMatch[1]}`;
    }
    return null;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Songs', href: songsIndex().url },
    { title: props.song.title, href: '#' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Song header -->
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold leading-tight">{{ song.title }}</h1>
                    <Button variant="ghost" size="icon" class="shrink-0" @click="editDialogOpen = true">
                        <Pencil class="size-4" />
                    </Button>
                </div>
                <p v-if="song.artist" class="text-muted-foreground">{{ song.artist }}</p>
                <p v-if="song.composer && song.composer !== song.artist" class="text-muted-foreground text-sm">
                    Composer: {{ song.composer }}
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <Badge variant="outline">{{ song.original_key }}</Badge>
                    <Badge v-if="song.tempo" variant="outline">{{ song.tempo }} BPM</Badge>
                    <Badge v-if="song.time_signature" variant="outline">{{ song.time_signature }}</Badge>
                </div>
            </div>

            <!-- Sticky toolbar: key transposer + mode toggle -->
            <div class="bg-background/95 sticky top-0 z-10 flex flex-wrap items-center justify-between gap-2 border-b py-2 backdrop-blur">
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

            <!-- Chord chart -->
            <div class="rounded-lg border p-4 text-sm md:p-6">
                <ChordDisplay
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

            <div v-if="song.notes" class="text-muted-foreground rounded-lg border p-4 text-sm">
                <p class="font-medium mb-1">Notes</p>
                <p>{{ song.notes }}</p>
            </div>
        </div>

        <SongFormDialog
            :open="editDialogOpen"
            :song="song"
            @update:open="editDialogOpen = $event"
            @saved="router.reload()"
        />
    </AppLayout>
</template>
