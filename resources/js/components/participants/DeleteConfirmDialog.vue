<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    itemName?: string;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();

function handleClose() {
    emit('update:open', false);
}

function handleConfirm() {
    emit('confirm');
}
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title || 'Confirm Delete' }}</DialogTitle>
                <DialogDescription>
                    {{
                        description ||
                        `Are you sure you want to delete "${itemName}"? This will also delete all their attendance records.`
                    }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="handleClose"> Cancel </Button>
                <Button variant="destructive" @click="handleConfirm">
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
