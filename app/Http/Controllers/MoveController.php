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
    //injecteert de tictactoe service in de controller
    public function __construct(protected TicTacToeService $ticTacToe)
    {
    }

    //verwerkt een nieuwe zet van een speler
    public function store(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'position' => ['required', 'integer', 'min:0', 'max:8'],
        ]);

        $userId = Auth::id();

        //controleren of de gebruiker mag spelen in deze game
        abort_unless(
            $game->player_one_id == $userId || $game->player_two_id == $userId,
            403,
            'Je bent geen deelnemer van deze game.'
        );

        //controleren of de game nog actief is
        if ($game->status != 'active') {
            return back()->with('error', 'Deze game is niet actief.');
        }

        //controleren of het de beurt van de speler is
        if ($game->current_turn_user_id != $userId) {
            return back()->with('error', 'Het is niet jouw beurt.');
        }

        //huidige bord opbouwen uit alle gespeelde zetten
        $board = $this->ticTacToe->buildBoard($game->load('moves'));

        //controleren of de gekozen positie nog vrij is
        if (!$this->ticTacToe->isValidMove($board, $validated['position'])) {
            return back()->with('error', 'Ongeldige zet, dit vakje is al bezet.');
        }

        //alle wijzigingen in een database transactie uitvoeren
        DB::transaction(function () use ($game, $userId, $validated, $board) {
            //bepalen welk symbool deze speler gebruikt
            $symbol = $game->player_one_id == $userId ? 'X' : 'O';

            GameMove::create([
                'game_id' => $game->id,
                'user_id' => $userId,
                'position' => $validated['position'],
                'symbol' => $symbol,
            ]);

            //bord bijwerken met de net gedane zet
            $board[$validated['position']] = $symbol;

            //controleren of er een winnaar is
            $winningSymbol = $this->ticTacToe->checkWinner($board);

            if ($winningSymbol != null) {
                $this->finishGame($game, $winningSymbol);

                return;
            }
            //controleren of het spel in een gelijkspel eindigt
            if ($this->ticTacToe->isDraw($board)) {
                $this->finishGame($game, null);

                return;
            }

            //beurt doorgeven aan de andere speler
            $nextTurnUserId = $userId == $game->player_one_id
                ? $game->player_two_id
                : $game->player_one_id;

            $game->update(['current_turn_user_id' => $nextTurnUserId]);
        });

        return redirect()->route('games.show', $game);
    }

    //eindigen spel en opslaan van de resultaten
    protected function finishGame(Game $game, ?string $winningSymbol): void
    {
        //winnende speler bepalen op basis van het symbool
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

        //resultaat voor beide spelers opslaan
        foreach ([$game->player_one_id, $game->player_two_id] as $playerId) {
            $result = match (true) {
                $winnerUserId == null => 'draw',
                $winnerUserId == $playerId => 'win',
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
