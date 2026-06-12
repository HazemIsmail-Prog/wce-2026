<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Prediction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WorldCupSyncService
{
    public function __construct(private PredictionScorer $scorer) {}

    /**
     * @return array{synced: int, scored: int}
     */
    public function sync(): array
    {
        $response = Http::timeout(30)->get(config('worldcup.data_url'));

        if (! $response->successful()) {
            throw new \RuntimeException('Unable to fetch World Cup data.');
        }

        /** @var array{name?: string, matches?: list<array<string, mixed>>} $payload */
        $payload = $response->json();
        $matches = $payload['matches'] ?? [];

        $synced = 0;

        // reset all prediction points and pen points
        Prediction::query()->update([
            'points' => null,
            'pen_points' => null,
        ]);

        foreach ($matches as $match) {
            $game = $this->upsertGame($match);

            if ($game->wasRecentlyCreated || $game->wasChanged()) {
                $synced++;
            }

            if ($game->is_finished) {
                $this->scorePredictionsForGame($game);
            }
        }

        return [
            'synced' => $synced,
            'scored' => Prediction::query()->whereNotNull('points')->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $match
     */
    private function upsertGame(array $match): Game
    {
        $team1 = (string) ($match['team1'] ?? '');
        $team2 = (string) ($match['team2'] ?? '');
        $date = (string) ($match['date'] ?? '');
        $time = (string) ($match['time'] ?? '12:00');

        $externalKey = sha1(Str::lower($team1.'|'.$team2.'|'.$date));

        return Game::query()->updateOrCreate(
            ['external_key' => $externalKey],
            [
                'round' => (string) ($match['round'] ?? 'Unknown'),
                'group_name' => isset($match['group']) ? (string) $match['group'] : null,
                'ground' => isset($match['ground']) ? (string) $match['ground'] : null,
                'team1' => $team1,
                'team2' => $team2,
                'kickoff_at' => $this->parseKickoff($date, $time),
            ],
        );
    }

    private function parseKickoff(string $date, string $time): Carbon
    {
        if (preg_match('/^(\d{1,2}:\d{2})\s*UTC([+-]\d+(?:\.\d+)?)?$/i', trim($time), $matches)) {
            $clock = $matches[1];
            $offsetHours = isset($matches[2]) ? (float) $matches[2] : 0.0;

            $local = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$clock}", 'UTC');
            $local->subHours((int) $offsetHours);
            $local->subMinutes((int) round(abs($offsetHours - (int) $offsetHours) * 60));

            return $local;
        }

        if (preg_match('/^(\d{1,2}:\d{2})$/', trim($time), $matches)) {
            return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$matches[1]}", 'UTC');
        }

        return Carbon::parse("{$date} 12:00", 'UTC');
    }

    public function scoreGamePredictions(Game $game): void
    {
        $this->scorePredictionsForGame($game);
    }

    private function scorePredictionsForGame(Game $game): void
    {
        if ($game->home_score === null || $game->away_score === null) {
            return;
        }

        $game->predictions()
            ->each(function (Prediction $prediction) use ($game): void {
                $points = $this->scorer->calculate(
                    $prediction->home_score,
                    $prediction->away_score,
                    $game,
                );

                $penPoints = $this->scorer->calculatePenaltyPoints(
                    $prediction->home_score,
                    $prediction->away_score,
                    $prediction->pen_home_score,
                    $prediction->pen_away_score,
                    $game,
                );

                $updates = [];

                if ($prediction->points !== $points) {
                    $updates['points'] = $points;
                }

                if ($prediction->pen_points !== $penPoints) {
                    $updates['pen_points'] = $penPoints;
                }

                if ($updates !== []) {
                    $prediction->update($updates);
                }
            });
    }
}
