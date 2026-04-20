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
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <p
                    class="mb-3 text-sm font-semibold tracking-widest text-secondary uppercase"
                >
                    From Our Community
                </p>
                <h1 class="mb-4 text-4xl font-bold text-white md:text-5xl">
                    Blog &amp; News
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-primary-foreground/70">
                    Insights, stories, and updates from Faith Community Church
                    Assembly of God.
                </p>
            </div>
        </section>

        <!-- Breadcrumb -->
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-2 text-sm text-gray-500">
                <Link href="/" class="hover:text-primary">Home</Link>
                <span>/</span>
                <span class="font-medium text-primary">Blog</span>
            </nav>
        </div>

        <div class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <!-- Empty state -->
            <div v-if="posts.length === 0" class="py-24 text-center">
                <div
                    class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                >
                    <svg
                        class="h-8 w-8 text-primary"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6m-6 4h6m8-4H7"
                        />
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-bold text-primary">
                    No posts yet
                </h3>
                <p class="text-gray-500">
                    Check back soon for new articles and updates.
                </p>
            </div>

            <template v-else>
                <!-- Featured post -->
                <div class="mt-8 mb-12">
                    <Link
                        :href="`/blog/${featuredPost.slug}`"
                        class="group block overflow-hidden rounded-2xl border border-border/50 bg-card shadow-md transition-shadow hover:shadow-xl"
                    >
                        <div class="md:flex">
                            <div
                                class="flex min-h-52 items-center justify-center bg-gradient-to-br from-primary to-primary/70 p-12 md:w-2/5"
                            >
                                <svg
                                    class="h-20 w-20 text-secondary/40"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 12h6"
                                    />
                                </svg>
                            </div>
                            <div
                                class="flex flex-col justify-center p-8 md:w-3/5"
                            >
                                <div class="mb-4 flex items-center gap-3">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase"
                                        :style="{
                                            backgroundColor: `color-mix(in srgb, ${categoryColor[featuredPost.category] || 'var(--color-primary)'} 15%, transparent)`,
                                            color:
                                                categoryColor[
                                                    featuredPost.category
                                                ] || 'var(--color-primary)',
                                        }"
                                        >{{
                                            categoryLabel[
                                                featuredPost.category
                                            ] ?? featuredPost.category
                                        }}</span
                                    >
                                    <span
                                        class="rounded-full bg-secondary px-2 py-0.5 text-xs font-bold text-secondary-foreground"
                                        >Featured</span
                                    >
                                </div>
                                <h2
                                    class="mb-3 text-2xl leading-tight font-bold text-primary transition-colors group-hover:text-accent"
                                >
                                    {{ featuredPost.title }}
                                </h2>
                                <p
                                    class="mb-6 line-clamp-3 text-sm leading-relaxed text-gray-500"
                                >
                                    {{
                                        featuredPost.excerpt ??
                                        'Read the full article to learn more.'
                                    }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-gray-400">
                                        <span v-if="featuredPost.author"
                                            >By {{ featuredPost.author }} ·
                                        </span>
                                        {{ featuredPost.published_at }}
                                    </div>
                                    <span
                                        class="text-sm font-semibold text-primary transition-colors group-hover:text-accent"
                                    >
                                        Read more →
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Rest of posts -->
                <div v-if="restPosts.length > 0">
                    <h2 class="mb-6 text-xl font-bold text-primary">
                        More Articles
                    </h2>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="post in restPosts"
                            :key="post.id"
                            :href="`/blog/${post.slug}`"
                            class="group flex flex-col rounded-2xl border border-border/50 bg-card p-6 shadow-sm transition-shadow hover:shadow-md"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase"
                                    :style="{
                                        backgroundColor: `color-mix(in srgb, ${categoryColor[post.category] || 'var(--color-primary)'} 15%, transparent)`,
                                        color:
                                            categoryColor[post.category] ||
                                            'var(--color-primary)',
                                    }"
                                    >{{
                                        categoryLabel[post.category] ??
                                        post.category
                                    }}</span
                                >
                                <span class="text-xs text-gray-400">{{
                                    post.published_at
                                }}</span>
                            </div>
                            <h3
                                class="mb-3 flex-1 text-lg leading-tight font-bold text-primary transition-colors group-hover:text-accent"
                            >
                                {{ post.title }}
                            </h3>
                            <p
                                class="mb-4 line-clamp-2 text-sm leading-relaxed text-gray-500"
                            >
                                {{ post.excerpt ?? '' }}
                            </p>
                            <div
                                class="mt-auto flex items-center justify-between border-t border-border/30 pt-4"
                            >
                                <span class="text-xs text-gray-400">{{
                                    post.author ?? 'FCCAG'
                                }}</span>
                                <span
                                    class="text-xs font-semibold text-primary transition-colors group-hover:text-accent"
                                    >Read →</span
                                >
                            </div>
                        </Link>
                    </div>
                </div>
            </template>
        </div>
    </PublicLayout>
</template>
