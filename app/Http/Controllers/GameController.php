<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use App\Services\TicTacToeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameController extends Controller
{
    //injecteert de tictactoe service in de controller
    public function __construct(protected TicTacToeService $ticTacToe)
    {
    }

    //toont een overzicht van eigen en beschikbare games
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

    //maakt een nieuwe game aan en wacht op een tegenstander
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

    //laat een gebruiker deelnemen aan een openstaande game
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

    //maakt direct een game aan tegen een vriend
    public function challenge(User $friend): RedirectResponse
    {
        $userId = Auth::id();

        abort_if($friend->id === $userId, 403, 'Je kunt jezelf niet uitdagen.');

        //auth::user() retourneert voor de IDE een Authenticatable
        //met deze typehint weet Intelephense dat het om een User-model gaat,
        //anders wordt friends() niet correct worden gezien
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $isFriend = $user->friends()
            ->where('id', $friend->id)
            ->exists();

        abort_unless($isFriend, 403, 'Je kunt alleen vrienden uitdagen.');

        $game = Game::create([
            'player_one_id' => $userId,
            'player_two_id' => $friend->id,
            'status' => 'active',
            'current_turn_user_id' => $userId,
        ]);

        return redirect()->route('games.show', $game);
    }

    //zoekt een open game of maakt er zelf een aan
    public function matchmake(): RedirectResponse
    {
        $userId = Auth::id();

        $openGame = Game::where('status', 'waiting')
            ->where('player_one_id', '!=', $userId)
            ->oldest()
            ->first();

        if ($openGame) {
            $openGame->update([
                'player_two_id' => $userId,
                'status' => 'active',
                'current_turn_user_id' => $openGame->player_one_id,
            ]);

            return redirect()->route('games.show', $openGame)
                ->with('status', 'Tegenstander gevonden!');
        }

        $game = Game::create([
            'player_one_id' => $userId,
            'status' => 'waiting',
        ]);

        return redirect()->route('games.show', $game)
            ->with('status', 'Geen tegenstander beschikbaar, je wacht nu zelf op iemand.');
    }

    //toont het speelbord van een game
    public function show(Game $game): View
    {
        $this->authorizeParticipant($game);

        $board = $this->ticTacToe->buildBoard($game->load('moves'));

        return view('games.show', [
            'game' => $game->load(['playerOne', 'playerTwo', 'winner', 'comments.user']),
            'board' => $board,
        ]);
    }

    //controleert of de gebruiker deelneemt aan de game
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
