<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { index, store } from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Blog Posts', href: index() },
    { title: 'New Post', href: '#' },
];

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    body: '',
    category: 'blog',
    is_published: false,
    published_at: '',
});

const autoSlug = computed(() =>
    form.slug ||
    form.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, ''),
);

function submit(): void {
    form.post("/posts", {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
        <Heading title="New Post" description="Create a new blog post, news item, or announcement." />

        <form class="max-w-3xl space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="title">Title <span class="text-destructive">*</span></Label>
                <Input id="title" v-model="form.title" type="text" placeholder="Post title" required autofocus />
                <InputError :message="form.errors.title" />
            </div>

            <div class="grid gap-2">
                <Label for="slug">Slug</Label>
                <Input id="slug" v-model="form.slug" type="text" :placeholder="autoSlug" />
                <p class="text-xs text-muted-foreground">Leave blank to auto-generate from title. URL: /blog/{{ autoSlug }}</p>
                <InputError :message="form.errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="excerpt">Excerpt</Label>
                <textarea
                    id="excerpt"
                    v-model="form.excerpt"
                    rows="2"
                    maxlength="500"
                    placeholder="Short summary shown in listings (optional)"
                    class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                ></textarea>
                <InputError :message="form.errors.excerpt" />
            </div>

            <div class="grid gap-2">
                <Label for="body">Body <span class="text-destructive">*</span></Label>
                <textarea
                    id="body"
                    v-model="form.body"
                    rows="14"
                    required
                    placeholder="Write your post content here..."
                    class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring font-mono"
                ></textarea>
                <InputError :message="form.errors.body" />
            </div>

            <div class="grid sm:grid-cols-2 gap-6">
                <div class="grid gap-2">
                    <Label for="category">Category <span class="text-destructive">*</span></Label>
                    <select
                        id="category"
                        v-model="form.category"
                        class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                        <option value="blog">Blog</option>
                        <option value="news">News</option>
                        <option value="announcement">Announcement</option>
                    </select>
                    <InputError :message="form.errors.category" />
                </div>

                <div class="grid gap-2">
                    <Label for="published_at">Publish Date</Label>
                    <Input id="published_at" v-model="form.published_at" type="datetime-local" />
                    <InputError :message="form.errors.published_at" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input
                    id="is_published"
                    v-model="form.is_published"
                    type="checkbox"
                    class="w-4 h-4 rounded border-input text-primary"
                />
                <Label for="is_published" class="cursor-pointer">Publish immediately</Label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving…' : 'Create Post' }}
                </Button>
                <Button variant="outline" as="a" :href="index().url">Cancel</Button>
            </div>
        </form>
        </div>
    </AppLayout>
</template>
