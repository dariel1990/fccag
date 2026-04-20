<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeMinistry,
    update as updateMinistry,
} from '@/actions/App/Http/Controllers/MinistryController';
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

type Ministry = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
};

const props = defineProps<{
    open: boolean;
    ministry?: Ministry | null;
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
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.ministry) {
                isEdit.value = true;
                form.value = {
                    name: props.ministry.name,
                    description: props.ministry.description ?? '',
                    is_active: props.ministry.is_active,
                };
            } else {
                isEdit.value = false;
                form.value = { name: '', description: '', is_active: true };
            }
        }
    },
);

function submit() {
    isProcessing.value = true;
    errors.value = {};

    const options = {
        preserveScroll: true,
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
        router.put(updateMinistry(props.ministry!.id).url, form.value, options);
    } else {
        router.post(storeMinistry().url, form.value, options);
    }
}

function handleClose() {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{
                    isEdit ? 'Edit Ministry' : 'Add Ministry'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEdit
                            ? 'Update ministry details'
                            : 'Add a new church ministry'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="min_name">Name</Label>
                    <Input
                        id="min_name"
                        v-model="form.name"
                        required
                        placeholder="e.g. Worship Ministry"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="min_description">Description</Label>
                    <Input
                        id="min_description"
                        v-model="form.description"
                        placeholder="Brief description"
                    />
                    <InputError :message="errors.description" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="min_is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="min_is_active">Active</Label>
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
