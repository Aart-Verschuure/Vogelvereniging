@php
    $select = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm';
    $money = fn ($amount) => '€ '.number_format($amount, 2, ',', '.');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $member->fullName() }}</h2>
            <div class="flex gap-3">
                <a href="{{ route('members.index') }}" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">Terug naar leden</a>
                <a href="{{ route('members.edit', $member) }}" class="bg-[#7d848c] hover:bg-gray-600 text-white px-4 py-2 text-sm font-semibold">Wijzigen</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-gray-900 dark:text-gray-100">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        {{-- Gegevens --}}
        <section class="bg-white dark:bg-gray-800 shadow p-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div class="space-y-1">
                <h3 class="font-semibold text-base mb-2">Persoonlijk</h3>
                <p>Geboren: {{ $member->date_of_birth?->format('d-m-Y') }}</p>
                <p>E-mail: {{ $member->email ?? '-' }}</p>
                <p>Telefoon: {{ $member->phone ?? '-' }}</p>
            </div>
            <div class="space-y-1">
                <h3 class="font-semibold text-base mb-2">Adres</h3>
                @if ($member->address)
                    <p>{{ $member->address->street }} {{ $member->address->house_number }}</p>
                    <p>{{ $member->address->postal_code }} {{ $member->address->city }}</p>
                @else
                    <p>-</p>
                @endif
            </div>
            <div class="space-y-1">
                <h3 class="font-semibold text-base mb-2">Lidmaatschap</h3>
                <p>@include('members.partials.status', ['member' => $member])</p>
                <p>Lidsoort: {{ $member->memberType?->name }}</p>
                <p>NBvV-nummer: {{ $member->nbvv_number ?? '-' }}</p>
                <p>Opgave: {{ $member->registration_date?->format('d-m-Y') ?? '-' }}
                    @if ($member->registration_date) · lid per {{ membership_start_date($member->registration_date)->format('d-m-Y') }} @endif</p>
                @if ($member->signed_at)
                    <p class="text-gray-500">Ondertekend door {{ $member->signature_name }} op {{ $member->signed_at->format('d-m-Y H:i') }}</p>
                @endif
            </div>
        </section>

        {{-- Kweeknummers --}}
        <section class="bg-white dark:bg-gray-800 shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Kweeknummers</h3>

            @forelse ($member->breedingNumbers as $breedingNumber)
                <div class="border-t border-gray-200 dark:border-gray-700 py-2 flex justify-between items-center text-sm">
                    <p><span class="font-semibold">{{ $breedingNumber->breeding_number }}</span>
                        <span class="text-gray-500">— uitgegeven op {{ $breedingNumber->date_of_issue?->format('d-m-Y') }}</span></p>
                    <form method="POST" action="{{ route('breeding-numbers.destroy', $breedingNumber) }}"
                          onsubmit="return confirm('Weet je zeker dat je kweeknummer {{ $breedingNumber->breeding_number }} wilt verwijderen?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-700 dark:text-red-400 hover:underline">Verwijderen</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nog geen kweeknummers.</p>
            @endforelse

            @if ($member->isCurrentMember())
                <form method="POST" action="{{ route('members.breeding-numbers.store', $member) }}" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 items-end">
                    @csrf
                    <div>
                        <x-input-label for="breeding_number" value="Kweeknummer" />
                        <x-text-input id="breeding_number" name="breeding_number" type="text" class="mt-1 block text-sm" :value="old('breeding_number')" required />
                    </div>
                    <div>
                        <x-input-label for="date_of_issue" value="Datum van uitgifte" />
                        <x-text-input id="date_of_issue" name="date_of_issue" type="date" class="mt-1 block text-sm" :value="old('date_of_issue', today()->format('Y-m-d'))" required />
                    </div>
                    <x-primary-button>Kweeknummer koppelen</x-primary-button>
                </form>
            @else
                <p class="mt-4 text-sm text-gray-500">Kweeknummers kunnen alleen aan actieve leden worden gekoppeld.</p>
            @endif
            <x-input-error :messages="$errors->get('breeding_number')" class="mt-2" />
            <x-input-error :messages="$errors->get('date_of_issue')" class="mt-2" />
        </section>

        {{-- Facturen en teruggaves --}}
        <section class="bg-white dark:bg-gray-800 shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Facturen</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="border-b-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400">
                        <tr>
                            <th class="py-2 pr-4 font-semibold">Factuur</th>
                            <th class="py-2 pr-4 font-semibold">Jaar</th>
                            <th class="py-2 pr-4 font-semibold">Lidsoort</th>
                            <th class="py-2 pr-4 font-semibold">Maanden</th>
                            <th class="py-2 pr-4 font-semibold text-right">Bedrag</th>
                            <th class="py-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($member->contributions as $contribution)
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <td class="py-2 pr-4">
                                    @if ($contribution->amount > 0 && $contribution->invoice_number)
                                        <a href="{{ route('invoices.show', $contribution) }}" class="text-blue-700 dark:text-blue-400 hover:underline">{{ $contribution->invoice_number }}</a>
                                    @elseif ($contribution->amount < 0)
                                        Teruggave
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2 pr-4">{{ $contribution->year ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $contribution->memberType?->name ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $contribution->months ?? '-' }}</td>
                                <td class="py-2 pr-4 text-right">{{ $money($contribution->amount) }}</td>
                                <td class="py-2">
                                    @if ($contribution->isPaid())
                                        <span class="text-green-700">{{ $contribution->amount < 0 ? 'Uitbetaald' : 'Betaald' }}</span>
                                    @else
                                        <span class="text-yellow-700">{{ $contribution->amount < 0 ? 'Nog uit te betalen' : 'Open' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-3 text-gray-500">Nog geen facturen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (in_array($member->status, [\App\Models\Member::STATUS_ACTIVE, \App\Models\Member::STATUS_CANCELLED], true))
                <form method="POST" action="{{ route('members.invoices.store', $member) }}" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-4 items-end">
                    @csrf
                    <div>
                        <x-input-label for="year" value="Jaar" />
                        <select id="year" name="year" class="mt-1 block {{ $select }}">
                            @foreach ($invoiceYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Factuur aanmaken</x-primary-button>
                    <p class="text-xs text-gray-500 basis-full">Het systeem bepaalt het bedrag: de prijs van de lidsoort in dat jaar, naar rato van de maanden dat iemand lid is.</p>
                </form>
            @endif
            <x-input-error :messages="$errors->get('invoice')" class="mt-2" />
        </section>
    </div>
</x-app-layout>
