<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    
    //toon de beste spelers: vandaag, deze week en all-time
    public function index(): View
    {
        return view('leaderboard.index', [
            'daily' => Leaderboard::dailyWins(),
            'weekly' => Leaderboard::weeklyWins(),
            'total' => Leaderboard::totalWins(),
        ]);
    }
}
