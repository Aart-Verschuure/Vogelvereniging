<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validatie voor het toevoegen en wijzigen van een lid door een beheerder.
 */
class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $member = $this->route('member');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'street' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:10'],
            'postal_code' => ['required', 'regex:/^[1-9][0-9]{3}\s?[a-zA-Z]{2}$/'],
            'city' => ['required', 'string', 'max:255'],
            'member_type_id' => ['required', Rule::exists('member_types', 'id')->whereNull('deleted_at')],
            'nbvv_number' => ['nullable', 'string', 'max:20', Rule::unique('members', 'nbvv_number')->ignore($member?->id)],
            'registration_date' => ['required', 'date'],
            'create_invoice' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'Vul :attribute in.',
            'email' => 'Vul een geldig e-mailadres in.',
            'date' => 'Vul een geldige datum in.',
            'before' => ':attribute moet in het verleden liggen.',
            'after' => 'Vul een geldige :attribute in.',
            'max' => ':attribute mag maximaal :max tekens bevatten.',
            'exists' => 'Kies een geldige :attribute.',
            'postal_code.regex' => 'Vul een geldige postcode in, bijvoorbeeld 1234 AB.',
            'nbvv_number.unique' => 'Dit NBvV-nummer hoort al bij een ander lid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'de voornaam',
            'last_name' => 'de achternaam',
            'date_of_birth' => 'de geboortedatum',
            'email' => 'het e-mailadres',
            'phone' => 'het telefoonnummer',
            'street' => 'de straat',
            'house_number' => 'het huisnummer',
            'postal_code' => 'de postcode',
            'city' => 'de woonplaats',
            'member_type_id' => 'lidsoort',
            'nbvv_number' => 'het NBvV-nummer',
            'registration_date' => 'de datum van opgave',
        ];
    }
}
