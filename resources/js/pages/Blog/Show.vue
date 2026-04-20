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
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center gap-3">
                    <span
                        class="rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase"
                        :style="{
                            backgroundColor: `color-mix(in srgb, ${categoryColor[post.category] || 'var(--color-primary)'} 20%, transparent)`,
                            color: 'var(--color-secondary)',
                        }"
                        >{{
                            categoryLabel[post.category] ?? post.category
                        }}</span
                    >
                </div>
                <h1
                    class="mb-4 text-3xl leading-tight font-bold text-white md:text-4xl"
                >
                    {{ post.title }}
                </h1>
                <p
                    v-if="post.excerpt"
                    class="mb-6 text-lg leading-relaxed text-primary-foreground/70"
                >
                    {{ post.excerpt }}
                </p>
                <div
                    class="flex items-center gap-4 text-sm text-primary-foreground/70"
                >
                    <span v-if="post.author"
                        >By
                        <span class="font-medium text-secondary">{{
                            post.author
                        }}</span></span
                    >
                    <span>{{ post.published_at }}</span>
                </div>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="mx-auto max-w-4xl px-4 py-4 sm:px-6 lg:px-8">
            <nav
                class="flex flex-wrap items-center gap-2 text-sm text-gray-500"
            >
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <Link href="/blog" class="hover:text-primary">Blog</Link>
                <span>/</span>
                <span class="max-w-xs truncate font-medium text-primary">{{
                    post.title
                }}</span>
            </nav>
        </div>

        <div class="mx-auto max-w-4xl px-4 pb-20 sm:px-6 lg:px-8">
            <!-- Article body -->
            <article
                class="mb-12 rounded-2xl border border-border/50 bg-card p-8 shadow-sm md:p-12"
            >
                <div
                    class="prose prose-lg max-w-none leading-relaxed whitespace-pre-wrap text-gray-700"
                    style="line-height: 1.9"
                >
                    {{ post.body }}
                </div>
            </article>

            <!-- Share / Back -->
            <div class="mb-12 flex items-center justify-between">
                <Link
                    href="/blog"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition-colors hover:text-accent"
                >
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
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    Back to Blog
                </Link>
            </div>

            <!-- Related posts -->
            <div v-if="related.length > 0">
                <h2 class="mb-6 text-xl font-bold text-primary">
                    More in this category
                </h2>
                <div class="grid gap-6 md:grid-cols-3">
                    <Link
                        v-for="item in related"
                        :key="item.id"
                        :href="`/blog/${item.slug}`"
                        class="group block rounded-2xl border border-border/50 bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                    >
                        <span
                            class="mb-3 inline-block rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase"
                            :style="{
                                backgroundColor: `color-mix(in srgb, ${categoryColor[item.category] || 'var(--color-primary)'} 15%, transparent)`,
                                color:
                                    categoryColor[item.category] ||
                                    'var(--color-primary)',
                            }"
                            >{{
                                categoryLabel[item.category] ?? item.category
                            }}</span
                        >
                        <h3
                            class="mt-3 mb-2 text-base leading-tight font-bold text-primary transition-colors group-hover:text-accent"
                        >
                            {{ item.title }}
                        </h3>
                        <p class="line-clamp-2 text-sm text-gray-500">
                            {{ item.excerpt ?? '' }}
                        </p>
                        <p class="mt-3 text-xs text-gray-400">
                            {{ item.published_at }}
                        </p>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
