<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="w-full max-w-xl mx-auto px-6    ">
        @csrf

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

        <div class="mb-6">
            <select id="function" 
                name="function" 
                required
                class="w-full px-4 py-3 text-lg border-none focus:ring-0 text-gray-800 bg-white">
                <option value="" disabled selected>Kies een lidmaatschap...</option>
                <option value="senior" {{ old('function') == 'senior' ? 'selected' : '' }}>Senior</option>
                <option value="junior" {{ old('function') == 'junior' ? 'selected' : '' }}>Junior</option>
                <option value="gastlid" {{ old('function') == 'gastlid' ? 'selected' : '' }}>Gastlid</option>
            </select>
            <x-input-error :messages="$errors->get('function')" class="mt-2" />
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
