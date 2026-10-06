<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Beheerdersaccounts: alleen een ingelogde beheerder kan nieuwe accounts toevoegen.
 */
class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'required' => 'Vul :attribute in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'email.unique' => 'Er bestaat al een account met dit e-mailadres.',
            'email.lowercase' => 'Gebruik alleen kleine letters in het e-mailadres.',
            'password.confirmed' => 'De wachtwoorden komen niet overeen.',
            'password.min' => 'Het wachtwoord moet minimaal :min tekens bevatten.',
        ], [
            'name' => 'een naam',
            'email' => 'een e-mailadres',
            'password' => 'een wachtwoord',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('status', 'Het account voor '.$data['name'].' is aangemaakt.');
    }

    public function destroy(Request $request, User $user)
    {
        // Er moet altijd minstens één account overblijven, anders kan niemand meer inloggen
        if (User::count() <= 1) {
            return back()->withErrors(['user' => 'Het laatste account kan niet worden verwijderd.']);
        }

        // Je eigen account verwijder je via je profiel, zodat daar om je wachtwoord wordt gevraagd
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Je eigen account verwijder je via je profiel.']);
        }

        $user->delete();

        return back()->with('status', 'Het account van '.$user->name.' is verwijderd.');
    }
}
