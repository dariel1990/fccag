<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

const programs = [
    {
        category: 'Worship & Prayer',
        color: 'var(--color-primary)',
        items: [
            {
                name: 'Sunday Morning Worship',
                schedule: 'Every Sunday at 9:00 AM & 11:00 AM',
                desc: 'Spirit-filled worship service with preaching, praise, and prayer for all ages.',
            },
            {
                name: 'Wednesday Night Prayer',
                schedule: 'Every Wednesday at 7:00 PM',
                desc: 'A powerful mid-week gathering focused on prayer, intercession, and Bible study.',
            },
            {
                name: 'Early Morning Prayer',
                schedule: 'Monday–Friday at 5:30 AM',
                desc: 'Start your day with God through guided prayer and devotional time.',
            },
        ],
    },
    {
        category: 'Youth & Children',
        color: 'var(--color-accent)',
        items: [
            {
                name: "Children's Church",
                schedule: 'Every Sunday during 11:00 AM service',
                desc: 'Age-appropriate worship and Bible lessons for kids ages 3–12.',
            },
            {
                name: 'Youth Friday Night',
                schedule: 'Every Friday at 6:30 PM',
                desc: 'Fun, faith-filled evenings for teens including games, worship, and teaching.',
            },
            {
                name: 'Vacation Bible School',
                schedule: 'Annual — held every July',
                desc: 'A week of Bible adventures, crafts, music, and friendship for children.',
            },
        ],
    },
    {
        category: 'Community & Outreach',
        color: 'var(--color-secondary)',
        items: [
            {
                name: 'Community Food Drive',
                schedule: 'First Saturday of every month',
                desc: 'Collecting and distributing food to families in need across our community.',
            },
            {
                name: 'Home Visitation Ministry',
                schedule: 'By appointment',
                desc: 'Pastoral visits to church members, the elderly, and the sick.',
            },
            {
                name: 'Street Evangelism',
                schedule: 'Every third Sunday afternoon',
                desc: 'Taking the Gospel message to our streets through music, testimony, and prayer.',
            },
        ],
    },
    {
        category: 'Discipleship & Growth',
        color: 'var(--color-primary)',
        items: [
            {
                name: 'New Believers Class',
                schedule: 'Every Sunday at 9:00 AM',
                desc: 'A foundational class for those new to the faith covering salvation, baptism, and church life.',
            },
            {
                name: 'Bible Study Fellowship',
                schedule: 'Wednesday at 7:00 PM',
                desc: 'Deep-dive into Scripture with small groups for both men and women.',
            },
            {
                name: 'Leadership Development',
                schedule: 'Monthly sessions',
                desc: 'Training and equipping the next generation of church leaders and ministers.',
            },
        ],
    },
];
</script>

<template>
    <PublicLayout title="Programs & Activities">
        <!-- Page Header -->
        <section class="bg-primary py-16">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <p
                    class="mb-3 text-sm font-semibold tracking-widest text-secondary uppercase"
                >
                    Get Involved
                </p>
                <h1 class="mb-4 text-4xl font-bold text-white md:text-5xl">
                    Programs &amp; Activities
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-primary-foreground/70">
                    We offer a wide range of programs for every age and stage of
                    life. Find your place in our community.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="font-medium text-primary">Programs</span>
            </nav>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Stats -->
            <div class="mb-16 grid grid-cols-2 gap-6 md:grid-cols-4">
                <div
                    v-for="stat in [
                        { value: '10+', label: 'Active Ministries' },
                        { value: '500+', label: 'Members Served' },
                        { value: '30+', label: 'Years of Service' },
                        { value: '12', label: 'Monthly Events' },
                    ]"
                    :key="stat.label"
                    class="rounded-2xl border border-border/50 bg-card p-6 text-center shadow-sm"
                >
                    <p class="mb-1 text-3xl font-bold text-primary">
                        {{ stat.value }}
                    </p>
                    <p class="text-sm text-gray-500">{{ stat.label }}</p>
                </div>
            </div>

            <!-- Programs by Category -->
            <div class="space-y-16">
                <div v-for="program in programs" :key="program.category">
                    <div class="mb-8 flex items-center gap-4">
                        <div
                            class="h-3 w-3 rounded-full"
                            :style="{ backgroundColor: program.color }"
                        ></div>
                        <h2 class="text-2xl font-bold text-primary">
                            {{ program.category }}
                        </h2>
                        <div class="h-px flex-1 bg-border/50"></div>
                    </div>
                    <div class="grid gap-6 md:grid-cols-3">
                        <div
                            v-for="item in program.items"
                            :key="item.name"
                            class="rounded-2xl border border-border/50 bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div
                                class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg"
                                :style="{
                                    backgroundColor: `color-mix(in srgb, ${program.color} 12%, transparent)`,
                                }"
                            >
                                <svg
                                    class="h-5 w-5"
                                    :style="{ color: program.color }"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <h3 class="mb-2 font-bold text-primary">
                                {{ item.name }}
                            </h3>
                            <p
                                class="mb-3 flex items-center gap-1 text-xs font-semibold text-accent"
                            >
                                <svg
                                    class="h-3 w-3"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {{ item.schedule }}
                            </p>
                            <p class="text-sm leading-relaxed text-gray-600">
                                {{ item.desc }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div
                class="mt-20 rounded-2xl bg-gradient-to-r from-primary to-primary/70 p-10 text-center text-white"
            >
                <h2 class="mb-4 text-2xl font-bold">Want to Get Involved?</h2>
                <p class="mx-auto mb-8 max-w-xl text-primary-foreground/70">
                    We would love to have you join one of our ministries. Reach
                    out and we will help you find the best fit.
                </p>
                <Link
                    href="/about"
                    class="rounded-lg bg-secondary px-8 py-3 text-sm font-bold tracking-wide text-secondary-foreground uppercase transition-all hover:bg-accent hover:text-accent-foreground"
                >
                    Contact Us
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
