<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeActivity,
    update as updateActivity,
} from '@/actions/App/Http/Controllers/ActivityController';
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

type ActivityType = { id: number; name: string };

type Activity = {
    id: number;
    activity_type_id: number;
    title: string;
    description: string | null;
    activity_date: string;
};

const props = defineProps<{
    open: boolean;
    activity?: Activity | null;
    activityTypes: ActivityType[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    activity_type_id: '' as number | '',
    title: '',
    description: '',
    activity_date: new Date().toISOString().split('T')[0],
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.activity) {
                isEdit.value = true;
                form.value = {
                    activity_type_id: props.activity.activity_type_id,
                    title: props.activity.title,
                    description: props.activity.description ?? '',
                    activity_date: props.activity.activity_date,
                };
            } else {
                isEdit.value = false;
                form.value = {
                    activity_type_id: '',
                    title: '',
                    description: '',
                    activity_date: new Date().toISOString().split('T')[0],
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
        router.put(updateActivity(props.activity!.id).url, form.value, options);
    } else {
        router.post(storeActivity().url, form.value, options);
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
                <DialogTitle>{{
                    isEdit ? 'Edit Activity' : 'Add Activity'
                }}</DialogTitle>
                <DialogDescription>
                    {{
                        isEdit
                            ? 'Update activity details'
                            : 'Add a new activity session'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <Label for="act_type">Activity Type</Label>
                    <select
                        id="act_type"
                        v-model="form.activity_type_id"
                        required
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <option value="" disabled>Select activity type</option>
                        <option
                            v-for="type in props.activityTypes"
                            :key="type.id"
                            :value="type.id"
                        >
                            {{ type.name }}
                        </option>
                    </select>
                    <InputError :message="errors.activity_type_id" />
                </div>

                <div class="space-y-1.5">
                    <Label for="act_title">Title</Label>
                    <Input
                        id="act_title"
                        v-model="form.title"
                        required
                        placeholder="e.g. Sunday Service - Week 1"
                    />
                    <InputError :message="errors.title" />
                </div>

                <div class="space-y-1.5">
                    <Label for="act_description">Description</Label>
                    <Input
                        id="act_description"
                        v-model="form.description"
                        placeholder="Optional description"
                    />
                    <InputError :message="errors.description" />
                </div>

                <div class="space-y-1.5">
                    <Label for="act_date">Date</Label>
                    <Input
                        id="act_date"
                        v-model="form.activity_date"
                        type="date"
                        required
                    />
                    <InputError :message="errors.activity_date" />
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
