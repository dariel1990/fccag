<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout title="Welcome">
        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-primary">
            <div class="absolute inset-0 opacity-10">
                <svg
                    viewBox="0 0 100 100"
                    class="h-full w-full"
                    preserveAspectRatio="xMidYMid slice"
                >
                    <defs>
                        <pattern
                            id="cross-pattern"
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
                    <rect width="100" height="100" fill="url(#cross-pattern)" />
                </svg>
            </div>
            <div
                class="relative mx-auto max-w-7xl px-4 py-24 text-center sm:px-6 md:py-36 lg:px-8"
            >
                <div
                    class="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-full bg-secondary shadow-xl"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-11 w-11 text-secondary-foreground"
                        fill="currentColor"
                    >
                        <path
                            d="M11 2v5H6l6 5-2.5 8L14 17l3.5 3L15 12l6-5h-5V2h-5z"
                        />
                    </svg>
                </div>
                <h1
                    class="mb-6 text-4xl leading-tight font-bold text-white md:text-6xl"
                >
                    Welcome to<br />
                    <span class="text-secondary">Faith Community Church</span
                    ><br />
                    <span
                        class="text-2xl font-normal text-primary-foreground/60 md:text-3xl"
                        >Assembly of God</span
                    >
                </h1>
                <p
                    class="mx-auto mb-10 max-w-2xl text-lg leading-relaxed text-primary-foreground/70 md:text-xl"
                >
                    A place of worship, community, and spiritual growth. Join us
                    as we walk together in faith, love, and service.
                </p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <Link
                        href="/about"
                        class="rounded-lg bg-secondary px-8 py-3 text-sm font-semibold tracking-wide text-secondary-foreground uppercase shadow-md transition-all hover:bg-accent hover:text-accent-foreground"
                    >
                        Learn About Us
                    </Link>
                    <Link
                        href="/programs"
                        class="rounded-lg border-2 border-primary-foreground/40 px-8 py-3 text-sm font-semibold tracking-wide text-primary-foreground/70 uppercase transition-all hover:border-secondary hover:text-secondary"
                    >
                        Our Programs
                    </Link>
                </div>
            </div>
        </section>

        <!-- Service Times Banner -->
        <section class="bg-secondary">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col items-center justify-center gap-6 text-sm font-semibold text-secondary-foreground sm:flex-row"
                >
                    <div class="flex items-center gap-2">
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Sunday Service: 9:00 AM &amp; 11:00 AM
                    </div>
                    <div class="hidden h-4 w-px bg-accent sm:block"></div>
                    <div class="flex items-center gap-2">
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Wednesday Bible Study: 7:00 PM
                    </div>
                    <div class="hidden h-4 w-px bg-accent sm:block"></div>
                    <div class="flex items-center gap-2">
                        <svg
                            class="h-4 w-4"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Friday Youth Night: 6:30 PM
                    </div>
                </div>
            </div>
        </section>

        <!-- Welcome Message -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-24 lg:px-8">
            <div class="grid items-center gap-12 md:grid-cols-2">
                <div>
                    <p
                        class="mb-3 text-sm font-semibold tracking-widest text-accent uppercase"
                    >
                        Our Mission
                    </p>
                    <h2
                        class="mb-6 text-3xl leading-tight font-bold text-primary md:text-4xl"
                    >
                        Building Faith,<br />Building Community
                    </h2>
                    <p class="mb-6 leading-relaxed text-gray-600">
                        At FCCAG, we are committed to sharing the love of Christ
                        through worship, discipleship, fellowship, and service.
                        We believe every person has a God-given purpose and we
                        are here to walk alongside you.
                    </p>
                    <p class="mb-8 leading-relaxed text-gray-600">
                        Whether you are exploring faith for the first time or
                        looking for a church home, you are welcome here. Come as
                        you are.
                    </p>
                    <Link
                        href="/about"
                        class="inline-flex items-center gap-2 font-semibold text-primary transition-colors hover:text-accent"
                    >
                        Discover Our Story
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div
                        class="rounded-2xl border border-border/50 bg-card p-6 text-center shadow-md"
                    >
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-primary">Worship</h3>
                        <p class="text-xs text-gray-500">
                            Spirit-filled services every Sunday
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/50 bg-card p-6 text-center shadow-md"
                    >
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-secondary/20"
                        >
                            <svg
                                class="h-6 w-6 text-accent"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-primary">
                            Discipleship
                        </h3>
                        <p class="text-xs text-gray-500">
                            Bible study and spiritual growth
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/50 bg-card p-6 text-center shadow-md"
                    >
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted"
                        >
                            <svg
                                class="h-6 w-6 text-primary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-primary">
                            Fellowship
                        </h3>
                        <p class="text-xs text-gray-500">
                            Community groups and gatherings
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/50 bg-card p-6 text-center shadow-md"
                    >
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-accent/10"
                        >
                            <svg
                                class="h-6 w-6 text-accent"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-1 font-semibold text-primary">
                            Outreach
                        </h3>
                        <p class="text-xs text-gray-500">
                            Serving our local community
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Programs Preview -->
        <section class="bg-card py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center">
                    <p
                        class="mb-2 text-sm font-semibold tracking-widest text-accent uppercase"
                    >
                        Get Involved
                    </p>
                    <h2 class="text-3xl font-bold text-primary">
                        Our Programs & Ministries
                    </h2>
                </div>
                <div class="grid gap-8 md:grid-cols-3">
                    <div
                        v-for="program in [
                            {
                                title: 'Youth Ministry',
                                desc: 'Empowering the next generation through fun, faith, and friendship.',
                                icon: '🙏',
                            },
                            {
                                title: 'Children\'s Church',
                                desc: 'A safe and joyful environment where kids learn about God\'s love.',
                                icon: '✝️',
                            },
                            {
                                title: 'Women\'s Ministry',
                                desc: 'Building sisterhood and spiritual strength in every season of life.',
                                icon: '🌸',
                            },
                        ]"
                        :key="program.title"
                        class="rounded-2xl border border-border/50 bg-background p-6 transition-shadow hover:shadow-lg"
                    >
                        <div class="mb-4 text-3xl">{{ program.icon }}</div>
                        <h3 class="mb-2 text-lg font-bold text-primary">
                            {{ program.title }}
                        </h3>
                        <p class="mb-4 text-sm leading-relaxed text-gray-600">
                            {{ program.desc }}
                        </p>
                        <Link
                            href="/programs"
                            class="text-sm font-semibold text-accent transition-colors hover:text-primary"
                            >Learn more →</Link
                        >
                    </div>
                </div>
                <div class="mt-10 text-center">
                    <Link
                        href="/programs"
                        class="rounded-lg bg-primary px-8 py-3 text-sm font-semibold tracking-wide text-primary-foreground uppercase shadow-md transition-colors hover:bg-primary/80"
                    >
                        View All Programs
                    </Link>
                </div>
            </div>
        </section>

        <!-- Latest Announcements Preview -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 flex items-center justify-between">
                <div>
                    <p
                        class="mb-1 text-sm font-semibold tracking-widest text-accent uppercase"
                    >
                        Stay Updated
                    </p>
                    <h2 class="text-3xl font-bold text-primary">
                        Latest Announcements
                    </h2>
                </div>
                <Link
                    href="/news"
                    class="hidden items-center gap-1 text-sm font-semibold text-primary transition-colors hover:text-accent sm:inline-flex"
                >
                    View all
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </Link>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <div
                    v-for="item in [
                        {
                            title: 'Annual Church Conference 2026',
                            date: 'March 15, 2026',
                            excerpt:
                                'Join us for our annual conference featuring guest speakers, worship, and workshops. Registration is now open.',
                        },
                        {
                            title: 'Community Outreach Day',
                            date: 'March 22, 2026',
                            excerpt:
                                'We will be serving our local community with food, prayer, and practical assistance. Volunteers needed!',
                        },
                    ]"
                    :key="item.title"
                    class="rounded-2xl border border-border/50 bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                >
                    <p
                        class="mb-2 text-xs font-semibold tracking-wide text-accent uppercase"
                    >
                        {{ item.date }}
                    </p>
                    <h3 class="mb-3 text-lg font-bold text-primary">
                        {{ item.title }}
                    </h3>
                    <p class="mb-4 text-sm leading-relaxed text-gray-600">
                        {{ item.excerpt }}
                    </p>
                    <Link
                        href="/news"
                        class="text-sm font-semibold text-primary transition-colors hover:text-accent"
                        >Read more →</Link
                    >
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-primary py-16">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <h2 class="mb-4 text-3xl font-bold text-white md:text-4xl">
                    You Are Welcome Here
                </h2>
                <p
                    class="mb-8 text-lg leading-relaxed text-primary-foreground/70"
                >
                    We would love to meet you. Join us this Sunday and
                    experience the warmth of our community.
                </p>
                <Link
                    href="/about"
                    class="rounded-lg bg-secondary px-10 py-4 text-sm font-bold tracking-wide text-secondary-foreground uppercase shadow-lg transition-all hover:bg-accent hover:text-accent-foreground"
                >
                    Plan Your Visit
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>
