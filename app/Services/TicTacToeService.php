<?php

namespace App\Services;

use App\Models\Game;

class TicTacToeService
{
    
     //alle winnende combinaties op een 3x3 bord (posities 0-8).
    protected const WINNING_LINES = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8], 
        [0, 4, 8], [2, 4, 6],            
    ];

    //bouwt het speelbord op basis van alle gespeelde zetten
    public function buildBoard(Game $game): array
    {
        $board = array_fill(0, 9, null);

        foreach ($game->moves as $move) {
            $board[$move->position] = $move->symbol;
        }

        return $board;
    }

    
     //bepaalt of de gegeven user_id heeft gewonnen op basis van het bord
     //geeft het winnende symbool (X of O) of null als er geen winnaar is vandaar de ?string 
    public function checkWinner(array $board): ?string
    {
        foreach (self::WINNING_LINES as [$a, $b, $c]) {
            if ($board[$a] != null && $board[$a] == $board[$b] && $board[$b] == $board[$c]) {
                return $board[$a];
            }
        }

        return null;
    }

    //controleert of het spel in een gelijkspel is geeindigd
    public function isDraw(array $board): bool
    {
        return ! in_array(null, $board, true) && $this->checkWinner($board) == null;
    }

    //controleert of een zet geldig is
    public function isValidMove(array $board, int $position): bool
    {
        return $position >= 0 && $position <= 8 && $board[$position] == null;
    }
}
