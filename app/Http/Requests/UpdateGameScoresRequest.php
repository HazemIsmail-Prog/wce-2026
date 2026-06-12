<?php

namespace App\Http\Requests;

use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateGameScoresRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->id === config('worldcup.admin_user_id');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_finished')) {
            $this->merge([
                'is_finished' => filter_var(
                    $this->input('is_finished'),
                    FILTER_VALIDATE_BOOLEAN,
                ),
            ]);
        }

        if ($this->has('went_to_penalties')) {
            $this->merge([
                'went_to_penalties' => filter_var(
                    $this->input('went_to_penalties'),
                    FILTER_VALIDATE_BOOLEAN,
                ),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'home_score' => ['required', 'integer', 'min:0', 'max:20'],
            'away_score' => ['required', 'integer', 'min:0', 'max:20'],
            'is_finished' => ['required', 'boolean'],
            'went_to_penalties' => ['sometimes', 'boolean'],
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

            $homeScore = (int) $this->input('home_score');
            $awayScore = (int) $this->input('away_score');
            $wentToPenalties = (bool) $this->boolean('went_to_penalties');
            $penHome = $this->input('pen_home_score');
            $penAway = $this->input('pen_away_score');

            $hasPenHome = $penHome !== null && $penHome !== '';
            $hasPenAway = $penAway !== null && $penAway !== '';

            if ($hasPenHome xor $hasPenAway) {
                $validator->errors()->add('pen_home_score', 'Enter both penalty shootout scores.');
            }

            if ($wentToPenalties || $hasPenHome || $hasPenAway) {
                if (! $game->isKnockout()) {
                    $validator->errors()->add('went_to_penalties', 'Penalties are only for knockout matches.');
                }

                if ($homeScore !== $awayScore) {
                    $validator->errors()->add('went_to_penalties', 'Penalties require a drawn full-time score.');
                }

                if (! $hasPenHome || ! $hasPenAway) {
                    $validator->errors()->add('pen_home_score', 'Enter both penalty shootout scores.');
                } elseif ((int) $penHome === (int) $penAway) {
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

        $wentToPenalties = (bool) ($validated['went_to_penalties'] ?? false);
        $isDraw = (int) $validated['home_score'] === (int) $validated['away_score'];

        if (! $isDraw || ! $wentToPenalties) {
            $validated['went_to_penalties'] = false;
            $validated['pen_home_score'] = null;
            $validated['pen_away_score'] = null;
        }

        return $validated;
    }
}
