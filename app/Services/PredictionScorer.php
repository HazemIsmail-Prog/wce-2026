<?php

namespace App\Services;

use App\Models\Game;

class PredictionScorer
{
    /**
     * Score a prediction against a finished game.
     *
     * Exact score and goal difference use 90-minute full-time results only.
     * The winner tier uses the FT winner, a correctly predicted FT draw,
     * or the penalty shootout winner when regulation ends level.
     */
    public function calculate(int $predictedHome, int $predictedAway, Game $game): int
    {
        $actualHome = (int) $game->home_score;
        $actualAway = (int) $game->away_score;

        if ($predictedHome === $actualHome && $predictedAway === $actualAway) {
            return 4;
        }

        $predictedDiff = $predictedHome - $predictedAway;
        $actualDiff = $actualHome - $actualAway;

        if ($predictedDiff === $actualDiff) {
            return 2;
        }

        if ($this->winnerMatches($predictedHome, $predictedAway, $game)) {
            return 1;
        }

        return 0;
    }

    public function calculatePenaltyPoints(
        int $predictedHome,
        int $predictedAway,
        ?int $predictedPenHome,
        ?int $predictedPenAway,
        Game $game,
    ): ?int {
        if (! $game->went_to_penalties) {
            return null;
        }

        if ($predictedHome !== $predictedAway) {
            return null;
        }

        if ($predictedPenHome === null || $predictedPenAway === null) {
            return null;
        }

        if ($game->pen_home_score === null || $game->pen_away_score === null) {
            return null;
        }

        if (
            $predictedPenHome === $game->pen_home_score
            && $predictedPenAway === $game->pen_away_score
        ) {
            return config('worldcup.penalty_points.exact');
        }

        $predictedWinner = $this->side($predictedPenHome, $predictedPenAway);
        $actualWinner = $this->penaltyWinner($game);

        if ($predictedWinner !== null && $predictedWinner === $actualWinner) {
            return config('worldcup.penalty_points.winner');
        }

        return 0;
    }

    private function winnerMatches(int $predictedHome, int $predictedAway, Game $game): bool
    {
        $predicted = $this->side($predictedHome, $predictedAway);
        $actualFt = $this->side((int) $game->home_score, (int) $game->away_score);

        if ($predicted === $actualFt) {
            return true;
        }

        if ($actualFt === 'draw' && $game->went_to_penalties) {
            return $predicted === $this->penaltyWinner($game);
        }

        return false;
    }

    private function penaltyWinner(Game $game): ?string
    {
        if ($game->pen_home_score === null || $game->pen_away_score === null) {
            return null;
        }

        return $this->side($game->pen_home_score, $game->pen_away_score) !== 'draw'
            ? $this->side($game->pen_home_score, $game->pen_away_score)
            : null;
    }

    private function side(int $home, int $away): string
    {
        if ($home > $away) {
            return 'home';
        }

        if ($home < $away) {
            return 'away';
        }

        return 'draw';
    }
}
