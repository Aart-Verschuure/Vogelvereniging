<?php

use App\Models\User;

test('registration screen can be rendered when there are no accounts', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('the first user can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration is closed once an account exists', function () {
    User::factory()->create();

    $this->get('/register')->assertRedirect(route('login'));

    $this->post('/register', [
        'name' => 'Indringer',
        'email' => 'indringer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('login'));

    $this->assertGuest();
    expect(User::count())->toBe(1);
});
