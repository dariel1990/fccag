<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import { ref, watch } from 'vue';
import {
    store as storeActivity,
    update as updateActivity,
} from '@/actions/App/Http/Controllers/Si/SiActivityController';
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

type Category = { id: number; name: string };
type Member = { id: number; name: string };
type ChurchActivity = { id: number; title: string; activity_date: string };

type SiActivityData = {
    id: number;
    si_activity_category_id: number;
    activity_id: number | null;
    title: string;
    speaker: string | null;
    topic: string | null;
    memory_verse: string | null;
    conducted_at: string;
    member_ids: number[];
};

const props = defineProps<{
    open: boolean;
    activity?: SiActivityData | null;
    categories: Category[];
    members: Member[];
    churchActivities: ChurchActivity[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const categoryOptions = ref(props.categories.map((c) => ({ value: String(c.id), label: c.name })));
const memberOptions = ref(props.members.map((m) => ({ value: m.id, label: m.name })));
const churchActivityOptions = ref(
    props.churchActivities.map((a) => ({ value: a.id, label: `${a.title} (${a.activity_date})` })),
);

const form = ref({
    si_activity_category_id: '',
    activity_id: null as number | null,
    title: '',
    speaker: '',
    topic: '',
    memory_verse: '',
    conducted_at: '',
    member_ids: [] as number[],
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            categoryOptions.value = props.categories.map((c) => ({ value: String(c.id), label: c.name }));
            memberOptions.value = props.members.map((m) => ({ value: m.id, label: m.name }));
            churchActivityOptions.value = props.churchActivities.map((a) => ({
                value: a.id,
                label: `${a.title} (${a.activity_date})`,
            }));

            if (props.activity) {
                isEdit.value = true;
                form.value = {
                    si_activity_category_id: String(props.activity.si_activity_category_id),
                    activity_id: props.activity.activity_id,
                    title: props.activity.title,
                    speaker: props.activity.speaker ?? '',
                    topic: props.activity.topic ?? '',
                    memory_verse: props.activity.memory_verse ?? '',
                    conducted_at: props.activity.conducted_at,
                    member_ids: [...props.activity.member_ids],
                };
            } else {
                isEdit.value = false;
                form.value = {
                    si_activity_category_id: '',
                    activity_id: null,
                    title: '',
                    speaker: '',
                    topic: '',
                    memory_verse: '',
                    conducted_at: '',
                    member_ids: [],
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
        <DialogContent class="max-h-[90vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ isEdit ? `Edit: ${activity?.title}` : 'Create SI Activity' }}</DialogTitle>
                <DialogDescription>
                    {{ isEdit ? 'Update activity details and assigned members' : 'Add a new activity and assign members' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label>Category</Label>
                        <Multiselect
                            v-model="form.si_activity_category_id"
                            :options="categoryOptions"
                            label="label"
                            value-prop="value"
                            placeholder="Select category"
                            :can-clear="false"
                        />
                        <InputError :message="errors.si_activity_category_id" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Date</Label>
                        <Input v-model="form.conducted_at" type="date" required />
                        <InputError :message="errors.conducted_at" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Activity Title</Label>
                    <Input v-model="form.title" required placeholder="e.g. Life Class Zone 1" />
                    <InputError :message="errors.title" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label>Speaker / Teacher</Label>
                        <Input v-model="form.speaker" placeholder="Speaker name" />
                        <InputError :message="errors.speaker" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Topic</Label>
                        <Input v-model="form.topic" placeholder="Session topic" />
                        <InputError :message="errors.topic" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Memory Verse</Label>
                    <Input v-model="form.memory_verse" placeholder="e.g. Psalms 127:3" />
                    <InputError :message="errors.memory_verse" />
                </div>

                <div class="space-y-1.5">
                    <Label>Link to Existing Activity (optional)</Label>
                    <Multiselect
                        v-model="form.activity_id"
                        :options="churchActivityOptions"
                        label="label"
                        value-prop="value"
                        placeholder="Search activities..."
                        :searchable="true"
                        :can-clear="true"
                    />
                    <InputError :message="errors.activity_id" />
                </div>

                <div class="space-y-1.5">
                    <Label>Assign Members</Label>
                    <Multiselect
                        v-model="form.member_ids"
                        :options="memberOptions"
                        label="label"
                        value-prop="value"
                        mode="tags"
                        placeholder="Select members for this activity"
                        :searchable="true"
                        :close-on-select="false"
                    />
                    <InputError :message="errors.member_ids" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Create Activity' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
