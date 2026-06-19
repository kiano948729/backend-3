<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'avatar', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function gamesAsPlayerOne()
    {
        return $this->hasMany(Game::class, 'player_one_id');
    }

    public function gamesAsPlayerTwo()
    {
        return $this->hasMany(Game::class, 'player_two_id');
    }

    public function games()
    {
        return Game::where('player_one_id', $this->id)
            ->orWhere('player_two_id', $this->id);
    }

    public function gamesWon()
    {
        return $this->hasMany(Game::class, 'winner_user_id');
    }

    public function moves()
    {
        return $this->hasMany(GameMove::class);
    }


    public function gameResults()
    {
        return $this->hasMany(GameResult::class);
    }


    public function friendsInitiated()
    {
        return $this->hasMany(Friend::class, 'user_id');
    }

    public function friendsReceived()
    {
        return $this->hasMany(Friend::class, 'friend_id');
    }

    public function friends()
    {
        $initiated = $this->friendsInitiated()->where('status', 'accepted')->pluck('friend_id');
        $received = $this->friendsReceived()->where('status', 'accepted')->pluck('user_id');

        return User::whereIn('id', $initiated->merge($received)->unique());
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function profileComments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
