<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storePastor,
    update as updatePastor,
} from '@/actions/App/Http/Controllers/PastorController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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

const props = defineProps<{
    open: boolean;
    pastor?: Pastor | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    first_name: '',
    last_name: '',
    title: '',
    role: '',
    bio: '',
    contact_number: '',
    email: '',
    date_started: '',
    photo: null as File | null,
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.pastor) {
                isEdit.value = true;
                form.value = {
                    first_name: props.pastor.first_name,
                    last_name: props.pastor.last_name,
                    title: props.pastor.title ?? '',
                    role: props.pastor.role ?? '',
                    bio: props.pastor.bio ?? '',
                    contact_number: props.pastor.contact_number ?? '',
                    email: props.pastor.email ?? '',
                    date_started: props.pastor.date_started ?? '',
                    photo: null,
                    is_active: props.pastor.is_active,
                };
            } else {
                isEdit.value = false;
                form.value = {
                    first_name: '',
                    last_name: '',
                    title: '',
                    role: '',
                    bio: '',
                    contact_number: '',
                    email: '',
                    date_started: '',
                    photo: null,
                    is_active: true,
                };
            }
        }
    },
);

function onPhotoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    form.value.photo = input.files?.[0] ?? null;
}

function buildPayload() {
    const payload: Record<string, unknown> = {
        first_name: form.value.first_name,
        last_name: form.value.last_name,
        title: form.value.title,
        role: form.value.role,
        bio: form.value.bio,
        contact_number: form.value.contact_number,
        email: form.value.email,
        date_started: form.value.date_started,
        is_active: form.value.is_active,
    };
    if (form.value.photo) {
        payload.photo = form.value.photo;
    }
    return payload;
}

function submit() {
    isProcessing.value = true;
    errors.value = {};

    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            emit('saved');
            handleClose();
        },
        onError: (errs: Record<string, string>) => {
            errors.value = errs;
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    };

    if (isEdit.value) {
        router.post(
            updatePastor(props.pastor!.id).url,
            { ...buildPayload(), _method: 'PUT' },
            options,
        );
    } else {
        router.post(storePastor().url, buildPayload(), options);
    }
}

function handleClose() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{
                    isEdit ? 'Edit Pastor' : 'Add Pastor'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEdit
                            ? 'Update pastor details'
                            : 'Add a new pastor or church leader'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label for="pas_first_name">First Name</Label>
                        <Input
                            id="pas_first_name"
                            v-model="form.first_name"
                            required
                            placeholder="Juan"
                        />
                        <InputError :message="errors.first_name" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="pas_last_name">Last Name</Label>
                        <Input
                            id="pas_last_name"
                            v-model="form.last_name"
                            required
                            placeholder="Dela Cruz"
                        />
                        <InputError :message="errors.last_name" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label for="pas_title">Title</Label>
                        <Input
                            id="pas_title"
                            v-model="form.title"
                            placeholder="e.g. Pastor, Rev."
                        />
                        <InputError :message="errors.title" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="pas_role">Role</Label>
                        <Input
                            id="pas_role"
                            v-model="form.role"
                            placeholder="e.g. Senior Pastor"
                        />
                        <InputError :message="errors.role" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label for="pas_bio">Bio</Label>
                    <Input
                        id="pas_bio"
                        v-model="form.bio"
                        placeholder="Short biography"
                    />
                    <InputError :message="errors.bio" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label for="pas_contact">Contact Number</Label>
                        <Input
                            id="pas_contact"
                            v-model="form.contact_number"
                            placeholder="+63 900 000 0000"
                        />
                        <InputError :message="errors.contact_number" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="pas_email">Email</Label>
                        <Input
                            id="pas_email"
                            v-model="form.email"
                            type="email"
                            placeholder="pastor@church.org"
                        />
                        <InputError :message="errors.email" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label for="pas_date_started">Date Started</Label>
                    <Input
                        id="pas_date_started"
                        v-model="form.date_started"
                        type="date"
                    />
                    <InputError :message="errors.date_started" />
                </div>

                <div
                    v-if="isEdit && props.pastor?.photo_url"
                    class="space-y-1.5"
                >
                    <Label>Current Photo</Label>
                    <img
                        :src="props.pastor.photo_url"
                        :alt="`${props.pastor.first_name} ${props.pastor.last_name}`"
                        class="h-20 w-20 rounded-lg object-cover"
                    />
                </div>

                <div class="space-y-1.5">
                    <Label for="pas_photo">{{
                        isEdit ? 'Replace Photo' : 'Photo'
                    }}</Label>
                    <input
                        id="pas_photo"
                        type="file"
                        accept="image/*"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm text-foreground shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        @change="onPhotoChange"
                    />
                    <InputError :message="errors.photo" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="pas_is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="pas_is_active">Active</Label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose"
                        >Cancel</Button
                    >
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Create' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
