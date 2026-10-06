<?php

use App\Models\Contribution;
use App\Models\Member;
use App\Models\MemberType;
use App\Models\User;
use Database\Seeders\MemberTypeSeeder;

beforeEach(function () {
    $this->travelTo('2026-06-15');
    $this->seed(MemberTypeSeeder::class);
});

function memberOfType(string $type, array $attributes = []): Member
{
    return Member::factory()->create(array_merge([
        'member_type_id' => MemberType::where('name', $type)->value('id'),
        'date_of_birth' => '1970-01-01',
        'registration_date' => '2025-01-01',
    ], $attributes));
}

test('een heel jaar lid betaalt de volledige jaarprijs', function () {
    $invoice = Contribution::createInvoice(memberOfType(MemberType::SENIORLID), 2026);

    expect($invoice->amount)->toBe(36.0)
        ->and($invoice->months)->toBe(12)
        ->and($invoice->invoice_number)->toBe('2026-0001');
});

test('in het jaar van aanmelding wordt pas betaald vanaf de maand na 3 weken', function () {
    // 5 maart + 3 weken = 26 maart => lid per 1 april => 9 maanden
    $invoice = Contribution::createInvoice(memberOfType(MemberType::SENIORLID, ['registration_date' => '2026-03-05']), 2026);

    expect($invoice->months)->toBe(9)
        ->and($invoice->amount)->toBe(27.0)
        ->and($invoice->Pay_date->toDateString())->toBe('2026-04-01');
});

test('in het jaar dat iemand 18 wordt betaalt hij nog jeugdlid, zonder correctie', function () {
    // Wordt 18 op 1 januari 2026: heel 2026 nog jeugdlid
    $member = memberOfType(MemberType::JEUGDLID, ['date_of_birth' => '2008-01-01']);

    $invoice2026 = Contribution::createInvoice($member, 2026);
    expect($invoice2026->memberType->name)->toBe(MemberType::JEUGDLID)
        ->and($invoice2026->amount)->toBe(18.0);

    // In 2027 is hij seniorlid
    $invoice2027 = Contribution::createInvoice($member->fresh(), 2027);
    expect($invoice2027->memberType->name)->toBe(MemberType::SENIORLID)
        ->and($invoice2027->amount)->toBe(36.0)
        ->and($member->fresh()->memberType->name)->toBe(MemberType::SENIORLID);
});

test('een gastlid blijft gastlid, ongeacht leeftijd', function () {
    $invoice = Contribution::createInvoice(memberOfType(MemberType::GASTLID, ['date_of_birth' => '2015-01-01']), 2026);

    expect($invoice->memberType->name)->toBe(MemberType::GASTLID);
});

test('geen dubbele factuur, en geen factuur als iemand dat jaar geen lid is', function () {
    $member = memberOfType(MemberType::SENIORLID);
    Contribution::createInvoice($member, 2026);

    expect(Contribution::createInvoice($member, 2026))->toBeNull()
        ->and(Contribution::createInvoice(memberOfType(MemberType::SENIORLID, ['registration_date' => '2026-12-20']), 2026))->toBeNull();
});

test('facturen aanmaken voor alle leden van een jaar', function () {
    memberOfType(MemberType::SENIORLID);
    memberOfType(MemberType::GASTLID);
    Member::factory()->pending()->create();
    memberOfType(MemberType::SENIORLID, ['status' => Member::STATUS_CANCELLED, 'membership_end_date' => '2026-01-01']);

    $this->actingAs(User::factory()->create())
        ->post(route('invoices.store-for-year'), ['year' => 2026])
        ->assertRedirect();

    // Alleen de twee leden die in 2026 lid zijn
    expect(Contribution::invoices()->where('year', 2026)->count())->toBe(2);
});

test('een factuur aanmaken, bekijken en als betaald markeren', function () {
    $member = memberOfType(MemberType::SENIORLID);
    $this->actingAs(User::factory()->create());

    $this->post(route('members.invoices.store', $member), ['year' => 2026])->assertRedirect();
    $invoice = $member->invoices()->first();

    $this->get(route('invoices.show', $invoice))->assertOk()->assertSee($invoice->invoice_number)->assertSee('36,00');
    $this->get(route('invoices.index'))->assertOk()->assertSee($invoice->invoice_number);

    $this->post(route('invoices.paid', $invoice));
    expect($invoice->fresh()->isPaid())->toBeTrue();
});

test('bij afmelden met een openstaande factuur wordt de factuur verlaagd', function () {
    $this->travelTo('2026-03-05');
    $member = memberOfType(MemberType::SENIORLID, ['email' => 'a@example.com']);
    $invoice = Contribution::createInvoice($member, 2026);

    $this->post(route('membership.cancel.store'), ['email' => 'a@example.com', 'date_of_birth' => '1970-01-01', 'confirmation' => '1']);

    // Lid t/m maart: 3 maanden van € 36 = € 9, geen teruggave nodig
    expect($invoice->fresh()->amount)->toBe(9.0)
        ->and($invoice->fresh()->months)->toBe(3)
        ->and(Contribution::where('amount', '<', 0)->count())->toBe(0);
});
