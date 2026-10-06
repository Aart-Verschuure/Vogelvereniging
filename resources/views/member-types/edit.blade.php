@php($money = fn ($amount) => '€ '.number_format($amount, 2, ',', '.'))

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Lidsoort {{ $memberType->name }}</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-gray-900 dark:text-gray-100">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        {{-- Naam en omschrijving --}}
        <form method="POST" action="{{ route('member-types.update', $memberType) }}" class="bg-white dark:bg-gray-800 shadow p-6 space-y-4">
            @csrf
            @method('PUT')
            <h3 class="text-lg font-semibold">Gegevens</h3>

            <div>
                <x-input-label for="name" value="Naam" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $memberType->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="description" value="Omschrijving" />
                <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $memberType->description)" required />
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>
            <x-primary-button>Opslaan</x-primary-button>
        </form>

        {{-- Prijzen --}}
        <section class="bg-white dark:bg-gray-800 shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Prijzen</h3>

            <table class="w-full text-sm text-left">
                <thead class="border-b-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="py-2 pr-4 font-semibold">Vanaf</th>
                        <th class="py-2 pr-4 font-semibold text-right">Prijs per jaar</th>
                        <th class="py-2 font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @php($currentPriceYear = $memberType->prices->where('year', '<=', now()->year)->max('year'))
                    @foreach ($memberType->prices as $price)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2 pr-4">1 januari {{ $price->year }}</td>
                            <td class="py-2 pr-4 text-right">{{ $money($price->price) }}</td>
                            <td class="py-2 text-gray-500">
                                @if ($price->year > now()->year)
                                    Aangekondigd
                                @elseif ($price->year === $currentPriceYear)
                                    Geldt nu
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form method="POST" action="{{ route('member-types.prices.store', $memberType) }}" class="pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                @csrf
                <h4 class="font-semibold">Prijswijziging aankondigen</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Een nieuwe prijs geldt altijd vanaf 1 januari van een komend jaar. Het lopende jaar en de historie veranderen niet.
                    Bestaat er al een prijs voor dat jaar, dan wordt die vervangen.
                </p>
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <x-input-label for="year" value="Vanaf 1 januari" />
                        <x-text-input id="year" name="year" type="number" min="{{ $nextYear }}" max="{{ $nextYear + 4 }}" class="mt-1 block w-28" :value="old('year', $nextYear)" required />
                    </div>
                    <div>
                        <x-input-label for="price" value="Prijs per jaar (€)" />
                        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-32" :value="old('price')" required />
                    </div>
                    <x-primary-button>Aankondigen</x-primary-button>
                </div>
                <x-input-error :messages="$errors->get('year')" />
                <x-input-error :messages="$errors->get('price')" />
            </form>
        </section>

        {{-- Verwijderen --}}
        <section class="bg-white dark:bg-gray-800 shadow p-6 space-y-3">
            <h3 class="text-lg font-semibold">Lidsoort verwijderen</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Kan alleen als er geen leden meer met deze lidsoort zijn. Jeugdlid, Seniorlid en Gastlid kunnen niet worden verwijderd.</p>
            <form method="POST" action="{{ route('member-types.destroy', $memberType) }}"
                  onsubmit="return confirm('Weet je zeker dat je lidsoort {{ $memberType->name }} wilt verwijderen?')">
                @csrf
                @method('DELETE')
                <x-danger-button>Verwijderen</x-danger-button>
            </form>
            <x-input-error :messages="$errors->get('member_type')" />
        </section>
    </div>
</x-app-layout>
