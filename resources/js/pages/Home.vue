<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout title="Welcome">
        <!-- Hero Section -->
        <section class="relative bg-primary overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg viewBox="0 0 100 100" class="w-full h-full" preserveAspectRatio="xMidYMid slice">
                    <defs>
                        <pattern id="cross-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                            <line x1="10" y1="2" x2="10" y2="18" stroke="white" stroke-width="1"/>
                            <line x1="2" y1="7" x2="18" y2="7" stroke="white" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#cross-pattern)"/>
                </svg>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-36 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-secondary shadow-xl mb-8">
                    <svg viewBox="0 0 24 24" class="w-11 h-11 text-secondary-foreground" fill="currentColor">
                        <path d="M11 2v5H6l6 5-2.5 8L14 17l3.5 3L15 12l6-5h-5V2h-5z"/>
                    </svg>
                </div>
                <h1 class="text-4xl md:text-6xl font-bold text-white leading-tight mb-6">
                    Welcome to<br>
                    <span class="text-secondary">Faith Community Church</span><br>
                    <span class="text-primary-foreground/60 text-2xl md:text-3xl font-normal">Assembly of God</span>
                </h1>
                <p class="text-primary-foreground/70 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
                    A place of worship, community, and spiritual growth. Join us as we walk together in faith, love, and service.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/about" class="px-8 py-3 bg-secondary text-secondary-foreground font-semibold rounded-lg shadow-md hover:bg-accent hover:text-accent-foreground transition-all text-sm uppercase tracking-wide">
                        Learn About Us
                    </Link>
                    <Link href="/programs" class="px-8 py-3 border-2 border-primary-foreground/40 text-primary-foreground/70 font-semibold rounded-lg hover:border-secondary hover:text-secondary transition-all text-sm uppercase tracking-wide">
                        Our Programs
                    </Link>
                </div>
            </div>
        </section>

        <!-- Service Times Banner -->
        <section class="bg-secondary">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 text-secondary-foreground text-sm font-semibold">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Sunday Service: 9:00 AM &amp; 11:00 AM
                    </div>
                    <div class="hidden sm:block w-px h-4 bg-accent"></div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Wednesday Bible Study: 7:00 PM
                    </div>
                    <div class="hidden sm:block w-px h-4 bg-accent"></div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Friday Youth Night: 6:30 PM
                    </div>
                </div>
            </div>
        </section>

        <!-- Welcome Message -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-accent text-sm font-semibold uppercase tracking-widest mb-3">Our Mission</p>
                    <h2 class="text-3xl md:text-4xl font-bold text-primary mb-6 leading-tight">
                        Building Faith,<br>Building Community
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        At FCCAG, we are committed to sharing the love of Christ through worship, discipleship, fellowship, and service. We believe every person has a God-given purpose and we are here to walk alongside you.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-8">
                        Whether you are exploring faith for the first time or looking for a church home, you are welcome here. Come as you are.
                    </p>
                    <Link href="/about" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-accent transition-colors">
                        Discover Our Story
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-card rounded-2xl p-6 shadow-md border border-border/50 text-center">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-primary mb-1">Worship</h3>
                        <p class="text-xs text-gray-500">Spirit-filled services every Sunday</p>
                    </div>
                    <div class="bg-card rounded-2xl p-6 shadow-md border border-border/50 text-center">
                        <div class="w-12 h-12 rounded-full bg-secondary/20 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-primary mb-1">Discipleship</h3>
                        <p class="text-xs text-gray-500">Bible study and spiritual growth</p>
                    </div>
                    <div class="bg-card rounded-2xl p-6 shadow-md border border-border/50 text-center">
                        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-primary mb-1">Fellowship</h3>
                        <p class="text-xs text-gray-500">Community groups and gatherings</p>
                    </div>
                    <div class="bg-card rounded-2xl p-6 shadow-md border border-border/50 text-center">
                        <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-primary mb-1">Outreach</h3>
                        <p class="text-xs text-gray-500">Serving our local community</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Programs Preview -->
        <section class="bg-card py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <p class="text-accent text-sm font-semibold uppercase tracking-widest mb-2">Get Involved</p>
                    <h2 class="text-3xl font-bold text-primary">Our Programs & Ministries</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="program in [
                        { title: 'Youth Ministry', desc: 'Empowering the next generation through fun, faith, and friendship.', icon: '🙏' },
                        { title: 'Children\'s Church', desc: 'A safe and joyful environment where kids learn about God\'s love.', icon: '✝️' },
                        { title: 'Women\'s Ministry', desc: 'Building sisterhood and spiritual strength in every season of life.', icon: '🌸' },
                    ]" :key="program.title"
                        class="rounded-2xl p-6 border border-border/50 hover:shadow-lg transition-shadow bg-background"
                    >
                        <div class="text-3xl mb-4">{{ program.icon }}</div>
                        <h3 class="font-bold text-primary text-lg mb-2">{{ program.title }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ program.desc }}</p>
                        <Link href="/programs" class="text-accent text-sm font-semibold hover:text-primary transition-colors">Learn more →</Link>
                    </div>
                </div>
                <div class="text-center mt-10">
                    <Link href="/programs" class="px-8 py-3 bg-primary text-primary-foreground font-semibold rounded-lg hover:bg-primary/80 transition-colors text-sm uppercase tracking-wide shadow-md">
                        View All Programs
                    </Link>
                </div>
            </div>
        </section>

        <!-- Latest Announcements Preview -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <p class="text-accent text-sm font-semibold uppercase tracking-widest mb-1">Stay Updated</p>
                    <h2 class="text-3xl font-bold text-primary">Latest Announcements</h2>
                </div>
                <Link href="/news" class="hidden sm:inline-flex items-center gap-1 text-primary font-semibold hover:text-accent transition-colors text-sm">
                    View all
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
                <div v-for="item in [
                    { title: 'Annual Church Conference 2026', date: 'March 15, 2026', excerpt: 'Join us for our annual conference featuring guest speakers, worship, and workshops. Registration is now open.' },
                    { title: 'Community Outreach Day', date: 'March 22, 2026', excerpt: 'We will be serving our local community with food, prayer, and practical assistance. Volunteers needed!' },
                ]" :key="item.title"
                    class="bg-card rounded-2xl p-6 shadow-sm border border-border/50 hover:shadow-md transition-shadow"
                >
                    <p class="text-accent text-xs font-semibold uppercase tracking-wide mb-2">{{ item.date }}</p>
                    <h3 class="font-bold text-primary text-lg mb-3">{{ item.title }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ item.excerpt }}</p>
                    <Link href="/news" class="text-primary text-sm font-semibold hover:text-accent transition-colors">Read more →</Link>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-primary py-16">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">You Are Welcome Here</h2>
                <p class="text-primary-foreground/70 text-lg mb-8 leading-relaxed">
                    We would love to meet you. Join us this Sunday and experience the warmth of our community.
                </p>
                <Link href="/about" class="px-10 py-4 bg-secondary text-secondary-foreground font-bold rounded-lg hover:bg-accent hover:text-accent-foreground transition-all shadow-lg text-sm uppercase tracking-wide">
                    Plan Your Visit
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>
