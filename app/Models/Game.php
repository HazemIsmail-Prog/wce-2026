<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Game extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'external_key',
        'round',
        'group_name',
        'ground',
        'team1',
        'team2',
        'kickoff_at',
        'home_score',
        'away_score',
        'is_finished',
        'went_to_penalties',
        'pen_home_score',
        'pen_away_score',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kickoff_at' => 'datetime',
            'is_finished' => 'boolean',
            'went_to_penalties' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Prediction, $this>
     */
    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    public function lockAt(): Carbon
    {
        return Carbon::parse($this->kickoff_at)->subMinutes(config('worldcup.lock_minutes_before'));
    }

    public function isLocked(): bool
    {
        return now()->gte($this->lockAt());
    }

    public function predictionsAreVisible(): bool
    {
        return $this->isLocked();
    }

    public function isKnockout(): bool
    {
        if ($this->group_name !== null) {
            return false;
        }

        return ! str_starts_with($this->round, 'Matchday');
    }
}
