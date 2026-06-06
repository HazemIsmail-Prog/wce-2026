<?php

use App\Models\Game;
use App\Services\PredictionScorer;
use Tests\TestCase;

uses(TestCase::class);

function penGame(array $attributes = []): Game
{
    return new Game(array_merge([
        'home_score' => 0,
        'away_score' => 0,
        'went_to_penalties' => true,
        'pen_home_score' => 4,
        'pen_away_score' => 2,
    ], $attributes));
}

it('awards three bonus points for an exact penalty shootout score', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculatePenaltyPoints(0, 0, 4, 2, penGame()))->toBe(3);
});

it('awards one bonus point for the correct penalty winner', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculatePenaltyPoints(1, 1, 5, 4, penGame()))->toBe(1);
});

it('returns null when no penalty pick was submitted', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculatePenaltyPoints(0, 0, null, null, penGame()))->toBeNull();
});

it('returns null when the full-time pick was not a draw', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculatePenaltyPoints(1, 0, 4, 3, penGame()))->toBeNull();
});
