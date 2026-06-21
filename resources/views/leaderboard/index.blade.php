<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leaderboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">

            @foreach ([
                'Vandaag' => $daily,
                'Deze week' => $weekly,
                'All-time' => $total,
            ] as $title => $rows)
                <div class="bg-white shadow sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">{{ $title }}</h3>

                    @forelse ($rows as $index => $row)
                        <div class="flex items-center justify-between border-b py-2 last:border-0">
                            <span>
                                <span class="text-gray-400 mr-2">#{{ $index + 1 }}</span>
                                {{ $row->name }}
                            </span>
                            <span class="font-semibold">{{ $row->wins }} {{ $row->wins == 1 ? 'win' : 'wins' }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500">Nog geen overwinningen.</p>
                    @endforelse
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
