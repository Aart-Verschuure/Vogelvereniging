<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="w-full max-w-md mx-auto px-6">
        @csrf

        <div class="mb-4">
            <input id="email" 
                   type="email" 
                   name="email" 
                   placeholder="Emailadres" 
                   required 
                   autofocus 
                   autocomplete="username"
                   class="w-full px-4 py-3 text-lg border-none focus:ring-0 text-gray-800 placeholder-gray-500 bg-white" />
        </div>

        <div class="mb-6">
            <input id="password" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="current-password" 
                   placeholder="Wachtwoord"
                   class="w-full px-4 py-3 text-lg border-none focus:ring-0 text-gray-800 placeholder-gray-500 bg-white" />
        </div>

        <x-input-error :messages="$errors->get('email')" class="mt-2" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />

        <div class="flex flex-col sm:flex-row gap-3 items-stretch">
            <button type="submit" 
                    class="flex-1 bg-[#FF7A22] hover:bg-[#000] text-white font-medium text-xl py-3 px-6 transition duration-150 ease-in-out text-center">
                Inloggen
            </button>
            
            <a href="/" 
               class="flex-1 bg-[#FF7A22] hover:bg-[#000] text-white font-medium text-xl py-3 px-6 transition duration-150 ease-in-out text-center">
                Terug
            </a>
        </div>

        @if (Route::has('password.request'))
            <div class="text-center mt-4">
                <a class="underline text-sm text-green-600 hover:text-green-400" href="{{ route('password.request') }}">
                    Wachtwoord vergeten?
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>