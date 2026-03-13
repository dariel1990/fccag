<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Multiselect from '@vueform/multiselect';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/ParticipantController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

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
    ministry_ids: number[];
    date_joined: string;
    is_active: boolean;
};

type Props = {
    participant?: Participant;
    cellGroups: { id: number; name: string }[];
    classifications: { id: number; name: string; code: string }[];
    ministries: { id: number; name: string }[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'People of God',
        href: index().url,
    },
    {
        title: 'Edit',
    },
];

const form = useForm({
    first_name: props.participant?.first_name || '',
    last_name: props.participant?.last_name || '',
    gender: props.participant?.gender || '',
    birthday: props.participant?.birthday ?? '',
    contact_number: props.participant?.contact_number ?? '',
    address: props.participant?.address ?? '',
    cell_group_id: props.participant?.cell_group_id ?? (null as number | null),
    classification_id: props.participant?.classification_id ?? (null as number | null),
    ministry_ids: props.participant?.ministry_ids ?? ([] as number[]),
    date_joined: props.participant?.date_joined || '',
    is_active: props.participant?.is_active ?? true,
});

function submit() {
    if (props.participant) {
        form.put(update(props.participant.id).url);
    }
}
</script>

<template>
    <Head title="Edit Participant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Edit Participant"
                description="Update participant information"
            />

            <div
                class="mx-auto w-full max-w-4xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                v-model="form.first_name"
                                required
                                placeholder="First name"
                            />
                            <InputError :message="form.errors.first_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                v-model="form.last_name"
                                required
                                placeholder="Last name"
                            />
                            <InputError :message="form.errors.last_name" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label>Gender</Label>
                            <Multiselect
                                v-model="form.gender"
                                :options="['male', 'female']"
                                placeholder="Select gender"
                                :searchable="false"
                                :can-clear="false"

                            />
                            <InputError :message="form.errors.gender" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="birthday">Birthday</Label>
                            <Input
                                id="birthday"
                                v-model="form.birthday"
                                type="date"
                            />
                            <InputError :message="form.errors.birthday" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="contact_number">Contact Number</Label>
                            <Input
                                id="contact_number"
                                v-model="form.contact_number"
                                placeholder="Contact number"
                            />
                            <InputError :message="form.errors.contact_number" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="date_joined">Date Joined</Label>
                            <Input
                                id="date_joined"
                                v-model="form.date_joined"
                                type="date"
                                required
                            />
                            <InputError :message="form.errors.date_joined" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="address">Address</Label>
                        <Input
                            id="address"
                            v-model="form.address"
                            placeholder="Full address"
                        />
                        <InputError :message="form.errors.address" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label>Cell Group</Label>
                            <Multiselect
                                v-model="form.cell_group_id"
                                :options="props.cellGroups"
                                label="name"
                                value-prop="id"
                                placeholder="Select cell group"
                                :searchable="true"
                                :can-clear="true"

                            />
                            <InputError :message="form.errors.cell_group_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Classification</Label>
                            <Multiselect
                                v-model="form.classification_id"
                                :options="props.classifications"
                                label="name"
                                value-prop="id"
                                placeholder="Select classification"
                                :searchable="true"
                                :can-clear="true"

                            />
                            <InputError :message="form.errors.classification_id" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Ministries</Label>
                            <Multiselect
                                v-model="form.ministry_ids"
                                :options="props.ministries"
                                label="name"
                                value-prop="id"
                                mode="tags"
                                placeholder="Select ministries"
                                :searchable="true"
                                :close-on-select="false"

                            />
                            <InputError :message="form.errors.ministry_ids" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="is_active"
                            :model-value="form.is_active"
                            @update:model-value="
                                form.is_active = $event as boolean
                            "
                        />
                        <Label for="is_active">Active</Label>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Update</Button>
                        <Button variant="outline" as-child>
                            <Link :href="index().url">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
