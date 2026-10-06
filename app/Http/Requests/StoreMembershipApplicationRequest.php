<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipApplicationRequest extends FormRequest
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
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'street' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:10'],
            'postal_code' => ['required', 'regex:/^[1-9][0-9]{3}\s?[a-zA-Z]{2}$/'],
            'city' => ['required', 'string', 'max:255'],
            'nbvv_member' => ['required', 'in:0,1'],
            'nbvv_number' => ['nullable', 'required_if:nbvv_member,1', 'string', 'max:20', 'unique:members,nbvv_number'],
            'agreement' => ['accepted'],
            'signature_name' => ['required', 'string', 'max:255'],
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
            'postal_code.regex' => 'Vul een geldige postcode in, bijvoorbeeld 1234 AB.',
            'nbvv_member.required' => 'Geef aan of je lid bent van de NBvV.',
            'nbvv_number.required_if' => 'Vul je NBvV-nummer in als je lid bent van de NBvV.',
            'nbvv_number.unique' => 'Dit NBvV-nummer is al bij ons bekend.',
            'agreement.accepted' => 'Je moet akkoord gaan om je aan te kunnen melden.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'je voornaam',
            'last_name' => 'je achternaam',
            'date_of_birth' => 'je geboortedatum',
            'email' => 'je e-mailadres',
            'phone' => 'je telefoonnummer',
            'street' => 'je straat',
            'house_number' => 'je huisnummer',
            'postal_code' => 'je postcode',
            'city' => 'je woonplaats',
            'nbvv_number' => 'je NBvV-nummer',
            'signature_name' => 'de naam van de ondertekenaar',
        ];
    }
}
