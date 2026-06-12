<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Clock, MapPin, Swords, Trophy } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ScoreStepper from '@/components/worldcup/ScoreStepper.vue';
import TeamFlag from '@/components/worldcup/TeamFlag.vue';
import { Button } from '@/components/ui/button';
import { toBool } from '@/lib/utils';
import type { AdminGameItem } from '@/types/worldcup';

const props = defineProps<{
    game: AdminGameItem;
}>();

const homeScore = ref(props.game.home_score ?? 0);
const awayScore = ref(props.game.away_score ?? 0);
const isFinished = ref(toBool(props.game.is_finished));
const wentToPenalties = ref(toBool(props.game.went_to_penalties));
const penHomeScore = ref(props.game.pen_home_score ?? 4);
const penAwayScore = ref(props.game.pen_away_score ?? 3);
const saving = ref(false);

function syncFromGame(game: AdminGameItem): void {
    homeScore.value = game.home_score ?? 0;
    awayScore.value = game.away_score ?? 0;
    isFinished.value = toBool(game.is_finished);
    wentToPenalties.value = toBool(game.went_to_penalties);
    penHomeScore.value = game.pen_home_score ?? 4;
    penAwayScore.value = game.pen_away_score ?? 3;
}

watch(
    () => props.game.id,
    () => syncFromGame(props.game),
    { immediate: true },
);

watch(
    () => props.game.is_finished,
    (value) => {
        isFinished.value = toBool(value);
    },
);

watch(
    () => props.game.went_to_penalties,
    (value) => {
        wentToPenalties.value = toBool(value);
    },
);

watch([homeScore, awayScore], ([home, away]) => {
    if (home !== away) {
        wentToPenalties.value = false;
    }
});

const kickoffLabel = computed(() =>
    new Date(props.game.kickoff_at).toLocaleString(undefined, {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }),
);

const isDraw = computed(() => homeScore.value === awayScore.value);

const showPenaltyPicker = computed(
    () => props.game.is_knockout && isDraw.value,
);

const penScoresValid = computed(
    () =>
        !wentToPenalties.value ||
        penHomeScore.value !== penAwayScore.value,
);

const hasChanges = computed(() => {
    const penPayload = wentToPenalties.value && isDraw.value
        ? { home: penHomeScore.value, away: penAwayScore.value }
        : { home: null, away: null };

    const currentPen =
        props.game.went_to_penalties && props.game.pen_home_score !== null
            ? {
                  home: props.game.pen_home_score,
                  away: props.game.pen_away_score,
              }
            : { home: null, away: null };

    return (
        (props.game.home_score ?? 0) !== homeScore.value ||
        (props.game.away_score ?? 0) !== awayScore.value ||
        toBool(props.game.is_finished) !== isFinished.value ||
        toBool(props.game.went_to_penalties) !== wentToPenalties.value ||
        currentPen.home !== penPayload.home ||
        currentPen.away !== penPayload.away
    );
});

function toggleFinished(): void {
    isFinished.value = !isFinished.value;
}

function saveScores(): void {
    if (!penScoresValid.value) {
        return;
    }

    saving.value = true;

    const payload: Record<string, number | boolean | null> = {
        home_score: homeScore.value,
        away_score: awayScore.value,
        is_finished: isFinished.value,
        went_to_penalties: false,
        pen_home_score: null,
        pen_away_score: null,
    };

    if (props.game.is_knockout && isDraw.value && wentToPenalties.value) {
        payload.went_to_penalties = true;
        payload.pen_home_score = penHomeScore.value;
        payload.pen_away_score = penAwayScore.value;
    }

    router.post(`/scores/${props.game.id}`, payload, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
}
</script>

<template>
    <article
        class="group relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-slate-900/90 via-slate-900/70 to-amber-950/30 p-3 shadow-xl shadow-black/30 backdrop-blur-sm transition hover:border-amber-500/30 sm:p-5"
    >
        <div
            class="pointer-events-none absolute -right-8 -top-8 size-32 rounded-full bg-amber-500/10 blur-2xl transition group-hover:bg-amber-500/20"
        />

        <div class="mb-3 flex flex-col gap-2 sm:mb-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span
                    class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide ring-1 ring-inset"
                    :class="
                        isFinished
                            ? 'bg-amber-500/20 text-amber-300 ring-amber-400/30'
                            : 'bg-slate-500/20 text-slate-300 ring-slate-400/30'
                    "
                >
                    {{ isFinished ? 'Finished' : 'Pending' }}
                </span>
                <span
                    v-if="game.group_name"
                    class="rounded-full bg-white/5 px-2.5 py-1 text-xs font-medium text-slate-300"
                >
                    {{ game.group_name }}
                </span>
                <span
                    v-if="game.is_knockout"
                    class="rounded-full bg-violet-500/15 px-2.5 py-1 text-xs font-medium text-violet-300"
                >
                    Knockout
                </span>
                <span class="text-[11px] font-medium text-slate-400 sm:text-xs">
                    {{ game.round }}
                </span>
            </div>
            <div class="flex items-center gap-1 text-[11px] text-slate-400 sm:text-xs">
                <Clock class="size-3.5" />
                <span>{{ kickoffLabel }}</span>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
            <div
                class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-x-2 sm:gap-4"
            >
                <div class="flex min-w-0 flex-col items-center gap-1.5 text-center">
                    <TeamFlag :team="game.team1" size="sm" class="sm:hidden" />
                    <TeamFlag :team="game.team1" class="hidden sm:block" />
                    <p
                        class="line-clamp-2 text-xs font-bold leading-tight text-white sm:text-lg"
                    >
                        {{ game.team1 }}
                    </p>
                </div>

                <div class="flex min-w-[6.75rem] flex-col items-center gap-2 sm:min-w-0">
                    <div class="flex items-center gap-1 sm:gap-3">
                        <ScoreStepper
                            v-model="homeScore"
                            variant="default"
                            compact
                            class="sm:hidden"
                        />
                        <ScoreStepper v-model="homeScore" class="hidden sm:flex" />
                        <span class="text-base font-bold text-slate-500 sm:text-xl">:</span>
                        <ScoreStepper
                            v-model="awayScore"
                            compact
                            class="sm:hidden"
                        />
                        <ScoreStepper v-model="awayScore" class="hidden sm:flex" />
                    </div>
                    <span
                        class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs"
                    >
                        Full time
                    </span>
                </div>

                <div class="flex min-w-0 flex-col items-center gap-1.5 text-center">
                    <TeamFlag :team="game.team2" size="sm" class="sm:hidden" />
                    <TeamFlag :team="game.team2" class="hidden sm:block" />
                    <p
                        class="line-clamp-2 text-xs font-bold leading-tight text-white sm:text-lg"
                    >
                        {{ game.team2 }}
                    </p>
                </div>
            </div>

            <div
                v-if="showPenaltyPicker"
                class="rounded-xl border border-violet-500/20 bg-violet-500/5 p-3 sm:p-4"
            >
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div
                        class="flex min-w-0 items-center gap-2 text-sm font-semibold text-violet-200"
                    >
                        <Swords class="size-4 shrink-0" />
                        <span class="truncate">Penalty shootout</span>
                    </div>
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="wentToPenalties"
                        aria-label="Match went to penalties"
                        class="flex shrink-0 items-center gap-2 rounded-full transition sm:inline-flex sm:py-1 sm:pl-1 sm:pr-3 sm:text-sm sm:font-semibold sm:ring-1 sm:ring-inset"
                        :class="
                            wentToPenalties
                                ? 'sm:bg-violet-500/20 sm:text-violet-200 sm:ring-violet-400/40'
                                : 'sm:bg-black/30 sm:text-slate-400 sm:ring-white/10'
                        "
                        @click="wentToPenalties = !wentToPenalties"
                    >
                        <span
                            class="relative flex h-6 w-11 items-center rounded-full p-0.5 transition-colors sm:h-5 sm:w-9"
                            :class="
                                wentToPenalties
                                    ? 'bg-violet-500'
                                    : 'bg-white/10 ring-1 ring-inset ring-white/10'
                            "
                        >
                            <span
                                class="size-5 rounded-full bg-white shadow-sm transition-transform sm:size-4"
                                :class="
                                    wentToPenalties
                                        ? 'translate-x-5 sm:translate-x-4'
                                        : 'translate-x-0'
                                "
                            />
                        </span>
                        <span class="hidden sm:inline">Went to pens</span>
                    </button>
                </div>
                <div
                    v-if="wentToPenalties"
                    class="flex flex-col items-center gap-2"
                >
                    <div class="flex items-center gap-1.5 sm:gap-3">
                        <ScoreStepper
                            v-model="penHomeScore"
                            :max="12"
                            variant="violet"
                            compact
                            class="sm:hidden"
                        />
                        <ScoreStepper
                            v-model="penHomeScore"
                            :max="12"
                            variant="violet"
                            class="hidden sm:flex"
                        />
                        <span class="text-base font-bold text-violet-400 sm:text-lg">–</span>
                        <ScoreStepper
                            v-model="penAwayScore"
                            :max="12"
                            variant="violet"
                            compact
                            class="sm:hidden"
                        />
                        <ScoreStepper
                            v-model="penAwayScore"
                            :max="12"
                            variant="violet"
                            class="hidden sm:flex"
                        />
                    </div>
                    <p v-if="!penScoresValid" class="text-xs text-red-400">
                        Shootout must have a winner
                    </p>
                </div>
            </div>

            <div
                class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 sm:p-4"
            >
                <div class="flex items-center justify-between gap-3">
                    <div
                        class="flex min-w-0 items-center gap-2 text-sm font-semibold text-amber-200"
                    >
                        <Trophy class="size-4 shrink-0" />
                        <span class="truncate">Match finished</span>
                    </div>
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="isFinished"
                        aria-label="Mark match as finished"
                        class="flex shrink-0 items-center gap-2 rounded-full transition sm:inline-flex sm:py-1 sm:pl-1 sm:pr-3 sm:text-sm sm:font-semibold sm:ring-1 sm:ring-inset"
                        :class="
                            isFinished
                                ? 'sm:bg-amber-500/20 sm:text-amber-200 sm:ring-amber-400/40'
                                : 'sm:bg-black/30 sm:text-slate-400 sm:ring-white/10'
                        "
                        @click="toggleFinished"
                    >
                        <span
                            class="text-[11px] font-medium sm:hidden"
                            :class="
                                isFinished
                                    ? 'text-amber-300'
                                    : 'text-slate-400'
                            "
                        >
                            Finished
                        </span>
                        <span
                            class="relative flex h-6 w-11 items-center rounded-full p-0.5 transition-colors sm:h-5 sm:w-9"
                            :class="
                                isFinished
                                    ? 'bg-amber-500'
                                    : 'bg-white/10 ring-1 ring-inset ring-white/10'
                            "
                        >
                            <span
                                class="size-5 rounded-full bg-white shadow-sm transition-transform sm:size-4"
                                :class="
                                    isFinished
                                        ? 'translate-x-5 sm:translate-x-4'
                                        : 'translate-x-0'
                                "
                            />
                        </span>
                        <span class="hidden sm:inline">Mark finished</span>
                    </button>
                </div>
            </div>

            <Button
                class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-sm font-bold text-slate-950 hover:from-amber-400 hover:to-orange-400 sm:text-base"
                :disabled="saving || !hasChanges || !penScoresValid"
                @click="saveScores"
            >
                Save result
            </Button>
        </div>

        <div
            v-if="game.ground"
            class="mt-3 flex items-start gap-1 text-[11px] text-slate-500 sm:mt-4 sm:text-xs"
        >
            <MapPin class="size-3.5 shrink-0" />
            {{ game.ground }}
        </div>
    </article>
</template>
