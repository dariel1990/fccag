<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/PastorController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Pastor = {
    id: number;
    first_name: string;
    last_name: string;
    title: string | null;
    role: string | null;
    bio: string | null;
    contact_number: string | null;
    email: string | null;
    date_started: string | null;
    is_active: boolean;
    photo_url: string | null;
};

type Props = {
    pastor: Pastor;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pastors',
        href: index().url,
    },
    {
        title: 'Edit',
    },
];

const form = useForm({
    first_name: props.pastor.first_name,
    last_name: props.pastor.last_name,
    title: props.pastor.title ?? '',
    role: props.pastor.role ?? '',
    bio: props.pastor.bio ?? '',
    contact_number: props.pastor.contact_number ?? '',
    email: props.pastor.email ?? '',
    date_started: props.pastor.date_started ?? '',
    photo: null as File | null,
    is_active: props.pastor.is_active,
});

function onPhotoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    form.photo = input.files?.[0] ?? null;
}

function submit() {
    form.transform((data) => ({ ...data, _method: 'PUT' })).post(
        update(props.pastor.id).url,
    );
}
</script>

<template>
    <Head title="Edit Pastor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Edit Pastor"
                description="Update the pastor's details"
            />

            <div
                class="mx-auto w-full max-w-2xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                required
                                placeholder="Juan"
                            />
                            <InputError :message="form.errors.first_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                required
                                placeholder="Dela Cruz"
                            />
                            <InputError :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="title">Title</Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="e.g. Pastor, Rev."
                            />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="role">Role</Label>
                            <Input
                                id="role"
                                v-model="form.role"
                                placeholder="e.g. Senior Pastor"
                            />
                            <InputError :message="form.errors.role" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="bio">Bio</Label>
                        <Input
                            id="bio"
                            v-model="form.bio"
                            placeholder="Short biography"
                        />
                        <InputError :message="form.errors.bio" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="contact_number">Contact Number</Label>
                            <Input
                                id="contact_number"
                                v-model="form.contact_number"
                                placeholder="+63 900 000 0000"
                            />
                            <InputError
                                :message="form.errors.contact_number"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="pastor@church.org"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="date_started">Date Started</Label>
                        <Input
                            id="date_started"
                            type="date"
                            v-model="form.date_started"
                        />
                        <InputError :message="form.errors.date_started" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Current Photo</Label>
                        <div v-if="props.pastor.photo_url">
                            <img
                                :src="props.pastor.photo_url"
                                alt="Current photo"
                                class="h-20 w-20 rounded-full object-cover"
                            />
                        </div>
                        <p v-else class="text-muted-foreground text-sm">
                            No photo uploaded.
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Label for="photo">Replace Photo</Label>
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
