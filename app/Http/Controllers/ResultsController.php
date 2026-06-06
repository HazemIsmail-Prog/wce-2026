<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use App\Services\WorldCupSyncService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResultsController extends Controller
{
    public function index(Request $request, WorldCupSyncService $syncService): Response
    {
        if (Game::query()->doesntExist()) {
            $syncService->sync();
        } else {
            try {
                $syncService->sync();
            } catch (\Throwable) {
                // Keep showing cached data if the feed is temporarily unavailable.
            }
        }

        $user = $request->user();

        $leaderboard = User::query()
            ->select('users.id', 'users.name')
            ->selectRaw('COALESCE(SUM(predictions.points), 0) + COALESCE(SUM(predictions.pen_points), 0) as total_points')
            ->selectRaw('COUNT(predictions.id) as predictions_count')
            ->leftJoin('predictions', 'predictions.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->having('predictions_count', '>', 0)
            ->orderByDesc('total_points')
            ->orderBy('users.name')
            ->get()
            ->values()
            ->map(fn (User $entry, int $index) => [
                'rank' => $index + 1,
                'id' => $entry->id,
                'name' => $entry->name,
                'total_points' => (int) $entry->total_points,
                'predictions_count' => (int) $entry->predictions_count,
                'is_current_user' => $entry->id === $user->id,
            ]);

        $finishedGames = Game::query()
            ->where('is_finished', true)
            ->orderByDesc('kickoff_at')
            ->get()
            ->map(function (Game $game) use ($user) {
                $userPrediction = Prediction::query()
                    ->where('game_id', $game->id)
                    ->where('user_id', $user->id)
                    ->first();

                return [
                    'id' => $game->id,
                    'round' => $game->round,
                    'group_name' => $game->group_name,
                    'team1' => $game->team1,
                    'team2' => $game->team2,
                    'kickoff_at' => $game->kickoff_at->toIso8601String(),
                    'home_score' => $game->home_score,
                    'away_score' => $game->away_score,
                    'went_to_penalties' => $game->went_to_penalties,
                    'pen_home_score' => $game->pen_home_score,
                    'pen_away_score' => $game->pen_away_score,
                    'user_prediction' => $userPrediction ? [
                        'home_score' => $userPrediction->home_score,
                        'away_score' => $userPrediction->away_score,
                        'pen_home_score' => $userPrediction->pen_home_score,
                        'pen_away_score' => $userPrediction->pen_away_score,
                        'points' => $userPrediction->points,
                        'pen_points' => $userPrediction->pen_points,
                    ] : null,
                ];
            });

        $userStats = [
            'total_points' => (int) Prediction::query()
                ->where('user_id', $user->id)
                ->get()
                ->sum(fn (Prediction $prediction) => ($prediction->points ?? 0) + ($prediction->pen_points ?? 0)),
            'exact_hits' => Prediction::query()
                ->where('user_id', $user->id)
                ->where('points', 4)
                ->count(),
            'pen_hits' => Prediction::query()
                ->where('user_id', $user->id)
                ->where('pen_points', '>', 0)
                ->count(),
            'predictions_made' => Prediction::query()
                ->where('user_id', $user->id)
                ->count(),
            'games_finished' => Game::query()->where('is_finished', true)->count(),
        ];

        $scoringRules = [
            ['label' => 'Exact 90-min score', 'points' => 4],
            ['label' => 'Correct 90-min goal difference', 'points' => 2],
            ['label' => 'Correct winner or FT draw', 'points' => 1],
            ['label' => 'Exact penalty shootout (knockout)', 'points' => config('worldcup.penalty_points.exact')],
            ['label' => 'Correct pen shootout winner (knockout)', 'points' => config('worldcup.penalty_points.winner')],
            ['label' => 'Miss', 'points' => 0],
        ];

        return Inertia::render('Results/Index', [
            'leaderboard' => $leaderboard,
            'finishedGames' => $finishedGames,
            'userStats' => $userStats,
            'scoringRules' => $scoringRules,
        ]);
    }
}
