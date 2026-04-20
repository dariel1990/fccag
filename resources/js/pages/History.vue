<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

const milestones = [
    {
        year: '1990',
        title: 'Church Founded',
        description:
            'FCCAG was established by a small group of faithful believers with a vision to build a Spirit-led church in the heart of the community.',
    },
    {
        year: '1995',
        title: 'First Permanent Building',
        description:
            "After years of meeting in homes and rented spaces, the church moved into its first permanent building, a testament to God's faithfulness.",
    },
    {
        year: '2000',
        title: 'Youth Ministry Launch',
        description:
            'A dedicated youth ministry was established, creating a space for the next generation to encounter God and grow in their faith.',
    },
    {
        year: '2005',
        title: 'Community Outreach Program',
        description:
            'The church launched its community outreach program, serving hundreds of families with food, assistance, and the Gospel.',
    },
    {
        year: '2010',
        title: 'Sanctuary Expansion',
        description:
            'With a growing congregation, the church undertook a major expansion of its sanctuary to accommodate more families.',
    },
    {
        year: '2015',
        title: "Children's Ministry Center",
        description:
            "A dedicated Children's Ministry Center was opened, providing a world-class environment for kids to learn about God's love.",
    },
    {
        year: '2020',
        title: 'Digital Ministry Launch',
        description:
            'During challenging times, FCCAG embraced online ministry — reaching people across the region with live worship and teaching.',
    },
    {
        year: '2025',
        title: 'New Chapter',
        description:
            'As we celebrate over three decades of ministry, FCCAG continues to grow, serve, and trust God for even greater things ahead.',
    },
];
</script>

<template>
    <PublicLayout title="Our History">
        <!-- Page Header -->
        <section class="bg-primary py-16">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <p
                    class="mb-3 text-sm font-semibold tracking-widest text-secondary uppercase"
                >
                    Our Journey
                </p>
                <h1 class="mb-4 text-4xl font-bold text-white md:text-5xl">
                    Church History
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-primary-foreground/70">
                    A story of faith, perseverance, and God's unceasing
                    faithfulness across the decades.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="font-medium text-primary">History</span>
            </nav>
        </div>

        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Intro -->
            <div class="mb-16 text-center">
                <p
                    class="mx-auto max-w-3xl text-lg leading-relaxed text-gray-600"
                >
                    For over three decades, Faith Community Church Assembly of
                    God has been a pillar of faith in our community. Our history
                    is a testimony of what God can do through ordinary people
                    who say yes to His call.
                </p>
            </div>

            <!-- Timeline -->
            <div class="relative">
                <!-- Center line -->
                <div
                    class="absolute top-0 bottom-0 left-1/2 w-0.5 -translate-x-px transform bg-border"
                ></div>

                <div class="space-y-12">
                    <div
                        v-for="(milestone, index) in milestones"
                        :key="milestone.year"
                        class="relative flex items-start gap-8"
                        :class="
                            index % 2 === 0 ? 'flex-row' : 'flex-row-reverse'
                        "
                    >
                        <!-- Content -->
                        <div class="w-5/12">
                            <div
                                class="rounded-2xl border border-border/50 bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                                :class="
                                    index % 2 === 0 ? 'text-right' : 'text-left'
                                "
                            >
                                <span
                                    class="mb-3 inline-block rounded-full bg-primary px-3 py-1 text-xs font-bold tracking-widest text-secondary"
                                >
                                    {{ milestone.year }}
                                </span>
                                <h3 class="mb-2 text-lg font-bold text-primary">
                                    {{ milestone.title }}
                                </h3>
                                <p
                                    class="text-sm leading-relaxed text-gray-600"
                                >
                                    {{ milestone.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Center dot -->
                        <div
                            class="absolute left-1/2 z-10 mt-4 h-5 w-5 -translate-x-1/2 transform rounded-full border-4 border-primary bg-secondary"
                        ></div>

                        <!-- Empty spacer -->
                        <div class="w-5/12"></div>
                    </div>
                </div>
            </div>

            <!-- Closing Statement -->
            <div
                class="mt-20 rounded-2xl bg-primary p-10 text-center text-white"
            >
                <div
                    class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-secondary"
                >
                    <svg
                        viewBox="0 0 24 24"
                        class="h-8 w-8 text-secondary-foreground"
                        fill="currentColor"
                    >
                        <path
                            d="M12 2L11 8H5l5 4-2 7 4-3 4 3-2-7 5-4h-6L12 2z"
                        />
                    </svg>
                </div>
                <h2 class="mb-4 text-2xl font-bold">
                    God is Still Writing Our Story
                </h2>
                <p
                    class="mx-auto mb-8 max-w-2xl leading-relaxed text-primary-foreground/70"
                >
                    The best chapters of FCCAG are yet to come. We invite you to
                    be part of this ongoing story of faith and transformation.
                </p>
                <Link
                    href="/about"
                    class="rounded-lg bg-secondary px-8 py-3 text-sm font-bold tracking-wide text-secondary-foreground uppercase transition-all hover:bg-accent hover:text-accent-foreground"
                >
                    Learn More About Us
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
