<?php

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires authentication to view scores', function () {
    $this->get(route('scores.index'))->assertRedirect(route('login'));
});

it('forbids non-admin users from viewing scores', function () {
    User::factory()->create(['id' => 1]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('scores.index'))
        ->assertForbidden();
});

it('allows the admin user to view scores', function () {
    $admin = User::factory()->create(['id' => 1]);

    $game = Game::query()->create([
        'external_key' => 'finished-view-game',
        'round' => 'Group A',
        'group_name' => 'Group A',
        'ground' => 'Test Stadium',
        'team1' => 'Team A',
        'team2' => 'Team B',
        'kickoff_at' => now()->subHour(),
        'home_score' => 2,
        'away_score' => 1,
        'is_finished' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('scores.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Scores/Index')
            ->has('games', 1)
            ->where('games.0.id', $game->id)
            ->where('games.0.is_finished', true));

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('canManageScores', true)
            ->where('scoresAdminUserId', 1));
});

it('forbids non-admin users from updating scores', function () {
    User::factory()->create(['id' => 1]);
    $user = User::factory()->create();

    $game = Game::query()->create([
        'external_key' => 'blocked-game',
        'round' => 'Group A',
        'group_name' => 'Group A',
        'ground' => 'Test Stadium',
        'team1' => 'Team A',
        'team2' => 'Team B',
        'kickoff_at' => now()->subHour(),
        'is_finished' => false,
    ]);

    $this->actingAs($user)
        ->post(route('scores.update', $game), [
            'home_score' => 2,
            'away_score' => 1,
            'is_finished' => true,
        ])
        ->assertForbidden();
});

it('stores match results and scores predictions for the admin user', function () {
    $admin = User::factory()->create(['id' => 1]);
    $player = User::factory()->create();

    $game = Game::query()->create([
        'external_key' => 'admin-game',
        'round' => 'Group A',
        'group_name' => 'Group A',
        'ground' => 'Test Stadium',
        'team1' => 'Team A',
        'team2' => 'Team B',
        'kickoff_at' => now()->subHour(),
        'is_finished' => false,
    ]);

    Prediction::query()->create([
        'user_id' => $player->id,
        'game_id' => $game->id,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    $this->actingAs($admin)
        ->post(route('scores.update', $game), [
            'home_score' => 2,
            'away_score' => 1,
            'is_finished' => true,
            'went_to_penalties' => false,
        ])
        ->assertRedirect();

    $game->refresh();

    expect($game->home_score)->toBe(2)
        ->and($game->away_score)->toBe(1)
        ->and($game->is_finished)->toBeTrue();

    $this->assertDatabaseHas('predictions', [
        'user_id' => $player->id,
        'game_id' => $game->id,
        'points' => 4,
    ]);
});

it('stores knockout penalty results for the admin user', function () {
    $admin = User::factory()->create(['id' => 1]);

    $game = Game::query()->create([
        'external_key' => 'knockout-admin-game',
        'round' => 'Round of 16',
        'group_name' => null,
        'ground' => 'Test Stadium',
        'team1' => 'Team A',
        'team2' => 'Team B',
        'kickoff_at' => now()->subHour(),
        'is_finished' => false,
    ]);

    $this->actingAs($admin)
        ->post(route('scores.update', $game), [
            'home_score' => 1,
            'away_score' => 1,
            'is_finished' => true,
            'went_to_penalties' => true,
            'pen_home_score' => 4,
            'pen_away_score' => 3,
        ])
        ->assertRedirect();

    $game->refresh();

    expect($game->went_to_penalties)->toBeTrue()
        ->and($game->pen_home_score)->toBe(4)
        ->and($game->pen_away_score)->toBe(3);
});
