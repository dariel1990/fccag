<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import { index, destroy } from '@/actions/App/Http/Controllers/Si/SiMemberController';
import Heading from '@/components/Heading.vue';
import SiMemberFormDialog from '@/components/si/SiMemberFormDialog.vue';
import SiMemberSheet from '@/components/si/SiMemberSheet.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableEmpty,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type Caregiver = { id: number; first_name: string; last_name: string };

type Member = {
    id: number;
    caregiver_id: number | null;
    name: string;
    sex: string;
    sex_label: string;
    ph_id: string | null;
    address: string | null;
    status: string;
    status_label: string;
    status_color: string;
    enrolled_at: string;
    exited_at: string | null;
    caregiver: { id: number; name: string } | null;
};

const props = defineProps<{
    members: Member[];
    caregivers: Caregiver[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'SI Members', href: index().url }];

const formDialogOpen = ref(false);
const formMember = ref<Member | null>(null);
const sheetOpen = ref(false);
const sheetMember = ref<Member | null>(null);

function openCreateDialog() {
    formMember.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(member: Member) {
    formMember.value = member;
    formDialogOpen.value = true;
}

function openSheet(member: Member) {
    sheetMember.value = member;
    sheetOpen.value = true;
}

function deleteMember(member: Member) {
    if (confirm(`Are you sure you want to remove "${member.name}" from the SI program?`)) {
        router.delete(destroy(member.id).url);
    }
}
</script>

<template>
    <Head title="SI Members" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading title="SI Members" description="Survival Intervention program participants" />
                <Button @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Enroll Member
                </Button>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>PH ID</TableHead>
                            <TableHead>Sex</TableHead>
                            <TableHead>Caregiver</TableHead>
                            <TableHead>Enrolled</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="members.length === 0" :colspan="7">
                            No SI members enrolled yet.
                        </TableEmpty>
                        <TableRow v-for="member in members" :key="member.id">
                            <TableCell class="font-medium">{{ member.name }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ member.ph_id || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ member.sex_label }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ member.caregiver?.name || '—' }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ member.enrolled_at }}</TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="member.status === 'active' ? 'default' : 'destructive'">
                                    {{ member.status_label }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openSheet(member)">
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openEditDialog(member)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="deleteMember(member)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <SiMemberFormDialog
            v-model:open="formDialogOpen"
            :member="formMember"
            :caregivers="props.caregivers"
            @saved="router.reload()"
        />
        <SiMemberSheet v-model:open="sheetOpen" :member="sheetMember" />
    </AppLayout>
</template>
