<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Lid toevoegen</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('members.store') }}" class="bg-white dark:bg-gray-800 shadow p-6 space-y-6"
              x-data="requiredForm()" @input="check" @change="check">
            @include('members.partials.form')

            <label class="flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                <input type="hidden" name="create_invoice" value="0">
                <input type="checkbox" name="create_invoice" value="1" @checked(old('create_invoice', true)) class="rounded border-gray-300">
                Direct de eerste factuur aanmaken
            </label>

            <div class="flex gap-3">
                <x-highlight-button>Lid toevoegen</x-highlight-button>
                <a href="{{ route('members.index') }}" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">Annuleren</a>
            </div>
        </form>
    </div>
</x-app-layout>
