<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\TicTacToeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(protected TicTacToeService $ticTacToe)
    {
    }

    public function index(): View
    {
        $userId = Auth::id();

        $myGames = Game::where('player_one_id', $userId)
            ->orWhere('player_two_id', $userId)
            ->latest()
            ->get();

        $openGames = Game::where('status', 'waiting')
            ->where('player_one_id', '!=', $userId)
            ->latest()
            ->get();

        return view('games.index', [
            'myGames' => $myGames,
            'openGames' => $openGames,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $game = Game::create([
            'player_one_id' => Auth::id(),
            'status' => 'waiting',
        ]);

        return redirect()
            ->route('games.show', $game)
            ->with('status', 'Game aangemaakt. Wacht op een tegenstander, of vraag een vriend.');
    }

    public function join(Game $game): RedirectResponse
    {
        abort_if($game->status !== 'waiting', 403, 'Deze game is niet meer beschikbaar.');
        abort_if($game->player_one_id === Auth::id(), 403, 'Je kunt niet tegen jezelf spelen.');

        $game->update([
            'player_two_id' => Auth::id(),
            'status' => 'active',
            'current_turn_user_id' => $game->player_one_id, //speler 1 (X) begint altijd
        ]);

        return redirect()->route('games.show', $game);
    }

    public function show(Game $game): View
    {
        $this->authorizeParticipant($game);

        $board = $this->ticTacToe->buildBoard($game->load('moves'));

        return view('games.show', [
            'game' => $game->load(['playerOne', 'playerTwo', 'winner', 'comments.user']),
            'board' => $board,
        ]);
    }

    protected function authorizeParticipant(Game $game): void
    {
        $userId = Auth::id();

        abort_unless(
            $game->player_one_id === $userId || $game->player_two_id === $userId,
            403,
            'Je bent geen deelnemer van deze game.'
        );
    }
}
