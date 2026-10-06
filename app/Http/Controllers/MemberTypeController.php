<?php

namespace App\Http\Controllers;

use App\Models\MemberType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Beheer van lidsoorten en hun prijzen.
 *
 * Prijswijzigingen worden altijd aangekondigd en gelden vanaf een komend jaar. Zo hebben ze
 * geen effect op het lopende jaar of op de historie (facturen bewaren hun eigen bedrag).
 */
class MemberTypeController extends Controller
{
    public function index()
    {
        return view('member-types.index', [
            'memberTypes' => MemberType::with('prices')->withCount('members')->orderBy('name')->get(),
            'nextYear' => now()->year + 1,
        ]);
    }

    public function create()
    {
        return view('member-types.create', ['memberType' => new MemberType]);
    }

    /**
     * Een nieuwe lidsoort krijgt direct een prijs voor het lopende jaar: er is nog geen historie.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('member_types', 'name')->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999'],
        ], $this->messages(), $this->attributes());

        $memberType = MemberType::create($request->only('name', 'description'));
        $memberType->prices()->create(['year' => now()->year, 'price' => $data['price']]);

        return redirect()->route('member-types.index')->with('status', 'Lidsoort '.$memberType->name.' is toegevoegd.');
    }

    public function edit(MemberType $memberType)
    {
        return view('member-types.edit', [
            'memberType' => $memberType->load('prices'),
            'nextYear' => now()->year + 1,
        ]);
    }

    /**
     * Naam en omschrijving wijzigen. De prijs wijzig je via storePrice.
     */
    public function update(Request $request, MemberType $memberType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('member_types', 'name')->ignore($memberType->id)->whereNull('deleted_at')],
            'description' => ['required', 'string', 'max:255'],
        ], $this->messages(), $this->attributes());

        // De namen Jeugdlid, Seniorlid en Gastlid gebruikt het systeem voor de bedrijfsregels
        if ($this->isSystemType($memberType) && $data['name'] !== $memberType->name) {
            return back()->withInput()->withErrors(['name' => 'De naam van '.$memberType->name.' kan niet worden gewijzigd, het systeem gebruikt deze.']);
        }

        $memberType->update($data);

        return redirect()->route('member-types.index')->with('status', 'Lidsoort '.$memberType->name.' is opgeslagen.');
    }

    /**
     * Een prijswijziging aankondigen. Die geldt altijd vanaf een komend jaar.
     */
    public function storePrice(Request $request, MemberType $memberType)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:'.(now()->year + 1), 'max:'.(now()->year + 5)],
            'price' => ['required', 'numeric', 'min:0', 'max:9999'],
        ], [
            'year.min' => 'Een prijswijziging geldt altijd vanaf een komend jaar, dus vanaf '.(now()->year + 1).' of later.',
            'year.max' => 'Kies een jaar tot en met '.(now()->year + 5).'.',
            'required' => 'Vul :attribute in.',
            'numeric' => ':attribute moet een getal zijn.',
            'min' => ':attribute mag niet negatief zijn.',
        ], ['year' => 'het jaar', 'price' => 'de prijs']);

        $memberType->prices()->updateOrCreate(['year' => $data['year']], ['price' => $data['price']]);

        return redirect()->route('member-types.edit', $memberType)
            ->with('status', 'De nieuwe prijs voor '.$memberType->name.' geldt vanaf 1 januari '.$data['year'].'.');
    }

    public function destroy(MemberType $memberType)
    {
        if ($this->isSystemType($memberType)) {
            return back()->withErrors(['member_type' => $memberType->name.' kan niet worden verwijderd, het systeem gebruikt deze lidsoort.']);
        }

        if ($memberType->members()->exists()) {
            return back()->withErrors(['member_type' => $memberType->name.' kan niet worden verwijderd, er zijn nog leden met deze lidsoort.']);
        }

        $memberType->delete();

        return redirect()->route('member-types.index')->with('status', 'Lidsoort '.$memberType->name.' is verwijderd.');
    }

    private function isSystemType(MemberType $memberType): bool
    {
        return in_array($memberType->name, [MemberType::JEUGDLID, MemberType::SENIORLID, MemberType::GASTLID], true);
    }

    private function messages(): array
    {
        return [
            'required' => 'Vul :attribute in.',
            'max' => ':attribute is te lang.',
            'unique' => 'Er bestaat al een lidsoort met deze naam.',
            'numeric' => ':attribute moet een getal zijn.',
            'min' => ':attribute mag niet negatief zijn.',
        ];
    }

    private function attributes(): array
    {
        return ['name' => 'de naam', 'description' => 'de omschrijving', 'price' => 'de prijs'];
    }
}
