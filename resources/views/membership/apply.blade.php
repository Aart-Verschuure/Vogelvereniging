@extends('layouts.public')

@section('title', 'Lid worden')

@php
    $input = 'w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $label = 'block font-medium text-sm text-gray-700 mb-1';
@endphp

@section('content')
    <h1 class="text-2xl font-bold mb-4">Lid worden van 't Fratertje</h1>

    @if (session('success'))
        @php($s = session('success'))
        <div class="bg-white p-6 shadow-md space-y-3">
            <h2 class="text-xl font-semibold">Bedankt voor je aanmelding, {{ $s['name'] }}!</h2>
            <p>We hebben je aanmelding als <strong>{{ $s['type'] }}</strong> ontvangen. De administratie verwerkt je aanmelding en neemt contact met je op.</p>
            <p>Je bent lid per <strong>{{ $s['start_date'] }}</strong>. De contributie voor dit jaar is
                <strong>€ {{ number_format($s['contribution'], 2, ',', '.') }}</strong> ({{ $s['months'] }} {{ $s['months'] === 1 ? 'maand' : 'maanden' }}).</p>
            <a href="{{ url('/') }}" class="inline-block text-blue-700 hover:underline">Terug naar de homepagina</a>
        </div>
    @else
        <div class="bg-white p-6 shadow-md mb-6 space-y-2 text-sm leading-relaxed">
            <p>Na je opgave duurt het 3 weken voordat je daadwerkelijk lid bent. Je bent lid per de 1e van de maand daarna.
               Je betaalt contributie vanaf die maand tot en met december.</p>
            <p>Meld je je vandaag aan, dan ben je lid per <strong>{{ $startDate->format('d-m-Y') }}</strong>
               en betaal je dit jaar voor <strong>{{ $months }} {{ $months === 1 ? 'maand' : 'maanden' }}</strong>:</p>
            <ul class="list-disc pl-6">
                @foreach ($memberTypes as $type)
                    @php($price = $type->priceForYear($startDate->year))
                    @continue($price === null)
                    <li>{{ $type->name }} ({{ $type->description }}): € {{ number_format(round($price / 12 * $months, 2), 2, ',', '.') }}
                        <span class="text-gray-600">(€ {{ number_format($price, 2, ',', '.') }} per jaar)</span>
                        @if ($announced = $type->announcedPrice())
                            <span class="text-gray-600">— per 1 januari {{ $announced->year }} wordt dit € {{ number_format($announced->price, 2, ',', '.') }} per jaar</span>
                        @endif
                    </li>
                @endforeach
            </ul>
            <p class="text-gray-600">Jeugdleden betalen ook in het jaar dat ze 18 worden nog de contributie voor jeugdlid.</p>
        </div>

        <form method="POST" action="{{ route('membership.apply.store') }}" class="bg-white p-6 shadow-md space-y-6"
              x-data="requiredForm({ nbvv: @js(old('nbvv_member', '')) })" @input="check" @change="check">
            @csrf

            <p class="text-sm">Velden met een <x-required /> zijn verplicht.</p>

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold mb-2">Persoonlijke gegevens</legend>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="{{ $label }}">Voornaam <x-required /></label>
                        <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required autocomplete="given-name" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                    </div>
                    <div>
                        <label for="last_name" class="{{ $label }}">Achternaam <x-required /></label>
                        <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required autocomplete="family-name" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                    </div>
                    <div>
                        <label for="date_of_birth" class="{{ $label }}">Geboortedatum <x-required /></label>
                        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" required autocomplete="bday" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
                    </div>
                    <div>
                        <label for="email" class="{{ $label }}">E-mailadres <x-required /></label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <div>
                        <label for="phone" class="{{ $label }}">Telefoonnummer <span class="text-gray-500">(optioneel)</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4">
                <legend class="text-lg font-semibold mb-2">Adres</legend>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="street" class="{{ $label }}">Straat <x-required /></label>
                        <input id="street" name="street" type="text" value="{{ old('street') }}" required autocomplete="address-line1" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('street')" class="mt-1" />
                    </div>
                    <div>
                        <label for="house_number" class="{{ $label }}">Huisnummer <x-required /></label>
                        <input id="house_number" name="house_number" type="text" value="{{ old('house_number') }}" required class="{{ $input }}">
                        <x-input-error :messages="$errors->get('house_number')" class="mt-1" />
                    </div>
                    <div>
                        <label for="postal_code" class="{{ $label }}">Postcode <x-required /></label>
                        <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code') }}" required autocomplete="postal-code" placeholder="1234 AB" pattern="[1-9][0-9]{3}\s?[a-zA-Z]{2}" title="Bijvoorbeeld 1234 AB" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('postal_code')" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="city" class="{{ $label }}">Woonplaats <x-required /></label>
                        <input id="city" name="city" type="text" value="{{ old('city') }}" required autocomplete="address-level2" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('city')" class="mt-1" />
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="text-lg font-semibold mb-2">Lidmaatschap</legend>

                <p class="text-sm">Ben je lid van de NBvV (Nederlandse Bond van Vogelliefhebbers)? <x-required /></p>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="nbvv_member" value="1" x-model="nbvv" required> Ja
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="nbvv_member" value="0" x-model="nbvv"> Nee, ik word gastlid
                    </label>
                </div>
                <x-input-error :messages="$errors->get('nbvv_member')" />

                <div x-show="nbvv === '1'" x-cloak>
                    <label for="nbvv_number" class="{{ $label }}">NBvV-nummer <x-required /></label>
                    <input id="nbvv_number" name="nbvv_number" type="text" value="{{ old('nbvv_number') }}" :required="nbvv === '1'" class="{{ $input }} sm:w-1/2">
                    <x-input-error :messages="$errors->get('nbvv_number')" class="mt-1" />
                </div>

                <p class="text-sm text-gray-600">Ben je lid van de NBvV, dan word je jeugdlid (tot en met het jaar waarin je 18 wordt) of seniorlid. Anders word je gastlid.</p>
            </fieldset>

            <fieldset class="space-y-4 border-t pt-6">
                <legend class="text-lg font-semibold mb-2">Ondertekening</legend>

                <label class="flex items-start gap-3">
                    <input type="checkbox" name="agreement" value="1" @checked(old('agreement')) required class="mt-1 rounded border-gray-300">
                    <span class="text-sm leading-relaxed">
                        Ik meld mij aan als lid van Vogelvereniging 't Fratertje en verplicht mij tot het betalen van de contributie.
                        Ik ga akkoord met de statuten en het huishoudelijk reglement van de vereniging, en met het verwerken van
                        mijn gegevens voor de ledenadministratie. Ik verklaar dat ik dit formulier naar waarheid heb ingevuld. <x-required />
                    </span>
                </label>
                <x-input-error :messages="$errors->get('agreement')" />

                <div>
                    <label for="signature_name" class="{{ $label }}">Naam ondertekenaar <x-required /></label>
                    <input id="signature_name" name="signature_name" type="text" value="{{ old('signature_name') }}" required class="{{ $input }} sm:w-1/2">
                    <p class="text-sm text-gray-600 mt-1">Typ je volledige naam als handtekening. Ben je jonger dan 18, dan ondertekent een ouder of verzorger.</p>
                    <x-input-error :messages="$errors->get('signature_name')" class="mt-1" />
                </div>

                <p class="text-xs text-gray-500">Bij het versturen worden de datum, het tijdstip en je IP-adres vastgelegd als bewijs van ondertekening.</p>
            </fieldset>

            <x-highlight-button>Aanmelding versturen</x-highlight-button>
        </form>
    @endif
@endsection
