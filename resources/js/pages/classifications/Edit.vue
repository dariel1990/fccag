<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/ClassificationController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Classification = {
    id: number;
    name: string;
    code: string;
    description: string | null;
};

type Props = {
    classification: Classification;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Classifications',
        href: index().url,
    },
    {
        title: 'Edit',
    },
];

const form = useForm({
    name: props.classification.name,
    code: props.classification.code,
    description: props.classification.description ?? '',
});

function submit() {
    form.put(update(props.classification.id).url);
}
</script>

<template>
    <Head title="Edit Classification" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Edit Classification"
                description="Update the classification details"
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
                            placeholder="e.g. Regular Member"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="code">Code</Label>
                        <Input
                            id="code"
                            v-model="form.code"
                            required
                            placeholder="e.g. RM"
                            class="uppercase"
                        />
                        <p class="text-muted-foreground text-sm">
                            Use uppercase letters for consistency.
                        </p>
                        <InputError :message="form.errors.code" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Brief description of this classification"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Update</Button>
                        <Button variant="outline" as-child>
                            <Link :href="index().url">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
