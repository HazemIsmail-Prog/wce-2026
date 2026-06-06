<script setup lang="ts">
import { Minus, Plus } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        modelValue: number;
        disabled?: boolean;
        min?: number;
        max?: number;
        compact?: boolean;
        variant?: 'default' | 'violet';
    }>(),
    {
        disabled: false,
        min: 0,
        max: 20,
        compact: false,
        variant: 'default',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const canDecrement = computed(
    () => !props.disabled && props.modelValue > props.min,
);
const canIncrement = computed(
    () => !props.disabled && props.modelValue < props.max,
);

const borderClass = computed(() =>
    props.variant === 'violet'
        ? 'border-violet-500/30'
        : 'border-emerald-500/30',
);

const buttonClass = computed(() =>
    props.variant === 'violet'
        ? 'text-violet-300 hover:bg-violet-500/20 hover:text-violet-200'
        : 'text-emerald-300 hover:bg-emerald-500/20 hover:text-emerald-200',
);

function decrement(): void {
    if (canDecrement.value) {
        emit('update:modelValue', props.modelValue - 1);
    }
}

function increment(): void {
    if (canIncrement.value) {
        emit('update:modelValue', props.modelValue + 1);
    }
}
</script>

<template>
    <div
        :class="
            cn(
                'flex items-center rounded-xl border bg-black/40',
                borderClass,
                compact ? 'gap-0 p-0.5' : 'gap-1 p-1',
            )
        "
    >
        <Button
            type="button"
            variant="ghost"
            size="icon"
            :class="
                cn(
                    'rounded-lg',
                    buttonClass,
                    compact ? 'size-7' : 'size-9',
                )
            "
            :disabled="!canDecrement"
            @click="decrement"
        >
            <Minus :class="compact ? 'size-3.5' : 'size-4'" />
        </Button>
        <span
            :class="
                cn(
                    'text-center font-black tabular-nums text-white',
                    compact ? 'min-w-7 text-lg' : 'min-w-10 text-2xl',
                )
            "
        >
            {{ modelValue }}
        </span>
        <Button
            type="button"
            variant="ghost"
            size="icon"
            :class="
                cn(
                    'rounded-lg',
                    buttonClass,
                    compact ? 'size-7' : 'size-9',
                )
            "
            :disabled="!canIncrement"
            @click="increment"
        >
            <Plus :class="compact ? 'size-3.5' : 'size-4'" />
        </Button>
    </div>
</template>
