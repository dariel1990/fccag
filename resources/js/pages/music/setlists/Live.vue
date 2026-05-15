<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onClickOutside, useSwipe } from '@vueuse/core';
import { AlignLeft, ChevronDown, FileText, Guitar, Hash, Maximize2, Minimize2, Minus, Music2, Palette, Play, Plus, RotateCcw, X } from 'lucide-vue-next';
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { show as setlistShow } from '@/actions/App/Http/Controllers/Music/SetlistController';
import ChordDisplay from '@/components/music/ChordDisplay.vue';
import PerformanceNav from '@/components/music/PerformanceNav.vue';
import { useChordTransposer } from '@/composables/useChordTransposer';

type LiveSong = {
    id: number;
    title: string;
    artist: string | null;
    original_key: string;
    display_key: string;
    lyrics_with_chords: string;
    tempo: number | null;
    video_link: string | null;
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
const VIEW_MODE_KEY = 'live-view-mode';

type ViewMode = 'both' | 'lyrics' | 'chords';

function readStoredViewMode(): ViewMode {
    const stored = localStorage.getItem(VIEW_MODE_KEY);
    return stored === 'lyrics' || stored === 'chords' ? stored : 'both';
}

const props = defineProps<{ setlist: LiveSetlist; isPublic?: boolean }>();

const currentIndex = ref(0);
const displayKeys = ref<Record<number, string>>({});
const mode = ref<'chord' | 'nashville'>('chord');
const viewMode = ref<ViewMode>(readStoredViewMode());
const showThemePicker = ref(false);
const activeThemeId = ref(localStorage.getItem(THEME_KEY) ?? 'dark');
const isFullscreen = ref(false);
const showVideo = ref(false);

function setViewMode(next: ViewMode): void {
    viewMode.value = next;
    localStorage.setItem(VIEW_MODE_KEY, next);
}

function toggleFullscreen(): void {
    isFullscreen.value = !isFullscreen.value;
}

const { keys: allKeys } = useChordTransposer();
const keyPickerOpen = ref(false);
const keyPickerTriggerEl = ref<HTMLElement | null>(null);
const keyPickerPanelEl = ref<HTMLElement | null>(null);
const keyPickerPos = ref({ top: 0, left: 0 });
const KEY_PANEL_WIDTH = 224;

function updateKeyPickerPos(): void {
    if (!keyPickerTriggerEl.value) { return; }
    const rect = keyPickerTriggerEl.value.getBoundingClientRect();
    const left = Math.max(8, Math.min(rect.left, window.innerWidth - KEY_PANEL_WIDTH - 8));
    keyPickerPos.value = { top: rect.bottom + 8, left };
}

const currentSong = computed(() => props.setlist.songs[currentIndex.value] ?? null);

const videoEmbedUrl = computed(() => {
    const url = currentSong.value?.video_link;
    if (!url) { return null; }

    const youtube = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/);
    if (youtube) {
        return `https://www.youtube.com/embed/${youtube[1]}?autoplay=1`;
    }

    const vimeo = url.match(/vimeo\.com\/(\d+)/);
    if (vimeo) {
        return `https://player.vimeo.com/video/${vimeo[1]}?autoplay=1`;
    }

    return url;
});
const prevSong = computed(() => props.setlist.songs[currentIndex.value - 1] ?? null);
const nextSong = computed(() => props.setlist.songs[currentIndex.value + 1] ?? null);

function stepKey(direction: 1 | -1): void {
    if (!currentSong.value) { return; }
    const current = getDisplayKey(currentSong.value);
    const idx = allKeys.indexOf(current);
    if (idx === -1) { return; }
    const nextIdx = (idx + direction + allKeys.length) % allKeys.length;
    setDisplayKey(allKeys[nextIdx]);
}

async function toggleKeyPicker(): Promise<void> {
    keyPickerOpen.value = !keyPickerOpen.value;
    if (keyPickerOpen.value) {
        await nextTick();
        updateKeyPickerPos();
    }
}

onClickOutside(keyPickerPanelEl, (event) => {
    if (keyPickerTriggerEl.value?.contains(event.target as Node)) { return; }
    keyPickerOpen.value = false;
});

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
        showVideo.value = false;
        currentIndex.value++;
    }
}

function goPrev(): void {
    if (currentIndex.value > 0) {
        showVideo.value = false;
        currentIndex.value--;
    }
}

function goTo(index: number): void {
    if (index >= 0 && index < props.setlist.songs.length) {
        showVideo.value = false;
        currentIndex.value = index;
    }
}

function handleKeydown(e: KeyboardEvent): void {
    if (e.key === 'ArrowRight') {
        goNext();
    } else if (e.key === 'ArrowLeft') {
        goPrev();
    } else if (e.key === 'Escape') {
        if (showVideo.value) {
            showVideo.value = false;
        } else if (isFullscreen.value) {
            isFullscreen.value = false;
        } else {
            showThemePicker.value = false;
        }
    } else if (e.key === 'f' || e.key === 'F') {
        toggleFullscreen();
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

function handleViewportChange(): void {
    if (keyPickerOpen.value) { updateKeyPickerPos(); }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    window.addEventListener('resize', handleViewportChange);
    window.addEventListener('scroll', handleViewportChange, true);
    requestWakeLock();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('resize', handleViewportChange);
    window.removeEventListener('scroll', handleViewportChange, true);
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
            v-show="!isFullscreen"
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
                    <p v-if="currentSong.artist && !isFullscreen" class="mt-0.5 text-sm" :style="{ color: currentTheme.muted }">
                        {{ currentSong.artist }}
                    </p>
                </div>

                <!-- Controls toolbar (fixed) -->
                <div
                    class="shrink-0 mb-5 flex flex-nowrap items-center gap-1.5 overflow-x-auto pb-4 sm:gap-2"
                    :style="{ borderBottom: `1px solid ${currentTheme.border}` }"
                >
                    <!-- Modern key picker with step buttons -->
                    <div class="inline-flex shrink-0 items-stretch overflow-hidden rounded-md" :style="{ backgroundColor: currentTheme.ctrlBg }">
                        <button
                            type="button"
                            class="flex h-8 w-7 items-center justify-center transition-colors"
                            :style="{ color: currentTheme.text }"
                            title="Transpose down"
                            @click="stepKey(-1)"
                        ><Minus class="size-3.5" /></button>
                        <button
                            ref="keyPickerTriggerEl"
                            type="button"
                            class="flex h-8 items-center gap-1.5 px-2.5 transition-colors"
                            :style="{
                                backgroundColor: keyPickerOpen ? currentTheme.ctrlActive : 'transparent',
                                color: keyPickerOpen ? currentTheme.ctrlActiveText : currentTheme.text,
                            }"
                            @click="toggleKeyPicker"
                        >
                            <span class="text-[10px] font-semibold uppercase tracking-wider opacity-60">Key</span>
                            <span class="text-sm font-bold tabular-nums">{{ getDisplayKey(currentSong) }}</span>
                            <ChevronDown class="size-3.5 transition-transform" :class="{ 'rotate-180': keyPickerOpen }" />
                        </button>
                        <button
                            type="button"
                            class="flex h-8 w-7 items-center justify-center transition-colors"
                            :style="{ color: currentTheme.text }"
                            title="Transpose up"
                            @click="stepKey(1)"
                        ><Plus class="size-3.5" /></button>
                    </div>

                    <!-- Chord / Nashville toggle -->
                    <div class="inline-flex shrink-0 rounded-md p-0.5" :style="{ backgroundColor: currentTheme.ctrlBg }">
                        <button
                            type="button"
                            class="flex items-center justify-center size-7 rounded transition-colors"
                            :style="mode === 'chord'
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { color: currentTheme.muted }"
                            title="Chord names"
                            @click="mode = 'chord'"
                        ><Guitar class="size-3.5" /></button>
                        <button
                            type="button"
                            class="flex items-center justify-center size-7 rounded transition-colors"
                            :style="mode === 'nashville'
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { color: currentTheme.muted }"
                            title="Nashville numbers"
                            @click="mode = 'nashville'"
                        ><Hash class="size-3.5" /></button>
                    </div>

                    <!-- View mode toggle: both / lyrics / chords -->
                    <div class="inline-flex shrink-0 rounded-md p-0.5" :style="{ backgroundColor: currentTheme.ctrlBg }">
                        <button
                            type="button"
                            class="flex items-center justify-center size-7 rounded transition-colors"
                            :style="viewMode === 'both'
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { color: currentTheme.muted }"
                            title="Lyrics + chords"
                            @click="setViewMode('both')"
                        ><AlignLeft class="size-3.5" /></button>
                        <button
                            type="button"
                            class="flex items-center justify-center size-7 rounded transition-colors"
                            :style="viewMode === 'lyrics'
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { color: currentTheme.muted }"
                            title="Lyrics only"
                            @click="setViewMode('lyrics')"
                        ><FileText class="size-3.5" /></button>
                        <button
                            type="button"
                            class="flex items-center justify-center size-7 rounded transition-colors"
                            :style="viewMode === 'chords'
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { color: currentTheme.muted }"
                            title="Chords only"
                            @click="setViewMode('chords')"
                        ><Music2 class="size-3.5" /></button>
                    </div>

                    <!-- Play video inline -->
                    <button
                        v-if="currentSong.video_link"
                        type="button"
                        class="ml-auto flex h-8 shrink-0 items-center justify-center gap-1.5 rounded-md px-3 transition-colors"
                        :style="{ backgroundColor: currentTheme.ctrlBg, color: currentTheme.text }"
                        title="Play video"
                        @click="showVideo = true"
                    >
                        <Play class="size-3.5" />
                        <span class="text-xs font-semibold">Play</span>
                    </button>
                </div>

                <!-- Chord chart (scrollable) with fullscreen toggle -->
                <div class="relative flex flex-1 overflow-hidden rounded-lg" :style="{ backgroundColor: currentTheme.surface }">
                    <div
                        class="chord-scroll flex-1 overflow-y-auto p-4 text-sm md:p-6"
                        :class="{ 'is-scrolling': isScrolling }"
                        @scroll.passive="handleChartScroll"
                    >
                        <ChordDisplay
                            :key="'cd-' + currentSong.id + '-' + mode"
                            :lyrics="currentSong.lyrics_with_chords"
                            :original-key="currentSong.original_key"
                            :display-key="getDisplayKey(currentSong)"
                            :mode="mode"
                            :view-mode="viewMode"
                            live
                        />

                        <p v-if="currentSong.pivot_notes" class="mt-4 text-sm italic" :style="{ color: currentTheme.muted }">
                            {{ currentSong.pivot_notes }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="fullscreen-toggle absolute bottom-3 right-3 flex items-center justify-center size-9 rounded-md transition-opacity duration-200"
                        :class="{ 'is-visible': isScrolling || isFullscreen }"
                        :style="{
                            backgroundColor: currentTheme.ctrlBg,
                            color: currentTheme.text,
                            border: `1px solid ${currentTheme.border}`,
                        }"
                        :title="isFullscreen ? 'Exit fullscreen (Esc)' : 'Fullscreen (F)'"
                        @click="toggleFullscreen"
                    >
                        <Minimize2 v-if="isFullscreen" class="size-4" />
                        <Maximize2 v-else class="size-4" />
                    </button>
                </div>

                <!-- Spacer to give the scroll panel breathing room above the footer -->
                <div class="shrink-0 h-4" />
            </div>
        </main>

        <!-- Teleported key picker popover -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-150 ease-out"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-1"
            >
                <div
                    v-if="keyPickerOpen && currentSong"
                    ref="keyPickerPanelEl"
                    class="fixed z-[60] w-56 rounded-lg p-2 shadow-xl"
                    :style="{
                        top: keyPickerPos.top + 'px',
                        left: keyPickerPos.left + 'px',
                        backgroundColor: currentTheme.bg,
                        border: `1px solid ${currentTheme.border}`,
                        color: currentTheme.text,
                    }"
                >
                    <div class="mb-2 flex items-center justify-between px-1">
                        <span class="text-[10px] font-semibold uppercase tracking-wider" :style="{ color: currentTheme.muted }">Transpose</span>
                        <button
                            v-if="getDisplayKey(currentSong) !== currentSong.original_key"
                            type="button"
                            class="flex items-center gap-1 rounded px-1.5 py-0.5 text-[10px] font-medium transition-colors"
                            :style="{ color: currentTheme.muted }"
                            @click="setDisplayKey(currentSong.original_key); keyPickerOpen = false"
                        >
                            <RotateCcw class="size-3" />
                            <span>{{ currentSong.original_key }}</span>
                        </button>
                    </div>
                    <div class="grid grid-cols-6 gap-1">
                        <button
                            v-for="k in allKeys"
                            :key="k"
                            type="button"
                            class="flex h-8 items-center justify-center rounded text-xs font-bold tabular-nums transition-colors"
                            :style="getDisplayKey(currentSong) === k
                                ? { backgroundColor: currentTheme.ctrlActive, color: currentTheme.ctrlActiveText }
                                : { backgroundColor: currentTheme.surface, color: currentTheme.text }"
                            @click="setDisplayKey(k); keyPickerOpen = false"
                        >{{ k }}</button>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- In-page video player overlay -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showVideo && videoEmbedUrl"
                    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90 p-4"
                    @click.self="showVideo = false"
                >
                    <button
                        type="button"
                        class="absolute right-4 top-4 flex size-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20"
                        title="Close (Esc)"
                        @click="showVideo = false"
                    >
                        <X class="size-5" />
                    </button>
                    <div class="aspect-video w-full max-w-4xl overflow-hidden rounded-lg bg-black shadow-2xl">
                        <iframe
                            :src="videoEmbedUrl"
                            class="h-full w-full"
                            title="Song video"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Fixed footer navigation -->
        <PerformanceNav
            v-show="!isFullscreen"
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
.fullscreen-toggle {
    opacity: 0.3;
}
.fullscreen-toggle.is-visible {
    opacity: 1;
}
@media (hover: hover) {
    .relative:hover > .fullscreen-toggle {
        opacity: 1;
    }
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
