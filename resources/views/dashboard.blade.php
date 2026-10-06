@vite(['resources/css/app.css', 'resources/js/app.js'])
@include('layouts.navigation') <div class="relative min-h-[calc(100vh-4rem)] bg-cover bg-center bg-fixed flex flex-col justify-between"
    style="background-image: url('images/dashboard_achtergrond.jpeg');">
    <div class="absolute inset-0 bg-black/20 pointer-events-none"></div>

    <div class="relative z-10 flex-grow px-4 sm:px-6 lg:px-8 py-8 max-w-5xl mx-auto w-full space-y-6">

        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-3 shadow-lg">{{ session('status') }}</div>
        @endif

        {{-- Aanmeldingen in quarantaine --}}
        <section class="bg-white/95 shadow-lg p-6 text-gray-900">
            <h2 class="text-xl font-semibold flex items-center gap-3 mb-1">
                Nieuwe aanmeldingen
                @if ($applications->isNotEmpty())
                    {{-- Signaal voor de administratie: er staat nog iets open --}}
                    <span class="bg-red-600 text-white text-sm font-bold px-2.5 py-0.5 rounded-full">{{ $applications->count() }} te verwerken</span>
                @endif
            </h2>
            <p class="text-sm text-gray-600 mb-4">Deze aanmeldingen staan in quarantaine. Na goedkeuring wordt de contributie voor dit jaar klaargezet.</p>

            @forelse ($applications as $member)
                <div class="border-t border-gray-200 py-4 flex flex-col md:flex-row md:justify-between gap-4">
                    <div class="text-sm space-y-1">
                        <p class="font-semibold text-base">{{ $member->fullName() }} <span class="font-normal text-gray-500">— {{ $member->memberType->name }}</span></p>
                        <p>{{ $member->email }} @if ($member->phone) · {{ $member->phone }} @endif</p>
                        <p>{{ $member->address->street }} {{ $member->address->house_number }}, {{ $member->address->postal_code }} {{ $member->address->city }}</p>
                        <p>Geboren: {{ $member->date_of_birth->format('d-m-Y') }} · NBvV-nummer: {{ $member->nbvv_number ?? '-' }}</p>
                        <p>Opgave: {{ $member->registration_date->format('d-m-Y') }} · Lid per: {{ membership_start_date($member->registration_date)->format('d-m-Y') }}
                           · Contributie: € {{ number_format(member_contribution($member), 2, ',', '.') }}</p>
                        <p class="text-gray-500">Ondertekend door {{ $member->signature_name }} op {{ $member->signed_at?->format('d-m-Y H:i') }} (IP {{ $member->signature_ip }})</p>
                    </div>
                    <div class="flex gap-2 items-start">
                        <form method="POST" action="{{ route('admin.membership-requests.approve', $member) }}">
                            @csrf
                            <button class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 text-sm font-semibold">Goedkeuren</button>
                        </form>
                        <form method="POST" action="{{ route('admin.membership-requests.reject', $member) }}"
                              onsubmit="return confirm('Weet je zeker dat je deze aanmelding wilt afwijzen?')">
                            @csrf
                            <button class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 text-sm font-semibold">Afwijzen</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">Er zijn geen nieuwe aanmeldingen.</p>
            @endforelse
        </section>

        {{-- Terugbetalingen na afmelding --}}
        <section class="bg-white/95 shadow-lg p-6 text-gray-900">
            <h2 class="text-xl font-semibold flex items-center gap-3 mb-4">
                Nog uit te betalen na afmelding
                @if ($refunds->isNotEmpty())
                    <span class="bg-red-600 text-white text-sm font-bold px-2.5 py-0.5 rounded-full">{{ $refunds->count() }} te verwerken</span>
                @endif
            </h2>

            @forelse ($refunds as $refund)
                <div class="border-t border-gray-200 py-3 flex justify-between items-center gap-4 text-sm">
                    <p>
                        <span class="font-semibold">{{ $refund->member->fullName() }}</span>
                        — € {{ number_format(abs($refund->amount), 2, ',', '.') }}
                        <span class="text-gray-500">(geen lid meer per {{ $refund->member->membership_end_date?->format('d-m-Y') }})</span>
                    </p>
                    <form method="POST" action="{{ route('admin.refunds.paid', $refund) }}">
                        @csrf
                        <button class="bg-[#7d848c] hover:bg-gray-600 text-white px-4 py-2 text-sm font-semibold">Uitbetaald</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500">Er staan geen terugbetalingen open.</p>
            @endforelse
        </section>

        {{-- Recente afmeldingen --}}
        <section class="bg-white/95 shadow-lg p-6 text-gray-900">
            <h2 class="text-xl font-semibold mb-4">Recente afmeldingen</h2>

            @forelse ($cancellations as $member)
                <div class="border-t border-gray-200 py-3 text-sm">
                    <p><span class="font-semibold">{{ $member->fullName() }}</span> ({{ $member->memberType->name }})
                       — afgemeld op {{ $member->cancellation_requested_at?->format('d-m-Y') }}, geen lid meer per {{ $member->membership_end_date?->format('d-m-Y') }}</p>
                    @if ($member->cancellation_reason)
                        <p class="text-gray-500">Reden: {{ $member->cancellation_reason }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">Er zijn nog geen afmeldingen.</p>
            @endforelse
        </section>

        {{-- Ledenoverzicht: wie is er lid en wie niet --}}
        @php($currentCount = $members->filter->isCurrentMember()->count())
        <section class="bg-white/95 shadow-lg p-6 text-gray-900" x-data="{ filter: 'lid', search: '' }">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
                <h2 class="text-xl font-semibold">
                    Ledenoverzicht
                    <a href="{{ route('members.index') }}" class="ms-3 text-sm font-normal text-blue-700 hover:underline">Leden beheren &rarr;</a>
                </h2>

                <div class="flex flex-wrap gap-2 items-center">
                    <button type="button" @click="filter = 'lid'" :class="filter === 'lid' ? 'bg-[#7d848c] text-white' : 'bg-gray-200 text-gray-800'" class="px-3 py-1.5 text-sm font-semibold">
                        Lid ({{ $currentCount }})
                    </button>
                    <button type="button" @click="filter = 'geen'" :class="filter === 'geen' ? 'bg-[#7d848c] text-white' : 'bg-gray-200 text-gray-800'" class="px-3 py-1.5 text-sm font-semibold">
                        Geen Lid ({{ $members->count() - $currentCount }})
                    </button>
                    <button type="button" @click="filter = 'alle'" :class="filter === 'alle' ? 'bg-[#7d848c] text-white' : 'bg-gray-200 text-gray-800'" class="px-3 py-1.5 text-sm font-semibold">
                        Alle ({{ $members->count() }})
                    </button>
                    <input type="search" x-model="search" placeholder="Zoek op naam..." class="border-gray-300 rounded-md shadow-sm text-sm py-1.5">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="border-b-2 border-gray-300 text-gray-600">
                        <tr>
                            <th class="py-2 pr-4 font-semibold">Naam</th>
                            <th class="py-2 pr-4 font-semibold">Type</th>
                            <th class="py-2 pr-4 font-semibold">E-mail</th>
                            <th class="py-2 pr-4 font-semibold">NBvV-nummer</th>
                            <th class="py-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            @php($isMember = $member->isCurrentMember())
                            <tr class="border-b border-gray-200"
                                x-show="(filter === 'alle' || filter === '{{ $isMember ? 'lid' : 'geen' }}')
                                        && @js(strtolower($member->fullName())).includes(search.toLowerCase())">
                                <td class="py-2 pr-4 font-medium">{{ $member->fullName() }}</td>
                                <td class="py-2 pr-4">{{ $member->memberType?->name }}</td>
                                <td class="py-2 pr-4">{{ $member->email }}</td>
                                <td class="py-2 pr-4">{{ $member->nbvv_number ?? '-' }}</td>
                                <td class="py-2">
                                    <span @class([
                                        'inline-block px-2 py-0.5 text-xs font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $isMember,
                                        'bg-yellow-100 text-yellow-800' => ! $isMember && in_array($member->status, [\App\Models\Member::STATUS_PENDING, \App\Models\Member::STATUS_ACTIVE]),
                                        'bg-gray-200 text-gray-700' => ! $isMember && ! in_array($member->status, [\App\Models\Member::STATUS_PENDING, \App\Models\Member::STATUS_ACTIVE]),
                                    ])>{{ $member->statusLabel() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-3 text-gray-500">Er zijn nog geen leden.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="relative z-10 w-full bg-[#f3b05a] text-gray-800 py-2 text-center text-xs font-medium tracking-wide shadow_inner">
        &copy; - Made by Aart Verschuure
    </div>
</div>
