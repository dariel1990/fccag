<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeCategory,
    update as updateCategory,
} from '@/actions/App/Http/Controllers/Si/SiActivityCategoryController';
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

type Category = {
    id: number;
    name: string;
    weight: number;
    is_active: boolean;
};

const props = defineProps<{
    open: boolean;
    category?: Category | null;
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
    weight: 0.25,
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.category) {
                isEdit.value = true;
                form.value = {
                    name: props.category.name,
                    weight: props.category.weight,
                    is_active: props.category.is_active,
                };
            } else {
                isEdit.value = false;
                form.value = { name: '', weight: 0.25, is_active: true };
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
        router.put(updateCategory(props.category!.id).url, form.value, options);
    } else {
        router.post(storeCategory().url, form.value, options);
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
                    isEdit ? 'Edit Category' : 'Add Category'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEdit
                            ? 'Update scoring category settings'
                            : 'Add a new scoring category'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="cat_name">Name</Label>
                    <Input
                        id="cat_name"
                        v-model="form.name"
                        required
                        placeholder="e.g. Life Class"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="cat_weight">Weight (0–1)</Label>
                    <Input
                        id="cat_weight"
                        v-model.number="form.weight"
                        type="number"
                        step="0.0001"
                        min="0"
                        max="1"
                        required
                    />
                    <p class="text-xs text-muted-foreground">
                        Current value: {{ (form.weight * 100).toFixed(2) }}% of
                        the total score.
                    </p>
                    <InputError :message="errors.weight" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="cat_is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="cat_is_active">Active</Label>
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
