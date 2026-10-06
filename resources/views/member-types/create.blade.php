<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Lidsoort toevoegen</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('member-types.store') }}" class="bg-white dark:bg-gray-800 shadow p-6 space-y-4">
            @csrf

            <div>
                <x-input-label for="name" value="Naam" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="description" value="Omschrijving" />
                <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" required />
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="price" value="Prijs per jaar (€), geldt vanaf {{ now()->year }}" />
                <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-40" :value="old('price')" required />
                <x-input-error :messages="$errors->get('price')" class="mt-1" />
            </div>

            <div class="flex gap-3">
                <x-primary-button>Toevoegen</x-primary-button>
                <a href="{{ route('member-types.index') }}" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">Annuleren</a>
            </div>
        </form>
    </div>
</x-app-layout>
