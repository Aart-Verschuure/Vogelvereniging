<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Beheerders</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif
        <x-input-error :messages="$errors->get('user')" />

        <section class="bg-white dark:bg-gray-800 shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Accounts ({{ $users->count() }})</h3>

            @foreach ($users as $user)
                <div class="border-t border-gray-200 dark:border-gray-700 py-3 flex justify-between items-center gap-4 text-sm text-gray-900 dark:text-gray-100">
                    <p>
                        <span class="font-semibold">{{ $user->name }}</span>
                        <span class="text-gray-500">— {{ $user->email }}</span>
                        @if ($user->is(auth()->user()))
                            <span class="text-gray-500">(jij)</span>
                        @endif
                    </p>
                    @if ($users->count() > 1 && ! $user->is(auth()->user()))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Weet je zeker dat je het account van {{ $user->name }} wilt verwijderen?')">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 text-sm font-semibold">Verwijderen</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </section>

        <section class="bg-white dark:bg-gray-800 shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Beheerder toevoegen</h3>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4 max-w-md">
                @csrf

                <div>
                    <x-input-label for="name" value="Naam" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="email" value="E-mailadres" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password" value="Wachtwoord" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Wachtwoord bevestigen" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                </div>

                <x-primary-button>Account aanmaken</x-primary-button>
            </form>
        </section>
    </div>
</x-app-layout>
