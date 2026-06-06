export type GamePrediction = {
    home_score: number;
    away_score: number;
    pen_home_score: number | null;
    pen_away_score: number | null;
};

export type OtherPrediction = {
    id: number;
    user_name: string;
    is_current_user: boolean;
    home_score: number;
    away_score: number;
    pen_home_score: number | null;
    pen_away_score: number | null;
};

export type GameItem = {
    id: number;
    round: string;
    group_name: string | null;
    ground: string | null;
    team1: string;
    team2: string;
    kickoff_at: string;
    lock_at: string;
    is_locked: boolean;
    is_finished: boolean;
    is_knockout: boolean;
    home_score: number | null;
    away_score: number | null;
    went_to_penalties: boolean;
    pen_home_score: number | null;
    pen_away_score: number | null;
    prediction: GamePrediction | null;
    other_predictions: OtherPrediction[];
};

export type LeaderboardEntry = {
    rank: number;
    id: number;
    name: string;
    total_points: number;
    predictions_count: number;
    is_current_user: boolean;
};

export type FinishedGameResult = {
    id: number;
    round: string;
    group_name: string | null;
    team1: string;
    team2: string;
    kickoff_at: string;
    home_score: number | null;
    away_score: number | null;
    went_to_penalties: boolean;
    pen_home_score: number | null;
    pen_away_score: number | null;
    user_prediction: (GamePrediction & {
        points: number | null;
        pen_points: number | null;
    }) | null;
};

export type UserStats = {
    total_points: number;
    exact_hits: number;
    pen_hits: number;
    predictions_made: number;
    games_finished: number;
};

export type ScoringRule = {
    label: string;
    points: number;
};
