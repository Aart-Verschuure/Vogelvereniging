<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $member->fullName() }} wijzigen</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <form method="POST" action="{{ route('members.update', $member) }}" class="bg-white dark:bg-gray-800 shadow p-6 space-y-6">
            @method('PUT')
            @include('members.partials.form')

            <div class="flex gap-3">
                <x-primary-button>Opslaan</x-primary-button>
                <a href="{{ route('members.show', $member) }}" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">Annuleren</a>
            </div>
        </form>

        {{-- Verwijderen na bevestiging (soft-delete) --}}
        <section class="bg-white dark:bg-gray-800 shadow p-6 space-y-3" x-data="{ confirming: false }">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lid verwijderen</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Het lid verdwijnt uit de overzichten. De gegevens en facturen blijven bewaard.</p>

            <x-danger-button type="button" x-show="! confirming" @click="confirming = true">Lid verwijderen</x-danger-button>

            <form method="POST" action="{{ route('members.destroy', $member) }}" x-show="confirming" x-cloak class="space-y-3">
                @csrf
                @method('DELETE')
                <p class="text-sm font-semibold text-red-700 dark:text-red-400">Weet je zeker dat je {{ $member->fullName() }} wilt verwijderen?</p>
                <div class="flex gap-3">
                    <x-danger-button>Ja, verwijderen</x-danger-button>
                    <x-secondary-button type="button" @click="confirming = false">Nee, annuleren</x-secondary-button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
