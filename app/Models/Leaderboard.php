<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Leaderboard
{
    public static function totalWins(int $limit = 10)
    {
        return self::winsQuery()->limit($limit)->get();
    }

    public static function dailyWins(int $limit = 10)
    {
        return self::winsQuery()
            ->whereDate('game_results.created_at', Carbon::today())
            ->limit($limit)
            ->get();
    }

    public static function weeklyWins(int $limit = 10)
    {
        return self::winsQuery()
            ->whereBetween('game_results.created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->limit($limit)
            ->get();
    }

    
     //telt win resultaten per user, gesorteerd van hoog naar laag.
     
    protected static function winsQuery()
    {
        return DB::table('game_results')
            ->join('users', 'users.id', '=', 'game_results.user_id')
            ->select('users.id as user_id', 'users.name', DB::raw('COUNT(*) as wins'))
            ->where('game_results.result', 'win')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('wins');
    }
}
