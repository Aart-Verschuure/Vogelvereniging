<?php

use App\Models\User;

test('een beheerder kan een nieuw account toevoegen', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [
            'name' => 'Tweede Beheerder',
            'email' => 'tweede@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'tweede@example.com')->exists())->toBeTrue();
});

test('een gast kan geen accounts toevoegen', function () {
    $this->post(route('admin.users.store'), [
        'name' => 'Indringer',
        'email' => 'indringer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('login'));

    expect(User::count())->toBe(0);
});

test('de beheerderspagina is bereikbaar', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Beheerder toevoegen');
});

test('een beheerder kan een ander account verwijderen', function () {
    $admin = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($admin)->delete(route('admin.users.destroy', $other));

    expect($other->fresh())->toBeNull();
});

test('je kan je eigen account niet via de beheerderspagina verwijderen', function () {
    $admin = User::factory()->create();
    User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertSessionHasErrors('user');

    expect($admin->fresh())->not->toBeNull();
});

test('het laatste account kan niet via het profiel worden verwijderd', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/profile')
        ->delete('/profile', ['password' => 'password'])
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertAuthenticated();
    expect($user->fresh())->not->toBeNull();
});

test('via de terminal kan een noodaccount worden aangemaakt', function () {
    $this->artisan('beheerder:aanmaken')
        ->expectsQuestion('Naam', 'Nood Beheerder')
        ->expectsQuestion('E-mailadres', 'nood@example.com')
        ->expectsQuestion('Wachtwoord (minimaal 8 tekens)', 'geheim123')
        ->assertSuccessful();

    expect(User::where('email', 'nood@example.com')->exists())->toBeTrue();
});
