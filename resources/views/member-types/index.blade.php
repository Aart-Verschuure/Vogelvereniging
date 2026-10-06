@php($money = fn ($amount) => '€ '.number_format($amount, 2, ',', '.'))

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Lidsoorten</h2>
            <a href="{{ route('member-types.create') }}" class="bg-[#7d848c] hover:bg-gray-600 text-white px-4 py-2 text-sm font-semibold">Lidsoort toevoegen</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif
        <x-input-error :messages="$errors->get('member_type')" />

        <div class="bg-white dark:bg-gray-800 shadow p-6 overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100">
                <thead class="border-b-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="py-2 pr-4 font-semibold">Lidsoort</th>
                        <th class="py-2 pr-4 font-semibold">Omschrijving</th>
                        <th class="py-2 pr-4 font-semibold text-right">Prijs {{ now()->year }}</th>
                        <th class="py-2 pr-4 font-semibold">Aangekondigd</th>
                        <th class="py-2 pr-4 font-semibold text-right">Leden</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($memberTypes as $type)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2 pr-4 font-medium">{{ $type->name }}</td>
                            <td class="py-2 pr-4">{{ $type->description }}</td>
                            <td class="py-2 pr-4 text-right">{{ $type->currentPrice() !== null ? $money($type->currentPrice()) : '-' }}</td>
                            <td class="py-2 pr-4">
                                @if ($announced = $type->announcedPrice())
                                    {{ $money($announced->price) }} per 1-1-{{ $announced->year }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-right">{{ $type->members_count }}</td>
                            <td class="py-2 text-right whitespace-nowrap">
                                <a href="{{ route('member-types.edit', $type) }}" class="text-blue-700 dark:text-blue-400 hover:underline">Wijzigen / prijs</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                Prijswijzigingen worden aangekondigd en gelden altijd vanaf een komend jaar ({{ $nextYear }} of later).
                Het lopende jaar en eerder verstuurde facturen veranderen daardoor niet.
            </p>
        </div>
    </div>
</x-app-layout>
