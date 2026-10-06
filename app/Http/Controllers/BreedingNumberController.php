<?php

namespace App\Http\Controllers;

use App\Models\BreedingNumber;
use App\Models\Member;
use Illuminate\Http\Request;

/**
 * Kweeknummers registreren en koppelen aan actieve leden.
 */
class BreedingNumberController extends Controller
{
    public function store(Request $request, Member $member)
    {
        if (! $member->isCurrentMember()) {
            return back()->withErrors(['breeding_number' => 'Een kweeknummer kan alleen aan een actief lid worden gekoppeld.']);
        }

        $data = $request->validate([
            'breeding_number' => ['required', 'string', 'max:50', 'unique:breeding_numbers,breeding_number'],
            'date_of_issue' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'required' => 'Vul :attribute in.',
            'unique' => 'Dit kweeknummer is al geregistreerd.',
            'date' => 'Vul een geldige datum in.',
            'before_or_equal' => ':attribute kan niet in de toekomst liggen.',
        ], ['breeding_number' => 'het kweeknummer', 'date_of_issue' => 'de datum van uitgifte']);

        $member->breedingNumbers()->create($data);

        return back()->with('status', 'Kweeknummer '.$data['breeding_number'].' is gekoppeld aan '.$member->fullName().'.');
    }

    public function destroy(BreedingNumber $breedingNumber)
    {
        $breedingNumber->delete();

        return back()->with('status', 'Kweeknummer '.$breedingNumber->breeding_number.' is verwijderd.');
    }
}
