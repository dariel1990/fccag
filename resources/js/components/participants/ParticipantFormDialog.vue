<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { ref, watch } from 'vue';
import {
    store as storeParticipant,
    update as updateParticipant,
} from '@/actions/App/Http/Controllers/ParticipantController';
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

type Participant = {
    id: number;
    first_name: string;
    last_name: string;
    gender: string;
    birthday: string | null;
    contact_number: string | null;
    address: string | null;
    cell_group_id: number | null;
    classification_id: number | null;
    department_id: number | null;
    ministry_ids: number[];
    date_joined: string;
    is_active: boolean;
};

type DropdownOption = { id: number; name: string };
type ClassificationOption = { id: number; name: string; code: string };

const props = defineProps<{
    open: boolean;
    participant?: Participant | null;
    cellGroups?: DropdownOption[];
    classifications?: ClassificationOption[];
    ministries?: DropdownOption[];
    departments?: DropdownOption[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const cellGroupOptions = ref<DropdownOption[]>(props.cellGroups ?? []);
const classificationOptions = ref<ClassificationOption[]>(
    props.classifications ?? [],
);
const ministryOptions = ref<DropdownOption[]>(props.ministries ?? []);
const departmentOptions = ref<DropdownOption[]>(props.departments ?? []);

const form = ref({
    first_name: '',
    last_name: '',
    gender: '',
    birthday: '',
    contact_number: '',
    address: '',
    cell_group_id: null as number | null,
    classification_id: null as number | null,
    department_id: null as number | null,
    ministry_ids: [] as number[],
    date_joined: new Date().toISOString().split('T')[0],
    is_active: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            cellGroupOptions.value = props.cellGroups ?? [];
            classificationOptions.value = props.classifications ?? [];
            ministryOptions.value = props.ministries ?? [];
            departmentOptions.value = props.departments ?? [];

            if (props.participant) {
                isEdit.value = true;
                form.value = {
                    first_name: props.participant.first_name,
                    last_name: props.participant.last_name,
                    gender: props.participant.gender,
                    birthday: props.participant.birthday || '',
                    contact_number: props.participant.contact_number || '',
                    address: props.participant.address || '',
                    cell_group_id: props.participant.cell_group_id ?? null,
                    classification_id:
                        props.participant.classification_id ?? null,
                    department_id: props.participant.department_id ?? null,
                    ministry_ids: props.participant.ministry_ids ?? [],
                    date_joined: props.participant.date_joined,
                    is_active: props.participant.is_active,
                };
            } else {
                isEdit.value = false;
                form.value = {
                    first_name: '',
                    last_name: '',
                    gender: '',
                    birthday: '',
                    contact_number: '',
                    address: '',
                    cell_group_id: null,
                    classification_id: null,
                    department_id: null,
                    ministry_ids: [],
                    date_joined: new Date().toISOString().split('T')[0],
                    is_active: true,
                };
            }
            errors.value = {};
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
            updateParticipant(props.participant!.id).url,
            form.value,
            options,
        );
    } else {
        router.post(storeParticipant().url, form.value, options);
    }
}

function handleClose() {
    emit('update:open', false);
}

function getError(field: string) {
    return errors.value[field];
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="max-h-[90vh] w-[95vw] max-w-2xl overflow-y-auto">
            <DialogHeader class="pb-2 sm:pb-3">
                <DialogTitle class="text-base sm:text-lg">
                    {{ isEdit ? 'Edit Person of God' : 'Add Person of God' }}
                </DialogTitle>
                <DialogDescription class="text-xs sm:text-sm">
                    {{
                        isEdit
                            ? 'Update information for this person of God'
                            : 'Register a new person of God'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-3 sm:space-y-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                    <div class="space-y-1.5">
                        <Label for="first_name" class="text-xs sm:text-sm"
                            >First Name</Label
                        >
                        <Input
                            id="first_name"
                            v-model="form.first_name"
                            required
                            placeholder="First name"
                            class="h-8 text-xs sm:h-9 sm:text-sm"
                        />
                        <InputError
                            :message="getError('first_name')"
                            class="text-[10px]"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="last_name" class="text-xs sm:text-sm"
                            >Last Name</Label
                        >
                        <Input
                            id="last_name"
                            v-model="form.last_name"
                            required
                            placeholder="Last name"
                            class="h-8 text-xs sm:h-9 sm:text-sm"
                        />
                        <InputError
                            :message="getError('last_name')"
                            class="text-[10px]"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                    <div class="space-y-1.5">
                        <Label for="gender" class="text-xs sm:text-sm"
                            >Gender</Label
                        >
                        <Multiselect
                            id="gender"
                            v-model="form.gender"
                            :options="['male', 'female']"
                            placeholder="Select gender"
                            :searchable="false"
                            :can-clear="false"
                        />
                        <InputError
                            :message="getError('gender')"
                            class="text-[10px]"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="birthday" class="text-xs sm:text-sm"
                            >Birthday</Label
                        >
                        <Input
                            id="birthday"
                            v-model="form.birthday"
                            type="date"
                            class="h-8 text-xs sm:h-9 sm:text-sm"
                        />
                        <InputError
                            :message="getError('birthday')"
                            class="text-[10px]"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                    <div class="space-y-1.5">
                        <Label for="contact_number" class="text-xs sm:text-sm"
                            >Contact Number</Label
                        >
                        <Input
                            id="contact_number"
                            v-model="form.contact_number"
                            placeholder="Contact number"
                            class="h-8 text-xs sm:h-9 sm:text-sm"
                        />
                        <InputError
                            :message="getError('contact_number')"
                            class="text-[10px]"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="date_joined" class="text-xs sm:text-sm"
                            >Date Joined</Label
                        >
                        <Input
                            id="date_joined"
                            v-model="form.date_joined"
                            type="date"
                            required
                            class="h-8 text-xs sm:h-9 sm:text-sm"
                        />
                        <InputError
                            :message="getError('date_joined')"
                            class="text-[10px]"
                        />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label for="address" class="text-xs sm:text-sm"
                        >Address</Label
                    >
                    <Input
                        id="address"
                        v-model="form.address"
                        placeholder="Full address"
                        class="h-8 text-xs sm:h-9 sm:text-sm"
                    />
                    <InputError
                        :message="getError('address')"
                        class="text-[10px]"
                    />
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs sm:text-sm">Cell Group</Label>
                        <Multiselect
                            v-model="form.cell_group_id"
                            :options="cellGroupOptions"
                            label="name"
                            value-prop="id"
                            placeholder="Select cell group"
                            :searchable="true"
                            :can-clear="true"
                        />
                        <InputError
                            :message="getError('cell_group_id')"
                            class="text-[10px]"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <Label class="text-xs sm:text-sm">Classification</Label>
                        <Multiselect
                            v-model="form.classification_id"
                            :options="classificationOptions"
                            label="name"
                            value-prop="id"
                            placeholder="Select classification"
                            :searchable="true"
                            :can-clear="true"
                        />
                        <InputError
                            :message="getError('classification_id')"
                            class="text-[10px]"
                        />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs sm:text-sm">Department</Label>
                    <Multiselect
                        v-model="form.department_id"
                        :options="departmentOptions"
                        label="name"
                        value-prop="id"
                        placeholder="Select department"
                        :searchable="true"
                        :can-clear="true"
                    />
                    <InputError
                        :message="getError('department_id')"
                        class="text-[10px]"
                    />
                </div>

                <div class="space-y-1.5">
                    <Label class="text-xs sm:text-sm">Ministries</Label>
                    <Multiselect
                        v-model="form.ministry_ids"
                        :options="ministryOptions"
                        label="name"
                        value-prop="id"
                        mode="tags"
                        placeholder="Select ministries"
                        :searchable="true"
                        :close-on-select="false"
                    />
                    <InputError
                        :message="getError('ministry_ids')"
                        class="text-[10px]"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="is_active" class="text-xs sm:text-sm"
                        >Active</Label
                    >
                </div>

                <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row">
                    <Button
                        type="button"
                        variant="outline"
                        class="h-8 w-full text-xs sm:h-9 sm:w-auto sm:text-sm"
                        @click="handleClose"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="isProcessing"
                        class="h-8 w-full text-xs sm:h-9 sm:w-auto sm:text-sm"
                    >
                        {{ isEdit ? 'Update' : 'Add Person of God' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
