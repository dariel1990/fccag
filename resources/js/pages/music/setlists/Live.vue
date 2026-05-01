<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useSwipe } from '@vueuse/core';
import { Palette, X } from 'lucide-vue-next';
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

type LiveTheme = {
    id: string;
    label: string;
    swatch: string;
    bg: string;
    text: string;
    muted: string;
    chord: string;
    border: string;
    surface: string;
    ctrlBg: string;
    ctrlActive: string;
    ctrlActiveText: string;
    btnHover: string;
};

const THEMES: LiveTheme[] = [
    {
        id: 'dark',
        label: 'Dark',
        swatch: '#111827',
        bg: '#030712',
        text: '#ffffff',
        muted: 'rgba(255,255,255,0.45)',
        chord: '#fde047',
        border: 'rgba(255,255,255,0.10)',
        surface: 'rgba(255,255,255,0.05)',
        ctrlBg: 'rgba(255,255,255,0.10)',
        ctrlActive: '#ffffff',
        ctrlActiveText: '#111827',
        btnHover: 'rgba(255,255,255,0.10)',
    },
    {
        id: 'midnight',
        label: 'Midnight',
        swatch: '#1e1b4b',
        bg: '#0c0a1e',
        text: '#e8e6ff',
        muted: 'rgba(200,196,255,0.5)',
        chord: '#a78bfa',
        border: 'rgba(167,139,250,0.15)',
        surface: 'rgba(167,139,250,0.06)',
        ctrlBg: 'rgba(167,139,250,0.12)',
        ctrlActive: '#a78bfa',
        ctrlActiveText: '#0c0a1e',
        btnHover: 'rgba(167,139,250,0.12)',
    },
    {
        id: 'light',
        label: 'Light',
        swatch: '#f8fafc',
        bg: '#f8fafc',
        text: '#0f172a',
        muted: '#64748b',
        chord: '#1d4ed8',
        border: '#e2e8f0',
        surface: '#f1f5f9',
        ctrlBg: '#e2e8f0',
        ctrlActive: '#0f172a',
        ctrlActiveText: '#f8fafc',
        btnHover: '#e2e8f0',
    },
    {
        id: 'sepia',
        label: 'Sepia',
        swatch: '#fdf6e3',
        bg: '#fdf6e3',
        text: '#3d2b1f',
        muted: '#a07840',
        chord: '#92400e',
        border: '#d4b896',
        surface: '#fef3c7',
        ctrlBg: '#edd5a3',
        ctrlActive: '#92400e',
        ctrlActiveText: '#fdf6e3',
        btnHover: '#edd5a3',
    },
    {
        id: 'stage',
        label: 'Stage',
        swatch: '#001a00',
        bg: '#001a00',
        text: '#00e676',
        muted: 'rgba(0,230,118,0.5)',
        chord: '#69ff47',
        border: 'rgba(0,230,118,0.15)',
        surface: 'rgba(0,230,118,0.04)',
        ctrlBg: 'rgba(0,230,118,0.10)',
        ctrlActive: '#00e676',
        ctrlActiveText: '#001a00',
        btnHover: 'rgba(0,230,118,0.10)',
    },
];

const THEME_KEY = 'live-theme';

const props = defineProps<{ setlist: LiveSetlist; isPublic?: boolean }>();

const currentIndex = ref(0);
const displayKeys = ref<Record<number, string>>({});
const mode = ref<'chord' | 'nashville'>('chord');
const showThemePicker = ref(false);
const activeThemeId = ref(localStorage.getItem(THEME_KEY) ?? 'dark');

const currentTheme = computed(() => THEMES.find(t => t.id === activeThemeId.value) ?? THEMES[0]);

const themeVars = computed(() => ({
    '--lt-bg': currentTheme.value.bg,
    '--lt-text': currentTheme.value.text,
    '--lt-muted': currentTheme.value.muted,
    '--lt-chord': currentTheme.value.chord,
    '--lt-border': currentTheme.value.border,
    '--lt-surface': currentTheme.value.surface,
    '--lt-ctrl-bg': currentTheme.value.ctrlBg,
    '--lt-ctrl-active': currentTheme.value.ctrlActive,
    '--lt-ctrl-active-text': currentTheme.value.ctrlActiveText,
    '--lt-btn-hover': currentTheme.value.btnHover,
    backgroundColor: currentTheme.value.bg,
    color: currentTheme.value.text,
}));

function setTheme(id: string): void {
    activeThemeId.value = id;
    localStorage.setItem(THEME_KEY, id);
    showThemePicker.value = false;
}

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
const isScrolling = ref(false);
let scrollHideTimer: ReturnType<typeof setTimeout> | null = null;

function handleChartScroll(): void {
    isScrolling.value = true;
    if (scrollHideTimer) { clearTimeout(scrollHideTimer); }
    scrollHideTimer = setTimeout(() => {
        isScrolling.value = false;
    }, 800);
}
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
    } else if (e.key === 'Escape') {
        showThemePicker.value = false;
    } else if (e.key >= '1' && e.key <= '9') {
        goTo(Number(e.key) - 1);
    }
}

useSwipe(swipeTarget, {
    onSwipeEnd(_e, direction) {
        if (direction === 'left') { goNext(); }
        else if (direction === 'right') { goPrev(); }
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
    <div
        ref="swipeTarget"
        class="flex h-screen flex-col overflow-hidden select-none"
        :style="themeVars"
    >
        <!-- Fixed header -->
        <header
            class="shrink-0 flex items-center justify-between px-4 py-3"
            :style="{ borderBottom: `1px solid ${currentTheme.border}` }"
        >
            <div class="flex flex-col min-w-0">
                <span class="font-semibold text-sm truncate">{{ setlist.title }}</span>
                <span class="text-xs" :style="{ color: currentTheme.muted }">{{ formattedDate }}</span>
            </div>

            <span class="text-sm font-medium shrink-0 px-3" :style="{ color: currentTheme.muted }">
                {{ currentIndex + 1 }} / {{ setlist.songs.length }}
            </span>

            <div class="flex items-center gap-1 shrink-0">
                <!-- Theme picker toggle -->
                <button
                    type="button"
                    class="flex items-center justify-center size-9 rounded-md transition-colors"
                    :style="{
                        color: showThemePicker ? currentTheme.ctrlActive : currentTheme.muted,
                        backgroundColor: showThemePicker ? currentTheme.ctrlBg : 'transparent',
                    }"
                    :title="'Change theme'"
                    @click="showThemePicker = !showThemePicker"
                >
                    <Palette class="size-4" />
                </button>

                <!-- Close / back (hidden for public share view) -->
                <Link v-if="!isPublic" :href="setlistShow(setlist.id).url">
                    <button
                        type="button"
                        class="flex items-center justify-center size-9 rounded-md transition-colors"
                        :style="{ color: currentTheme.muted }"
                    >
                        <X class="size-5" />
                    </button>
                </Link>
            </div>
        </header>

        <!-- Theme picker panel -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="showThemePicker"
                class="shrink-0 px-4 py-3 flex items-center gap-4"
                :style="{
                    backgroundColor: currentTheme.surface,
                    borderBottom: `1px solid ${currentTheme.border}`,
                }"
            >
                <span class="text-xs font-semibold uppercase tracking-wider shrink-0" :style="{ color: currentTheme.muted }">Theme</span>
                <div class="flex items-center gap-3 flex-wrap">
                    <button
                        v-for="theme in THEMES"
                        :key="theme.id"
                        type="button"
                        class="flex flex-col items-center gap-1.5 group"
                        @click="setTheme(theme.id)"
                    >
                        <span
                            class="size-8 rounded-full border-2 transition-transform group-hover:scale-110 flex items-center justify-center"
                            :style="{
                                backgroundColor: theme.swatch,
                                borderColor: activeThemeId === theme.id ? currentTheme.ctrlActive : currentTheme.border,
                                boxShadow: activeThemeId === theme.id ? `0 0 0 2px ${currentTheme.ctrlActive}` : 'none',
                            }"
                        >
                            <span
                                v-if="activeThemeId === theme.id"
                                class="size-2 rounded-full"
                                :style="{ backgroundColor: theme.chord }"
                            />
                        </span>
                        <span class="text-xs" :style="{ color: activeThemeId === theme.id ? currentTheme.text : currentTheme.muted }">
                            {{ theme.label }}
                        </span>
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Main content: header & controls fixed, chord chart scrolls -->
        <main class="flex flex-1 flex-col overflow-hidden">
            <div v-if="currentSong" class="mx-auto flex w-full max-w-3xl flex-1 flex-col overflow-hidden px-4 md:px-8">

                <!-- Song title (fixed) -->
                <div class="shrink-0 pt-6 mb-3">
                    <h1 class="text-2xl font-bold leading-tight md:text-3xl">{{ currentSong.title }}</h1>
                    <p v-if="currentSong.artist" class="mt-0.5 text-sm" :style="{ color: currentTheme.muted }">
                        {{ currentSong.artist }}
                    </p>
                </div>

                <!-- Controls toolbar (fixed) -->
                <div
                    class="shrink-0 mb-5 flex flex-wrap items-center gap-2 pb-4"
                    :style="{ borderBottom: `1px solid ${currentTheme.border}` }"
                >
                    <!-- Key transposer -->
                    <KeyTransposer
                        :key="'kt-' + currentSong.id"
                        :model-value="getDisplayKey(currentSong)"
                        :original-key="currentSong.original_key"
                        @update:model-value="setDisplayKey"
                    />

                    <!-- Chord / Nashville toggle -->
                    <div class="inline-flex rounded-md p-0.5" :style="{ backgroundColor: currentTheme.ctrlBg }">
                        <button
                            type="button"
                            class="rounded px-2.5 py-1 text-xs font-medium transition-colors"
                            :style="mode === 'chord'
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { color: currentTheme.muted }"
                            @click="mode = 'chord'"
                        >Chords</button>
                        <button
                            type="button"
                            class="rounded px-2.5 py-1 text-xs font-medium transition-colors"
                            :style="mode === 'nashville'
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { color: currentTheme.muted }"
                            @click="mode = 'nashville'"
                        >Nashville</button>
                    </div>
                </div>

                <!-- Chord chart (scrollable) -->
                <div
                    class="chord-scroll flex-1 overflow-y-auto rounded-lg p-4 text-sm md:p-6"
                    :class="{ 'is-scrolling': isScrolling }"
                    :style="{ backgroundColor: currentTheme.surface }"
                    @scroll.passive="handleChartScroll"
                >
                    <ChordDisplay
                        :key="'cd-' + currentSong.id + '-' + mode"
                        :lyrics="currentSong.lyrics_with_chords"
                        :original-key="currentSong.original_key"
                        :display-key="getDisplayKey(currentSong)"
                        :mode="mode"
                        live
                    />

                    <p v-if="currentSong.pivot_notes" class="mt-4 text-sm italic" :style="{ color: currentTheme.muted }">
                        {{ currentSong.pivot_notes }}
                    </p>
                </div>

                <!-- Spacer to give the scroll panel breathing room above the footer -->
                <div class="shrink-0 h-4" />
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

<style scoped>
.chord-scroll {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.chord-scroll::-webkit-scrollbar {
    width: 0;
    height: 0;
    background: transparent;
}
.chord-scroll.is-scrolling {
    scrollbar-width: thin;
    scrollbar-color: rgba(127, 127, 127, 0.4) transparent;
}
.chord-scroll.is-scrolling::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.chord-scroll.is-scrolling::-webkit-scrollbar-thumb {
    background: rgba(127, 127, 127, 0.4);
    border-radius: 3px;
}
@media (hover: hover) {
    .chord-scroll:hover {
        scrollbar-width: thin;
        scrollbar-color: rgba(127, 127, 127, 0.4) transparent;
    }
    .chord-scroll:hover::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .chord-scroll:hover::-webkit-scrollbar-thumb {
        background: rgba(127, 127, 127, 0.4);
        border-radius: 3px;
    }
}
</style>
