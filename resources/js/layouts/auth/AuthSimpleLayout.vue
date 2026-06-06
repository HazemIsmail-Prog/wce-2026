<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Trophy } from '@lucide/vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="game-shell flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-3 font-medium"
                    >
                        <div
                            class="flex size-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-lime-500 shadow-xl shadow-emerald-500/30"
                        >
                            <Trophy class="size-7 text-slate-950" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-2xl font-black text-white">
                            {{ title }}
                        </h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-white/10 bg-card/80 p-6 shadow-2xl backdrop-blur-sm"
                >
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
