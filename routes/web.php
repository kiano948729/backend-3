<?php

use App\Http\Controllers\FriendController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MoveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //games
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
    Route::post('/games/{game}/join', [GameController::class, 'join'])->name('games.join');
    Route::post('/games/challenge/{friend}', [GameController::class, 'challenge'])->name('games.challenge');
    Route::post('/games/matchmake', [GameController::class, 'matchmake'])->name('games.matchmake');
    
    //moves
    Route::post('/games/{game}/moves', [MoveController::class, 'store'])->name('moves.store');

    //friends
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends', [FriendController::class, 'store'])->name('friends.store');
    Route::patch('/friends/{friend}', [FriendController::class, 'update'])->name('friends.update');
    Route::delete('/friends/{friend}', [FriendController::class, 'destroy'])->name('friends.destroy');
});

require __DIR__.'/auth.php';
