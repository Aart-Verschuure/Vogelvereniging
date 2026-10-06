<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Noodoplossing als niemand meer kan inloggen: maak via de terminal een beheerdersaccount aan
Artisan::command('beheerder:aanmaken', function () {
    $name = $this->ask('Naam');
    $email = strtolower($this->ask('E-mailadres'));

    if (User::where('email', $email)->exists()) {
        $this->error('Er bestaat al een account met dit e-mailadres. Gebruik "wachtwoord vergeten" op de inlogpagina.');

        return 1;
    }

    $password = $this->secret('Wachtwoord (minimaal 8 tekens)');

    if (strlen((string) $password) < 8) {
        $this->error('Het wachtwoord moet minimaal 8 tekens bevatten.');

        return 1;
    }

    User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);

    $this->info("Het account voor {$name} is aangemaakt.");
})->purpose('Maak een beheerdersaccount aan (noodoplossing als niemand meer kan inloggen)');
