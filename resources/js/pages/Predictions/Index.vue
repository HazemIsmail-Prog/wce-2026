<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarDays, Filter, Target } from '@lucide/vue';
import { computed, ref } from 'vue';
import MatchPredictionCard from '@/components/worldcup/MatchPredictionCard.vue';
import { index as predictionsIndex } from '@/routes/predictions';
import type { GameItem } from '@/types/worldcup';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Predictions',
                href: predictionsIndex(),
            },
        ],
    },
});

const props = defineProps<{
    games: GameItem[];
    lockMinutes: number;
}>();

type FilterKey = 'all' | 'open' | 'locked' | 'finished';

const activeFilter = ref<FilterKey>('all');

const filters: { key: FilterKey; label: string }[] = [
    { key: 'all', label: 'All matches' },
    { key: 'open', label: 'Open' },
    { key: 'locked', label: 'Locked' },
    { key: 'finished', label: 'Finished' },
];

const filteredGames = computed(() => {
    switch (activeFilter.value) {
        case 'open':
            return props.games.filter(
                (game) => !game.is_locked && !game.is_finished,
            );
        case 'locked':
            return props.games.filter(
                (game) => game.is_locked && !game.is_finished,
            );
        case 'finished':
            return props.games.filter((game) => game.is_finished);
        default:
            return props.games;
    }
});

const stats = computed(() => ({
    total: props.games.length,
    open: props.games.filter((g) => !g.is_locked && !g.is_finished).length,
    picked: props.games.filter((g) => g.prediction !== null).length,
}));
</script>

<template>
    <Head title="Predictions" />

    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-950 via-slate-950 to-slate-900 p-4 shadow-2xl shadow-emerald-950/40 sm:rounded-3xl sm:p-6 md:p-8"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.25),transparent_50%)]"
            />
            <div class="relative flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <p
                        class="mb-2 inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-bold uppercase tracking-widest text-emerald-300 ring-1 ring-emerald-500/30"
                    >
                        <Target class="size-3.5" />
                        World Cup 2026
                    </p>
                    <h1
                        class="text-2xl font-black tracking-tight text-white sm:text-3xl md:text-4xl"
                    >
                        Score Predictions
                    </h1>
                    <p class="mt-2 max-w-xl text-sm text-slate-300 md:text-base">
                        Predict the 90-minute score for every match. Knockout
                        draws are scored on full time — penalty shootouts only
                        matter for picking the advancing team.
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                    <div
                        class="rounded-xl border border-white/10 bg-black/30 px-2 py-2 text-center sm:rounded-2xl sm:px-4 sm:py-3"
                    >
                        <p class="text-xl font-black text-emerald-300 sm:text-2xl">
                            {{ stats.total }}
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Matches
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-white/10 bg-black/30 px-2 py-2 text-center sm:rounded-2xl sm:px-4 sm:py-3"
                    >
                        <p class="text-xl font-black text-lime-300 sm:text-2xl">
                            {{ stats.open }}
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Open
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-white/10 bg-black/30 px-2 py-2 text-center sm:rounded-2xl sm:px-4 sm:py-3"
                    >
                        <p class="text-xl font-black text-amber-300 sm:text-2xl">
                            {{ stats.picked }}
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Your picks
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="-mx-3 flex items-center gap-2 overflow-x-auto px-3 pb-1 sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0"
        >
            <Filter class="size-4 shrink-0 text-slate-400" />
            <button
                v-for="filter in filters"
                :key="filter.key"
                type="button"
                class="shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition sm:px-4 sm:text-sm"
                :class="
                    activeFilter === filter.key
                        ? 'bg-emerald-500 text-slate-950 shadow-lg shadow-emerald-500/30'
                        : 'bg-white/5 text-slate-300 hover:bg-white/10'
                "
                @click="activeFilter = filter.key"
            >
                {{ filter.label }}
            </button>
        </section>

        <div
            v-if="filteredGames.length === 0"
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-white/15 bg-white/5 py-16 text-center"
        >
            <CalendarDays class="mb-3 size-10 text-slate-500" />
            <p class="font-semibold text-slate-300">No matches in this filter</p>
        </div>

        <div v-else class="grid gap-3 sm:gap-5">
            <MatchPredictionCard
                v-for="game in filteredGames"
                :key="game.id"
                :game="game"
                :lock-minutes="lockMinutes"
            />
        </div>
    </div>
</template>
