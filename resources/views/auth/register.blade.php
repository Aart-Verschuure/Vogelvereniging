<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="w-full max-w-xl mx-auto px-6    ">
        @csrf

        <h1 class="text-xl font-semibold mb-1">Eerste beheerdersaccount aanmaken</h1>
        <p class="text-sm text-gray-600 mb-4">Er is nog geen account. Het account dat je nu aanmaakt is de eerste beheerder. Daarna is registreren gesloten en kunnen alleen beheerders nieuwe accounts toevoegen.</p>

        <div class="mb-4">
            <input id="name"
                type="text" 
                name="name" 
                value="{{ old('name') }}" 
                placeholder="Naam" 
                required 
                autofocus 
                autocomplete="name"
                class="w-full px-4 py-3 text-lg border-none focus:ring-0 text-gray-800 placeholder-gray-500 bg-white" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mb-4">
            <input id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                placeholder="Email" 
                required 
                autocomplete="username"
                class="w-full px-4 py-3 text-lg border-none focus:ring-0 text-gray-800 placeholder-gray-500 bg-white" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-4">
            <input id="password" 
                type="password" 
                name="password" 
                placeholder="Wachtwoord"
                required 
                autocomplete="new-password"
                class="w-full px-4 py-3 text-lg border-none focus:ring-0 text-gray-800 placeholder-gray-500 bg-white" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mb-4">
            <input id="password_confirmation" 
                type="password" 
                name="password_confirmation" 
                placeholder="Wachtwoord bevestigen"
                required 
                autocomplete="new-password"
                class="w-full px-4 py-3 text-lg border-none focus:ring-0 text-gray-800 placeholder-gray-500 bg-white" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-col sm:flex-row gap-3 items-stretch">
            <button type="submit" 
                    class="flex-1 bg-[#FF7A22] hover:bg-[#d2583d] text-white font-medium text-xl py-3 px-6 transition duration-150 ease-in-out text-center">
                Registreren
            </button>
            
            <a href="/" 
                class="flex-1 bg-[#FF7A22] hover:bg-[#d2583d] text-white font-medium text-xl py-3 px-6 transition duration-150 ease-in-out text-center">
                Terug
            </a>
        </div>
        @if (Route::has('password.request'))
            <div class="text-center mt-4">
                <a class="underline text-sm text-orange-600 hover:text-orange-400" href="{{ route('login') }}">
                    heb je al een account? 
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
