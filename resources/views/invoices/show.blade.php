@php
    $money = fn ($amount) => '€ '.number_format($amount, 2, ',', '.');
    $member = $invoice->member;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Factuur {{ $invoice->invoice_number }}</h2>
            <div class="flex gap-3">
                @if ($member)
                    <a href="{{ route('members.show', $member) }}" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:underline">Naar het lid</a>
                @endif
                <button type="button" onclick="window.print()" class="bg-[#7d848c] hover:bg-gray-600 text-white px-4 py-2 text-sm font-semibold">Afdrukken / PDF</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 print:p-0">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3 mb-6 print:hidden">{{ session('status') }}</div>
        @endif

        <article class="bg-white shadow p-10 text-gray-900 text-sm space-y-8 print:shadow-none">
            <header class="flex justify-between gap-6">
                <div>
                    <p class="text-2xl font-bold">Factuur</p>
                    <p class="mt-1">Factuurnummer: {{ $invoice->invoice_number }}</p>
                    <p>Factuurdatum: {{ $invoice->created_at->format('d-m-Y') }}</p>
                    <p>Te betalen vanaf: {{ $invoice->Pay_date?->format('d-m-Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold">Vogelvereniging 't Fratertje</p>
                    <p>Noorderelsweg 4A</p>
                    <p>3329 KH Dordrecht</p>
                    <p>contact@vogelvereniging.nl</p>
                </div>
            </header>

            <section>
                <p class="font-semibold">{{ $member?->fullName() }}</p>
                @if ($member?->address)
                    <p>{{ $member->address->street }} {{ $member->address->house_number }}</p>
                    <p>{{ $member->address->postal_code }} {{ $member->address->city }}</p>
                @endif
                @if ($member?->nbvv_number)
                    <p>NBvV-nummer: {{ $member->nbvv_number }}</p>
                @endif
            </section>

            <table class="w-full text-left">
                <thead class="border-b-2 border-gray-300">
                    <tr>
                        <th class="py-2 font-semibold">Omschrijving</th>
                        <th class="py-2 font-semibold text-right">Bedrag</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200">
                        <td class="py-3">
                            Contributie {{ $invoice->year }} — {{ $invoice->memberType?->name }}<br>
                            <span class="text-gray-600">
                                @if ($invoice->months === 12)
                                    Heel {{ $invoice->year }}: {{ $money($invoice->yearly_price) }} per jaar
                                @else
                                    {{ $invoice->months }} {{ $invoice->months === 1 ? 'maand' : 'maanden' }} lidmaatschap,
                                    naar rato van {{ $money($invoice->yearly_price) }} per jaar
                                @endif
                            </span>
                        </td>
                        <td class="py-3 text-right align-top">{{ $money($invoice->amount) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-bold">
                        <td class="py-3">Totaal te betalen</td>
                        <td class="py-3 text-right">{{ $money($invoice->amount) }}</td>
                    </tr>
                </tfoot>
            </table>

            <section class="text-gray-600 space-y-1">
                <p>Vermeld bij betaling het factuurnummer {{ $invoice->invoice_number }}.</p>
                @if ($invoice->isPaid())
                    <p class="font-semibold text-green-700">Deze factuur is betaald.</p>
                @endif
            </section>
        </article>

        @unless ($invoice->isPaid())
            <form method="POST" action="{{ route('invoices.paid', $invoice) }}" class="mt-6 print:hidden">
                @csrf
                <x-primary-button>Markeren als betaald</x-primary-button>
            </form>
        @endunless
    </div>
</x-app-layout>
