<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FriendController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $friends = Auth::user()->friends()->get();

        $receivedPending = Friend::with('user')
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->get();

        $sentPending = Friend::with('friend')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->get();

        $connectedIds = $friends->pluck('id')
            ->merge($receivedPending->pluck('user_id'))
            ->merge($sentPending->pluck('friend_id'))
            ->push($userId);

        $otherUsers = User::whereNotIn('id', $connectedIds)->get();

        return view('friends.index', [
            'friends' => $friends,
            'receivedPending' => $receivedPending,
            'sentPending' => $sentPending,
            'otherUsers' => $otherUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'friend_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $friendId = (int) $validated['friend_id'];
        $userId = Auth::id();

        abort_if($friendId === $userId, 403, 'Je kunt jezelf geen verzoek sturen.');

        $exists = Friend::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $userId);
        })->exists();

        if ($exists) {
            return back()->with('error', 'Er bestaat al een verzoek of vriendschap met deze gebruiker.');
        }

        Friend::create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Vriendschapsverzoek verstuurd.');
    }

    public function update(Request $request, Friend $friend): RedirectResponse
    {
        abort_unless($friend->friend_id === Auth::id(), 403, 'Dit verzoek is niet voor jou.');

        $validated = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
        ]);

        $friend->update(['status' => $validated['status']]);

        return back()->with('status', $validated['status'] === 'accepted'
            ? 'Vriendschapsverzoek geaccepteerd.'
            : 'Vriendschapsverzoek geweigerd.');
    }

    public function destroy(Friend $friend): RedirectResponse
    {
        $userId = Auth::id();

        abort_unless(
            $friend->user_id === $userId || $friend->friend_id === $userId,
            403,
            'Je hebt geen toegang tot dit verzoek.'
        );

        $friend->delete();

        return back()->with('status', 'Verwijderd.');
    }
}
