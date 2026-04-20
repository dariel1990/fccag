<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen bg-secondary/10">
        <!-- Left Panel - Branding -->
        <div
            class="relative hidden flex-col items-center justify-center overflow-hidden bg-primary p-12 lg:flex lg:w-1/2"
        >
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg
                    viewBox="0 0 100 100"
                    class="h-full w-full"
                    preserveAspectRatio="xMidYMid slice"
                >
                    <defs>
                        <pattern
                            id="cross-bg"
                            x="0"
                            y="0"
                            width="20"
                            height="20"
                            patternUnits="userSpaceOnUse"
                        >
                            <line
                                x1="10"
                                y1="2"
                                x2="10"
                                y2="18"
                                stroke="white"
                                stroke-width="1"
                            />
                            <line
                                x1="2"
                                y1="7"
                                x2="18"
                                y2="7"
                                stroke="white"
                                stroke-width="1"
                            />
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#cross-bg)" />
                </svg>
            </div>
            <div class="relative text-center">
                <div
                    class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-secondary shadow-xl"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="text-primary"
                        style="width: 3.25rem; height: 3.25rem"
                        fill="currentColor"
                    >
                        <path
                            d="M12 2L11 8H5l5 4-2 7 4-3 4 3-2-7 5-4h-6L12 2z"
                        />
                    </svg>
                </div>
                <h1 class="mb-2 text-3xl font-bold tracking-wide text-white">
                    FCCAG
                </h1>
                <p class="mb-8 text-sm leading-relaxed text-white/60">
                    Faith Community Church<br />Assembly of God
                </p>
                <div class="mx-auto mb-8 h-0.5 w-16 bg-secondary"></div>
                <p
                    class="max-w-xs text-sm leading-relaxed text-white/60 italic"
                >
                    "For I know the plans I have for you, declares the Lord,
                    plans to prosper you and not to harm you."<br />
                    <span class="font-semibold text-secondary not-italic"
                        >— Jeremiah 29:11</span
                    >
                </p>
            </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="flex w-full items-center justify-center p-8 lg:w-1/2">
            <div class="w-full max-w-md">
                <!-- Mobile logo (shown on small screens) -->
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-primary"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5 text-secondary"
                            fill="currentColor"
                        >
                            <path
                                d="M12 2L11 8H5l5 4-2 7 4-3 4 3-2-7 5-4h-6L12 2z"
                            />
                        </svg>
                    </div>
                    <div>
                        <p
                            class="text-sm font-bold tracking-widest text-primary"
                        >
                            FCCAG
                        </p>
                        <p class="text-xs text-primary/60">Admin Portal</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="mb-2 text-2xl font-bold text-primary">
                        {{ title }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>

                <slot />

                <div class="mt-8 border-t border-primary/20 pt-6 text-center">
                    <Link
                        href="/"
                        class="text-xs text-primary/60 transition-colors hover:text-primary"
                    >
                        ← Back to church website
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
