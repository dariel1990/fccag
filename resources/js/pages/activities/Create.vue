<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    index,
    store,
} from '@/actions/App/Http/Controllers/ActivityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type ActivityType = {
    id: number;
    name: string;
};

type Props = {
    activityTypes?: ActivityType[];
};

const props = defineProps<Props>();

const activityTypes = ref<ActivityType[]>(props.activityTypes || []);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activities',
        href: index().url,
    },
    {
        title: 'Create',
    },
];

const form = useForm({
    activity_type_id: '',
    title: '',
    description: '',
    activity_date: new Date().toISOString().split('T')[0],
});

function submit() {
    form.post(store().url);
}
</script>

<template>
    <Head title="Create Activity" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Create Activity"
                description="Add a new activity session"
            />

            <div
                class="mx-auto w-full max-w-2xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="activity_type_id">Activity Type</Label>
                        <select
                            id="activity_type_id"
                            v-model="form.activity_type_id"
                            required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <option value="" disabled>
                                Select activity type
                            </option>
                            <option
                                v-for="type in activityTypes"
                                :key="type.id"
                                :value="type.id"
                            >
                                {{ type.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.activity_type_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            required
                            placeholder="e.g. Sunday Service - Week 1"
                        />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Optional description"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="activity_date">Date</Label>
                        <Input
                            id="activity_date"
                            v-model="form.activity_date"
                            type="date"
                            required
                        />
                        <InputError :message="form.errors.activity_date" />
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
