<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    currentIndex: number;
    total: number;
    prevTitle: string | null;
    nextTitle: string | null;
}>();

const emit = defineEmits<{
    prev: [];
    next: [];
}>();

const hoveredBtn = ref<'prev' | 'next' | null>(null);
</script>

<template>
    <nav
        class="px-4 py-3"
        :style="{
            borderTop: '1px solid var(--lt-border, rgba(255,255,255,0.1))',
            backgroundColor: 'var(--lt-bg, #030712)',
        }"
    >
        <div class="flex items-stretch justify-between gap-2 max-w-3xl mx-auto">
            <button
                class="flex flex-1 flex-col items-start gap-0.5 rounded-lg px-4 py-2 text-left transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                :style="{
                    backgroundColor: hoveredBtn === 'prev' ? 'var(--lt-btn-hover, rgba(255,255,255,0.1))' : 'transparent',
                }"
                :disabled="currentIndex === 0"
                @click="emit('prev')"
                @mouseenter="hoveredBtn = 'prev'"
                @mouseleave="hoveredBtn = null"
            >
                <span class="text-xs" :style="{ color: 'var(--lt-muted, rgba(255,255,255,0.5))' }">← Previous</span>
                <span v-if="prevTitle" class="text-sm font-medium truncate max-w-[200px]">{{ prevTitle }}</span>
            </button>

            <button
                class="flex flex-1 flex-col items-end gap-0.5 rounded-lg px-4 py-2 text-right transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                :style="{
                    backgroundColor: hoveredBtn === 'next' ? 'var(--lt-btn-hover, rgba(255,255,255,0.1))' : 'transparent',
                }"
                :disabled="currentIndex === total - 1"
                @click="emit('next')"
                @mouseenter="hoveredBtn = 'next'"
                @mouseleave="hoveredBtn = null"
            >
                <span class="text-xs" :style="{ color: 'var(--lt-muted, rgba(255,255,255,0.5))' }">Next →</span>
                <span v-if="nextTitle" class="text-sm font-medium truncate max-w-[200px]">{{ nextTitle }}</span>
            </button>
        </div>
    </nav>
</template>
