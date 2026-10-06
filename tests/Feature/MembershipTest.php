<?php

use App\Models\Contribution;
use App\Models\Member;
use App\Models\User;
use App\Notifications\MembershipCancelled;
use App\Notifications\NewMembershipApplication;
use Database\Seeders\MemberTypeSeeder;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(MemberTypeSeeder::class);
    Notification::fake();
});

function applicationData(array $overrides = []): array
{
    return array_merge([
        'first_name' => 'Jan',
        'last_name' => 'Jansen',
        'date_of_birth' => '1980-05-10',
        'email' => 'jan@example.com',
        'phone' => '0612345678',
        'street' => 'Dorpsstraat',
        'house_number' => '1',
        'postal_code' => '3329 KH',
        'city' => 'Dordrecht',
        'nbvv_member' => '1',
        'nbvv_number' => '654321',
        'agreement' => '1',
        'signature_name' => 'Jan Jansen',
    ], $overrides);
}

test('de aanmeldpagina is bereikbaar', function () {
    $this->get(route('membership.apply'))->assertOk()->assertSee('Lid worden');
});

test('een aanmelding komt in quarantaine en de administratie krijgt een melding', function () {
    $this->travelTo('2026-03-05');

    $this->post(route('membership.apply.store'), applicationData())
        ->assertRedirect(route('membership.apply'))
        ->assertSessionHas('success');

    $member = Member::firstWhere('email', 'jan@example.com');

    expect($member->status)->toBe(Member::STATUS_PENDING)
        ->and($member->is_active)->toBe(0)
        ->and($member->registration_date->toDateString())->toBe('2026-03-05')
        ->and($member->memberType->name)->toBe('Seniorlid')
        ->and($member->signature_name)->toBe('Jan Jansen')
        ->and($member->signed_at)->not->toBeNull()
        ->and($member->address->city)->toBe('Dordrecht')
        ->and(member_contribution($member))->toBe(27.0);

    Notification::assertSentTo(new AnonymousNotifiable, NewMembershipApplication::class);
});

test('zonder vinkje kan je je niet aanmelden', function () {
    $this->post(route('membership.apply.store'), applicationData(['agreement' => null]))
        ->assertSessionHasErrors('agreement');

    expect(Member::count())->toBe(0);
});

test('zonder NBvV-lidmaatschap word je gastlid', function () {
    $this->post(route('membership.apply.store'), applicationData(['nbvv_member' => '0', 'nbvv_number' => null]));

    expect(Member::first()->memberType->name)->toBe('Gastlid');
});

test('jonger dan 18 wordt jeugdlid', function () {
    $this->post(route('membership.apply.store'), applicationData(['date_of_birth' => now()->subYears(12)->toDateString()]));

    expect(Member::first()->memberType->name)->toBe('Jeugdlid');
});

test('een lid kan zich afmelden en krijgt de resterende maanden terug', function () {
    $this->travelTo('2026-03-05');
    $member = Member::factory()->create(['email' => 'piet@example.com', 'date_of_birth' => '1970-01-01', 'member_type_id' => 2, 'registration_date' => '2025-06-01']);
    Contribution::createInvoice($member, 2026)->update(['is_paid' => '1']);

    $this->post(route('membership.cancel.store'), [
        'email' => 'piet@example.com',
        'date_of_birth' => '1970-01-01',
        'confirmation' => '1',
    ])->assertRedirect(route('membership.cancel'));

    $member->refresh();

    // 5 maart + 3 weken = 26 maart => geen lid meer per 1 april => 9 maanden terug van € 36
    expect($member->status)->toBe(Member::STATUS_CANCELLED)
        ->and($member->membership_end_date->toDateString())->toBe('2026-04-01')
        ->and((float) Contribution::where('member_id', $member->id)->where('amount', '<', 0)->value('amount'))->toBe(-27.0);

    Notification::assertSentTo(new AnonymousNotifiable, MembershipCancelled::class);
});

test('afmelden met onbekende gegevens geeft een foutmelding', function () {
    $this->post(route('membership.cancel.store'), [
        'email' => 'onbekend@example.com',
        'date_of_birth' => '1970-01-01',
        'confirmation' => '1',
    ])->assertSessionHasErrors('email');

    Notification::assertNothingSent();
});

test('de administratie kan een aanmelding goedkeuren', function () {
    $this->travelTo('2026-03-05');
    $member = Member::factory()->pending()->create(['member_type_id' => 2, 'date_of_birth' => '1970-01-01']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.membership-requests.approve', $member))
        ->assertRedirect();

    $member->refresh();

    expect($member->status)->toBe(Member::STATUS_ACTIVE)
        ->and($member->approved_at)->not->toBeNull()
        ->and((float) $member->contributions()->value('amount'))->toBe(27.0);
});

test('het dashboard toont de aanmeldingen direct', function () {
    $members = Member::factory()->pending()->count(2)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('2 te verwerken')
        ->assertSee($members[0]->fullName())
        ->assertSee('Goedkeuren');
});

test('het dashboard toont de terugbetalingen en afmeldingen direct', function () {
    $this->travelTo('2026-03-05');
    $member = Member::factory()->create(['email' => 'piet@example.com', 'date_of_birth' => '1970-01-01', 'member_type_id' => 2, 'registration_date' => '2025-06-01']);
    Contribution::createInvoice($member, 2026)->update(['is_paid' => '1']);

    $this->post(route('membership.cancel.store'), [
        'email' => 'piet@example.com',
        'date_of_birth' => '1970-01-01',
        'confirmation' => '1',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($member->fullName())
        ->assertSee('27,00')
        ->assertSee('Uitbetaald');
});

test('na goedkeuren kom je terug op het dashboard', function () {
    $member = Member::factory()->pending()->create(['member_type_id' => 2]);

    $this->actingAs(User::factory()->create())
        ->from(route('dashboard'))
        ->post(route('admin.membership-requests.approve', $member))
        ->assertRedirect(route('dashboard'));
});

test('de oude aanmeldingenpagina stuurt door naar het dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/aanmeldingen')
        ->assertRedirect('/dashboard');
});

test('wie is er op dit moment lid', function () {
    $this->travelTo('2026-06-15');

    // Goedgekeurd en ingegaan
    expect(Member::factory()->create(['registration_date' => '2026-01-05'])->isCurrentMember())->toBeTrue();

    // Goedgekeurd maar nog niet ingegaan: 10 juni + 3 weken = 1 juli
    $notYet = Member::factory()->create(['registration_date' => '2026-06-10']);
    expect($notYet->isCurrentMember())->toBeFalse()
        ->and($notYet->statusLabel())->toBe('Goedgekeurd, lid per 01-07-2026');

    // Afgemeld, maar nog lid tot de einddatum
    $leaving = Member::factory()->create(['status' => Member::STATUS_CANCELLED, 'membership_end_date' => '2026-07-01']);
    expect($leaving->isCurrentMember())->toBeTrue()
        ->and($leaving->statusLabel())->toBe('Opgezegd, lid tot 01-07-2026');

    // Afgemeld en einddatum voorbij
    $gone = Member::factory()->create(['status' => Member::STATUS_CANCELLED, 'membership_end_date' => '2026-05-01']);
    expect($gone->isCurrentMember())->toBeFalse()
        ->and($gone->statusLabel())->toBe('Geen lid meer sinds 01-05-2026');

    // In quarantaine en afgewezen zijn geen lid
    expect(Member::factory()->pending()->create()->isCurrentMember())->toBeFalse()
        ->and(Member::factory()->create(['status' => Member::STATUS_REJECTED])->isCurrentMember())->toBeFalse();
});

test('het dashboard toont een ledenoverzicht met wie wel en geen lid is', function () {
    $this->travelTo('2026-06-15');
    $active = Member::factory()->create(['registration_date' => '2026-01-05']);
    $gone = Member::factory()->create(['status' => Member::STATUS_CANCELLED, 'membership_end_date' => '2026-05-01']);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ledenoverzicht')
        ->assertSee($active->fullName())
        ->assertSee($gone->fullName())
        ->assertSee('Lid (1)')
        ->assertSee('Geen lid (1)');
});
