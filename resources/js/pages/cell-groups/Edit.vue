<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/CellGroupController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type CellGroup = {
    id: number;
    name: string;
    leader: string | null;
    description: string | null;
    is_active: boolean;
};

type Props = {
    cellGroup: CellGroup;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cell Groups',
        href: index().url,
    },
    {
        title: 'Edit',
    },
];

const form = useForm({
    name: props.cellGroup.name,
    leader: props.cellGroup.leader ?? '',
    description: props.cellGroup.description ?? '',
    is_active: props.cellGroup.is_active,
});

function submit() {
    form.put(update(props.cellGroup.id).url);
}
</script>

<template>
    <Head title="Edit Cell Group" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Heading
                title="Edit Cell Group"
                description="Update the cell group details"
            />

            <div
                class="mx-auto w-full max-w-2xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            required
                            placeholder="e.g. Nehemiah Group"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="leader">Leader</Label>
                        <Input
                            id="leader"
                            v-model="form.leader"
                            placeholder="Leader's name"
                        />
                        <InputError :message="form.errors.leader" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Brief description of this cell group"
                        />
                        <InputError :message="form.errors.description" />
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
