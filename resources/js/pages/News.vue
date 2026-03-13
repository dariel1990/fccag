<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

const announcements = [
    {
        id: 1,
        title: 'Annual Church Conference 2026',
        category: 'Conference',
        date: 'March 15, 2026',
        excerpt: 'Join us for our annual conference featuring guest speakers, worship sessions, and workshops. Early bird registration is now open. All are welcome!',
        featured: true,
    },
    {
        id: 2,
        title: 'Community Outreach Day',
        category: 'Outreach',
        date: 'March 22, 2026',
        excerpt: 'We will be serving our local community with food packs, prayer, and practical assistance. Volunteers are needed — sign up at the church office.',
        featured: false,
    },
    {
        id: 3,
        title: 'Easter Sunday Celebration',
        category: 'Worship',
        date: 'April 5, 2026',
        excerpt: 'Celebrate the risen Christ with us! Special services at 8:00 AM, 10:00 AM, and 12:00 PM. Invite your family and friends.',
        featured: false,
    },
    {
        id: 4,
        title: 'Youth Leadership Camp',
        category: 'Youth',
        date: 'April 18–20, 2026',
        excerpt: 'A three-day camp for young leaders aged 13–25. Sessions on identity, calling, and servant leadership. Limited slots available.',
        featured: false,
    },
    {
        id: 5,
        title: 'New Believers Baptism Sunday',
        category: 'Discipleship',
        date: 'April 27, 2026',
        excerpt: 'We are celebrating water baptism for our new believers! If you have accepted Christ and wish to be baptized, please register with the church office.',
        featured: false,
    },
    {
        id: 6,
        title: 'Mother\'s Day Special Service',
        category: 'Worship',
        date: 'May 11, 2026',
        excerpt: 'Honor the amazing mothers in your life with a special Sunday service. Gifts and surprises await all mothers in attendance.',
        featured: false,
    },
];

const categoryColors: Record<string, string> = {
    Conference: 'var(--color-primary)',
    Outreach: 'var(--color-accent)',
    Worship: 'var(--color-secondary)',
    Youth: 'var(--color-primary)',
    Discipleship: 'var(--color-muted-foreground)',
};
</script>

<template>
    <PublicLayout title="Announcements">
        <!-- Page Header -->
        <section class="bg-primary py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-secondary text-sm font-semibold uppercase tracking-widest mb-3">Stay Connected</p>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Announcements</h1>
                <p class="text-primary-foreground/70 text-lg max-w-2xl mx-auto">
                    Stay up to date with the latest news, events, and updates from FCCAG.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm text-gray-500 flex items-center gap-2">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="text-primary font-medium">Announcements</span>
            </nav>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Featured Announcement -->
            <div class="mb-12">
                <div
                    v-for="item in announcements.filter(a => a.featured)"
                    :key="item.id"
                    class="bg-primary rounded-2xl p-8 md:p-12 text-white relative overflow-hidden"
                >
                    <div class="absolute top-0 right-0 w-48 h-48 opacity-10">
                        <svg viewBox="0 0 100 100" class="w-full h-full">
                            <circle cx="80" cy="20" r="60" fill="white"/>
                        </svg>
                    </div>
                    <div class="relative">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-secondary text-secondary-foreground text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                Featured
                            </span>
                            <span class="text-primary-foreground/70 text-sm">{{ item.date }}</span>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-bold mb-4">{{ item.title }}</h2>
                        <p class="text-primary-foreground/70 leading-relaxed text-lg max-w-3xl">{{ item.excerpt }}</p>
                    </div>
                </div>
            </div>

            <!-- All Announcements Grid -->
            <div>
                <h2 class="text-2xl font-bold text-primary mb-8">All Announcements</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="item in announcements"
                        :key="item.id"
                        class="bg-card rounded-2xl p-6 shadow-sm border border-border/50 hover:shadow-md transition-shadow flex flex-col"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <span
                                class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide"
                                :style="{
                                    backgroundColor: `color-mix(in srgb, ${categoryColors[item.category] || 'var(--color-primary)'} 15%, transparent)`,
                                    color: categoryColors[item.category] || 'var(--color-primary)',
                                }"
                            >
                                {{ item.category }}
                            </span>
                            <span class="text-gray-400 text-xs">{{ item.date }}</span>
                        </div>
                        <h3 class="font-bold text-primary text-lg mb-3 leading-tight">{{ item.title }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed flex-1">{{ item.excerpt }}</p>
                    </div>
                </div>
            </div>

            <!-- Newsletter Signup -->
            <div class="mt-20 bg-background border-2 border-border/50 rounded-2xl p-10 text-center">
                <div class="w-14 h-14 rounded-full bg-primary flex items-center justify-center mx-auto mb-6">
                    <svg class="w-7 h-7 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-primary mb-3">Never Miss an Update</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Check back regularly for the latest announcements, or visit us on Sunday and stay connected with our growing community.
                </p>
                <Link href="/programs" class="px-8 py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/80 transition-colors text-sm uppercase tracking-wide shadow-md">
                    View Our Programs
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
