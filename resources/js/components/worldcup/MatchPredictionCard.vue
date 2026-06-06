<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Clock, Lock, MapPin, Swords, Trophy, Users } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ScoreStepper from '@/components/worldcup/ScoreStepper.vue';
import TeamFlag from '@/components/worldcup/TeamFlag.vue';
import { Button } from '@/components/ui/button';
import type { GameItem } from '@/types/worldcup';

const props = defineProps<{
    game: GameItem;
    lockMinutes: number;
}>();

const homeScore = ref(props.game.prediction?.home_score ?? 0);
const awayScore = ref(props.game.prediction?.away_score ?? 0);
const penHomeScore = ref(props.game.prediction?.pen_home_score ?? 4);
const penAwayScore = ref(props.game.prediction?.pen_away_score ?? 3);
const includePenalties = ref(
    props.game.prediction?.pen_home_score !== null &&
        props.game.prediction?.pen_home_score !== undefined,
);
const saving = ref(false);

watch(
    () => props.game.prediction,
    (prediction) => {
        homeScore.value = prediction?.home_score ?? 0;
        awayScore.value = prediction?.away_score ?? 0;
        includePenalties.value =
            prediction?.pen_home_score !== null &&
            prediction?.pen_home_score !== undefined;
        penHomeScore.value = prediction?.pen_home_score ?? 4;
        penAwayScore.value = prediction?.pen_away_score ?? 3;
    },
);

watch([homeScore, awayScore], ([home, away]) => {
    if (home !== away) {
        includePenalties.value = false;
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

const lockLabel = computed(() =>
    new Date(props.game.lock_at).toLocaleString(undefined, {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }),
);

const isDrawPrediction = computed(
    () => homeScore.value === awayScore.value,
);

const showPenaltyPicker = computed(
    () =>
        props.game.is_knockout &&
        isDrawPrediction.value &&
        canEdit.value,
);

const statusLabel = computed(() => {
    if (props.game.is_finished) {
        return 'Full time';
    }

    if (props.game.is_locked) {
        return 'Locked';
    }

    return 'Open for picks';
});

const statusClass = computed(() => {
    if (props.game.is_finished) {
        return 'bg-amber-500/20 text-amber-300 ring-amber-400/30';
    }

    if (props.game.is_locked) {
        return 'bg-red-500/20 text-red-300 ring-red-400/30';
    }

    return 'bg-emerald-500/20 text-emerald-300 ring-emerald-400/30';
});

const canEdit = computed(
    () => !props.game.is_locked && !props.game.is_finished,
);

const penPredictionValid = computed(
    () =>
        !includePenalties.value ||
        penHomeScore.value !== penAwayScore.value,
);

const hasChanges = computed(() => {
    const current = props.game.prediction;

    const penPayload = includePenalties.value && isDrawPrediction.value
        ? { home: penHomeScore.value, away: penAwayScore.value }
        : { home: null, away: null };

    if (!current) {
        return (
            homeScore.value !== 0 ||
            awayScore.value !== 0 ||
            penPayload.home !== null
        );
    }

    const currentPen =
        current.pen_home_score !== null
            ? { home: current.pen_home_score, away: current.pen_away_score }
            : { home: null, away: null };

    return (
        current.home_score !== homeScore.value ||
        current.away_score !== awayScore.value ||
        currentPen.home !== penPayload.home ||
        currentPen.away !== penPayload.away
    );
});

function formatPredictionLine(
    home: number,
    away: number,
    penHome: number | null,
    penAway: number | null,
): string {
    let line = `${home} : ${away}`;

    if (penHome !== null && penAway !== null) {
        line += ` (pens ${penHome}–${penAway})`;
    }

    return line;
}

function savePrediction(): void {
    if (!canEdit.value || !penPredictionValid.value) {
        return;
    }

    saving.value = true;

    const payload: Record<string, number | null> = {
        home_score: homeScore.value,
        away_score: awayScore.value,
        pen_home_score: null,
        pen_away_score: null,
    };

    if (
        props.game.is_knockout &&
        isDrawPrediction.value &&
        includePenalties.value
    ) {
        payload.pen_home_score = penHomeScore.value;
        payload.pen_away_score = penAwayScore.value;
    }

    router.post(`/predictions/${props.game.id}`, payload, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
}
</script>

<template>
    <article
        class="group relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-slate-900/90 via-slate-900/70 to-emerald-950/40 p-3 shadow-xl shadow-black/30 backdrop-blur-sm transition hover:border-emerald-500/30 sm:p-5"
    >
        <div
            class="pointer-events-none absolute -right-8 -top-8 size-32 rounded-full bg-emerald-500/10 blur-2xl transition group-hover:bg-emerald-500/20"
        />

        <div class="mb-3 flex flex-col gap-2 sm:mb-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span
                    class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide ring-1 ring-inset"
                    :class="statusClass"
                >
                    {{ statusLabel }}
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
            <div
                class="flex items-center gap-1 text-[11px] text-slate-400 sm:text-xs"
                :title="`Locks ${lockMinutes} min before kickoff`"
            >
                <Lock v-if="game.is_locked" class="size-3.5" />
                <Clock v-else class="size-3.5" />
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

                <div
                    class="flex min-w-[6.75rem] flex-col items-center gap-2 sm:min-w-0"
                >
                    <template v-if="game.is_finished">
                        <div
                            class="flex items-center gap-1.5 text-2xl font-black tabular-nums text-amber-300 sm:gap-3 sm:text-4xl"
                        >
                            <span>{{ game.home_score }}</span>
                            <span class="text-base text-slate-500 sm:text-xl"
                                >:</span
                            >
                            <span>{{ game.away_score }}</span>
                        </div>
                        <span
                            class="hidden items-center gap-1 text-[10px] font-semibold uppercase tracking-wider text-amber-400/80 sm:flex sm:text-xs"
                        >
                            <Trophy class="size-3.5" />
                            FT
                        </span>
                        <p
                            v-if="game.went_to_penalties"
                            class="rounded-full bg-violet-500/15 px-2 py-0.5 text-[10px] font-semibold text-violet-300 ring-1 ring-violet-500/30 sm:px-3 sm:py-1 sm:text-xs"
                        >
                            {{ game.pen_home_score }}–{{ game.pen_away_score }}
                        </p>
                    </template>
                    <template v-else-if="canEdit">
                        <div class="flex items-center gap-1 sm:gap-3">
                            <ScoreStepper
                                v-model="homeScore"
                                compact
                                class="sm:hidden"
                            />
                            <ScoreStepper
                                v-model="homeScore"
                                class="hidden sm:flex"
                            />
                            <span
                                class="text-base font-bold text-slate-500 sm:text-xl"
                                >:</span
                            >
                            <ScoreStepper
                                v-model="awayScore"
                                compact
                                class="sm:hidden"
                            />
                            <ScoreStepper
                                v-model="awayScore"
                                class="hidden sm:flex"
                            />
                        </div>
                    </template>
                    <template v-else>
                        <div
                            class="flex items-center gap-1.5 text-2xl font-black tabular-nums text-white sm:gap-3 sm:text-3xl"
                        >
                            <span>{{ game.prediction?.home_score ?? '–' }}</span>
                            <span class="text-base text-slate-500 sm:text-lg"
                                >:</span
                            >
                            <span>{{ game.prediction?.away_score ?? '–' }}</span>
                        </div>
                        <p
                            v-if="
                                game.prediction?.pen_home_score !== null &&
                                game.prediction?.pen_home_score !== undefined
                            "
                            class="text-[10px] font-medium text-violet-300 sm:text-xs"
                        >
                            Pens {{ game.prediction.pen_home_score }}–{{
                                game.prediction.pen_away_score
                            }}
                        </p>
                    </template>
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
                v-if="canEdit && showPenaltyPicker"
                class="rounded-xl border border-violet-500/20 bg-violet-500/5 p-3 sm:p-4"
            >
                <div
                    class="mb-3 flex items-center justify-between gap-3"
                >
                    <div
                        class="flex min-w-0 items-center gap-2 text-sm font-semibold text-violet-200"
                    >
                        <Swords class="size-4 shrink-0" />
                        <span class="truncate">Penalty shootout</span>
                    </div>
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="includePenalties"
                        aria-label="Include penalty shootout prediction"
                        class="flex shrink-0 items-center gap-2 rounded-full transition sm:inline-flex sm:py-1 sm:pl-1 sm:pr-3 sm:text-sm sm:font-semibold sm:ring-1 sm:ring-inset"
                        :class="
                            includePenalties
                                ? 'sm:bg-violet-500/20 sm:text-violet-200 sm:ring-violet-400/40 sm:shadow-sm sm:shadow-violet-500/10'
                                : 'sm:bg-black/30 sm:text-slate-400 sm:ring-white/10 sm:hover:bg-white/5 sm:hover:text-slate-300'
                        "
                        @click="includePenalties = !includePenalties"
                    >
                        <span
                            class="text-[11px] font-medium sm:hidden"
                            :class="
                                includePenalties
                                    ? 'text-violet-300'
                                    : 'text-slate-400'
                            "
                        >
                            Include pens
                        </span>
                        <span
                            class="relative flex h-6 w-11 items-center rounded-full p-0.5 transition-colors sm:h-5 sm:w-9"
                            :class="
                                includePenalties
                                    ? 'bg-violet-500'
                                    : 'bg-white/10 ring-1 ring-inset ring-white/10'
                            "
                        >
                            <span
                                class="size-5 rounded-full bg-white shadow-sm transition-transform sm:size-4"
                                :class="
                                    includePenalties
                                        ? 'translate-x-5 sm:translate-x-4'
                                        : 'translate-x-0'
                                "
                            />
                        </span>
                        <span class="hidden sm:inline">Include pens</span>
                    </button>
                </div>
                <div
                    v-if="includePenalties"
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
                        <span class="text-base font-bold text-violet-400 sm:text-lg"
                            >–</span
                        >
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
                    <p v-if="!penPredictionValid" class="text-xs text-red-400">
                        Shootout must have a winner
                    </p>
                    <p v-else class="text-center text-xs text-slate-400">
                        Optional bonus: +3 exact pens, +1 correct winner
                    </p>
                </div>
                <p v-else class="text-xs text-slate-400">
                    Predicting a draw? Optionally pick the penalty shootout
                    score for bonus points.
                </p>
            </div>

            <div v-if="canEdit" class="space-y-2">
                <Button
                    class="w-full bg-gradient-to-r from-emerald-500 to-lime-500 text-sm font-bold text-slate-950 hover:from-emerald-400 hover:to-lime-400 sm:text-base"
                    :disabled="saving || !hasChanges || !penPredictionValid"
                    @click="savePrediction"
                >
                    {{
                        game.prediction ? 'Update prediction' : 'Save prediction'
                    }}
                </Button>
                <p class="text-center text-[11px] text-slate-400 sm:text-xs">
                    Editable until {{ lockLabel }}
                </p>
            </div>

            <p
                v-else-if="!game.is_finished && game.prediction"
                class="text-center text-[11px] text-slate-400 sm:text-xs"
            >
                Your locked prediction
            </p>
        </div>

        <div
            v-if="game.ground"
            class="mt-3 flex items-start gap-1 text-[11px] text-slate-500 sm:mt-4 sm:text-xs"
        >
            <MapPin class="size-3.5 shrink-0" />
            {{ game.ground }}
        </div>

        <div
            v-if="game.other_predictions.length"
            class="mt-5 rounded-xl border border-white/10 bg-black/30 p-4"
        >
            <div
                class="mb-3 flex items-center gap-2 text-sm font-semibold text-slate-200"
            >
                <Users class="size-4 text-emerald-400" />
                Community picks
            </div>
            <ul class="space-y-2">
                <li
                    v-for="prediction in game.other_predictions"
                    :key="prediction.id"
                    class="flex flex-col gap-1 rounded-lg px-3 py-2 text-sm sm:flex-row sm:items-center sm:justify-between"
                    :class="
                        prediction.is_current_user
                            ? 'bg-emerald-500/15 text-emerald-100'
                            : 'bg-white/5 text-slate-300'
                    "
                >
                    <span class="font-medium">
                        {{ prediction.user_name }}
                        <span
                            v-if="prediction.is_current_user"
                            class="ms-1 text-xs text-emerald-400"
                        >
                            (you)
                        </span>
                    </span>
                    <span
                        class="font-mono text-xs font-bold tabular-nums sm:text-sm"
                    >
                        {{
                            formatPredictionLine(
                                prediction.home_score,
                                prediction.away_score,
                                prediction.pen_home_score,
                                prediction.pen_away_score,
                            )
                        }}
                    </span>
                </li>
            </ul>
        </div>
    </article>
</template>
