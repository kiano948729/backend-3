<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameMove;
use App\Models\GameResult;
use App\Services\TicTacToeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoveController extends Controller
{
    public function __construct(protected TicTacToeService $ticTacToe)
    {
    }

    public function store(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'position' => ['required', 'integer', 'min:0', 'max:8'],
        ]);

        $userId = Auth::id();

        //validaties 
        abort_unless(
            $game->player_one_id === $userId || $game->player_two_id === $userId,
            403,
            'Je bent geen deelnemer van deze game.'
        );

        if ($game->status !== 'active') {
            return back()->with('error', 'Deze game is niet actief.');
        }

        if ($game->current_turn_user_id !== $userId) {
            return back()->with('error', 'Het is niet jouw beurt.');
        }

        $board = $this->ticTacToe->buildBoard($game->load('moves'));

        if (! $this->ticTacToe->isValidMove($board, $validated['position'])) {
            return back()->with('error', 'Ongeldige zet, dit vakje is al bezet.');
        }

        DB::transaction(function () use ($game, $userId, $validated, $board) {
            $symbol = $game->player_one_id === $userId ? 'X' : 'O';

            GameMove::create([
                'game_id' => $game->id,
                'user_id' => $userId,
                'position' => $validated['position'],
                'symbol' => $symbol,
            ]);

            //bord bijwerken met de net gedane zet
            $board[$validated['position']] = $symbol;

            $winningSymbol = $this->ticTacToe->checkWinner($board);

            if ($winningSymbol !== null) {
                $this->finishGame($game, $winningSymbol);

                return;
            }

            if ($this->ticTacToe->isDraw($board)) {
                $this->finishGame($game, null);

                return;
            }

            $nextTurnUserId = $userId === $game->player_one_id
                ? $game->player_two_id
                : $game->player_one_id;

            $game->update(['current_turn_user_id' => $nextTurnUserId]);
        });

        return redirect()->route('games.show', $game);
    }

    //eindigen spel en opslaan van de resultaten
    protected function finishGame(Game $game, ?string $winningSymbol): void
    {
        $winnerUserId = match ($winningSymbol) {
            'X' => $game->player_one_id,
            'O' => $game->player_two_id,
            default => null,
        };

        $game->update([
            'status' => 'finished',
            'winner_user_id' => $winnerUserId,
            'current_turn_user_id' => null,
        ]);

        foreach ([$game->player_one_id, $game->player_two_id] as $playerId) {
            $result = match (true) {
                $winnerUserId === null => 'draw',
                $winnerUserId === $playerId => 'win',
                default => 'loss',
            };

            GameResult::create([
                'game_id' => $game->id,
                'user_id' => $playerId,
                'winner_user_id' => $winnerUserId,
                'result' => $result,
            ]);
        }
    }
}
