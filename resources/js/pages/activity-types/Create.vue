<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import {
    index,
    store,
} from '@/actions/App/Http/Controllers/ActivityTypeController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Department = { id: number; name: string };

const props = defineProps<{
    departments: Department[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activity Types',
        href: index().url,
    },
    {
        title: 'Create',
    },
];

const form = useForm({
    name: '',
    description: '',
    is_active: true,
    department_ids: [] as number[],
});

function submit() {
    form.post(store().url);
}
</script>

<template>
    <Head title="Create Activity Type" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Create Activity Type"
                description="Add a new type of church activity"
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
                            placeholder="e.g. Sunday Service"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Brief description of this activity type"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Departments</Label>
                        <Multiselect
                            v-model="form.department_ids"
                            :options="props.departments"
                            label="name"
                            value-prop="id"
                            mode="tags"
                            placeholder="Select departments (leave empty for all members)"
                            :searchable="true"
                            :close-on-select="false"
                        />
                        <p class="text-xs text-muted-foreground">
                            Only members of selected departments will appear in
                            attendance. Leave empty to include all active
                            members.
                        </p>
                        <InputError :message="form.errors.department_ids" />
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
