<?php

use App\Models\Game;
use App\Services\PredictionScorer;

function finishedGame(array $attributes = []): Game
{
    return new Game(array_merge([
        'home_score' => 2,
        'away_score' => 1,
        'went_to_penalties' => false,
        'pen_home_score' => null,
        'pen_away_score' => null,
    ], $attributes));
}

it('awards four points for an exact score', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculate(2, 1, finishedGame()))->toBe(4);
});

it('awards two points for matching goal difference', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculate(3, 1, finishedGame(['home_score' => 2, 'away_score' => 0])))->toBe(2);
    expect($scorer->calculate(0, 0, finishedGame(['home_score' => 1, 'away_score' => 1])))->toBe(2);
});

it('awards one point for correct winner', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculate(3, 0, finishedGame()))->toBe(1);
    expect($scorer->calculate(0, 2, finishedGame(['home_score' => 0, 'away_score' => 1])))->toBe(1);
    expect($scorer->calculate(1, 0, finishedGame(['home_score' => 2, 'away_score' => 0])))->toBe(1);
});

it('awards one point for a correctly predicted full-time draw', function () {
    $scorer = new PredictionScorer;

    $game = finishedGame([
        'home_score' => 1,
        'away_score' => 1,
        'went_to_penalties' => true,
        'pen_home_score' => 4,
        'pen_away_score' => 3,
    ]);

    expect($scorer->calculate(1, 1, $game))->toBe(4);
});

it('awards one point when the predicted winner wins on penalties', function () {
    $scorer = new PredictionScorer;

    $game = finishedGame([
        'home_score' => 0,
        'away_score' => 0,
        'went_to_penalties' => true,
        'pen_home_score' => 3,
        'pen_away_score' => 0,
    ]);

    expect($scorer->calculate(1, 0, $game))->toBe(1);
    expect($scorer->calculate(2, 1, $game))->toBe(1);
});

it('awards zero points when nothing matches', function () {
    $scorer = new PredictionScorer;

    expect($scorer->calculate(2, 0, finishedGame(['home_score' => 0, 'away_score' => 2])))->toBe(0);

    $penGame = finishedGame([
        'home_score' => 0,
        'away_score' => 0,
        'went_to_penalties' => true,
        'pen_home_score' => 4,
        'pen_away_score' => 2,
    ]);

    expect($scorer->calculate(0, 1, $penGame))->toBe(0);
});
