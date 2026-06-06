<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePredictionRequest;
use App\Models\Game;
use App\Models\Prediction;
use App\Services\WorldCupSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PredictionController extends Controller
{
    public function index(Request $request, WorldCupSyncService $syncService): Response
    {
        if (Game::query()->doesntExist()) {
            $syncService->sync();
        }

        $user = $request->user();

        $games = Game::query()
            ->with([
                'predictions' => function ($query) use ($user): void {
                    $query->where('user_id', $user->id);
                },
            ])
            ->orderBy('kickoff_at')
            ->get()
            ->map(fn (Game $game) => $this->formatGame($game, $user->id));

        return Inertia::render('Predictions/Index', [
            'games' => $games,
            'lockMinutes' => config('worldcup.lock_minutes_before'),
        ]);
    }

    public function store(StorePredictionRequest $request, Game $game): RedirectResponse
    {
        $validated = $request->validated();

        Prediction::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'game_id' => $game->id,
            ],
            [
                'home_score' => $validated['home_score'],
                'away_score' => $validated['away_score'],
                'pen_home_score' => $validated['pen_home_score'] ?? null,
                'pen_away_score' => $validated['pen_away_score'] ?? null,
                'points' => null,
                'pen_points' => null,
            ],
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Prediction saved!',
        ]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatGame(Game $game, int $userId): array
    {
        $userPrediction = $game->predictions->first();
        $isLocked = $game->isLocked();

        $otherPredictions = [];

        if ($isLocked) {
            $otherPredictions = Prediction::query()
                ->with('user:id,name')
                ->where('game_id', $game->id)
                ->orderBy('user_id')
                ->get()
                ->map(fn (Prediction $prediction) => [
                    'id' => $prediction->id,
                    'user_name' => $prediction->user->name,
                    'is_current_user' => $prediction->user_id === $userId,
                    'home_score' => $prediction->home_score,
                    'away_score' => $prediction->away_score,
                    'pen_home_score' => $prediction->pen_home_score,
                    'pen_away_score' => $prediction->pen_away_score,
                ])
                ->values()
                ->all();
        }

        return [
            'id' => $game->id,
            'round' => $game->round,
            'group_name' => $game->group_name,
            'ground' => $game->ground,
            'team1' => $game->team1,
            'team2' => $game->team2,
            'kickoff_at' => $game->kickoff_at->toIso8601String(),
            'lock_at' => $game->lockAt()->toIso8601String(),
            'is_locked' => $isLocked,
            'is_finished' => $game->is_finished,
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
            'went_to_penalties' => $game->went_to_penalties,
            'pen_home_score' => $game->pen_home_score,
            'pen_away_score' => $game->pen_away_score,
            'is_knockout' => $game->isKnockout(),
            'prediction' => $userPrediction ? [
                'home_score' => $userPrediction->home_score,
                'away_score' => $userPrediction->away_score,
                'pen_home_score' => $userPrediction->pen_home_score,
                'pen_away_score' => $userPrediction->pen_away_score,
            ] : null,
            'other_predictions' => $otherPredictions,
        ];
    }
}
