<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';

defineProps<{ breadcrumbs?: BreadcrumbItem[] }>();
</script>

<template>
    <div class="theme-masquerade relative">
        <div class="mq-bg-decor" aria-hidden="true">
            <div class="mq-float absolute left-[6%] top-[10%] text-6xl">🎭</div>
            <div class="mq-float-slow absolute right-[8%] top-[22%] text-5xl">🎭</div>
            <div class="mq-float absolute left-[18%] bottom-[18%] text-4xl" style="animation-delay: -3s">🎭</div>
            <div class="mq-float-slow absolute right-[14%] bottom-[8%] text-5xl" style="animation-delay: -5s">🎭</div>
            <div class="mq-float absolute left-[58%] top-[6%] text-3xl" style="animation-delay: -2s">✨</div>
            <div class="mq-float-slow absolute left-[32%] top-[58%] text-3xl" style="animation-delay: -7s">✨</div>
            <div class="mq-float absolute right-[28%] top-[50%] text-2xl" style="animation-delay: -4s">✨</div>
        </div>

        <main class="relative z-10 mx-auto w-full max-w-md px-4 pb-12 pt-6">
            <slot />
        </main>
    </div>
</template>
