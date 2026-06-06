<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Crown, Medal, Star, Target, Trophy, Zap } from '@lucide/vue';
import { computed } from 'vue';
import TeamFlag from '@/components/worldcup/TeamFlag.vue';
import { index as resultsIndex } from '@/routes/results';
import type {
    FinishedGameResult,
    LeaderboardEntry,
    ScoringRule,
    UserStats,
} from '@/types/worldcup';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Results',
                href: resultsIndex(),
            },
        ],
    },
});

const props = defineProps<{
    leaderboard: LeaderboardEntry[];
    finishedGames: FinishedGameResult[];
    userStats: UserStats;
    scoringRules: ScoringRule[];
}>();

const rankIcon = (rank: number) => {
    if (rank === 1) {
        return Crown;
    }

    if (rank === 2) {
        return Medal;
    }

    if (rank === 3) {
        return Star;
    }

    return null;
};

const pointsBadgeClass = (points: number | null | undefined) => {
    switch (points) {
        case 4:
            return 'bg-amber-500/20 text-amber-300 ring-amber-400/40';
        case 2:
            return 'bg-emerald-500/20 text-emerald-300 ring-emerald-400/40';
        case 1:
            return 'bg-sky-500/20 text-sky-300 ring-sky-400/40';
        default:
            return 'bg-slate-500/20 text-slate-400 ring-slate-500/40';
    }
};

const pointsLabel = (points: number | null | undefined) => {
    if (points === null || points === undefined) {
        return '–';
    }

    return `${points} pt${points === 1 ? '' : 's'}`;
};

const currentUserRank = computed(
    () =>
        props.leaderboard.find((entry) => entry.is_current_user)?.rank ?? null,
);
</script>

<template>
    <Head title="Results" />

    <div class="space-y-6">
        <section
            class="relative overflow-hidden rounded-3xl border border-amber-500/20 bg-gradient-to-br from-amber-950/80 via-slate-950 to-slate-900 p-6 shadow-2xl shadow-amber-950/30 md:p-8"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.2),transparent_55%)]"
            />
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p
                        class="mb-2 inline-flex items-center gap-2 rounded-full bg-amber-500/15 px-3 py-1 text-xs font-bold uppercase tracking-widest text-amber-300 ring-1 ring-amber-500/30"
                    >
                        <Trophy class="size-3.5" />
                        Leaderboard
                    </p>
                    <h1 class="text-3xl font-black tracking-tight text-white md:text-4xl">
                        Your Results
                    </h1>
                    <p class="mt-2 max-w-xl text-sm text-slate-300 md:text-base">
                        Points update automatically when final scores arrive from
                        the World Cup feed.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        class="rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-center"
                    >
                        <p class="text-2xl font-black text-amber-300">
                            {{ userStats.total_points }}
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Total pts
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-center"
                    >
                        <p class="text-2xl font-black text-emerald-300">
                            {{ userStats.exact_hits }}
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Exact hits
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-center"
                    >
                        <p class="text-2xl font-black text-violet-300">
                            {{ userStats.pen_hits }}
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Pen bonuses
                        </p>
                    </div>
                    <div
                        class="rounded-2xl border border-white/10 bg-black/30 px-4 py-3 text-center"
                    >
                        <p class="text-2xl font-black text-lime-300">
                            {{ currentUserRank ?? '–' }}
                        </p>
                        <p class="text-xs uppercase tracking-wide text-slate-400">
                            Your rank
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section
                class="rounded-2xl border border-white/10 bg-slate-900/60 p-5 shadow-xl backdrop-blur-sm"
            >
                <h2
                    class="mb-4 flex items-center gap-2 text-lg font-bold text-white"
                >
                    <Crown class="size-5 text-amber-400" />
                    Standings
                </h2>

                <div
                    v-if="leaderboard.length === 0"
                    class="rounded-xl border border-dashed border-white/10 py-12 text-center text-slate-400"
                >
                    No predictions yet. Be the first to play!
                </div>

                <ul v-else class="space-y-2">
                    <li
                        v-for="entry in leaderboard"
                        :key="entry.id"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 transition"
                        :class="
                            entry.is_current_user
                                ? 'bg-gradient-to-r from-emerald-500/20 to-transparent ring-1 ring-emerald-500/30'
                                : 'bg-white/5 hover:bg-white/8'
                        "
                    >
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-black/40 text-sm font-black"
                            :class="
                                entry.rank <= 3
                                    ? 'text-amber-300'
                                    : 'text-slate-400'
                            "
                        >
                            <component
                                :is="rankIcon(entry.rank)"
                                v-if="rankIcon(entry.rank)"
                                class="size-4"
                            />
                            <span v-else>{{ entry.rank }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-white">
                                {{ entry.name }}
                                <span
                                    v-if="entry.is_current_user"
                                    class="ms-1 text-xs text-emerald-400"
                                >
                                    (you)
                                </span>
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ entry.predictions_count }} predictions
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-black tabular-nums text-amber-300">
                                {{ entry.total_points }}
                            </p>
                            <p class="text-xs uppercase text-slate-500">pts</p>
                        </div>
                    </li>
                </ul>
            </section>

            <section
                class="rounded-2xl border border-white/10 bg-slate-900/60 p-5 shadow-xl backdrop-blur-sm"
            >
                <h2
                    class="mb-4 flex items-center gap-2 text-lg font-bold text-white"
                >
                    <Zap class="size-5 text-lime-400" />
                    Scoring rules
                </h2>
                <ul class="space-y-3">
                    <li
                        v-for="rule in scoringRules"
                        :key="rule.label"
                        class="flex items-center justify-between rounded-xl bg-black/30 px-4 py-3"
                    >
                        <span class="text-sm text-slate-300">{{ rule.label }}</span>
                        <span
                            class="rounded-full px-3 py-1 text-sm font-black tabular-nums ring-1 ring-inset"
                            :class="pointsBadgeClass(rule.points)"
                        >
                            +{{ rule.points }}
                        </span>
                    </li>
                </ul>
                <p class="mt-4 text-xs leading-relaxed text-slate-500">
                    90-minute tiers are scored first. Penalty bonuses apply only
                    when you predicted a draw and submitted a shootout pick on
                    knockout matches that went to penalties.
                </p>
            </section>
        </div>

        <section
            class="rounded-2xl border border-white/10 bg-slate-900/60 p-5 shadow-xl backdrop-blur-sm"
        >
            <h2
                class="mb-4 flex items-center gap-2 text-lg font-bold text-white"
            >
                <Target class="size-5 text-emerald-400" />
                Match breakdown
                <span class="text-sm font-normal text-slate-400">
                    ({{ userStats.games_finished }} finished)
                </span>
            </h2>

            <div
                v-if="finishedGames.length === 0"
                class="rounded-xl border border-dashed border-white/10 py-12 text-center text-slate-400"
            >
                No finished matches yet. Results will appear here after games
                are played.
            </div>

            <div v-else class="grid gap-3">
                <article
                    v-for="game in finishedGames"
                    :key="game.id"
                    class="rounded-xl border border-white/10 bg-black/25 p-4"
                >
                    <div
                        class="mb-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400"
                    >
                        <span>{{ game.round }}</span>
                        <span v-if="game.group_name">{{ game.group_name }}</span>
                    </div>
                    <div
                        class="grid items-center gap-3 md:grid-cols-[1fr_auto_1fr_auto]"
                    >
                        <div
                            class="flex items-center justify-end gap-3 md:flex-row-reverse"
                        >
                            <p class="font-semibold text-white">
                                {{ game.team1 }}
                            </p>
                            <TeamFlag :team="game.team1" size="sm" />
                        </div>
                        <div
                            class="flex flex-col items-center gap-1 font-mono text-xl font-black tabular-nums text-amber-300"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ game.home_score }}</span>
                                <span class="text-slate-600">:</span>
                                <span>{{ game.away_score }}</span>
                            </div>
                            <p
                                v-if="game.went_to_penalties"
                                class="text-xs font-medium text-violet-300"
                            >
                                Pens {{ game.pen_home_score }}–{{
                                    game.pen_away_score
                                }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <TeamFlag :team="game.team2" size="sm" />
                            <p class="font-semibold text-white">
                                {{ game.team2 }}
                            </p>
                        </div>
                        <div class="text-right md:min-w-28">
                            <template v-if="game.user_prediction">
                                <p class="text-xs text-slate-400">Your pick</p>
                                <p
                                    class="font-mono text-sm font-bold text-slate-200"
                                >
                                    {{ game.user_prediction.home_score }} :
                                    {{ game.user_prediction.away_score }}
                                    <span
                                        v-if="
                                            game.user_prediction
                                                .pen_home_score !== null
                                        "
                                        class="text-violet-300"
                                    >
                                        (pens
                                        {{
                                            game.user_prediction.pen_home_score
                                        }}–{{
                                            game.user_prediction.pen_away_score
                                        }})
                                    </span>
                                </p>
                                <div class="mt-1 flex flex-wrap justify-end gap-1">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset"
                                        :class="
                                            pointsBadgeClass(
                                                game.user_prediction.points,
                                            )
                                        "
                                    >
                                        FT
                                        {{
                                            pointsLabel(
                                                game.user_prediction.points,
                                            )
                                        }}
                                    </span>
                                    <span
                                        v-if="
                                            game.user_prediction.pen_points !==
                                                null &&
                                            game.user_prediction.pen_points !==
                                                undefined
                                        "
                                        class="inline-flex rounded-full bg-violet-500/20 px-2.5 py-0.5 text-xs font-bold text-violet-300 ring-1 ring-violet-400/40 ring-inset"
                                    >
                                        Pens
                                        {{
                                            pointsLabel(
                                                game.user_prediction
                                                    .pen_points,
                                            )
                                        }}
                                    </span>
                                </div>
                            </template>
                            <span v-else class="text-xs text-slate-500">
                                No prediction
                            </span>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
