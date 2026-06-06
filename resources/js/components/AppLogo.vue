<script setup lang="ts">
import { Trophy } from '@lucide/vue';

withDefaults(
    defineProps<{
        compact?: boolean;
    }>(),
    {
        compact: false,
    },
);
</script>

<template>
    <div
        class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-lime-500 shadow-lg shadow-emerald-500/20"
    >
        <Trophy class="size-4 text-slate-950" />
    </div>
    <div
        class="ml-1 grid min-w-0 flex-1 text-left text-sm"
        :class="compact ? 'hidden min-[420px]:grid' : ''"
    >
        <span
            class="truncate leading-tight font-black tracking-tight text-emerald-300"
        >
            WC 2026
        </span>
        <span class="truncate text-xs text-muted-foreground">Predictions</span>
    </div>
</template>
