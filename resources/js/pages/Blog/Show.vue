<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

type Post = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    body: string;
    category: string;
    author: string | null;
    published_at: string;
};

type RelatedPost = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    category: string;
    published_at: string;
};

defineProps<{
    post: Post;
    related: RelatedPost[];
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
</script>

<template>
    <PublicLayout :title="post.title">
        <!-- Header -->
        <section class="bg-primary py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 mb-6">
                    <span
                        class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide"
                        :style="{ backgroundColor: `color-mix(in srgb, ${categoryColor[post.category] || 'var(--color-primary)'} 20%, transparent)`, color: 'var(--color-secondary)' }"
                    >{{ categoryLabel[post.category] ?? post.category }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight mb-4">
                    {{ post.title }}
                </h1>
                <p v-if="post.excerpt" class="text-primary-foreground/70 text-lg leading-relaxed mb-6">
                    {{ post.excerpt }}
                </p>
                <div class="flex items-center gap-4 text-sm text-primary-foreground/70">
                    <span v-if="post.author">By <span class="text-secondary font-medium">{{ post.author }}</span></span>
                    <span>{{ post.published_at }}</span>
                </div>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="text-sm text-gray-500 flex items-center gap-2 flex-wrap">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <Link href="/blog" class="hover:text-primary">Blog</Link>
                <span>/</span>
                <span class="text-primary font-medium truncate max-w-xs">{{ post.title }}</span>
            </nav>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
            <!-- Article body -->
            <article class="bg-card rounded-2xl shadow-sm border border-border/50 p-8 md:p-12 mb-12">
                <div
                    class="prose prose-lg max-w-none text-gray-700 leading-relaxed whitespace-pre-wrap"
                    style="line-height: 1.9;"
                >{{ post.body }}</div>
            </article>

            <!-- Share / Back -->
            <div class="flex items-center justify-between mb-12">
                <Link href="/blog" class="inline-flex items-center gap-2 text-primary font-semibold hover:text-accent transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Blog
                </Link>
            </div>

            <!-- Related posts -->
            <div v-if="related.length > 0">
                <h2 class="text-xl font-bold text-primary mb-6">More in this category</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <Link
                        v-for="item in related"
                        :key="item.id"
                        :href="`/blog/${item.slug}`"
                        class="group bg-card rounded-2xl p-6 shadow-sm border border-border/50 hover:shadow-md transition-shadow block"
                    >
                        <span
                            class="text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-3 inline-block"
                            :style="{ backgroundColor: `color-mix(in srgb, ${categoryColor[item.category] || 'var(--color-primary)'} 15%, transparent)`, color: categoryColor[item.category] || 'var(--color-primary)' }"
                        >{{ categoryLabel[item.category] ?? item.category }}</span>
                        <h3 class="font-bold text-primary text-base leading-tight mt-3 mb-2 group-hover:text-accent transition-colors">
                            {{ item.title }}
                        </h3>
                        <p class="text-gray-500 text-sm line-clamp-2">{{ item.excerpt ?? '' }}</p>
                        <p class="text-gray-400 text-xs mt-3">{{ item.published_at }}</p>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
