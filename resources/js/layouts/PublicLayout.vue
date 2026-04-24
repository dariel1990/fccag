<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    title?: string;
}>();

const mobileMenuOpen = ref(false);

const navLinks = [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'History', href: '/history' },
    { label: 'Programs', href: '/programs' },
    { label: 'Blog', href: '/blog' },
    { label: 'Announcements', href: '/news' },
];
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <Head :title="title ? `${title} — FCCAG` : 'FCCAG'" />

        <!-- Navigation -->
        <header class="sticky top-0 z-50 bg-primary shadow-lg">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <Link href="/" class="group flex items-center gap-3">
                        <img
                            src="/images/fccag-logo.png"
                            alt="FCCAG Logo"
                            class="h-10 w-10 object-contain drop-shadow-md"
                        />
                        <div class="leading-tight">
                            <p
                                class="text-sm font-bold tracking-widest text-secondary uppercase"
                            >
                                FCCAG
                            </p>
                            <p
                                class="hidden text-xs text-primary-foreground/60 sm:block"
                            >
                                Faith Community Church Assembly of God
                            </p>
                        </div>
                    </Link>

                    <!-- Desktop Nav -->
                    <nav class="hidden items-center gap-1 md:flex">
                        <Link
                            v-for="link in navLinks"
                            :key="link.href"
                            :href="link.href"
                            class="rounded-md px-4 py-2 text-sm font-medium text-primary-foreground/70 transition-all hover:bg-primary/80 hover:text-secondary"
                            :class="{
                                'bg-primary/80 text-secondary':
                                    $page.url === link.href ||
                                    ($page.url.startsWith(link.href) &&
                                        link.href !== '/'),
                            }"
                        >
                            {{ link.label }}
                        </Link>
                    </nav>

                    <!-- Mobile menu button -->
                    <button
                        class="rounded-md p-2 text-primary-foreground/70 hover:text-secondary md:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <svg
                            v-if="!mobileMenuOpen"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                        <svg
                            v-else
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav -->
            <div
                v-if="mobileMenuOpen"
                class="border-t border-primary/60 bg-primary/90 md:hidden"
            >
                <nav class="space-y-1 px-4 py-2">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="block rounded-md px-3 py-2 text-sm font-medium text-primary-foreground/70 transition-all hover:bg-primary hover:text-secondary"
                        @click="mobileMenuOpen = false"
                    >
                        {{ link.label }}
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="mt-16 bg-primary text-primary-foreground/70">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div>
                        <div class="mb-4 flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-5 w-5 text-secondary-foreground"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M12 2L11 8H5l5 4-2 7 4-3 4 3-2-7 5-4h-6L12 2z"
                                    />
                                </svg>
                            </div>
                            <span
                                class="text-sm font-bold tracking-widest text-secondary uppercase"
                                >FCCAG</span
                            >
                        </div>
                        <p class="text-sm leading-relaxed">
                            Faith Community Church Assembly of God — a community
                            rooted in faith, love, and service.
                        </p>
                    </div>
                    <div>
                        <h3
                            class="mb-4 text-sm font-semibold tracking-wider text-secondary uppercase"
                        >
                            Quick Links
                        </h3>
                        <ul class="space-y-2">
                            <li v-for="link in navLinks" :key="link.href">
                                <Link
                                    :href="link.href"
                                    class="text-sm transition-colors hover:text-secondary"
                                    >{{ link.label }}</Link
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3
                            class="mb-4 text-sm font-semibold tracking-wider text-secondary uppercase"
                        >
                            Connect With Us
                        </h3>
                        <address class="space-y-2 text-sm not-italic">
                            <p>Sunday Service: 9:00 AM &amp; 11:00 AM</p>
                            <p>Wednesday Bible Study: 7:00 PM</p>
                        </address>
                    </div>
                </div>
                <div
                    class="mt-8 border-t border-primary/60 pt-8 text-center text-xs"
                >
                    &copy; {{ new Date().getFullYear() }} Faith Community Church
                    Assembly of God. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
