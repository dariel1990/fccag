<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useSwipe } from '@vueuse/core';
import { X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { show as setlistShow } from '@/actions/App/Http/Controllers/Music/SetlistController';
import ChordDisplay from '@/components/music/ChordDisplay.vue';
import KeyTransposer from '@/components/music/KeyTransposer.vue';
import PerformanceNav from '@/components/music/PerformanceNav.vue';

type LiveSong = {
    id: number;
    title: string;
    artist: string | null;
    original_key: string;
    display_key: string;
    lyrics_with_chords: string;
    tempo: number | null;
    pivot_notes: string | null;
};

type LiveSetlist = {
    id: number;
    title: string;
    service_date: string;
    theme: string | null;
    songs: LiveSong[];
};

const props = defineProps<{ setlist: LiveSetlist }>();

const currentIndex = ref(0);
const displayKeys = ref<Record<number, string>>({});
const mode = ref<'chord' | 'nashville'>('chord');

const formattedDate = computed(() => {
    if (!props.setlist.service_date) { return ''; }
    return new Date(props.setlist.service_date).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        timeZone: 'UTC',
    });
});
const swipeTarget = ref<HTMLElement | null>(null);

const currentSong = computed(() => props.setlist.songs[currentIndex.value] ?? null);
const prevSong = computed(() => props.setlist.songs[currentIndex.value - 1] ?? null);
const nextSong = computed(() => props.setlist.songs[currentIndex.value + 1] ?? null);

function getDisplayKey(song: LiveSong): string {
    return displayKeys.value[song.id] ?? song.display_key;
}

function setDisplayKey(key: string): void {
    if (currentSong.value) {
        displayKeys.value[currentSong.value.id] = key;
    }
}

function goNext(): void {
    if (currentIndex.value < props.setlist.songs.length - 1) {
        currentIndex.value++;
    }
}

function goPrev(): void {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    }
}

function goTo(index: number): void {
    if (index >= 0 && index < props.setlist.songs.length) {
        currentIndex.value = index;
    }
}

function handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'ArrowRight') {
        goNext();
    } else if (e.key === 'ArrowLeft') {
        goPrev();
    } else if (e.key >= '1' && e.key <= '9') {
        goTo(Number(e.key) - 1);
    }
}

useSwipe(swipeTarget, {
    onSwipeEnd(_e, direction) {
        if (direction === 'left') {
            goNext();
        } else if (direction === 'right') {
            goPrev();
        }
    },
});

let wakeLock: WakeLockSentinel | null = null;

async function requestWakeLock(): Promise<void> {
    try {
        if ('wakeLock' in navigator) {
            wakeLock = await navigator.wakeLock.request('screen');
        }
    } catch {
        // Wake lock not supported or denied — silently continue
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    requestWakeLock();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    wakeLock?.release();
});
</script>

<template>
    <div ref="swipeTarget" class="flex h-screen flex-col overflow-hidden bg-gray-950 text-white select-none">

        <!-- Fixed header -->
        <header class="shrink-0 flex items-center justify-between border-b border-white/10 px-4 py-3">
            <div class="flex flex-col">
                <span class="font-semibold text-sm">{{ setlist.title }}</span>
                <span class="text-xs text-white/50">{{ formattedDate }}</span>
            </div>
            <span class="text-sm font-medium text-white/60">
                {{ currentIndex + 1 }} / {{ setlist.songs.length }}
            </span>
            <Link :href="setlistShow(setlist.id).url">
                <Button variant="ghost" size="icon" class="text-white/70 hover:bg-white/10 hover:text-white">
                    <X class="size-5" />
                </Button>
            </Link>
        </header>

        <!-- Scrollable main content -->
        <main class="flex-1 overflow-y-auto px-4 py-6 md:px-8">
            <div v-if="currentSong" class="mx-auto max-w-3xl">

                <!-- Song title -->
                <div class="mb-3">
                    <h1 class="text-2xl font-bold leading-tight md:text-3xl">{{ currentSong.title }}</h1>
                    <p v-if="currentSong.artist" class="mt-0.5 text-sm text-white/50">{{ currentSong.artist }}</p>
                </div>

                <!-- Controls toolbar -->
                <div class="mb-5 flex flex-wrap items-center gap-2 border-b border-white/10 pb-4">
                    <!-- Key transposer -->
                    <KeyTransposer
                        :key="'kt-' + currentSong.id"
                        :model-value="getDisplayKey(currentSong)"
                        :original-key="currentSong.original_key"
                        @update:model-value="setDisplayKey"
                    />

                    <!-- Chord / Nashville toggle -->
                    <div class="inline-flex rounded-md bg-white/10 p-0.5">
                        <button
                            type="button"
                            :class="[
                                'rounded px-2.5 py-1 text-xs font-medium transition-colors',
                                mode === 'chord' ? 'bg-white text-gray-900' : 'text-white/60 hover:text-white',
                            ]"
                            @click="mode = 'chord'"
                        >Chords</button>
                        <button
                            type="button"
                            :class="[
                                'rounded px-2.5 py-1 text-xs font-medium transition-colors',
                                mode === 'nashville' ? 'bg-white text-gray-900' : 'text-white/60 hover:text-white',
                            ]"
                            @click="mode = 'nashville'"
                        >Nashville</button>
                    </div>

                </div>

                <!-- Chord chart — chords are bright yellow for visibility on dark bg -->
                <div class="rounded-lg bg-white/5 p-4 text-sm md:p-6">
                    <ChordDisplay
                        :key="'cd-' + currentSong.id + '-' + mode"
                        :lyrics="currentSong.lyrics_with_chords"
                        :original-key="currentSong.original_key"
                        :display-key="getDisplayKey(currentSong)"
                        :mode="mode"
                        live
                    />
                </div>

                <p v-if="currentSong.pivot_notes" class="mt-4 text-sm italic text-white/40">
                    {{ currentSong.pivot_notes }}
                </p>
            </div>
        </main>

        <!-- Fixed footer navigation -->
        <PerformanceNav
            class="shrink-0"
            :current-index="currentIndex"
            :total="setlist.songs.length"
            :prev-title="prevSong?.title ?? null"
            :next-title="nextSong?.title ?? null"
            @prev="goPrev"
            @next="goNext"
        />
    </div>
</template>
