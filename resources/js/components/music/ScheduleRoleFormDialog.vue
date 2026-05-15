<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeScheduleRole,
    update as updateScheduleRole,
} from '@/actions/App/Http/Controllers/Music/ScheduleRoleController';
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
import { TEAMS, type Team } from './scheduleRoleConstants';

type ScheduleRole = {
    id: number;
    team: string;
    name: string;
    sort_order: number;
};

const props = defineProps<{
    open: boolean;
    model?: ScheduleRole | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    team: 'band' as Team,
    name: '',
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
                team: (TEAMS as readonly string[]).includes(props.model.team)
                    ? (props.model.team as Team)
                    : 'band',
                name: props.model.name,
                sort_order: props.model.sort_order,
            };
        } else {
            isEdit.value = false;
            form.value = {
                team: 'band',
                name: '',
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
        team: form.value.team,
        name: form.value.name,
        sort_order: Number(form.value.sort_order) || 0,
    };

    if (isEdit.value && props.model) {
        router.put(updateScheduleRole(props.model.id).url, payload, options);
    } else {
        router.post(storeScheduleRole().url, payload, options);
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
                <DialogTitle>{{ isEdit ? 'Edit Schedule Role' : 'Add Schedule Role' }}</DialogTitle>
                <DialogDescription class="sr-only">
                    {{ isEdit ? 'Update schedule role details' : 'Add a new schedule role' }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label for="role_team">Team</Label>
                    <Select v-model="form.team">
                        <SelectTrigger id="role_team">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="t in TEAMS" :key="t" :value="t">
                                {{ t.charAt(0).toUpperCase() + t.slice(1) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.team" />
                </div>

                <div class="space-y-1.5">
                    <Label for="role_name">Name</Label>
                    <Input
                        id="role_name"
                        v-model="form.name"
                        required
                        placeholder="e.g. Drums"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="role_sort">Sort order</Label>
                    <Input
                        id="role_sort"
                        v-model="form.sort_order"
                        type="number"
                        min="0"
                        placeholder="0"
                    />
                    <InputError :message="errors.sort_order" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Add Role' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
