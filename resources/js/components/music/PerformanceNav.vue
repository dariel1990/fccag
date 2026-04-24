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
    <nav class="border-t border-white/10 bg-gray-950 px-4 py-3">
        <div class="flex items-stretch justify-between gap-2 max-w-3xl mx-auto">
            <button
                class="flex flex-1 flex-col items-start gap-0.5 rounded-lg px-4 py-2 text-left transition-colors hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed"
                :disabled="currentIndex === 0"
                @click="emit('prev')"
            >
                <span class="text-xs text-white/50">← Previous</span>
                <span v-if="prevTitle" class="text-sm font-medium truncate max-w-[200px]">{{ prevTitle }}</span>
            </button>

            <button
                class="flex flex-1 flex-col items-end gap-0.5 rounded-lg px-4 py-2 text-right transition-colors hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed"
                :disabled="currentIndex === total - 1"
                @click="emit('next')"
            >
                <span class="text-xs text-white/50">Next →</span>
                <span v-if="nextTitle" class="text-sm font-medium truncate max-w-[200px]">{{ nextTitle }}</span>
            </button>
        </div>
    </nav>
</template>
