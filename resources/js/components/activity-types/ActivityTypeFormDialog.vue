<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { ref, watch } from 'vue';
import {
    store as storeActivityType,
    update as updateActivityType,
} from '@/actions/App/Http/Controllers/ActivityTypeController';
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

type Department = { id: number; name: string };

type ActivityType = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    department_ids: number[];
};

const props = defineProps<{
    open: boolean;
    activityType?: ActivityType | null;
    departments: Department[];
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
    department_ids: [] as number[],
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.activityType) {
                isEdit.value = true;
                form.value = {
                    name: props.activityType.name,
                    description: props.activityType.description ?? '',
                    is_active: props.activityType.is_active,
                    department_ids: [...props.activityType.department_ids],
                };
            } else {
                isEdit.value = false;
                form.value = {
                    name: '',
                    description: '',
                    is_active: true,
                    department_ids: [],
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
        router.put(updateActivityType(props.activityType!.id).url, form.value, options);
    } else {
        router.post(storeActivityType().url, form.value, options);
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
                <DialogTitle>{{ isEdit ? 'Edit Activity Type' : 'Add Activity Type' }}</DialogTitle>
                <DialogDescription>
                    {{ isEdit ? 'Update activity type details' : 'Add a new type of church activity' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="at_name">Name</Label>
                    <Input id="at_name" v-model="form.name" required placeholder="e.g. Sunday Service" />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="at_description">Description</Label>
                    <Input id="at_description" v-model="form.description" placeholder="Brief description" />
                    <InputError :message="errors.description" />
                </div>

                <div class="space-y-1.5">
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
                        Only members of selected departments will appear in attendance. Leave empty to include all active members.
                    </p>
                    <InputError :message="errors.department_ids" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="at_is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="at_is_active">Active</Label>
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
