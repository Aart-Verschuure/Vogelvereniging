<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipCancellationRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
            'confirmation' => ['accepted'],
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
            'max' => ':attribute mag maximaal :max tekens bevatten.',
            'confirmation.accepted' => 'Bevestig dat je je lidmaatschap wilt opzeggen.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'je e-mailadres',
            'date_of_birth' => 'je geboortedatum',
            'cancellation_reason' => 'de reden',
        ];
    }
}
