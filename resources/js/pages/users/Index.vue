<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Pencil, Plus, ShieldCheck, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
    destroy,
    index,
    store,
    update,
} from '@/actions/App/Http/Controllers/UserController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

type EnumCase = { name: string; value: string };

type User = {
    id: number;
    name: string;
    email: string;
    is_superadmin: boolean;
    permissions: Record<string, string[] | boolean> | null;
    created_at: string;
};

const props = defineProps<{
    users: User[];
    modules: EnumCase[];
    actions: EnumCase[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: index().url },
];

// ── Create dialog ────────────────────────────────────────────────────────────

const createDialogOpen = ref(false);

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_superadmin: false,
    full_access: false,
    permissions: {} as Record<string, string[]>,
});

function openCreateDialog() {
    createForm.reset();
    createDialogOpen.value = true;
}

function submitCreate() {
    createForm.post(store().url, {
        onSuccess: () => {
            createDialogOpen.value = false;
            createForm.reset();
        },
    });
}

// ── Edit sheet ───────────────────────────────────────────────────────────────

const editSheetOpen = ref(false);
const editingUser = ref<User | null>(null);

const editForm = useForm({
    name: '',
    email: '',
    is_superadmin: false,
    full_access: false,
    permissions: {} as Record<string, string[]>,
});

function openEditSheet(user: User) {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.is_superadmin = user.is_superadmin;

    const hasFullAccess =
        user.permissions !== null && user.permissions['*'] === true;

    editForm.full_access = hasFullAccess;

    const perms: Record<string, string[]> = {};

    if (user.permissions && !hasFullAccess) {
        for (const [key, value] of Object.entries(user.permissions)) {
            if (Array.isArray(value)) {
                perms[key] = value;
            }
        }
    }

    editForm.permissions = perms;
    editSheetOpen.value = true;
}

function submitEdit() {
    if (!editingUser.value) {
        return;
    }

    editForm.put(update(editingUser.value.id).url, {
        onSuccess: () => {
            editSheetOpen.value = false;
            editingUser.value = null;
        },
    });
}

// ── Delete ───────────────────────────────────────────────────────────────────

function deleteUser(user: User) {
    if (confirm(`Are you sure you want to delete "${user.name}"?`)) {
        router.delete(destroy(user.id).url);
    }
}

// ── Permissions helpers ──────────────────────────────────────────────────────

function isChecked(
    perms: Record<string, string[]>,
    moduleValue: string,
    actionValue: string,
): boolean {
    return perms[moduleValue]?.includes(actionValue) ?? false;
}

function togglePermission(
    form: typeof createForm | typeof editForm,
    moduleValue: string,
    actionValue: string,
) {
    const current = form.permissions[moduleValue] ?? [];

    if (current.includes(actionValue)) {
        form.permissions[moduleValue] = current.filter((a) => a !== actionValue);
    } else {
        form.permissions[moduleValue] = [...current, actionValue];
    }
}

function toggleAllActions(
    form: typeof createForm | typeof editForm,
    moduleValue: string,
) {
    const current = form.permissions[moduleValue] ?? [];
    const allActions = props.actions.map((a) => a.value);

    form.permissions[moduleValue] =
        current.length === allActions.length ? [] : [...allActions];
}

function isModuleAllChecked(
    perms: Record<string, string[]>,
    moduleValue: string,
): boolean {
    return (perms[moduleValue] ?? []).length === props.actions.length;
}

function formatLabel(name: string): string {
    return name.replace(/([A-Z])/g, ' $1').trim();
}

// ── Permission summary for table ─────────────────────────────────────────────

function permissionSummary(user: User): string {
    if (user.is_superadmin) {
        return 'Superadmin';
    }

    if (!user.permissions || Object.keys(user.permissions).length === 0) {
        return 'No permissions';
    }

    if (user.permissions['*'] === true) {
        return 'Full Access';
    }

    const moduleCount = Object.keys(user.permissions).length;

    return `${moduleCount} module${moduleCount !== 1 ? 's' : ''}`;
}

const isCreateFullAccess = computed(() => createForm.full_access);
const isEditFullAccess = computed(() => editForm.full_access);
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <Heading
                    title="Users"
                    description="Manage system users and their permissions"
                />
                <Button @click="openCreateDialog">
                    <Plus class="mr-2 h-4 w-4" />
                    Add User
                </Button>
            </div>

            <div
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Permissions</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableEmpty v-if="props.users.length === 0" :colspan="5">
                            No users found.
                        </TableEmpty>
                        <TableRow v-for="user in props.users" :key="user.id">
                            <TableCell class="font-medium">
                                <div class="flex items-center gap-2">
                                    {{ user.name }}
                                    <ShieldCheck
                                        v-if="user.is_superadmin"
                                        class="h-4 w-4 text-primary"
                                    />
                                </div>
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ user.email }}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        user.is_superadmin
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        user.is_superadmin ? 'Superadmin' : 'User'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ permissionSummary(user) }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditSheet(user)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="deleteUser(user)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>

    <!-- Create User Dialog -->
    <Dialog :open="createDialogOpen" @update:open="createDialogOpen = $event">
        <DialogScrollContent class="max-w-4xl">
            <DialogHeader>
                <DialogTitle>Add User</DialogTitle>
                <DialogDescription>
                    Add a new system user and configure their permissions.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitCreate" class="space-y-4">
                <!-- Basic info -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="create-name">Name</Label>
                        <Input
                            id="create-name"
                            v-model="createForm.name"
                            required
                            placeholder="Full name"
                        />
                        <InputError :message="createForm.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-email">Email</Label>
                        <Input
                            id="create-email"
                            type="email"
                            v-model="createForm.email"
                            required
                            placeholder="email@example.com"
                        />
                        <InputError :message="createForm.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-password">Password</Label>
                        <Input
                            id="create-password"
                            type="password"
                            v-model="createForm.password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError :message="createForm.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="create-password-confirm">Confirm Password</Label>
                        <Input
                            id="create-password-confirm"
                            type="password"
                            v-model="createForm.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <!-- Role toggles -->
                <div class="flex flex-col gap-3 border-t pt-4">
                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="create-is_superadmin"
                            :model-value="createForm.is_superadmin"
                            @update:model-value="createForm.is_superadmin = $event as boolean"
                        />
                        <Label for="create-is_superadmin">Superadmin</Label>
                        <span class="text-xs text-muted-foreground">— Grants unrestricted access to everything</span>
                    </div>

                    <div v-if="!createForm.is_superadmin" class="flex items-center gap-2">
                        <Checkbox
                            id="create-full_access"
                            :model-value="createForm.full_access"
                            @update:model-value="createForm.full_access = $event as boolean"
                        />
                        <Label for="create-full_access">Full Access</Label>
                        <span class="text-xs text-muted-foreground">— Grants access to all modules and actions</span>
                    </div>
                </div>

                <!-- Permission matrix -->
                <div
                    v-if="!createForm.is_superadmin"
                    class="border-t pt-4"
                    :class="{ 'pointer-events-none opacity-40': isCreateFullAccess }"
                >
                    <p class="mb-3 text-sm font-medium">Permissions</p>
                    <div class="overflow-x-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-2 text-left font-medium">Module</th>
                                    <th class="px-3 py-2 text-center font-medium text-primary">All</th>
                                    <th
                                        v-for="action in props.actions"
                                        :key="action.value"
                                        class="px-3 py-2 text-center font-medium capitalize"
                                    >
                                        {{ formatLabel(action.name) }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="module in props.modules"
                                    :key="module.value"
                                    class="border-b last:border-0 hover:bg-muted/30"
                                >
                                    <td class="px-4 py-2 font-medium">{{ formatLabel(module.name) }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <Checkbox
                                            :model-value="isModuleAllChecked(createForm.permissions, module.value)"
                                            @update:model-value="toggleAllActions(createForm, module.value)"
                                        />
                                    </td>
                                    <td
                                        v-for="action in props.actions"
                                        :key="action.value"
                                        class="px-3 py-2 text-center"
                                    >
                                        <Checkbox
                                            :model-value="isChecked(createForm.permissions, module.value, action.value)"
                                            @update:model-value="togglePermission(createForm, module.value, action.value)"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <InputError :message="createForm.errors.permissions" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="createDialogOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="createForm.processing">
                        Create
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>

    <!-- Edit User Dialog -->
    <Dialog :open="editSheetOpen" @update:open="editSheetOpen = $event">
        <DialogScrollContent class="max-w-4xl">
            <DialogHeader>
                <DialogTitle>Edit User</DialogTitle>
                <DialogDescription>
                    Update user details and permissions.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitEdit" class="space-y-4">
                <!-- Basic info -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="edit-name">Name</Label>
                        <Input
                            id="edit-name"
                            v-model="editForm.name"
                            required
                            placeholder="Full name"
                        />
                        <InputError :message="editForm.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit-email">Email</Label>
                        <Input
                            id="edit-email"
                            type="email"
                            v-model="editForm.email"
                            required
                            placeholder="email@example.com"
                        />
                        <InputError :message="editForm.errors.email" />
                    </div>
                </div>

                <!-- Role toggles -->
                <div class="flex flex-col gap-3 border-t pt-4">
                    <div class="flex items-center gap-2">
                        <Checkbox
                            id="edit-is_superadmin"
                            :model-value="editForm.is_superadmin"
                            @update:model-value="editForm.is_superadmin = $event as boolean"
                        />
                        <Label for="edit-is_superadmin">Superadmin</Label>
                        <span class="text-xs text-muted-foreground">— Grants unrestricted access to everything</span>
                    </div>

                    <div v-if="!editForm.is_superadmin" class="flex items-center gap-2">
                        <Checkbox
                            id="edit-full_access"
                            :model-value="editForm.full_access"
                            @update:model-value="editForm.full_access = $event as boolean"
                        />
                        <Label for="edit-full_access">Full Access</Label>
                        <span class="text-xs text-muted-foreground">— Grants access to all modules and actions</span>
                    </div>
                </div>

                <!-- Permission matrix -->
                <div
                    v-if="!editForm.is_superadmin"
                    class="border-t pt-4"
                    :class="{ 'pointer-events-none opacity-40': isEditFullAccess }"
                >
                    <p class="mb-3 text-sm font-medium">Permissions</p>
                    <div class="overflow-x-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-2 text-left font-medium">Module</th>
                                    <th class="px-3 py-2 text-center font-medium text-primary">All</th>
                                    <th
                                        v-for="action in props.actions"
                                        :key="action.value"
                                        class="px-3 py-2 text-center font-medium capitalize"
                                    >
                                        {{ formatLabel(action.name) }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="module in props.modules"
                                    :key="module.value"
                                    class="border-b last:border-0 hover:bg-muted/30"
                                >
                                    <td class="px-4 py-2 font-medium">{{ formatLabel(module.name) }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <Checkbox
                                            :model-value="isModuleAllChecked(editForm.permissions, module.value)"
                                            @update:model-value="toggleAllActions(editForm, module.value)"
                                        />
                                    </td>
                                    <td
                                        v-for="action in props.actions"
                                        :key="action.value"
                                        class="px-3 py-2 text-center"
                                    >
                                        <Checkbox
                                            :model-value="isChecked(editForm.permissions, module.value, action.value)"
                                            @update:model-value="togglePermission(editForm, module.value, action.value)"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <InputError :message="editForm.errors.permissions" />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="editSheetOpen = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="editForm.processing">
                        Update
                    </Button>
                </DialogFooter>
            </form>
        </DialogScrollContent>
    </Dialog>
</template>
