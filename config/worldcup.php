<?php

return [

    /*
    |--------------------------------------------------------------------------
    | World Cup Data Source
    |--------------------------------------------------------------------------
    |
    | Free public-domain JSON feed from openfootball/worldcup.json.
    | No API key required. Results are updated after each match.
    |
    */

    'data_url' => env(
        'WORLDCUP_DATA_URL',
        'https://raw.githubusercontent.com/openfootball/worldcup.json/master/2026/worldcup.json',
    ),

    /*
    |--------------------------------------------------------------------------
    | Prediction Lock Window
    |--------------------------------------------------------------------------
    |
    | Minutes before kickoff when predictions are locked.
    |
    */

    'lock_minutes_before' => (int) env('WORLDCUP_LOCK_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Penalty Shootout Bonus Points
    |--------------------------------------------------------------------------
    |
    | Awarded only on knockout matches that go to penalties, when the player
    | predicted a full-time draw and submitted a penalty shootout pick.
    |
    */

    'penalty_points' => [
        'exact' => 3,
        'winner' => 1,
    ],

];
