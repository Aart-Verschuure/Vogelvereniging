@php
    $select = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    $address = $member->address;
@endphp

@csrf

<p class="text-sm text-gray-600 dark:text-gray-400">Velden met een <x-required /> zijn verplicht.</p>

<fieldset class="space-y-4">
    <legend class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Persoonlijke gegevens</legend>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="first_name">Voornaam <x-required /></x-input-label>
            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $member->first_name)" required />
            <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="last_name">Achternaam <x-required /></x-input-label>
            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $member->last_name)" required />
            <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="date_of_birth">Geboortedatum <x-required /></x-input-label>
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $member->date_of_birth?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $member->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="phone" value="Telefoonnummer" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $member->phone)" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>
    </div>
</fieldset>

<fieldset class="space-y-4">
    <legend class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Adres</legend>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <x-input-label for="street">Straat <x-required /></x-input-label>
            <x-text-input id="street" name="street" type="text" class="mt-1 block w-full" :value="old('street', $address?->street)" required />
            <x-input-error :messages="$errors->get('street')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="house_number">Huisnummer <x-required /></x-input-label>
            <x-text-input id="house_number" name="house_number" type="text" class="mt-1 block w-full" :value="old('house_number', $address?->house_number)" required />
            <x-input-error :messages="$errors->get('house_number')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="postal_code">Postcode <x-required /></x-input-label>
            <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $address?->postal_code)" placeholder="1234 AB" pattern="[1-9][0-9]{3}\s?[a-zA-Z]{2}" title="Bijvoorbeeld 1234 AB" required />
            <x-input-error :messages="$errors->get('postal_code')" class="mt-1" />
        </div>
        <div class="sm:col-span-2">
            <x-input-label for="city">Woonplaats <x-required /></x-input-label>
            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $address?->city)" required />
            <x-input-error :messages="$errors->get('city')" class="mt-1" />
        </div>
    </div>
</fieldset>

<fieldset class="space-y-4">
    <legend class="text-lg font-semibold mb-2 text-gray-900 dark:text-gray-100">Lidmaatschap en NBvV</legend>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="member_type_id">Lidsoort <x-required /></x-input-label>
            <select id="member_type_id" name="member_type_id" class="mt-1 block w-full {{ $select }}" required>
                <option value="">Kies een lidsoort...</option>
                @foreach ($memberTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('member_type_id', $member->member_type_id) == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('member_type_id')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="nbvv_number" value="NBvV-nummer" />
            <x-text-input id="nbvv_number" name="nbvv_number" type="text" class="mt-1 block w-full" :value="old('nbvv_number', $member->nbvv_number)" />
            <x-input-error :messages="$errors->get('nbvv_number')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="registration_date">Datum van opgave <x-required /></x-input-label>
            <x-text-input id="registration_date" name="registration_date" type="date" class="mt-1 block w-full" :value="old('registration_date', $member->registration_date?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('registration_date')" class="mt-1" />
        </div>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-400">
        Jeugdlid of seniorlid wordt automatisch bepaald op basis van de leeftijd: in het jaar dat iemand 18 wordt is hij nog jeugdlid.
        Het lidmaatschap gaat in per de 1e van de maand, 3 weken na de datum van opgave.
    </p>
</fieldset>
