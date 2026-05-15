<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    destroy as destroyMusicMember,
    index as musicMembersIndex,
} from '@/actions/App/Http/Controllers/Music/MusicMemberController';
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import MusicMemberFormDialog from '@/components/music/MusicMemberFormDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type LinkedUser = { id: number; name: string; email: string };

type MusicMember = {
    id: number;
    name: string;
    user_id: number | null;
    instruments: string | null;
    is_active: boolean;
    user?: LinkedUser | null;
};

type UserOption = { id: number; name: string; email: string };

defineProps<{
    members: MusicMember[];
    users: UserOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Music Members',
        href: musicMembersIndex().url,
    },
];

const formDialogOpen = ref(false);
const formModel = ref<MusicMember | null>(null);

const deleteDialogOpen = ref(false);
const deleteTarget = ref<MusicMember | null>(null);

function openCreateDialog(): void {
    formModel.value = null;
    formDialogOpen.value = true;
}

function openEditDialog(member: MusicMember): void {
    formModel.value = member;
    formDialogOpen.value = true;
}

function openDeleteDialog(member: MusicMember): void {
    deleteTarget.value = member;
    deleteDialogOpen.value = true;
}

function handleFormSaved(): void {
    router.reload();
}

function handleDeleteConfirm(): void {
    if (!deleteTarget.value) { return; }

    router.delete(destroyMusicMember(deleteTarget.value.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            deleteTarget.value = null;
        },
    });
}
</script>

<template>
    <Head title="Music Members" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <Heading
                    title="Music Members"
                    description="Manage your music team roster"
                    no-margin
                />
                <Button class="w-full sm:w-auto sm:shrink-0" @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Member
                </Button>
            </div>

            <!-- Mobile card list -->
            <div class="space-y-2 md:hidden">
                <div
                    v-if="members.length === 0"
                    class="text-muted-foreground rounded-xl border px-4 py-8 text-center text-sm"
                >
                    No members found.
                </div>
                <div
                    v-for="member in members"
                    :key="member.id"
                    class="flex items-center gap-3 rounded-xl border px-3 py-3"
                >
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium">{{ member.name }}</div>
                        <div class="text-muted-foreground mt-0.5 flex flex-wrap items-center gap-2 text-xs">
                            <span>{{ member.instruments || '—' }}</span>
                            <span v-if="member.user">· {{ member.user.name }}</span>
                            <Badge :variant="member.is_active ? 'default' : 'secondary'" class="text-xs">
                                {{ member.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openEditDialog(member)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8" @click="openDeleteDialog(member)">
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Desktop table -->
            <div class="hidden rounded-xl border border-sidebar-border/70 md:block dark:border-sidebar-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Linked User</TableHead>
                            <TableHead>Instruments</TableHead>
                            <TableHead class="text-center">Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="members.length === 0" colspan="5">
                            No members found.
                        </TableEmpty>
                        <TableRow v-for="member in members" :key="member.id">
                            <TableCell class="font-medium">{{ member.name }}</TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ member.user ? member.user.name : '—' }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ member.instruments || '—' }}
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge :variant="member.is_active ? 'default' : 'secondary'">
                                    {{ member.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEditDialog(member)">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="openDeleteDialog(member)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <MusicMemberFormDialog
                v-model:open="formDialogOpen"
                :model="formModel"
                :users="users"
                @saved="handleFormSaved"
            />

            <DeleteConfirmDialog
                v-model:open="deleteDialogOpen"
                :item-name="deleteTarget?.name"
                :description="
                    deleteTarget
                        ? `Are you sure you want to delete &quot;${deleteTarget.name}&quot;?`
                        : undefined
                "
                @confirm="handleDeleteConfirm"
            />
        </div>
    </AppLayout>
</template>
