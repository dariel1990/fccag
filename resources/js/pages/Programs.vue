<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

const programs = [
    {
        category: 'Worship & Prayer',
        color: 'var(--color-primary)',
        items: [
            { name: 'Sunday Morning Worship', schedule: 'Every Sunday at 9:00 AM & 11:00 AM', desc: 'Spirit-filled worship service with preaching, praise, and prayer for all ages.' },
            { name: 'Wednesday Night Prayer', schedule: 'Every Wednesday at 7:00 PM', desc: 'A powerful mid-week gathering focused on prayer, intercession, and Bible study.' },
            { name: 'Early Morning Prayer', schedule: 'Monday–Friday at 5:30 AM', desc: 'Start your day with God through guided prayer and devotional time.' },
        ],
    },
    {
        category: 'Youth & Children',
        color: 'var(--color-accent)',
        items: [
            { name: 'Children\'s Church', schedule: 'Every Sunday during 11:00 AM service', desc: 'Age-appropriate worship and Bible lessons for kids ages 3–12.' },
            { name: 'Youth Friday Night', schedule: 'Every Friday at 6:30 PM', desc: 'Fun, faith-filled evenings for teens including games, worship, and teaching.' },
            { name: 'Vacation Bible School', schedule: 'Annual — held every July', desc: 'A week of Bible adventures, crafts, music, and friendship for children.' },
        ],
    },
    {
        category: 'Community & Outreach',
        color: 'var(--color-secondary)',
        items: [
            { name: 'Community Food Drive', schedule: 'First Saturday of every month', desc: 'Collecting and distributing food to families in need across our community.' },
            { name: 'Home Visitation Ministry', schedule: 'By appointment', desc: 'Pastoral visits to church members, the elderly, and the sick.' },
            { name: 'Street Evangelism', schedule: 'Every third Sunday afternoon', desc: 'Taking the Gospel message to our streets through music, testimony, and prayer.' },
        ],
    },
    {
        category: 'Discipleship & Growth',
        color: 'var(--color-primary)',
        items: [
            { name: 'New Believers Class', schedule: 'Every Sunday at 9:00 AM', desc: 'A foundational class for those new to the faith covering salvation, baptism, and church life.' },
            { name: 'Bible Study Fellowship', schedule: 'Wednesday at 7:00 PM', desc: 'Deep-dive into Scripture with small groups for both men and women.' },
            { name: 'Leadership Development', schedule: 'Monthly sessions', desc: 'Training and equipping the next generation of church leaders and ministers.' },
        ],
    },
];
</script>

<template>
    <PublicLayout title="Programs & Activities">
        <!-- Page Header -->
        <section class="bg-primary py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-secondary text-sm font-semibold uppercase tracking-widest mb-3">Get Involved</p>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Programs &amp; Activities</h1>
                <p class="text-primary-foreground/70 text-lg max-w-2xl mx-auto">
                    We offer a wide range of programs for every age and stage of life. Find your place in our community.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm text-gray-500 flex items-center gap-2">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="text-primary font-medium">Programs</span>
            </nav>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
                <div v-for="stat in [
                    { value: '10+', label: 'Active Ministries' },
                    { value: '500+', label: 'Members Served' },
                    { value: '30+', label: 'Years of Service' },
                    { value: '12', label: 'Monthly Events' },
                ]" :key="stat.label"
                    class="bg-card rounded-2xl p-6 text-center shadow-sm border border-border/50"
                >
                    <p class="text-3xl font-bold text-primary mb-1">{{ stat.value }}</p>
                    <p class="text-gray-500 text-sm">{{ stat.label }}</p>
                </div>
            </div>

            <!-- Programs by Category -->
            <div class="space-y-16">
                <div v-for="program in programs" :key="program.category">
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="w-3 h-3 rounded-full"
                            :style="{ backgroundColor: program.color }"
                        ></div>
                        <h2 class="text-2xl font-bold text-primary">{{ program.category }}</h2>
                        <div class="flex-1 h-px bg-border/50"></div>
                    </div>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div
                            v-for="item in program.items"
                            :key="item.name"
                            class="bg-card rounded-2xl p-6 shadow-sm border border-border/50 hover:shadow-md transition-shadow"
                        >
                            <div
                                class="w-10 h-10 rounded-lg flex items-center justify-center mb-4"
                                :style="{ backgroundColor: `color-mix(in srgb, ${program.color} 12%, transparent)` }"
                            >
                                <svg class="w-5 h-5" :style="{ color: program.color }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-primary mb-2">{{ item.name }}</h3>
                            <p class="text-accent text-xs font-semibold mb-3 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                {{ item.schedule }}
                            </p>
                            <p class="text-gray-600 text-sm leading-relaxed">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-20 bg-gradient-to-r from-primary to-primary/70 rounded-2xl p-10 text-center text-white">
                <h2 class="text-2xl font-bold mb-4">Want to Get Involved?</h2>
                <p class="text-primary-foreground/70 mb-8 max-w-xl mx-auto">
                    We would love to have you join one of our ministries. Reach out and we will help you find the best fit.
                </p>
                <Link href="/about" class="px-8 py-3 bg-secondary text-secondary-foreground font-bold rounded-lg hover:bg-accent hover:text-accent-foreground transition-all text-sm uppercase tracking-wide">
                    Contact Us
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
