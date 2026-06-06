<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to view predictions', function () {
    $this->get(route('predictions.index'))->assertRedirect(route('login'));
});

it('allows authenticated users to view predictions', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('predictions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Predictions/Index'));
});

it('blocks predictions after the lock window', function () {
    $user = User::factory()->create();

    $game = Game::query()->create([
        'external_key' => 'test-game',
        'round' => 'Group A',
        'group_name' => 'Group A',
        'ground' => 'Test Stadium',
        'team1' => 'Team A',
        'team2' => 'Team B',
        'kickoff_at' => now()->addMinutes(10),
        'is_finished' => false,
    ]);

    $this->actingAs($user)
        ->post(route('predictions.store', $game), [
            'home_score' => 2,
            'away_score' => 1,
        ])
        ->assertSessionHasErrors('home_score');
});

it('stores a prediction before the lock window', function () {
    $user = User::factory()->create();

    $game = Game::query()->create([
        'external_key' => 'open-game',
        'round' => 'Group A',
        'group_name' => 'Group A',
        'ground' => 'Test Stadium',
        'team1' => 'Team A',
        'team2' => 'Team B',
        'kickoff_at' => now()->addHour(),
        'is_finished' => false,
    ]);

    $this->actingAs($user)
        ->post(route('predictions.store', $game), [
            'home_score' => 2,
            'away_score' => 1,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('predictions', [
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);
});

it('stores penalty picks for knockout draw predictions', function () {
    $user = User::factory()->create();

    $game = Game::query()->create([
        'external_key' => 'knockout-game',
        'round' => 'Round of 16',
        'group_name' => null,
        'ground' => 'Test Stadium',
        'team1' => 'Team A',
        'team2' => 'Team B',
        'kickoff_at' => now()->addHour(),
        'is_finished' => false,
    ]);

    $this->actingAs($user)
        ->post(route('predictions.store', $game), [
            'home_score' => 1,
            'away_score' => 1,
            'pen_home_score' => 4,
            'pen_away_score' => 3,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('predictions', [
        'user_id' => $user->id,
        'game_id' => $game->id,
        'home_score' => 1,
        'away_score' => 1,
        'pen_home_score' => 4,
        'pen_away_score' => 3,
    ]);
});
