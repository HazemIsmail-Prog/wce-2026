<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Target, Trophy } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';

const features = [
    {
        icon: Target,
        title: 'Predict every match',
        text: 'Pick 90-minute scores for all 104 World Cup 2026 fixtures.',
    },
    {
        icon: Trophy,
        title: 'Climb the leaderboard',
        text: 'Earn up to 4 points per match with exact scores and smart picks.',
    },
];
</script>

<template>
    <Head title="World Cup 2026 Predictions" />

    <div
        class="game-shell flex min-h-screen flex-col bg-background text-foreground"
    >
        <header class="flex items-center justify-between px-6 py-5 md:px-10">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-lime-500"
                >
                    <Trophy class="size-5 text-slate-950" />
                </div>
                <div>
                    <p class="font-black text-emerald-300">WC 2026</p>
                    <p class="text-xs text-muted-foreground">Score Predictions</p>
                </div>
            </div>
            <nav class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="text-sm font-semibold text-emerald-300 hover:text-emerald-200"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="text-sm font-medium text-muted-foreground hover:text-white"
                    >
                        Log in
                    </Link>
                    <Button
                        as-child
                        class="bg-gradient-to-r from-emerald-500 to-lime-500 font-bold text-slate-950 hover:from-emerald-400 hover:to-lime-400"
                    >
                        <Link :href="register()">Join the game</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main
            class="mx-auto flex w-full max-w-6xl flex-1 flex-col items-center justify-center gap-10 px-6 pb-16 pt-8 md:flex-row md:gap-16 md:px-10"
        >
            <section class="max-w-xl text-center md:text-left">
                <p
                    class="mb-4 inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-bold uppercase tracking-widest text-emerald-300 ring-1 ring-emerald-500/30"
                >
                    FIFA World Cup 2026
                </p>
                <h1
                    class="text-4xl font-black leading-tight tracking-tight text-white md:text-6xl"
                >
                    Guess the scores.<br />
                    <span class="text-emerald-400">Win bragging rights.</span>
                </h1>
                <p class="mt-5 text-base text-slate-300 md:text-lg">
                    Register, predict full-time scores before each match locks,
                    and compete on the global leaderboard when results land.
                </p>
                <div
                    v-if="!$page.props.auth.user"
                    class="mt-8 flex flex-wrap justify-center gap-3 md:justify-start"
                >
                    <Button
                        as-child
                        size="lg"
                        class="bg-gradient-to-r from-emerald-500 to-lime-500 font-bold text-slate-950 hover:from-emerald-400 hover:to-lime-400"
                    >
                        <Link :href="register()">Create free account</Link>
                    </Button>
                    <Button
                        as-child
                        size="lg"
                        variant="outline"
                        class="border-white/20 bg-white/5 text-white hover:bg-white/10"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                </div>
                <Button
                    v-else
                    as-child
                    size="lg"
                    class="mt-8 bg-gradient-to-r from-emerald-500 to-lime-500 font-bold text-slate-950"
                >
                    <Link :href="dashboard()">Go to dashboard</Link>
                </Button>
            </section>

            <section class="grid w-full max-w-md gap-4">
                <article
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-2xl border border-white/10 bg-card/60 p-5 backdrop-blur-sm"
                >
                    <component
                        :is="feature.icon"
                        class="mb-3 size-8 text-emerald-400"
                    />
                    <h2 class="font-bold text-white">{{ feature.title }}</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ feature.text }}
                    </p>
                </article>
                <article
                    class="rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-950/40 to-transparent p-5"
                >
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-400">
                        Scoring
                    </p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li>Exact 90-min score → <strong class="text-amber-300">4 pts</strong></li>
                        <li>Correct goal difference → <strong class="text-emerald-300">2 pts</strong></li>
                        <li>Correct winner / FT draw → <strong class="text-sky-300">1 pt</strong></li>
                    </ul>
                </article>
            </section>
        </main>
    </div>
</template>
