<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Vrienden</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Mijn vrienden</h3>

                @forelse ($friends as $friend)
                    <div class="flex items-center justify-between border-b py-3 last:border-0">
                        <span>{{ $friend->name }}</span>
                        <form method="POST" action="{{ route('games.challenge', $friend) }}">
                            @csrf
                            <button type="submit" class="text-indigo-600 hover:underline">Uitdagen voor een game</button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500">Je hebt nog geen vrienden.</p>
                @endforelse
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Ontvangen verzoeken</h3>

                @forelse ($receivedPending as $request)
                    <div class="flex items-center justify-between border-b py-3 last:border-0">
                        <span>{{ $request->user->name }}</span>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('friends.update', $request) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="text-green-600 hover:underline">Accepteren</button>
                            </form>
                            <form method="POST" action="{{ route('friends.update', $request) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="text-red-600 hover:underline">Weigeren</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Geen openstaande verzoeken.</p>
                @endforelse
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Verstuurde verzoeken</h3>

                @forelse ($sentPending as $request)
                    <div class="flex items-center justify-between border-b py-3 last:border-0">
                        <span>{{ $request->friend->name }} <span class="text-sm text-gray-400">(in afwachting)</span></span>
                        <form method="POST" action="{{ route('friends.destroy', $request) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Intrekken</button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500">Geen verstuurde verzoeken.</p>
                @endforelse
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium mb-4">Andere gebruikers</h3>

                @forelse ($otherUsers as $user)
                    <div class="flex items-center justify-between border-b py-3 last:border-0">
                        <span>{{ $user->name }}</span>
                        <form method="POST" action="{{ route('friends.store') }}">
                            @csrf
                            <input type="hidden" name="friend_id" value="{{ $user->id }}">
                            <button type="submit" class="text-indigo-600 hover:underline">Vriendschapsverzoek
                                sturen</button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500">Geen andere gebruikers gevonden.</p>
                @endforelse
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6 text-center">
                <h3 class="text-lg font-medium mb-2">Geen vrienden online?</h3>
                <p class="text-gray-500 mb-4">Speel direct tegen een willekeurige tegenstander.</p>
                <form method="POST" action="{{ route('games.matchmake') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Zoek tegenstander
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>