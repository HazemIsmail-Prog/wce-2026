<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGameScoresRequest;
use App\Models\Game;
use App\Services\WorldCupSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScoresController extends Controller
{
    public function index(Request $request, WorldCupSyncService $syncService): Response
    {
        if (Game::query()->doesntExist()) {
            $syncService->sync();
        }

        $games = Game::query()
            ->orderBy('kickoff_at')
            ->get()
            ->map(fn (Game $game) => $this->formatGame($game));

        return Inertia::render('Scores/Index', [
            'games' => $games,
        ]);
    }

    public function update(
        UpdateGameScoresRequest $request,
        Game $game,
        WorldCupSyncService $syncService,
    ): RedirectResponse {
        $validated = $request->validated();

        $game->update([
            'home_score' => $validated['home_score'],
            'away_score' => $validated['away_score'],
            'is_finished' => $validated['is_finished'],
            'went_to_penalties' => $validated['went_to_penalties'],
            'pen_home_score' => $validated['pen_home_score'],
            'pen_away_score' => $validated['pen_away_score'],
        ]);

        if ($game->is_finished) {
            $syncService->scoreGamePredictions($game->fresh());
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Match result saved!',
        ]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatGame(Game $game): array
    {
        return [
            'id' => $game->id,
            'round' => $game->round,
            'group_name' => $game->group_name,
            'ground' => $game->ground,
            'team1' => $game->team1,
            'team2' => $game->team2,
            'kickoff_at' => $game->kickoff_at->toIso8601String(),
            'is_knockout' => $game->isKnockout(),
            'is_finished' => (bool) $game->is_finished,
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
            'went_to_penalties' => (bool) $game->went_to_penalties,
            'pen_home_score' => $game->pen_home_score,
            'pen_away_score' => $game->pen_away_score,
        ];
    }
}
