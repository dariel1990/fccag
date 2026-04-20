<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { ref, watch } from 'vue';
import {
    store as storeMember,
    update as updateMember,
} from '@/actions/App/Http/Controllers/Si/SiMemberController';
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

type Caregiver = { id: number; first_name: string; last_name: string };

type Member = {
    id: number;
    caregiver_id: number | null;
    name: string;
    sex: string;
    ph_id: string | null;
    address: string | null;
    status: string;
    enrolled_at: string;
    exited_at: string | null;
};

const props = defineProps<{
    open: boolean;
    member?: Member | null;
    caregivers: Caregiver[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});
const useExistingCaregiver = ref(true);

const caregiverOptions = ref(props.caregivers.map((c) => ({ value: c.id, label: `${c.first_name} ${c.last_name}` })));

const form = ref({
    caregiver_id: null as number | null,
    caregiver: { first_name: '', last_name: '', contact_number: '', address: '' },
    name: '',
    sex: '',
    ph_id: '',
    address: '',
    status: 'active',
    enrolled_at: '',
    exited_at: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            useExistingCaregiver.value = true;
            caregiverOptions.value = props.caregivers.map((c) => ({
                value: c.id,
                label: `${c.first_name} ${c.last_name}`,
            }));

            if (props.member) {
                isEdit.value = true;
                form.value = {
                    caregiver_id: props.member.caregiver_id,
                    caregiver: { first_name: '', last_name: '', contact_number: '', address: '' },
                    name: props.member.name,
                    sex: props.member.sex,
                    ph_id: props.member.ph_id ?? '',
                    address: props.member.address ?? '',
                    status: props.member.status,
                    enrolled_at: props.member.enrolled_at,
                    exited_at: props.member.exited_at ?? '',
                };
            } else {
                isEdit.value = false;
                form.value = {
                    caregiver_id: null,
                    caregiver: { first_name: '', last_name: '', contact_number: '', address: '' },
                    name: '',
                    sex: '',
                    ph_id: '',
                    address: '',
                    status: 'active',
                    enrolled_at: '',
                    exited_at: '',
                };
            }
        }
    },
);

function buildPayload() {
    const { caregiver, caregiver_id, ...rest } = form.value;

    return useExistingCaregiver.value
        ? { ...rest, caregiver_id }
        : { ...rest, caregiver };
}

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
        router.put(updateMember(props.member!.id).url, buildPayload(), options);
    } else {
        router.post(storeMember().url, buildPayload(), options);
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
                <DialogTitle>{{ isEdit ? `Edit: ${member?.name}` : 'Enroll SI Member' }}</DialogTitle>
                <DialogDescription>
                    {{ isEdit ? 'Update SI member information' : 'Register a new child in the SI program' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Caregiver -->
                <div class="space-y-3">
                    <div class="flex items-center gap-4">
                        <Label class="font-semibold">Caregiver</Label>
                        <div class="flex gap-3 text-sm">
                            <button
                                type="button"
                                class="underline"
                                :class="useExistingCaregiver ? 'font-semibold' : 'text-muted-foreground'"
                                @click="useExistingCaregiver = true"
                            >
                                Select existing
                            </button>
                            <button
                                type="button"
                                class="underline"
                                :class="!useExistingCaregiver ? 'font-semibold' : 'text-muted-foreground'"
                                @click="useExistingCaregiver = false"
                            >
                                Add new
                            </button>
                        </div>
                    </div>

                    <div v-if="isEdit || useExistingCaregiver" class="space-y-1.5">
                        <Multiselect
                            v-model="form.caregiver_id"
                            :options="caregiverOptions"
                            label="label"
                            value-prop="value"
                            placeholder="Search caregiver..."
                            :searchable="true"
                        />
                        <InputError :message="errors.caregiver_id" />
                    </div>

                    <div v-else class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label>First Name</Label>
                            <Input v-model="form.caregiver.first_name" placeholder="First name" />
                            <InputError :message="errors['caregiver.first_name']" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Last Name</Label>
                            <Input v-model="form.caregiver.last_name" placeholder="Last name" />
                            <InputError :message="errors['caregiver.last_name']" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Contact Number</Label>
                            <Input v-model="form.caregiver.contact_number" placeholder="Contact number" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Address</Label>
                            <Input v-model="form.caregiver.address" placeholder="Address" />
                        </div>
                    </div>
                </div>

                <hr class="border-sidebar-border/70" />

                <div class="space-y-1.5">
                    <Label>Child Name</Label>
                    <Input v-model="form.name" required placeholder="Full name of the child" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label>Sex</Label>
                        <Multiselect
                            v-model="form.sex"
                            :options="[{ value: 'male', label: 'Male' }, { value: 'female', label: 'Female' }, { value: 'unborn', label: 'Unborn' }]"
                            label="label"
                            value-prop="value"
                            placeholder="Select sex"
                            :can-clear="false"
                        />
                        <InputError :message="errors.sex" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>PH ID</Label>
                        <Input v-model="form.ph_id" placeholder="Program ID (optional)" />
                        <InputError :message="errors.ph_id" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Address</Label>
                    <Input v-model="form.address" placeholder="Child's address" />
                    <InputError :message="errors.address" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label>Status</Label>
                        <Multiselect
                            v-model="form.status"
                            :options="[{ value: 'active', label: 'Active' }, { value: 'exit', label: 'Exit' }]"
                            label="label"
                            value-prop="value"
                            placeholder="Select status"
                            :can-clear="false"
                        />
                        <InputError :message="errors.status" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Enrolled Date</Label>
                        <Input v-model="form.enrolled_at" type="date" required />
                        <InputError :message="errors.enrolled_at" />
                    </div>
                </div>

                <div v-if="form.status === 'exit'" class="space-y-1.5">
                    <Label>Exit Date</Label>
                    <Input v-model="form.exited_at" type="date" />
                    <InputError :message="errors.exited_at" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Enroll' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
