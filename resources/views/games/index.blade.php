<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mijn games
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('games.store') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-black rounded hover:bg-black-700">
                        Nieuwe game starten
                    </button>
                </form>
            </div>
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Jouw games</h3>

                @forelse ($myGames as $game)
                    <div class="flex items-center justify-between border-b py-3 last:border-0">
                        <div>
                            <span class="font-medium">Game #{{ $game->id }}</span>
                            <span class="text-sm text-gray-500">
                                - {{ $game->playerOne->name ?? '?' }}
                                vs
                                {{ $game->playerTwo->name ?? 'wachtend op tegenstander' }}
                            </span>
                            <span class="ml-2 inline-block text-xs px-2 py-1 rounded
                                                @class([
                                                    'bg-yellow-100 text-yellow-800' => $game->status == 'waiting',
                                                    'bg-blue-100 text-blue-800' => $game->status == 'active',
                                                    'bg-gray-200 text-gray-700' => $game->status == 'finished',
                                                ])">
                                {{ $game->status }}
                            </span>
                        </div>
                        <a href="{{ route('games.show', $game) }}" class="text-indigo-600 hover:underline">Bekijk -></a>
                    </div>
                @empty
                    <p class="text-gray-500">Je hebt nog geen games.</p>
                @endforelse
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Open games (wachtend op een tegenstander)</h3>

                @forelse ($openGames as $game)
                    <div class="flex items-center justify-between border-b py-3 last:border-0">
                        <div>
                            <span class="font-medium">Game #{{ $game->id }}</span>
                            <span class="text-sm text-gray-500">gestart door {{ $game->playerOne->name ?? '?' }}</span>
                        </div>
                        <form method="POST" action="{{ route('games.join', $game) }}">
                            @csrf
                            <button type="submit" class="text-indigo-600 hover:underline">Meedoen -></button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500">Er zijn momenteel geen open games.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>