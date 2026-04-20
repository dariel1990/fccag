<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeCellGroup,
    update as updateCellGroup,
} from '@/actions/App/Http/Controllers/CellGroupController';
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

type CellGroup = {
    id: number;
    name: string;
    leader: string | null;
    description: string | null;
    is_active: boolean;
};

const props = defineProps<{
    open: boolean;
    cellGroup?: CellGroup | null;
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
    leader: '',
    description: '',
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.cellGroup) {
                isEdit.value = true;
                form.value = {
                    name: props.cellGroup.name,
                    leader: props.cellGroup.leader ?? '',
                    description: props.cellGroup.description ?? '',
                    is_active: props.cellGroup.is_active,
                };
            } else {
                isEdit.value = false;
                form.value = {
                    name: '',
                    leader: '',
                    description: '',
                    is_active: true,
                };
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
            updateCellGroup(props.cellGroup!.id).url,
            form.value,
            options,
        );
    } else {
        router.post(storeCellGroup().url, form.value, options);
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
                    isEdit ? 'Edit Cell Group' : 'Add Cell Group'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEdit
                            ? 'Update cell group details'
                            : 'Add a new church cell group'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="cg_name">Name</Label>
                    <Input
                        id="cg_name"
                        v-model="form.name"
                        required
                        placeholder="e.g. Nehemiah Group"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="cg_leader">Leader</Label>
                    <Input
                        id="cg_leader"
                        v-model="form.leader"
                        placeholder="Leader's name"
                    />
                    <InputError :message="errors.leader" />
                </div>

                <div class="space-y-1.5">
                    <Label for="cg_description">Description</Label>
                    <Input
                        id="cg_description"
                        v-model="form.description"
                        placeholder="Brief description"
                    />
                    <InputError :message="errors.description" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="cg_is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="cg_is_active">Active</Label>
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
