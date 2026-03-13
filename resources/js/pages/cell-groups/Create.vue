<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    index,
    store,
} from '@/actions/App/Http/Controllers/CellGroupController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cell Groups',
        href: index().url,
    },
    {
        title: 'Create',
    },
];

const form = useForm({
    name: '',
    leader: '',
    description: '',
    is_active: true,
});

function submit() {
    form.post(store().url);
}
</script>

<template>
    <Head title="Create Cell Group" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Create Cell Group"
                description="Add a new church cell group"
            />

            <div
                class="mx-auto w-full max-w-2xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            required
                            placeholder="e.g. Nehemiah Group"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="leader">Leader</Label>
                        <Input
                            id="leader"
                            v-model="form.leader"
                            placeholder="Leader's name"
                        />
                        <InputError :message="form.errors.leader" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Brief description of this cell group"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="is_active"
                            :model-value="form.is_active"
                            @update:model-value="
                                form.is_active = $event as boolean
                            "
                        />
                        <Label for="is_active">Active</Label>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Create</Button>
                        <Button variant="outline" as-child>
                            <Link :href="index().url">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
