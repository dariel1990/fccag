<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <PublicLayout title="About the Church">
        <!-- Page Header -->
        <section class="bg-primary py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-secondary text-sm font-semibold uppercase tracking-widest mb-3">Who We Are</p>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">About the Church</h1>
                <p class="text-primary-foreground/70 text-lg max-w-2xl mx-auto">
                    Learn about our faith, our values, and the community we are building together.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm text-gray-500 flex items-center gap-2">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="text-primary font-medium">About</span>
            </nav>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Statement of Faith -->
            <div class="grid md:grid-cols-2 gap-12 items-center mb-20">
                <div>
                    <p class="text-accent text-sm font-semibold uppercase tracking-widest mb-3">Our Identity</p>
                    <h2 class="text-3xl font-bold text-primary mb-6">Faith Community Church Assembly of God</h2>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        We are a Spirit-filled, Bible-believing congregation that is part of the Assemblies of God fellowship — one of the largest Pentecostal denominations in the world.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Our church exists to glorify God and make disciples. We believe in the authority of Scripture, the power of prayer, and the importance of community.
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        We are committed to reaching the lost, nurturing believers, and serving our community in the name of Jesus Christ.
                    </p>
                </div>
                <div class="bg-card rounded-2xl p-8 shadow-md border border-border/50">
                    <h3 class="font-bold text-primary text-lg mb-6 pb-4 border-b border-border/50">Core Beliefs</h3>
                    <ul class="space-y-4">
                        <li v-for="belief in [
                            'The inspiration and authority of the Holy Scriptures',
                            'The one true God — Father, Son, and Holy Spirit',
                            'Salvation through faith in Jesus Christ',
                            'Baptism in the Holy Spirit with evidence of speaking in tongues',
                            'Divine healing through the atonement of Christ',
                            'The bodily resurrection and return of Jesus Christ',
                        ]" :key="belief" class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-secondary flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-secondary-foreground" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span class="text-gray-700 text-sm leading-relaxed">{{ belief }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Mission, Vision, Values -->
            <div class="mb-20">
                <div class="text-center mb-12">
                    <p class="text-accent text-sm font-semibold uppercase tracking-widest mb-2">Purpose &amp; Direction</p>
                    <h2 class="text-3xl font-bold text-primary">Mission, Vision &amp; Values</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-primary rounded-2xl p-8 text-white">
                        <div class="w-12 h-12 rounded-full bg-secondary flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-secondary-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-xl mb-3">Our Mission</h3>
                        <p class="text-primary-foreground/70 text-sm leading-relaxed">
                            To know Christ and make Him known — through authentic worship, transformative discipleship, and compassionate outreach.
                        </p>
                    </div>
                    <div class="bg-secondary rounded-2xl p-8">
                        <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-secondary-foreground text-xl mb-3">Our Vision</h3>
                        <p class="text-secondary-foreground/80 text-sm leading-relaxed">
                            To be a vibrant, Spirit-led church that transforms lives, strengthens families, and impacts our community with the love of God.
                        </p>
                    </div>
                    <div class="bg-card rounded-2xl p-8 shadow-md border border-border/50">
                        <div class="w-12 h-12 rounded-full bg-accent/20 flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-primary text-xl mb-3">Core Values</h3>
                        <ul class="text-gray-600 text-sm space-y-2">
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
                <div class="text-center mb-12">
                    <p class="text-accent text-sm font-semibold uppercase tracking-widest mb-2">Leadership</p>
                    <h2 class="text-3xl font-bold text-primary">Meet Our Pastoral Team</h2>
                </div>
                <div class="grid md:grid-cols-3 gap-8">
                    <div v-for="leader in [
                        { name: 'Pastor John', role: 'Senior Pastor', desc: 'Leading our congregation with wisdom, grace, and a deep love for God\'s Word.' },
                        { name: 'Pastor Maria', role: 'Associate Pastor', desc: 'Overseeing discipleship programs and women\'s ministry with passion and dedication.' },
                        { name: 'Minister James', role: 'Youth Pastor', desc: 'Igniting a passion for God in the hearts of our young people.' },
                    ]" :key="leader.name"
                        class="text-center bg-card rounded-2xl p-8 shadow-sm border border-border/50"
                    >
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-muted flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-primary text-lg">{{ leader.name }}</h3>
                        <p class="text-accent text-xs font-semibold uppercase tracking-wide mb-3">{{ leader.role }}</p>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ leader.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
