<script setup lang="ts">
import { useChordTransposer } from '@/composables/useChordTransposer';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    modelValue: string;
    originalKey: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [key: string];
}>();

const { keys } = useChordTransposer();
</script>

<template>
    <div class="flex items-center gap-2">
        <Label class="text-sm text-muted-foreground whitespace-nowrap">Key:</Label>
        <Select :model-value="modelValue" @update:model-value="emit('update:modelValue', $event)">
            <SelectTrigger class="w-24">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem v-for="key in keys" :key="key" :value="key">
                    {{ key }}
                </SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
