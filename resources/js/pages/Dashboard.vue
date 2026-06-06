<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Target, Trophy } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as predictionsIndex } from '@/routes/predictions';
import { index as resultsIndex } from '@/routes/results';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6">
        <section
            class="relative overflow-hidden rounded-3xl border border-emerald-500/20 bg-gradient-to-br from-emerald-950 via-slate-950 to-slate-900 p-8 shadow-2xl"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.25),transparent_50%)]"
            />
            <div class="relative max-w-2xl">
                <p
                    class="mb-3 inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-bold uppercase tracking-widest text-emerald-300 ring-1 ring-emerald-500/30"
                >
                    FIFA World Cup 2026
                </p>
                <h1 class="text-3xl font-black text-white md:text-4xl">
                    Welcome to the prediction game
                </h1>
                <p class="mt-3 text-slate-300">
                    Guess full-time scores, climb the leaderboard, and see how
                    you stack up against other players.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Button
                        as-child
                        class="bg-gradient-to-r from-emerald-500 to-lime-500 font-bold text-slate-950 hover:from-emerald-400 hover:to-lime-400"
                    >
                        <Link :href="predictionsIndex()">
                            <Target class="me-2 size-4" />
                            Make predictions
                        </Link>
                    </Button>
                    <Button
                        as-child
                        variant="outline"
                        class="border-white/20 bg-white/5 text-white hover:bg-white/10"
                    >
                        <Link :href="resultsIndex()">
                            <Trophy class="me-2 size-4" />
                            View results
                        </Link>
                    </Button>
                </div>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2">
            <Link
                :href="predictionsIndex()"
                class="group rounded-2xl border border-white/10 bg-slate-900/60 p-6 transition hover:border-emerald-500/30 hover:bg-slate-900"
            >
                <Target
                    class="mb-3 size-8 text-emerald-400 transition group-hover:scale-110"
                />
                <h2 class="text-lg font-bold text-white">Predictions</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Browse all 104 World Cup matches and submit your score
                    picks before lock time.
                </p>
            </Link>
            <Link
                :href="resultsIndex()"
                class="group rounded-2xl border border-white/10 bg-slate-900/60 p-6 transition hover:border-amber-500/30 hover:bg-slate-900"
            >
                <Trophy
                    class="mb-3 size-8 text-amber-400 transition group-hover:scale-110"
                />
                <h2 class="text-lg font-bold text-white">Results</h2>
                <p class="mt-1 text-sm text-slate-400">
                    Track your points, see the leaderboard, and review match
                    breakdowns.
                </p>
            </Link>
        </div>
    </div>
</template>
