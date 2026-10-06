<?php

use App\Models\Member;
use App\Models\User;
use Database\Seeders\MemberTypeSeeder;

beforeEach(function () {
    $this->travelTo('2026-06-15');
    $this->seed(MemberTypeSeeder::class);
    $this->actingAs(User::factory()->create());
});

test('een kweeknummer registreren bij een actief lid', function () {
    $member = Member::factory()->create(['registration_date' => '2025-01-01']);

    $this->post(route('members.breeding-numbers.store', $member), ['breeding_number' => 'XY-123', 'date_of_issue' => '2026-01-10'])
        ->assertSessionHasNoErrors();

    expect($member->breedingNumbers()->value('breeding_number'))->toBe('XY-123');
});

test('een kweeknummer kan niet aan een niet-actief lid worden gekoppeld', function () {
    $pending = Member::factory()->pending()->create();
    $gone = Member::factory()->create(['status' => Member::STATUS_CANCELLED, 'membership_end_date' => '2026-01-01']);

    foreach ([$pending, $gone] as $member) {
        $this->post(route('members.breeding-numbers.store', $member), ['breeding_number' => 'XY-'.$member->id, 'date_of_issue' => '2026-01-10'])
            ->assertSessionHasErrors('breeding_number');
    }
});

test('een kweeknummer is uniek', function () {
    $a = Member::factory()->create(['registration_date' => '2025-01-01']);
    $b = Member::factory()->create(['registration_date' => '2025-01-01']);

    $this->post(route('members.breeding-numbers.store', $a), ['breeding_number' => 'XY-1', 'date_of_issue' => '2026-01-10']);
    $this->post(route('members.breeding-numbers.store', $b), ['breeding_number' => 'XY-1', 'date_of_issue' => '2026-01-10'])
        ->assertSessionHasErrors('breeding_number');
});
