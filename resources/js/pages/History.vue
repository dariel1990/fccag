<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

const milestones = [
    {
        year: '1990',
        title: 'Church Founded',
        description: 'FCCAG was established by a small group of faithful believers with a vision to build a Spirit-led church in the heart of the community.',
    },
    {
        year: '1995',
        title: 'First Permanent Building',
        description: 'After years of meeting in homes and rented spaces, the church moved into its first permanent building, a testament to God\'s faithfulness.',
    },
    {
        year: '2000',
        title: 'Youth Ministry Launch',
        description: 'A dedicated youth ministry was established, creating a space for the next generation to encounter God and grow in their faith.',
    },
    {
        year: '2005',
        title: 'Community Outreach Program',
        description: 'The church launched its community outreach program, serving hundreds of families with food, assistance, and the Gospel.',
    },
    {
        year: '2010',
        title: 'Sanctuary Expansion',
        description: 'With a growing congregation, the church undertook a major expansion of its sanctuary to accommodate more families.',
    },
    {
        year: '2015',
        title: 'Children\'s Ministry Center',
        description: 'A dedicated Children\'s Ministry Center was opened, providing a world-class environment for kids to learn about God\'s love.',
    },
    {
        year: '2020',
        title: 'Digital Ministry Launch',
        description: 'During challenging times, FCCAG embraced online ministry — reaching people across the region with live worship and teaching.',
    },
    {
        year: '2025',
        title: 'New Chapter',
        description: 'As we celebrate over three decades of ministry, FCCAG continues to grow, serve, and trust God for even greater things ahead.',
    },
];
</script>

<template>
    <PublicLayout title="Our History">
        <!-- Page Header -->
        <section class="bg-primary py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-secondary text-sm font-semibold uppercase tracking-widest mb-3">Our Journey</p>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Church History</h1>
                <p class="text-primary-foreground/70 text-lg max-w-2xl mx-auto">
                    A story of faith, perseverance, and God's unceasing faithfulness across the decades.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm text-gray-500 flex items-center gap-2">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="text-primary font-medium">History</span>
            </nav>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Intro -->
            <div class="text-center mb-16">
                <p class="text-gray-600 leading-relaxed text-lg max-w-3xl mx-auto">
                    For over three decades, Faith Community Church Assembly of God has been a pillar of faith in our community. Our history is a testimony of what God can do through ordinary people who say yes to His call.
                </p>
            </div>

            <!-- Timeline -->
            <div class="relative">
                <!-- Center line -->
                <div class="absolute left-1/2 transform -translate-x-px top-0 bottom-0 w-0.5 bg-border"></div>

                <div class="space-y-12">
                    <div
                        v-for="(milestone, index) in milestones"
                        :key="milestone.year"
                        class="relative flex items-start gap-8"
                        :class="index % 2 === 0 ? 'flex-row' : 'flex-row-reverse'"
                    >
                        <!-- Content -->
                        <div class="w-5/12">
                            <div
                                class="bg-card rounded-2xl p-6 shadow-sm border border-border/50 hover:shadow-md transition-shadow"
                                :class="index % 2 === 0 ? 'text-right' : 'text-left'"
                            >
                                <span class="inline-block bg-primary text-secondary text-xs font-bold px-3 py-1 rounded-full mb-3 tracking-widest">
                                    {{ milestone.year }}
                                </span>
                                <h3 class="font-bold text-primary text-lg mb-2">{{ milestone.title }}</h3>
                                <p class="text-gray-600 text-sm leading-relaxed">{{ milestone.description }}</p>
                            </div>
                        </div>

                        <!-- Center dot -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 w-5 h-5 rounded-full bg-secondary border-4 border-primary z-10 mt-4"></div>

                        <!-- Empty spacer -->
                        <div class="w-5/12"></div>
                    </div>
                </div>
            </div>

            <!-- Closing Statement -->
            <div class="mt-20 bg-primary rounded-2xl p-10 text-center text-white">
                <div class="w-16 h-16 rounded-full bg-secondary flex items-center justify-center mx-auto mb-6">
                    <svg viewBox="0 0 24 24" class="w-8 h-8 text-secondary-foreground" fill="currentColor">
                        <path d="M12 2L11 8H5l5 4-2 7 4-3 4 3-2-7 5-4h-6L12 2z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-4">God is Still Writing Our Story</h2>
                <p class="text-primary-foreground/70 leading-relaxed max-w-2xl mx-auto mb-8">
                    The best chapters of FCCAG are yet to come. We invite you to be part of this ongoing story of faith and transformation.
                </p>
                <Link href="/about" class="px-8 py-3 bg-secondary text-secondary-foreground font-bold rounded-lg hover:bg-accent hover:text-accent-foreground transition-all text-sm uppercase tracking-wide">
                    Learn More About Us
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
