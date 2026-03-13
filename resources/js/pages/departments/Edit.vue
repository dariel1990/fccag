<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/DepartmentController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Department = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    photo_url: string | null;
};

type Props = {
    department: Department;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Departments',
        href: index().url,
    },
    {
        title: 'Edit',
    },
];

const form = useForm({
    name: props.department.name,
    description: props.department.description ?? '',
    photo: null as File | null,
    is_active: props.department.is_active,
});

function onPhotoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    form.photo = input.files?.[0] ?? null;
}

function submit() {
    form.transform((data) => ({ ...data, _method: 'PUT' })).post(
        update(props.department.id).url,
    );
}
</script>

<template>
    <Head title="Edit Department" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Edit Department"
                description="Update the department details"
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
                            placeholder="e.g. Youth Department"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Brief description of this department"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Current Logo / Photo</Label>
                        <div v-if="props.department.photo_url">
                            <img
                                :src="props.department.photo_url"
                                :alt="props.department.name"
                                class="h-20 w-20 rounded-lg object-cover"
                            />
                        </div>
                        <p v-else class="text-muted-foreground text-sm">
                            No photo uploaded.
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="photo">Replace Logo / Photo</Label>
                        <input
                            id="photo"
                            type="file"
                            accept="image/*"
                            class="border-input text-foreground file:text-foreground focus-visible:ring-ring flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-1 disabled:cursor-not-allowed disabled:opacity-50"
                            @change="onPhotoChange"
                        />
                        <InputError :message="form.errors.photo" />
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
