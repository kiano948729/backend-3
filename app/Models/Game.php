<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['player_one_id', 'player_two_id', 'status', 'current_turn_user_id', 'winner_user_id'])]
class Game extends Model
{
    public function playerOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_one_id');
    }

    public function playerTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_two_id');
    }

    public function currentTurnUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_turn_user_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function moves(): HasMany
    {
        return $this->hasMany(GameMove::class)->orderBy('created_at');
    }

    public function results(): HasMany
    {
        return $this->hasMany(GameResult::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
