<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

type Post = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    category: string;
    author: string | null;
    published_at: string;
};

const props = defineProps<{
    posts: Post[];
}>();

const categoryLabel: Record<string, string> = {
    blog: 'Blog',
    news: 'News',
    announcement: 'Announcement',
};

const categoryColor: Record<string, string> = {
    blog: 'var(--color-primary)',
    news: 'var(--color-accent)',
    announcement: 'var(--color-secondary)',
};

const featuredPost = props.posts[0] ?? null;
const restPosts = props.posts.slice(1);
</script>

<template>
    <PublicLayout title="Blog">
        <!-- Header -->
        <section class="bg-primary py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-secondary text-sm font-semibold uppercase tracking-widest mb-3">From Our Community</p>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Blog &amp; News</h1>
                <p class="text-primary-foreground/70 text-lg max-w-2xl mx-auto">
                    Insights, stories, and updates from Faith Community Church Assembly of God.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm text-gray-500 flex items-center gap-2">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="text-primary font-medium">Blog</span>
            </nav>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            <!-- Empty state -->
            <div v-if="posts.length === 0" class="py-24 text-center">
                <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6m-6 4h6m8-4H7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-primary mb-2">No posts yet</h3>
                <p class="text-gray-500">Check back soon for new articles and updates.</p>
            </div>

            <template v-else>
                <!-- Featured post -->
                <div class="mt-8 mb-12">
                    <Link :href="`/blog/${featuredPost.slug}`" class="group block bg-card rounded-2xl shadow-md border border-border/50 overflow-hidden hover:shadow-xl transition-shadow">
                        <div class="md:flex">
                            <div class="md:w-2/5 bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center p-12 min-h-52">
                                <svg class="w-20 h-20 text-secondary/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6" />
                                </svg>
                            </div>
                            <div class="md:w-3/5 p-8 flex flex-col justify-center">
                                <div class="flex items-center gap-3 mb-4">
                                    <span
                                        class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide"
                                        :style="{ backgroundColor: `color-mix(in srgb, ${categoryColor[featuredPost.category] || 'var(--color-primary)'} 15%, transparent)`, color: categoryColor[featuredPost.category] || 'var(--color-primary)' }"
                                    >{{ categoryLabel[featuredPost.category] ?? featuredPost.category }}</span>
                                    <span class="bg-secondary text-secondary-foreground text-xs font-bold px-2 py-0.5 rounded-full">Featured</span>
                                </div>
                                <h2 class="text-2xl font-bold text-primary mb-3 group-hover:text-accent transition-colors leading-tight">
                                    {{ featuredPost.title }}
                                </h2>
                                <p class="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3">
                                    {{ featuredPost.excerpt ?? 'Read the full article to learn more.' }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-gray-400">
                                        <span v-if="featuredPost.author">By {{ featuredPost.author }} · </span>
                                        {{ featuredPost.published_at }}
                                    </div>
                                    <span class="text-primary text-sm font-semibold group-hover:text-accent transition-colors">
                                        Read more →
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Rest of posts -->
                <div v-if="restPosts.length > 0">
                    <h2 class="text-xl font-bold text-primary mb-6">More Articles</h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link
                            v-for="post in restPosts"
                            :key="post.id"
                            :href="`/blog/${post.slug}`"
                            class="group bg-card rounded-2xl p-6 shadow-sm border border-border/50 hover:shadow-md transition-shadow flex flex-col"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <span
                                    class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide"
                                    :style="{ backgroundColor: `color-mix(in srgb, ${categoryColor[post.category] || 'var(--color-primary)'} 15%, transparent)`, color: categoryColor[post.category] || 'var(--color-primary)' }"
                                >{{ categoryLabel[post.category] ?? post.category }}</span>
                                <span class="text-gray-400 text-xs">{{ post.published_at }}</span>
                            </div>
                            <h3 class="font-bold text-primary text-lg mb-3 leading-tight group-hover:text-accent transition-colors flex-1">
                                {{ post.title }}
                            </h3>
                            <p class="text-gray-500 text-sm leading-relaxed mb-4 line-clamp-2">
                                {{ post.excerpt ?? '' }}
                            </p>
                            <div class="flex items-center justify-between mt-auto pt-4 border-t border-border/30">
                                <span class="text-xs text-gray-400">{{ post.author ?? 'FCCAG' }}</span>
                                <span class="text-primary text-xs font-semibold group-hover:text-accent transition-colors">Read →</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </template>
        </div>
    </PublicLayout>
</template>
