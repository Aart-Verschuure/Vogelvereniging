<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Member;

/**
 * Dashboard voor de beheerders, met direct de aan- en afmeldingen die nog verwerkt moeten worden.
 */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $applications = Member::with(['memberType', 'address'])
            ->where('status', Member::STATUS_PENDING)
            ->orderBy('registration_date')
            ->get();

        $refunds = Contribution::with('member')
            ->where('amount', '<', 0)
            ->where('is_paid', '0')
            ->orderBy('Pay_date')
            ->get();

        return view('dashboard', [
            'applications' => $applications,
            'refunds' => $refunds,
            'cancellations' => Member::with('memberType')
                ->where('status', Member::STATUS_CANCELLED)
                ->latest('cancellation_requested_at')
                ->limit(10)
                ->get(),
            // Signaal voor de administratie: hoeveel er nog verwerkt moet worden
            'openRequests' => $applications->count() + $refunds->count(),
            // Ledenoverzicht: iedereen, zodat in één lijst staat wie wel en geen lid is
            'members' => Member::with('memberType')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ]);
    }
}
