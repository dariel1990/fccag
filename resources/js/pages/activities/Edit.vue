<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import {
    index,
    update,
} from '@/actions/App/Http/Controllers/ActivityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useIsMobile, useApiBaseUrl } from '@/composables/useDataSource';
import AppLayout from '@/layouts/AppLayout.vue';
import MobileLayout from '@/layouts/mobile/MobileLayout.vue';
import { type BreadcrumbItem } from '@/types';

type ActivityType = {
    id: number;
    name: string;
};

type Activity = {
    id: number;
    activity_type_id: number;
    title: string;
    description: string | null;
    activity_date: string;
};

type Props = {
    activity: Activity;
    activityTypes?: ActivityType[];
};

const props = defineProps<Props>();

const isMobile = useIsMobile();
const apiBaseUrl = useApiBaseUrl();

const activityTypes = ref<ActivityType[]>(props.activityTypes || []);

onMounted(async () => {
    if (isMobile) {
        const token = localStorage.getItem('auth_token');
        if (!token) return;

        try {
            const response = await fetch(`${apiBaseUrl}/api/activity-types`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                activityTypes.value = data.data;
            }
        } catch {
            // Silently fail
        }
    }
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activities',
        href: index().url,
    },
    {
        title: 'Edit',
    },
];

const form = useForm({
    activity_type_id: props.activity.activity_type_id,
    title: props.activity.title,
    description: props.activity.description ?? '',
    activity_date: props.activity.activity_date,
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
                `${apiBaseUrl}/api/activities/${props.activity.id}`,
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
        form.put(update(props.activity.id).url);
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
    <Head title="Edit Activity" />

    <component :is="Layout" :breadcrumbs="isMobile ? undefined : breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4"
            :class="isMobile ? 'pb-4' : 'p-4'"
        >
            <Heading
                title="Edit Activity"
                description="Update activity details"
            />

            <div
                class="w-full rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                :class="isMobile ? '' : 'mx-auto max-w-2xl'"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="activity_type_id">Activity Type</Label>
                        <select
                            id="activity_type_id"
                            v-model="form.activity_type_id"
                            required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                        >
                            <option value="" disabled>
                                Select activity type
                            </option>
                            <option
                                v-for="type in activityTypes"
                                :key="type.id"
                                :value="type.id"
                            >
                                {{ type.name }}
                            </option>
                        </select>
                        <InputError :message="getError('activity_type_id')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            v-model="form.title"
                            required
                            placeholder="e.g. Sunday Service - Week 1"
                        />
                        <InputError :message="getError('title')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="description">Description</Label>
                        <Input
                            id="description"
                            v-model="form.description"
                            placeholder="Optional description"
                        />
                        <InputError :message="getError('description')" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="activity_date">Date</Label>
                        <Input
                            id="activity_date"
                            v-model="form.activity_date"
                            type="date"
                            required
                        />
                        <InputError :message="getError('activity_date')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="isProcessing">Update</Button>
                        <Button variant="outline" as-child>
                            <Link :href="index().url">Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </component>
</template>
