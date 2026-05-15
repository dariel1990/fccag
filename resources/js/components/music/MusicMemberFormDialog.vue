<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeMusicMember,
    update as updateMusicMember,
} from '@/actions/App/Http/Controllers/Music/MusicMemberController';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type MusicMember = {
    id: number;
    name: string;
    user_id: number | null;
    instruments: string | null;
    is_active: boolean;
};

type UserOption = {
    id: number;
    name: string;
    email: string;
};

const props = defineProps<{
    open: boolean;
    model?: MusicMember | null;
    users: UserOption[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const NONE_USER = '__none__';

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    name: '',
    user_id: NONE_USER as string,
    instruments: '',
    is_active: true,
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
                user_id: props.model.user_id ? String(props.model.user_id) : NONE_USER,
                instruments: props.model.instruments ?? '',
                is_active: props.model.is_active,
            };
        } else {
            isEdit.value = false;
            form.value = {
                name: '',
                user_id: NONE_USER,
                instruments: '',
                is_active: true,
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
        user_id: form.value.user_id === NONE_USER ? null : Number(form.value.user_id),
        instruments: form.value.instruments || null,
        is_active: form.value.is_active,
    };

    if (isEdit.value && props.model) {
        router.put(updateMusicMember(props.model.id).url, payload, options);
    } else {
        router.post(storeMusicMember().url, payload, options);
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
                <DialogTitle>{{ isEdit ? 'Edit Member' : 'Add Member' }}</DialogTitle>
                <DialogDescription class="sr-only">
                    {{ isEdit ? 'Update member details' : 'Add a new music team member' }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label for="member_name">Name</Label>
                    <Input
                        id="member_name"
                        v-model="form.name"
                        required
                        placeholder="Member name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="member_user">Linked user (optional)</Label>
                    <Select v-model="form.user_id">
                        <SelectTrigger id="member_user">
                            <SelectValue placeholder="No linked user" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="NONE_USER">— No linked user —</SelectItem>
                            <SelectItem
                                v-for="u in users"
                                :key="u.id"
                                :value="String(u.id)"
                            >
                                {{ u.name }} ({{ u.email }})
                            </SelectItem>
                            <SelectItem
                                v-if="model?.user_id && !users.some((u) => u.id === model?.user_id)"
                                :value="String(model.user_id)"
                            >
                                (Currently linked user)
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.user_id" />
                </div>

                <div class="space-y-1.5">
                    <Label for="member_instruments">Instruments</Label>
                    <Input
                        id="member_instruments"
                        v-model="form.instruments"
                        placeholder="e.g. Keys, Bass"
                    />
                    <InputError :message="errors.instruments" />
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="member_is_active"
                        :model-value="form.is_active"
                        @update:model-value="form.is_active = $event as boolean"
                    />
                    <Label for="member_is_active">Active</Label>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Add Member' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
