<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout title="About the Church">
        <!-- Page Header -->
        <section class="bg-primary py-16">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <p
                    class="mb-3 text-sm font-semibold tracking-widest text-secondary uppercase"
                >
                    Who We Are
                </p>
                <h1 class="mb-4 text-4xl font-bold text-white md:text-5xl">
                    About the Church
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-primary-foreground/70">
                    Learn about our faith, our values, and the community we are
                    building together.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="font-medium text-primary">About</span>
            </nav>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <!-- Statement of Faith -->
            <div class="mb-20 grid items-center gap-12 md:grid-cols-2">
                <div>
                    <p
                        class="mb-3 text-sm font-semibold tracking-widest text-accent uppercase"
                    >
                        Our Identity
                    </p>
                    <h2 class="mb-6 text-3xl font-bold text-primary">
                        Faith Community Church Assembly of God
                    </h2>
                    <p class="mb-4 leading-relaxed text-gray-600">
                        We are a Spirit-filled, Bible-believing congregation
                        that is part of the Assemblies of God fellowship — one
                        of the largest Pentecostal denominations in the world.
                    </p>
                    <p class="mb-4 leading-relaxed text-gray-600">
                        Our church exists to glorify God and make disciples. We
                        believe in the authority of Scripture, the power of
                        prayer, and the importance of community.
                    </p>
                    <p class="leading-relaxed text-gray-600">
                        We are committed to reaching the lost, nurturing
                        believers, and serving our community in the name of
                        Jesus Christ.
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-border/50 bg-card p-8 shadow-md"
                >
                    <h3
                        class="mb-6 border-b border-border/50 pb-4 text-lg font-bold text-primary"
                    >
                        Core Beliefs
                    </h3>
                    <ul class="space-y-4">
                        <li
                            v-for="belief in [
                                'The inspiration and authority of the Holy Scriptures',
                                'The one true God — Father, Son, and Holy Spirit',
                                'Salvation through faith in Jesus Christ',
                                'Baptism in the Holy Spirit with evidence of speaking in tongues',
                                'Divine healing through the atonement of Christ',
                                'The bodily resurrection and return of Jesus Christ',
                            ]"
                            :key="belief"
                            class="flex items-start gap-3"
                        >
                            <div
                                class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-secondary"
                            >
                                <svg
                                    class="h-3 w-3 text-secondary-foreground"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <span
                                class="text-sm leading-relaxed text-gray-700"
                                >{{ belief }}</span
                            >
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Mission, Vision, Values -->
            <div class="mb-20">
                <div class="mb-12 text-center">
                    <p
                        class="mb-2 text-sm font-semibold tracking-widest text-accent uppercase"
                    >
                        Purpose &amp; Direction
                    </p>
                    <h2 class="text-3xl font-bold text-primary">
                        Mission, Vision &amp; Values
                    </h2>
                </div>
                <div class="grid gap-8 md:grid-cols-3">
                    <div class="rounded-2xl bg-primary p-8 text-white">
                        <div
                            class="mb-6 flex h-12 w-12 items-center justify-center rounded-full bg-secondary"
                        >
                            <svg
                                class="h-6 w-6 text-secondary-foreground"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold">Our Mission</h3>
                        <p
                            class="text-sm leading-relaxed text-primary-foreground/70"
                        >
                            To know Christ and make Him known — through
                            authentic worship, transformative discipleship, and
                            compassionate outreach.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-secondary p-8">
                        <div
                            class="mb-6 flex h-12 w-12 items-center justify-center rounded-full bg-primary"
                        >
                            <svg
                                class="h-6 w-6 text-secondary"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>
                        </div>
                        <h3
                            class="mb-3 text-xl font-bold text-secondary-foreground"
                        >
                            Our Vision
                        </h3>
                        <p
                            class="text-sm leading-relaxed text-secondary-foreground/80"
                        >
                            To be a vibrant, Spirit-led church that transforms
                            lives, strengthens families, and impacts our
                            community with the love of God.
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-border/50 bg-card p-8 shadow-md"
                    >
                        <div
                            class="mb-6 flex h-12 w-12 items-center justify-center rounded-full bg-accent/20"
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
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-primary">
                            Core Values
                        </h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>• Biblical Authority</li>
                            <li>• Spirit-Led Worship</li>
                            <li>• Authentic Community</li>
                            <li>• Compassionate Service</li>
                            <li>• Evangelistic Passion</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Leadership -->
            <div>
                <div class="mb-12 text-center">
                    <p
                        class="mb-2 text-sm font-semibold tracking-widest text-accent uppercase"
                    >
                        Leadership
                    </p>
                    <h2 class="text-3xl font-bold text-primary">
                        Meet Our Pastoral Team
                    </h2>
                </div>
                <div class="grid gap-8 md:grid-cols-3">
                    <div
                        v-for="leader in [
                            {
                                name: 'Pastor John',
                                role: 'Senior Pastor',
                                desc: 'Leading our congregation with wisdom, grace, and a deep love for God\'s Word.',
                            },
                            {
                                name: 'Pastor Maria',
                                role: 'Associate Pastor',
                                desc: 'Overseeing discipleship programs and women\'s ministry with passion and dedication.',
                            },
                            {
                                name: 'Minister James',
                                role: 'Youth Pastor',
                                desc: 'Igniting a passion for God in the hearts of our young people.',
                            },
                        ]"
                        :key="leader.name"
                        class="rounded-2xl border border-border/50 bg-card p-8 text-center shadow-sm"
                    >
                        <div
                            class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-primary to-muted"
                        >
                            <svg
                                class="h-10 w-10 text-white"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"
                                />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-primary">
                            {{ leader.name }}
                        </h3>
                        <p
                            class="mb-3 text-xs font-semibold tracking-wide text-accent uppercase"
                        >
                            {{ leader.role }}
                        </p>
                        <p class="text-sm leading-relaxed text-gray-600">
                            {{ leader.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
