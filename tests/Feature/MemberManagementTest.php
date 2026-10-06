<?php

use App\Models\BreedingNumber;
use App\Models\Member;
use App\Models\MemberType;
use App\Models\User;
use Database\Seeders\MemberTypeSeeder;

beforeEach(function () {
    $this->seed(MemberTypeSeeder::class);
    $this->admin = User::factory()->create();
});

function memberFormData(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Kees',
        'last_name' => 'Kanarie',
        'date_of_birth' => '1975-04-12',
        'email' => 'kees@example.com',
        'phone' => '0612345678',
        'street' => 'Vogelweg',
        'house_number' => '7',
        'postal_code' => '3311 AB',
        'city' => 'Dordrecht',
        'member_type_id' => MemberType::where('name', MemberType::SENIORLID)->value('id'),
        'nbvv_number' => '112233',
        'registration_date' => '2026-03-05',
        'create_invoice' => '1',
    ], $overrides);
}

test('de beheerpaginas zijn alleen bereikbaar na inloggen', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with(['/members', '/members/create', '/member-types', '/facturen']);

test('een beheerder ziet het overzicht van alle leden', function () {
    $members = Member::factory()->count(3)->create(['registration_date' => '2025-01-01']);

    $response = $this->actingAs($this->admin)->get(route('members.index', ['status' => 'alle']))->assertOk();

    foreach ($members as $member) {
        $response->assertSee($member->fullName());
    }
});

test('leden zoeken op naam, lidsoort, status en kweeknummer', function () {
    $this->travelTo('2026-06-15');
    Member::factory()->create([
        'first_name' => 'Jan', 'last_name' => 'Merel', 'registration_date' => '2025-01-01', 'date_of_birth' => '1970-01-01',
        'member_type_id' => MemberType::where('name', MemberType::SENIORLID)->value('id'),
    ]);
    $piet = Member::factory()->create([
        'first_name' => 'Piet', 'last_name' => 'Vink', 'registration_date' => '2025-01-01',
        'member_type_id' => MemberType::where('name', MemberType::GASTLID)->value('id'),
    ]);
    Member::factory()->create(['first_name' => 'Oud', 'last_name' => 'Lid', 'status' => Member::STATUS_CANCELLED, 'membership_end_date' => '2026-01-01']);
    BreedingNumber::create(['member_id' => $piet->id, 'breeding_number' => 'AB12', 'date_of_issue' => '2025-01-01']);

    $this->actingAs($this->admin);

    $this->get(route('members.index', ['naam' => 'jan merel']))->assertSee('Jan Merel')->assertDontSee('Piet Vink');
    $this->get(route('members.index', ['lidsoort' => $piet->member_type_id]))->assertSee('Piet Vink')->assertDontSee('Jan Merel');
    $this->get(route('members.index', ['kweeknummer' => 'AB12']))->assertSee('Piet Vink')->assertDontSee('Jan Merel');
    $this->get(route('members.index', ['status' => 'lid']))->assertSee('Jan Merel')->assertDontSee('Oud Lid');
    $this->get(route('members.index', ['status' => 'geen_lid']))->assertSee('Oud Lid')->assertDontSee('Jan Merel');
});

test('een beheerder voegt een lid toe met adres, NBvV-gegevens en eerste factuur', function () {
    $this->travelTo('2026-03-10');

    $this->actingAs($this->admin)->post(route('members.store'), memberFormData())->assertRedirect();

    $member = Member::firstWhere('email', 'kees@example.com');

    expect($member->status)->toBe(Member::STATUS_ACTIVE)
        ->and($member->nbvv_number)->toBe('112233')
        ->and($member->address->street)->toBe('Vogelweg')
        ->and($member->address->postal_code)->toBe('3311AB')
        // 5 maart + 3 weken => lid per 1 april => 9 maanden van € 36
        ->and($member->invoices()->first()->amount)->toBe(27.0);
});

test('de lidsoort wordt op basis van leeftijd bepaald bij toevoegen', function () {
    $this->travelTo('2026-03-10');

    // Wordt in 2026 18: nog jeugdlid, ook al is Seniorlid gekozen
    $this->actingAs($this->admin)->post(route('members.store'), memberFormData(['date_of_birth' => '2008-01-01']));

    expect(Member::first()->memberType->name)->toBe(MemberType::JEUGDLID);
});

test('een beheerder wijzigt de gegevens van een lid', function () {
    $member = Member::factory()->create(['date_of_birth' => '1975-04-12']);

    $this->actingAs($this->admin)
        ->put(route('members.update', $member), memberFormData(['first_name' => 'Gewijzigd', 'city' => 'Gouda', 'nbvv_number' => $member->nbvv_number]))
        ->assertRedirect(route('members.show', $member));

    $member->refresh();
    expect($member->first_name)->toBe('Gewijzigd')
        ->and($member->address->city)->toBe('Gouda');
});

test('een lid verwijderen na bevestiging is een soft-delete', function () {
    $member = Member::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('members.edit', $member))
        ->assertSee('Weet je zeker dat je '.$member->fullName().' wilt verwijderen?', false);

    $this->delete(route('members.destroy', $member))->assertRedirect(route('members.index'));

    expect(Member::find($member->id))->toBeNull()
        ->and(Member::withTrashed()->find($member->id))->not->toBeNull();
});

test('de formulieren tonen de verplichte velden en de oplichtende knop', function () {
    $this->actingAs($this->admin)->get(route('members.create'))
        ->assertOk()
        ->assertSee('Velden met een', false)
        ->assertSee('requiredForm()', false);

    $this->get(route('membership.apply'))->assertOk()->assertSee('Velden met een', false)->assertSee('requiredForm(', false);
    $this->get(route('membership.cancel'))->assertOk()->assertSee('Velden met een', false)->assertSee('requiredForm()', false);
});

test('de detailpagina van een lid is bereikbaar', function () {
    $member = Member::factory()->create();

    $this->actingAs($this->admin)->get(route('members.show', $member))->assertOk()->assertSee($member->fullName());
});
