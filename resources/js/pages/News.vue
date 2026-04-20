<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

const announcements = [
    {
        id: 1,
        title: 'Annual Church Conference 2026',
        category: 'Conference',
        date: 'March 15, 2026',
        excerpt:
            'Join us for our annual conference featuring guest speakers, worship sessions, and workshops. Early bird registration is now open. All are welcome!',
        featured: true,
    },
    {
        id: 2,
        title: 'Community Outreach Day',
        category: 'Outreach',
        date: 'March 22, 2026',
        excerpt:
            'We will be serving our local community with food packs, prayer, and practical assistance. Volunteers are needed — sign up at the church office.',
        featured: false,
    },
    {
        id: 3,
        title: 'Easter Sunday Celebration',
        category: 'Worship',
        date: 'April 5, 2026',
        excerpt:
            'Celebrate the risen Christ with us! Special services at 8:00 AM, 10:00 AM, and 12:00 PM. Invite your family and friends.',
        featured: false,
    },
    {
        id: 4,
        title: 'Youth Leadership Camp',
        category: 'Youth',
        date: 'April 18–20, 2026',
        excerpt:
            'A three-day camp for young leaders aged 13–25. Sessions on identity, calling, and servant leadership. Limited slots available.',
        featured: false,
    },
    {
        id: 5,
        title: 'New Believers Baptism Sunday',
        category: 'Discipleship',
        date: 'April 27, 2026',
        excerpt:
            'We are celebrating water baptism for our new believers! If you have accepted Christ and wish to be baptized, please register with the church office.',
        featured: false,
    },
    {
        id: 6,
        title: "Mother's Day Special Service",
        category: 'Worship',
        date: 'May 11, 2026',
        excerpt:
            'Honor the amazing mothers in your life with a special Sunday service. Gifts and surprises await all mothers in attendance.',
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
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <p
                    class="mb-3 text-sm font-semibold tracking-widest text-secondary uppercase"
                >
                    Stay Connected
                </p>
                <h1 class="mb-4 text-4xl font-bold text-white md:text-5xl">
                    Announcements
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-primary-foreground/70">
                    Stay up to date with the latest news, events, and updates
                    from FCCAG.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="font-medium text-primary">Announcements</span>
            </nav>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Featured Announcement -->
            <div class="mb-12">
                <div
                    v-for="item in announcements.filter((a) => a.featured)"
                    :key="item.id"
                    class="relative overflow-hidden rounded-2xl bg-primary p-8 text-white md:p-12"
                >
                    <div class="absolute top-0 right-0 h-48 w-48 opacity-10">
                        <svg viewBox="0 0 100 100" class="h-full w-full">
                            <circle cx="80" cy="20" r="60" fill="white" />
                        </svg>
                    </div>
                    <div class="relative">
                        <div class="mb-4 flex items-center gap-3">
                            <span
                                class="rounded-full bg-secondary px-3 py-1 text-xs font-bold tracking-wide text-secondary-foreground uppercase"
                            >
                                Featured
                            </span>
                            <span class="text-sm text-primary-foreground/70">{{
                                item.date
                            }}</span>
                        </div>
                        <h2 class="mb-4 text-2xl font-bold md:text-3xl">
                            {{ item.title }}
                        </h2>
                        <p
                            class="max-w-3xl text-lg leading-relaxed text-primary-foreground/70"
                        >
                            {{ item.excerpt }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- All Announcements Grid -->
            <div>
                <h2 class="mb-8 text-2xl font-bold text-primary">
                    All Announcements
                </h2>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="item in announcements"
                        :key="item.id"
                        class="flex flex-col rounded-2xl border border-border/50 bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase"
                                :style="{
                                    backgroundColor: `color-mix(in srgb, ${categoryColors[item.category] || 'var(--color-primary)'} 15%, transparent)`,
                                    color:
                                        categoryColors[item.category] ||
                                        'var(--color-primary)',
                                }"
                            >
                                {{ item.category }}
                            </span>
                            <span class="text-xs text-gray-400">{{
                                item.date
                            }}</span>
                        </div>
                        <h3
                            class="mb-3 text-lg leading-tight font-bold text-primary"
                        >
                            {{ item.title }}
                        </h3>
                        <p class="flex-1 text-sm leading-relaxed text-gray-600">
                            {{ item.excerpt }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Newsletter Signup -->
            <div
                class="mt-20 rounded-2xl border-2 border-border/50 bg-background p-10 text-center"
            >
                <div
                    class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-primary"
                >
                    <svg
                        class="h-7 w-7 text-secondary"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>
                </div>
                <h2 class="mb-3 text-2xl font-bold text-primary">
                    Never Miss an Update
                </h2>
                <p class="mx-auto mb-8 max-w-md text-gray-600">
                    Check back regularly for the latest announcements, or visit
                    us on Sunday and stay connected with our growing community.
                </p>
                <Link
                    href="/programs"
                    class="rounded-lg bg-primary px-8 py-3 text-sm font-semibold tracking-wide text-primary-foreground uppercase shadow-md transition-colors hover:bg-primary/80"
                >
                    View Our Programs
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
