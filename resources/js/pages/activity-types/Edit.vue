<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Multiselect from '@vueform/multiselect';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/ActivityTypeController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useIsMobile, useApiBaseUrl } from '@/composables/useDataSource';
import AppLayout from '@/layouts/AppLayout.vue';
import MobileLayout from '@/layouts/mobile/MobileLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Department = { id: number; name: string };

type ActivityType = {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    department_ids: number[];
};

type Props = {
    activityType: ActivityType;
    departments: Department[];
};

const props = defineProps<Props>();

const isMobile = useIsMobile();
const apiBaseUrl = useApiBaseUrl();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activity Types',
        href: index().url,
    },
    {
        title: 'Edit',
    },
];

const form = useForm({
    name: props.activityType.name,
    description: props.activityType.description ?? '',
    is_active: props.activityType.is_active,
    department_ids: props.activityType.department_ids ?? [],
});

const mobileErrors = ref<Record<string, string>>({});
const mobileProcessing = ref(false);

async function submit() {
    if (isMobile) {
        mobileProcessing.value = true;
        mobileErrors.value = {};
        const token = localStorage.getItem('auth_token');

        try {
            const response = await fetch(
                `${apiBaseUrl}/api/activity-types/${props.activityType.id}`,
                {
                    method: 'PUT',
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify(form.data()),
                },
            );

            if (response.status === 422) {
                const data = await response.json();
                if (data.errors) {
                    mobileErrors.value = Object.fromEntries(
                        Object.entries(data.errors).map(([key, msgs]) => [
                            key,
                            (msgs as string[])[0],
                        ]),
                    );
                }
                return;
            }

            if (response.ok) {
                window.location.href = index().url;
            }
        } finally {
            mobileProcessing.value = false;
        }
    } else {
        form.put(update(props.activityType.id).url);
    }
}

const isProcessing = isMobile ? mobileProcessing : form.processing;
const getError = (field: string) =>
    isMobile
        ? mobileErrors.value[field]
        : form.errors[field as keyof typeof form.errors];

const Layout = isMobile ? MobileLayout : AppLayout;
</script>

<template>
    <Head title="Edit Activity Type" />

    <component :is="Layout" :breadcrumbs="isMobile ? undefined : breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4"
            :class="isMobile ? 'pb-4' : 'p-4'"
        >
            <Heading
                title="Edit Activity Type"
                description="Update the activity type details"
            />

            <div
                class="w-full rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                :class="isMobile ? '' : 'mx-auto max-w-2xl'"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            required
                            placeholder="e.g. Sunday Service"
                        />
                        <InputError :message="getError('name')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Brief description of this activity type"
                        />
                        <InputError :message="getError('description')" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Departments</Label>
                        <Multiselect
                            v-model="form.department_ids"
                            :options="props.departments"
                            label="name"
                            value-prop="id"
                            mode="tags"
                            placeholder="Select departments (leave empty for all members)"
                            :searchable="true"
                            :close-on-select="false"
                        />
                        <p class="text-xs text-muted-foreground">
                            Only members of selected departments will appear in attendance. Leave empty to include all active members.
                        </p>
                        <InputError :message="getError('department_ids')" />
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
                        <Button :disabled="isProcessing"> Update </Button>
                        <Button variant="outline" as-child>
                            <Link :href="index().url">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </component>
</template>
