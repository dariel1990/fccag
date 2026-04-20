<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeClassification,
    update as updateClassification,
} from '@/actions/App/Http/Controllers/ClassificationController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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

type Classification = {
    id: number;
    name: string;
    code: string;
    description: string | null;
};

const props = defineProps<{
    open: boolean;
    classification?: Classification | null;
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
    code: '',
    description: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.classification) {
                isEdit.value = true;
                form.value = {
                    name: props.classification.name,
                    code: props.classification.code,
                    description: props.classification.description ?? '',
                };
            } else {
                isEdit.value = false;
                form.value = { name: '', code: '', description: '' };
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
        router.put(
            updateClassification(props.classification!.id).url,
            form.value,
            options,
        );
    } else {
        router.post(storeClassification().url, form.value, options);
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
                    isEdit ? 'Edit Classification' : 'Add Classification'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEdit
                            ? 'Update classification details'
                            : 'Add a new member classification'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="cl_name">Name</Label>
                    <Input
                        id="cl_name"
                        v-model="form.name"
                        required
                        placeholder="e.g. Regular Member"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="cl_code">Code</Label>
                    <Input
                        id="cl_code"
                        v-model="form.code"
                        required
                        placeholder="e.g. RM"
                        class="uppercase"
                    />
                    <p class="text-xs text-muted-foreground">
                        Use uppercase letters for consistency.
                    </p>
                    <InputError :message="errors.code" />
                </div>

                <div class="space-y-1.5">
                    <Label for="cl_description">Description</Label>
                    <Input
                        id="cl_description"
                        v-model="form.description"
                        placeholder="Brief description"
                    />
                    <InputError :message="errors.description" />
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
