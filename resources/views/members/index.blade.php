@php
    $select = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Leden</h2>
            <a href="{{ route('members.create') }}" class="bg-[#7d848c] hover:bg-gray-600 text-white px-4 py-2 text-sm font-semibold">Lid toevoegen</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        {{-- Zoeken op naam, lidsoort, actieve status of kweeknummer --}}
        <form method="GET" action="{{ route('members.index') }}" class="bg-white dark:bg-gray-800 shadow p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
                <x-input-label for="naam" value="Naam" />
                <x-text-input id="naam" name="naam" type="search" class="mt-1 block w-full text-sm" :value="$filters['naam']" placeholder="Voor- of achternaam" />
            </div>
            <div>
                <x-input-label for="lidsoort" value="Lidsoort" />
                <select id="lidsoort" name="lidsoort" class="mt-1 block w-full {{ $select }}">
                    <option value="">Alle lidsoorten</option>
                    @foreach ($memberTypes as $type)
                        <option value="{{ $type->id }}" @selected($filters['lidsoort'] == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="mt-1 block w-full {{ $select }}">
                    @foreach ($statusFilters as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="kweeknummer" value="Kweeknummer" />
                <x-text-input id="kweeknummer" name="kweeknummer" type="search" class="mt-1 block w-full text-sm" :value="$filters['kweeknummer']" />
            </div>
            <div class="flex gap-2">
                <button class="bg-[#7d848c] hover:bg-gray-600 text-white px-4 py-2 text-sm font-semibold">Zoeken</button>
                <a href="{{ route('members.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 text-sm font-semibold">Wissen</a>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 shadow p-4 overflow-x-auto">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $members->count() }} {{ $members->count() === 1 ? 'lid' : 'leden' }} gevonden</p>

            <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100">
                <thead class="border-b-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="py-2 pr-4 font-semibold">Naam</th>
                        <th class="py-2 pr-4 font-semibold">Lidsoort</th>
                        <th class="py-2 pr-4 font-semibold">Woonplaats</th>
                        <th class="py-2 pr-4 font-semibold">NBvV-nummer</th>
                        <th class="py-2 pr-4 font-semibold">Kweeknummer(s)</th>
                        <th class="py-2 pr-4 font-semibold">Status</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2 pr-4 font-medium">
                                <a href="{{ route('members.show', $member) }}" class="hover:underline">{{ $member->fullName() }}</a>
                            </td>
                            <td class="py-2 pr-4">{{ $member->memberType?->name }}</td>
                            <td class="py-2 pr-4">{{ $member->address?->city }}</td>
                            <td class="py-2 pr-4">{{ $member->nbvv_number ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $member->breedingNumbers->pluck('breeding_number')->join(', ') ?: '-' }}</td>
                            <td class="py-2 pr-4">@include('members.partials.status', ['member' => $member])</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <a href="{{ route('members.show', $member) }}" class="text-blue-700 dark:text-blue-400 hover:underline">Bekijken</a>
                                <a href="{{ route('members.edit', $member) }}" class="ms-3 text-blue-700 dark:text-blue-400 hover:underline">Wijzigen</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-3 text-gray-500">Geen leden gevonden.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
