<script setup lang="ts">
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
                class="perf-nav-btn flex flex-1 flex-col items-start gap-0.5 rounded-lg px-4 py-2 text-left transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                :disabled="currentIndex === 0"
                @click="emit('prev')"
            >
                <span class="text-xs" :style="{ color: 'var(--lt-muted, rgba(255,255,255,0.5))' }">← Previous</span>
                <span v-if="prevTitle" class="text-sm font-medium truncate max-w-[200px]">{{ prevTitle }}</span>
            </button>

            <button
                class="perf-nav-btn flex flex-1 flex-col items-end gap-0.5 rounded-lg px-4 py-2 text-right transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                :disabled="currentIndex === total - 1"
                @click="emit('next')"
            >
                <span class="text-xs" :style="{ color: 'var(--lt-muted, rgba(255,255,255,0.5))' }">Next →</span>
                <span v-if="nextTitle" class="text-sm font-medium truncate max-w-[200px]">{{ nextTitle }}</span>
            </button>
        </div>
    </nav>
</template>

<style scoped>
.perf-nav-btn {
    background-color: transparent;
    -webkit-tap-highlight-color: transparent;
}
@media (hover: hover) {
    .perf-nav-btn:hover:not(:disabled) {
        background-color: var(--lt-btn-hover, rgba(255, 255, 255, 0.1));
    }
}
</style>
