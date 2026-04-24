<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
    noMargin?: boolean;
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
    noMargin: false,
});
</script>

<template>
    <header :class="variant === 'small' || noMargin ? 'space-y-0.5' : 'mb-8 space-y-0.5'">
        <h2
            :class="
                variant === 'small'
                    ? 'mb-0.5 text-base font-medium'
                    : 'text-xl font-semibold tracking-tight'
            "
        >
            {{ title }}
        </h2>
        <p v-if="description" class="text-sm text-muted-foreground">
            {{ description }}
        </p>
    </header>
</template>
