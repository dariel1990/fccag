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
    <div class="min-h-screen flex flex-col bg-background">
        <Head :title="title ? `${title} — FCCAG` : 'FCCAG'" />

        <!-- Navigation -->
        <header class="bg-primary shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center shadow-md group-hover:bg-accent transition-colors">
                            <svg viewBox="0 0 24 24" class="w-6 h-6 text-secondary-foreground" fill="currentColor">
                                <path d="M12 2L11 8H5l5 4-2 7 4-3 4 3-2-7 5-4h-6L12 2z"/>
                            </svg>
                        </div>
                        <div class="leading-tight">
                            <p class="text-secondary font-bold text-sm tracking-widest uppercase">FCCAG</p>
                            <p class="text-primary-foreground/60 text-xs hidden sm:block">Faith Community Church Assembly of God</p>
                        </div>
                    </Link>

                    <!-- Desktop Nav -->
                    <nav class="hidden md:flex items-center gap-1">
                        <Link
                            v-for="link in navLinks"
                            :key="link.href"
                            :href="link.href"
                            class="px-4 py-2 rounded-md text-sm font-medium text-primary-foreground/70 hover:text-secondary hover:bg-primary/80 transition-all"
                            :class="{ 'text-secondary bg-primary/80': $page.url === link.href || ($page.url.startsWith(link.href) && link.href !== '/') }"
                        >
                            {{ link.label }}
                        </Link>
                    </nav>

                    <!-- Mobile menu button -->
                    <button
                        class="md:hidden text-primary-foreground/70 hover:text-secondary p-2 rounded-md"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t border-primary/60 bg-primary/90">
                <nav class="px-4 py-2 space-y-1">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="block px-3 py-2 rounded-md text-sm font-medium text-primary-foreground/70 hover:text-secondary hover:bg-primary transition-all"
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
        <footer class="bg-primary text-primary-foreground/70 mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-full bg-secondary flex items-center justify-center">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 text-secondary-foreground" fill="currentColor">
                                    <path d="M12 2L11 8H5l5 4-2 7 4-3 4 3-2-7 5-4h-6L12 2z"/>
                                </svg>
                            </div>
                            <span class="text-secondary font-bold tracking-widest text-sm uppercase">FCCAG</span>
                        </div>
                        <p class="text-sm leading-relaxed">
                            Faith Community Church Assembly of God — a community rooted in faith, love, and service.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-secondary font-semibold mb-4 text-sm uppercase tracking-wider">Quick Links</h3>
                        <ul class="space-y-2">
                            <li v-for="link in navLinks" :key="link.href">
                                <Link :href="link.href" class="text-sm hover:text-secondary transition-colors">{{ link.label }}</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-secondary font-semibold mb-4 text-sm uppercase tracking-wider">Connect With Us</h3>
                        <address class="not-italic text-sm space-y-2">
                            <p>Sunday Service: 9:00 AM &amp; 11:00 AM</p>
                            <p>Wednesday Bible Study: 7:00 PM</p>
                        </address>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-primary/60 text-center text-xs">
                    &copy; {{ new Date().getFullYear() }} Faith Community Church Assembly of God. All rights reserved.
                </div>
            </div>
        </footer>
    </div>
</template>
