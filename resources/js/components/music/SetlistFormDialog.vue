<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    store as storeSetlist,
    update as updateSetlist,
} from '@/actions/App/Http/Controllers/Music/SetlistController';
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

type Setlist = {
    id: number;
    title: string;
    service_date: string;
    theme: string | null;
    status: string;
    notes: string | null;
};

const props = defineProps<{
    open: boolean;
    setlist?: Setlist | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const isEdit = ref(false);
const isProcessing = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref({
    title: '',
    service_date: '',
    theme: '',
    status: 'draft',
    notes: '',
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            if (props.setlist) {
                isEdit.value = true;
                form.value = {
                    title: props.setlist.title,
                    service_date: props.setlist.service_date,
                    theme: props.setlist.theme ?? '',
                    status: props.setlist.status,
                    notes: props.setlist.notes ?? '',
                };
            } else {
                isEdit.value = false;
                form.value = {
                    title: '',
                    service_date: '',
                    theme: '',
                    status: 'draft',
                    notes: '',
                };
            }
        }
    },
);

function submit(): void {
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

    const payload = {
        ...form.value,
        theme: form.value.theme || null,
        notes: form.value.notes || null,
    };

    if (isEdit.value) {
        router.put(updateSetlist(props.setlist!.id).url, payload, options);
    } else {
        router.post(storeSetlist().url, payload, options);
    }
}

function handleClose(): void {
    emit('update:open', false);
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="flex h-dvh max-w-lg flex-col overflow-hidden rounded-none p-0 sm:h-[96vh] sm:rounded-lg">
            <DialogHeader class="shrink-0 border-b px-6 pt-6 pb-4">
                <DialogTitle>{{ isEdit ? 'Edit Setlist' : 'New Setlist' }}</DialogTitle>
                <DialogDescription>
                    {{ isEdit ? 'Update setlist details' : 'Create a new setlist for a service' }}
                </DialogDescription>
            </DialogHeader>

            <form class="flex flex-1 flex-col overflow-hidden" @submit.prevent="submit">
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                    <div class="space-y-1.5">
                        <Label for="setlist_title">Title</Label>
                        <Input id="setlist_title" v-model="form.title" required placeholder="e.g. Sunday Morning Service" />
                        <InputError :message="errors.title" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <Label for="setlist_service_date">Service Date</Label>
                            <Input id="setlist_service_date" v-model="form.service_date" type="date" required />
                            <InputError :message="errors.service_date" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="draft">Draft</SelectItem>
                                    <SelectItem value="published">Published</SelectItem>
                                    <SelectItem value="archived">Archived</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors.status" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="setlist_theme">Theme</Label>
                        <Input id="setlist_theme" v-model="form.theme" placeholder="e.g. Hope and Redemption" />
                        <InputError :message="errors.theme" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="setlist_notes">Notes</Label>
                        <Textarea id="setlist_notes" v-model="form.notes" placeholder="Optional notes for the team" />
                        <InputError :message="errors.notes" />
                    </div>
                </div>

                <DialogFooter class="shrink-0 border-t px-6 py-4">
                    <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
                    <Button type="submit" :disabled="isProcessing">
                        {{ isEdit ? 'Save Changes' : 'Create Setlist' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
