<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { create, index } from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type Post = {
    id: number;
    title: string;
    slug: string;
    category: string;
    is_published: boolean;
    published_at: string | null;
    author: string | null;
    created_at: string;
};

defineProps<{
    posts: Post[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Blog Posts', href: index() }];

const deletingId = ref<number | null>(null);

const categoryLabel: Record<string, string> = {
    blog: 'Blog',
    news: 'News',
    announcement: 'Announcement',
};

function confirmDelete(post: Post): void {
    if (!confirm(`Delete "${post.title}"? This cannot be undone.`)) {
        return;
    }
    deletingId.value = post.id;
    router.delete(`/posts/${post.id}`, {
        onFinish: () => (deletingId.value = null),
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <Heading title="Blog Posts" description="Manage your church blog, news, and announcements." />
            <Button as-child>
                <Link :href="create()">New Post</Link>
            </Button>
        </div>

        <div class="rounded-xl border border-border overflow-hidden bg-card">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium">Title</th>
                        <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Category</th>
                        <th class="text-left px-4 py-3 font-medium hidden md:table-cell">Status</th>
                        <th class="text-left px-4 py-3 font-medium hidden lg:table-cell">Published</th>
                        <th class="text-right px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-if="posts.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-muted-foreground">
                            No posts yet.
                            <Link :href="create()" class="text-primary underline ml-1">Create your first post</Link>
                        </td>
                    </tr>
                    <tr v-for="post in posts" :key="post.id" class="hover:bg-muted/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-foreground truncate max-w-xs">{{ post.title }}</div>
                            <div class="text-xs text-muted-foreground mt-0.5">/blog/{{ post.slug }}</div>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full bg-primary/10 text-primary">
                                {{ categoryLabel[post.category] ?? post.category }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span
                                class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full"
                                :class="post.is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :class="post.is_published ? 'bg-green-500' : 'bg-yellow-500'"></span>
                                {{ post.is_published ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell text-muted-foreground text-xs">
                            {{ post.published_at ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/posts/${post.id}/edit`">Edit</Link>
                                </Button>
                                <Button
                                    variant="destructive"
                                    size="sm"
                                    :disabled="deletingId === post.id"
                                    @click="confirmDelete(post)"
                                >
                                    Delete
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
    </AppLayout>
</template>
