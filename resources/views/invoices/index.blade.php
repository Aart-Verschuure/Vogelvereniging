@php
    $select = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm';
    $money = fn ($amount) => '€ '.number_format($amount, 2, ',', '.');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Facturen</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 text-gray-900 dark:text-gray-100">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        <div class="flex flex-col md:flex-row md:justify-between gap-4">
            <form method="GET" action="{{ route('invoices.index') }}" class="bg-white dark:bg-gray-800 shadow p-4 flex flex-wrap gap-4 items-end">
                <div>
                    <x-input-label for="jaar" value="Jaar" />
                    <select id="jaar" name="jaar" class="mt-1 block {{ $select }}">
                        @foreach ($years as $option)
                            <option value="{{ $option }}" @selected($option === $year)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="status" value="Status" />
                    <select id="status" name="status" class="mt-1 block {{ $select }}">
                        <option value="alle" @selected($status === 'alle')>Alle</option>
                        <option value="open" @selected($status === 'open')>Open</option>
                        <option value="betaald" @selected($status === 'betaald')>Betaald</option>
                    </select>
                </div>
                <button class="bg-[#7d848c] hover:bg-gray-600 text-white px-4 py-2 text-sm font-semibold">Tonen</button>
            </form>

            <form method="POST" action="{{ route('invoices.store-for-year') }}" class="bg-white dark:bg-gray-800 shadow p-4 flex flex-col gap-2 justify-center">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <p class="text-sm">{{ $missingCount }} {{ $missingCount === 1 ? 'lid heeft' : 'leden hebben' }} nog geen factuur voor {{ $year }}.</p>
                <button @disabled($missingCount === 0) class="bg-[#7d848c] hover:bg-gray-600 disabled:opacity-50 text-white px-4 py-2 text-sm font-semibold">
                    Facturen aanmaken voor {{ $year }}
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow p-4 overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="border-b-2 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="py-2 pr-4 font-semibold">Factuur</th>
                        <th class="py-2 pr-4 font-semibold">Lid</th>
                        <th class="py-2 pr-4 font-semibold">Lidsoort</th>
                        <th class="py-2 pr-4 font-semibold">Maanden</th>
                        <th class="py-2 pr-4 font-semibold text-right">Bedrag</th>
                        <th class="py-2 pr-4 font-semibold">Status</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2 pr-4"><a href="{{ route('invoices.show', $invoice) }}" class="text-blue-700 dark:text-blue-400 hover:underline">{{ $invoice->invoice_number ?? '-' }}</a></td>
                            <td class="py-2 pr-4">
                                @if ($invoice->member)
                                    <a href="{{ route('members.show', $invoice->member) }}" class="hover:underline">{{ $invoice->member->fullName() }}</a>
                                @endif
                            </td>
                            <td class="py-2 pr-4">{{ $invoice->memberType?->name }}</td>
                            <td class="py-2 pr-4">{{ $invoice->months }}</td>
                            <td class="py-2 pr-4 text-right">{{ $money($invoice->amount) }}</td>
                            <td class="py-2 pr-4">
                                @if ($invoice->isPaid())
                                    <span class="text-green-700">Betaald</span>
                                @else
                                    <span class="text-yellow-700">Open</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                @unless ($invoice->isPaid())
                                    <form method="POST" action="{{ route('invoices.paid', $invoice) }}">
                                        @csrf
                                        <button class="text-blue-700 dark:text-blue-400 hover:underline">Betaald</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-3 text-gray-500">Geen facturen gevonden.</td></tr>
                    @endforelse
                </tbody>
                @if ($invoices->isNotEmpty())
                    <tfoot>
                        <tr class="font-semibold">
                            <td colspan="4" class="py-2 pr-4">Totaal</td>
                            <td class="py-2 pr-4 text-right">{{ $money($invoices->sum('amount')) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
