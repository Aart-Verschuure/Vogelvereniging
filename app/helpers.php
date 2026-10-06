<?php

use App\Models\Member;
use App\Models\MemberType;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

if (! function_exists('membership_change_date')) {
    /**
     * Bepaal per wanneer een aan- of afmelding ingaat.
     *
     * Na opgave duurt het 3 weken, daarna gaat het in per de 1e van de volgende maand.
     * Voorbeeld: opgave 5 maart + 3 weken = 26 maart => per 1 april.
     */
    function membership_change_date(Carbon|string $date): Carbon
    {
        $date = Carbon::parse($date)->addWeeks(3);

        // Valt de datum precies op de 1e van de maand, dan gaat het per die dag in
        if ($date->day === 1) {
            return $date->startOfDay();
        }

        return $date->startOfMonth()->addMonth();
    }
}

if (! function_exists('membership_start_date')) {
    /**
     * Per wanneer iemand daadwerkelijk lid is, op basis van de datum van opgave.
     */
    function membership_start_date(Carbon|string $registrationDate): Carbon
    {
        return membership_change_date($registrationDate);
    }
}

if (! function_exists('membership_end_date')) {
    /**
     * Per wanneer iemand geen lid meer is, op basis van de datum van afmelding.
     * Hiervoor geldt dezelfde regel als bij het lid worden.
     */
    function membership_end_date(Carbon|string $cancellationDate): Carbon
    {
        return membership_change_date($cancellationDate);
    }
}

if (! function_exists('contribution_months')) {
    /**
     * Het aantal maanden vanaf de ingangsdatum t/m december.
     *
     * Bij aanmelden: het aantal maanden waarover contributie betaald moet worden.
     * Bij afmelden: het aantal maanden dat het lid terugkrijgt.
     * Voorbeeld: per 1 april => april t/m december = 9 maanden.
     */
    function contribution_months(Carbon|string $date): int
    {
        return 13 - membership_change_date($date)->month;
    }
}

if (! function_exists('calculate_contribution')) {
    /**
     * Bereken de contributie (of teruggave) op basis van een jaarprijs en de datum van opgave.
     */
    function calculate_contribution(float|int $yearlyPrice, Carbon|string $date): float
    {
        return round($yearlyPrice / 12 * contribution_months($date), 2);
    }
}

if (! function_exists('is_youth_in_year')) {
    /**
     * Is iemand in een bepaald jaar nog jeugdlid?
     *
     * Om jeugdleden tegemoet te komen betalen ze in het jaar dat ze 18 worden nog de contributie
     * voor jeugdlid. Wie op 1 januari 18 wordt, heeft dus een jaar voordeel.
     */
    function is_youth_in_year(Carbon|string $dateOfBirth, int $year): bool
    {
        return $year - Carbon::parse($dateOfBirth)->year <= 18;
    }
}

if (! function_exists('member_type_for_year')) {
    /**
     * De lidsoort waarvoor een lid in een bepaald jaar betaalt.
     * Voor Jeugdlid en Seniorlid bepaalt de leeftijd dit, andere lidsoorten (zoals Gastlid) blijven gelijk.
     */
    function member_type_for_year(Member $member, int $year): MemberType
    {
        if (! $member->memberType->isAgeBased()) {
            return $member->memberType;
        }

        $name = is_youth_in_year($member->date_of_birth, $year) ? MemberType::JEUGDLID : MemberType::SENIORLID;

        return $member->memberType->name === $name
            ? $member->memberType
            : MemberType::where('name', $name)->firstOrFail();
    }
}

if (! function_exists('invoice_months')) {
    /**
     * Het aantal maanden in een jaar waarin iemand lid is, en waarover dus contributie betaald wordt.
     * Telt vanaf de ingangsdatum (opgave + 3 weken, per de 1e van de maand) tot de einddatum na afmelding.
     */
    function invoice_months(Member $member, int $year): int
    {
        $start = $member->registration_date ? membership_start_date($member->registration_date) : null;
        $end = $member->membership_end_date;

        $months = 0;
        for ($month = 1; $month <= 12; $month++) {
            $firstDay = Carbon::create($year, $month, 1);

            if ((! $start || $firstDay->gte($start)) && (! $end || $firstDay->lt($end))) {
                $months++;
            }
        }

        return $months;
    }
}

if (! function_exists('invoice_amount')) {
    /**
     * Het factuurbedrag voor een lid in een bepaald jaar, bepaald door het systeem:
     * de jaarprijs van de lidsoort in dat jaar, naar rato van het aantal maanden lidmaatschap.
     */
    function invoice_amount(Member $member, int $year): float
    {
        $price = member_type_for_year($member, $year)->priceForYear($year) ?? 0;

        return round($price / 12 * invoice_months($member, $year), 2);
    }
}

if (! function_exists('member_contribution')) {
    /**
     * De contributie van een lid in het jaar dat hij lid wordt.
     */
    function member_contribution(Member $member): float
    {
        $registrationDate = $member->registration_date ?? $member->created_at ?? now();

        return invoice_amount($member, membership_start_date($registrationDate)->year);
    }
}

if (! function_exists('notify_administration')) {
    /**
     * Stuur de administratie een melding per e-mail.
     *
     * Als het versturen mislukt, wordt de fout gelogd maar gaat de aan- of afmelding gewoon door:
     * de administratie ziet de openstaande aanmeldingen en afmeldingen ook op het dashboard.
     */
    function notify_administration(Notification $notification): void
    {
        try {
            NotificationFacade::route('mail', config('vereniging.admin_email'))->notify($notification);
        } catch (Throwable $e) {
            report($e);
        }
    }
}

if (! function_exists('member_refund')) {
    /**
     * Bereken hoeveel een lid terugkrijgt bij afmelding: de maanden die nog resteren in het jaar.
     * Dit gaat uit van de factuur van dat jaar, zodat de prijs gebruikt wordt die het lid ook betaald heeft.
     */
    function member_refund(Member $member, Carbon|string $cancellationDate): float
    {
        $endDate = membership_end_date($cancellationDate);
        $invoice = $member->invoices()->where('year', $endDate->year)->first();

        if (! $invoice) {
            return 0.0;
        }

        $invoiceMonths = $invoice->months ?: 12;
        $months = min(contribution_months($cancellationDate), $invoiceMonths);

        return round($invoice->amount / $invoiceMonths * $months, 2);
    }
}
