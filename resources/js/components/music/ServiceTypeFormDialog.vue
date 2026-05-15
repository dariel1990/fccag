<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeServiceType,
    update as updateServiceType,
} from '@/actions/App/Http/Controllers/Music/ServiceTypeController';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const ANY_DAY = '__any__';

const DAYS_OF_WEEK = [
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
    { value: 6, label: 'Saturday' },
] as const;

type ServiceType = {
    id: number;
    name: string;
    day_of_week: number | null;
    color: string | null;
    sort_order: number;
};

const props = defineProps<{
    open: boolean;
    model?: ServiceType | null;
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
    day_of_week: ANY_DAY as string,
    color: '#fb923c',
    sort_order: 0 as number | string,
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) { return; }
        errors.value = {};
        if (props.model) {
            isEdit.value = true;
            form.value = {
                name: props.model.name,
                day_of_week: props.model.day_of_week === null ? ANY_DAY : String(props.model.day_of_week),
                color: props.model.color ?? '#fb923c',
                sort_order: props.model.sort_order,
            };
        } else {
            isEdit.value = false;
            form.value = {
                name: '',
                day_of_week: ANY_DAY,
                color: '#fb923c',
                sort_order: 0,
            };
        }
    },
);

function submit(): void {
    isProcessing.value = true;
    errors.value = {};

    const options = {
        preserveScroll: true,
        preserveState: true,
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

    const payload = {
        name: form.value.name,
        day_of_week: form.value.day_of_week === ANY_DAY ? null : Number(form.value.day_of_week),
        color: form.value.color || null,
        sort_order: Number(form.value.sort_order) || 0,
    };

    if (isEdit.value && props.model) {
        router.put(updateServiceType(props.model.id).url, payload, options);
    } else {
        router.post(storeServiceType().url, payload, options);
    }
}

function handleClose(): void {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-w-md">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? 'Edit Service Type' : 'Add Service Type' }}</DialogTitle>
                <DialogDescription class="sr-only">
                    {{ isEdit ? 'Update service type details' : 'Add a new service type' }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label for="service_type_name">Name</Label>
                    <Input
                        id="service_type_name"
                        v-model="form.name"
                        required
                        placeholder="e.g. Divine Service"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="service_type_day">Day of week</Label>
                    <Select v-model="form.day_of_week">
                        <SelectTrigger id="service_type_day">
                            <SelectValue placeholder="Any day" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="ANY_DAY">Any day</SelectItem>
                            <SelectItem
                                v-for="d in DAYS_OF_WEEK"
                                :key="d.value"
                                :value="String(d.value)"
                            >
                                {{ d.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.day_of_week" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label for="service_type_color">Color</Label>
                        <input
                            id="service_type_color"
                            v-model="form.color"
                            type="color"
                            class="border-input bg-background h-9 w-full rounded-md border p-1"
                        />
                        <InputError :message="errors.color" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="service_type_sort">Sort order</Label>
                        <Input
                            id="service_type_sort"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            placeholder="0"
                        />
                        <InputError :message="errors.sort_order" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Add Service Type' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
