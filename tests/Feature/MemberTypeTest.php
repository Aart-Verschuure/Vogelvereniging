<?php

use App\Models\Contribution;
use App\Models\Member;
use App\Models\MemberType;
use App\Models\User;
use Database\Seeders\MemberTypeSeeder;

beforeEach(function () {
    $this->travelTo('2026-06-15');
    $this->seed(MemberTypeSeeder::class);
    $this->actingAs(User::factory()->create());
});

test('een beheerder ziet de lidsoorten met prijzen', function () {
    $this->get(route('member-types.index'))->assertOk()->assertSee('Seniorlid')->assertSee('36,00');
});

test('een lidsoort toevoegen met prijs voor het lopende jaar', function () {
    $this->post(route('member-types.store'), ['name' => 'Erelid', 'description' => 'Ereleden', 'price' => '0'])
        ->assertRedirect(route('member-types.index'));

    expect(MemberType::firstWhere('name', 'Erelid')->priceForYear(2026))->toBe(0.0);
});

test('een lidsoort wijzigen', function () {
    $type = MemberType::factory()->create(['name' => 'Steunlid']);

    $this->put(route('member-types.update', $type), ['name' => 'Donateur', 'description' => 'Steunt de vereniging'])
        ->assertRedirect(route('member-types.index'));

    expect($type->fresh()->name)->toBe('Donateur');
});

test('de namen die het systeem gebruikt kunnen niet worden gewijzigd', function () {
    $type = MemberType::firstWhere('name', MemberType::JEUGDLID);

    $this->put(route('member-types.update', $type), ['name' => 'Junior', 'description' => 'x'])->assertSessionHasErrors('name');
});

test('een lidsoort met leden kan niet worden verwijderd, een lege wel', function () {
    $used = MemberType::factory()->create();
    Member::factory()->create(['member_type_id' => $used->id]);
    $empty = MemberType::factory()->create();

    $this->delete(route('member-types.destroy', $used))->assertSessionHasErrors('member_type');
    $this->delete(route('member-types.destroy', $empty))->assertRedirect();

    expect(MemberType::find($used->id))->not->toBeNull()
        ->and(MemberType::find($empty->id))->toBeNull();
});

test('een prijswijziging geldt alleen voor een komend jaar', function () {
    $senior = MemberType::firstWhere('name', MemberType::SENIORLID);

    $this->post(route('member-types.prices.store', $senior), ['year' => 2026, 'price' => '50'])->assertSessionHasErrors('year');
    $this->post(route('member-types.prices.store', $senior), ['year' => 2027, 'price' => '40'])->assertSessionHasNoErrors();

    $senior->unsetRelation('prices');
    expect($senior->priceForYear(2026))->toBe(36.0)
        ->and($senior->priceForYear(2027))->toBe(40.0)
        ->and($senior->priceForYear(2030))->toBe(40.0)
        ->and($senior->announcedPrice()->price)->toBe(40.0);
});

test('een prijswijziging verandert bestaande facturen niet', function () {
    $senior = MemberType::firstWhere('name', MemberType::SENIORLID);
    $member = Member::factory()->create(['member_type_id' => $senior->id, 'date_of_birth' => '1970-01-01', 'registration_date' => '2025-01-01']);
    $invoice = Contribution::createInvoice($member, 2026);

    $this->post(route('member-types.prices.store', $senior), ['year' => 2027, 'price' => '40']);

    expect($invoice->fresh()->amount)->toBe(36.0)
        ->and(Contribution::createInvoice($member->fresh(), 2027)->amount)->toBe(40.0);
});

test('een aangekondigde prijs staat op de openbare website', function () {
    MemberType::firstWhere('name', MemberType::SENIORLID)->prices()->create(['year' => 2027, 'price' => 40]);

    $this->get('/')->assertSee('Per 1 januari 2027')->assertSee('40,00');
});
