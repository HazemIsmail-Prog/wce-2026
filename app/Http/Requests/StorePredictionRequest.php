<?php

namespace App\Http\Requests;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0', 'max:20'],
            'away_score' => ['required', 'integer', 'min:0', 'max:20'],
            'pen_home_score' => ['nullable', 'integer', 'min:0', 'max:15'],
            'pen_away_score' => ['nullable', 'integer', 'min:0', 'max:15'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Game|null $game */
            $game = $this->route('game');

            if (! $game instanceof Game) {
                return;
            }

            if ($game->isLocked()) {
                $validator->errors()->add('home_score', 'Predictions lock 15 minutes before kickoff.');
            }

            $homeScore = (int) $this->input('home_score');
            $awayScore = (int) $this->input('away_score');
            $penHome = $this->input('pen_home_score');
            $penAway = $this->input('pen_away_score');

            $hasPenHome = $penHome !== null && $penHome !== '';
            $hasPenAway = $penAway !== null && $penAway !== '';

            if ($hasPenHome xor $hasPenAway) {
                $validator->errors()->add('pen_home_score', 'Enter both penalty shootout scores.');
            }

            if ($hasPenHome && $hasPenAway) {
                if (! $game->isKnockout()) {
                    $validator->errors()->add('pen_home_score', 'Penalty picks are only for knockout matches.');
                }

                if ($homeScore !== $awayScore) {
                    $validator->errors()->add('pen_home_score', 'Penalty picks require a drawn full-time score.');
                }

                if ((int) $penHome === (int) $penAway) {
                    $validator->errors()->add('pen_home_score', 'Penalty shootout must have a winner.');
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array<string, mixed> $validated */
        $validated = parent::validated($key, $default);

        if ((int) $validated['home_score'] !== (int) $validated['away_score']) {
            $validated['pen_home_score'] = null;
            $validated['pen_away_score'] = null;
        } elseif (
            ! array_key_exists('pen_home_score', $validated)
            || $validated['pen_home_score'] === null
            || $validated['pen_home_score'] === ''
        ) {
            $validated['pen_home_score'] = null;
            $validated['pen_away_score'] = null;
        }

        return $validated;
    }
}
