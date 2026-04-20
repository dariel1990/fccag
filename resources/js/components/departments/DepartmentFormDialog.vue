<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeDepartment,
    update as updateDepartment,
} from '@/actions/App/Http/Controllers/DepartmentController';
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

type Department = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    photo_url: string | null;
};

const props = defineProps<{
    open: boolean;
    department?: Department | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    name: '',
    description: '',
    photo: null as File | null,
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.department) {
                isEdit.value = true;
                form.value = {
                    name: props.department.name,
                    description: props.department.description ?? '',
                    photo: null,
                    is_active: props.department.is_active,
                };
            } else {
                isEdit.value = false;
                form.value = { name: '', description: '', photo: null, is_active: true };
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
        name: form.value.name,
        description: form.value.description,
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
            updateDepartment(props.department!.id).url,
            { ...buildPayload(), _method: 'PUT' },
            options,
        );
    } else {
        router.post(storeDepartment().url, buildPayload(), options);
    }
}

function handleClose() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-lg">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? 'Edit Department' : 'Add Department' }}</DialogTitle>
                <DialogDescription>
                    {{ isEdit ? 'Update department details' : 'Add a new church department' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="dept_name">Name</Label>
                    <Input id="dept_name" v-model="form.name" required placeholder="e.g. Youth Department" />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="dept_description">Description</Label>
                    <Input id="dept_description" v-model="form.description" placeholder="Brief description" />
                    <InputError :message="errors.description" />
                </div>

                <div v-if="isEdit && props.department?.photo_url" class="space-y-1.5">
                    <Label>Current Logo / Photo</Label>
                    <img
                        :src="props.department.photo_url"
                        :alt="props.department.name"
                        class="h-20 w-20 rounded-lg object-cover"
                    />
                </div>

                <div class="space-y-1.5">
                    <Label for="dept_photo">{{ isEdit ? 'Replace Logo / Photo' : 'Logo / Photo' }}</Label>
                    <input
                        id="dept_photo"
                        type="file"
                        accept="image/*"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm text-foreground shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        @change="onPhotoChange"
                    />
                    <InputError :message="errors.photo" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="dept_is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="dept_is_active">Active</Label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Create' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
