<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Game #{{ $game->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6 text-center">
                <p class="mb-2">
                    <span class="font-medium">{{ $game->playerOne->name ?? '?' }}</span> (X)
                    vs
                    <span class="font-medium">{{ $game->playerTwo->name ?? 'wachtend...' }}</span> (O)
                </p>

                {{-- Status / beurt-indicator --}}
                <div id="game-status" class="text-lg font-semibold">
                    @if ($game->status == 'waiting')
                        <span class="text-yellow-600">Wachten op een tweede speler...</span>
                    @elseif ($game->status == 'active')
                        @if ($game->current_turn_user_id == auth()->id())
                            <span class="text-green-600">Het is jouw beurt!</span>
                        @else
                            <span class="text-gray-500">Wachten op
                                {{ $game->currentTurnUser->name ?? 'tegenstander' }}...</span>
                        @endif
                    @else
                        @if ($game->winner_user_id == null)
                            <span class="text-gray-700">Gelijkspel!</span>
                        @elseif ($game->winner_user_id == auth()->id())
                            <span class="text-green-600">Je hebt gewonnen!</span>
                        @else
                            <span class="text-red-600">Je hebt verloren.</span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- 3x3 speelbord --}}
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="grid grid-cols-3 gap-2 max-w-xs mx-auto">
                    @foreach ($board as $position => $symbol)
                        <form method="POST" action="{{ route('moves.store', $game) }}">
                            @csrf
                            <input type="hidden" name="position" value="{{ $position }}">
                            <button type="submit" @disabled($symbol != null || $game->status != 'active' || $game->current_turn_user_id != auth()->id()) class="w-20 h-20 flex items-center justify-center text-3xl font-bold border rounded
                                                    {{ $symbol == null ? 'bg-gray-50 hover:bg-gray-100' : 'bg-white' }}
                                                    {{ $symbol == 'X' ? 'text-indigo-600' : '' }}
                                                    {{ $symbol == 'O' ? 'text-rose-600' : '' }}
                                                    disabled:cursor-not-allowed">
                                {{ $symbol }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('games.index') }}" class="text-indigo-600 hover:underline"><- Terug naar overzicht</a>
        </div>
    </div>

    <!-- @if ($game->status != 'finished')
        <script>
            //eenvoudige polling: herlaad de pagina elke 4 seconden anders is irritant
            setTimeout(() => window.location.reload(), 4000);
        </script>
    @endif -->
</x-app-layout>