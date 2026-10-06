@extends('layouts.public')

@section('title', 'Lidmaatschap opzeggen')

@php
    $input = 'w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $label = 'block font-medium text-sm text-gray-700 mb-1';
@endphp

@section('content')
    <h1 class="text-2xl font-bold mb-4">Lidmaatschap opzeggen</h1>

    @if (session('success'))
        @php($s = session('success'))
        <div class="bg-white p-6 shadow-md space-y-3">
            <h2 class="text-xl font-semibold">Je afmelding is verwerkt, {{ $s['name'] }}</h2>
            <p>Je bent lid tot <strong>{{ $s['end_date'] }}</strong>.</p>
            @if ($s['refund'] > 0)
                <p>Je krijgt <strong>€ {{ number_format($s['refund'], 2, ',', '.') }}</strong> contributie terug voor de resterende maanden van dit jaar.
                   De administratie betaalt dit aan je uit.</p>
            @endif
            @if (($s['credited'] ?? 0) > 0)
                <p>Je openstaande factuur is verlaagd met <strong>€ {{ number_format($s['credited'], 2, ',', '.') }}</strong> voor de resterende maanden van dit jaar.</p>
            @endif
            <p>Jammer dat je gaat. Bedankt voor je lidmaatschap!</p>
            <a href="{{ url('/') }}" class="inline-block text-blue-700 hover:underline">Terug naar de homepagina</a>
        </div>
    @else
        <div class="bg-white p-6 shadow-md mb-6 space-y-2 text-sm leading-relaxed">
            <p>Na je afmelding duurt het 3 weken. Daarna ben je per de 1e van de maand geen lid meer.
               De contributie voor de resterende maanden van het jaar krijg je terug.</p>
            <p>Meld je je vandaag af, dan ben je geen lid meer per <strong>{{ $endDate->format('d-m-Y') }}</strong>
               en krijg je contributie terug voor <strong>{{ $months }} {{ $months === 1 ? 'maand' : 'maanden' }}</strong>.</p>
        </div>

        {{-- De knop licht op zodra e-mailadres, geboortedatum en vinkje goed zijn ingevuld --}}
        <form method="POST" action="{{ route('membership.cancel.store') }}" class="bg-white p-6 shadow-md space-y-5"
              x-data="requiredForm()" @input="check" @change="check">
            @csrf

            <p class="text-sm">Vul het e-mailadres en de geboortedatum in waarmee je bij ons bekend bent.
                Velden met een <x-required /> zijn verplicht.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="{{ $label }}">E-mailadres <x-required /></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $input }}">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label for="date_of_birth" class="{{ $label }}">Geboortedatum <x-required /></label>
                    <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" required class="{{ $input }}">
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
                </div>
            </div>

            <div>
                <label for="cancellation_reason" class="{{ $label }}">Reden van opzegging <span class="text-gray-500">(optioneel)</span></label>
                <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="{{ $input }}">{{ old('cancellation_reason') }}</textarea>
                <x-input-error :messages="$errors->get('cancellation_reason')" class="mt-1" />
            </div>

            <label class="flex items-start gap-3">
                <input type="checkbox" name="confirmation" value="1" required class="mt-1 rounded border-gray-300">
                <span class="text-sm">Ik zeg mijn lidmaatschap van Vogelvereniging 't Fratertje op. <x-required /></span>
            </label>
            <x-input-error :messages="$errors->get('confirmation')" />

            <x-highlight-button>Afmelding versturen</x-highlight-button>
        </form>
    @endif
@endsection
