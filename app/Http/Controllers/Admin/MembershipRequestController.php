<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\Member;

/**
 * Verwerking van aanmeldingen (quarantaine) en afmeldingen door de administratie.
 * Het overzicht staat op het dashboard (DashboardController).
 */
class MembershipRequestController extends Controller
{
    public function approve(Member $member)
    {
        abort_unless($member->status === Member::STATUS_PENDING, 404);

        $member->update([
            'status' => Member::STATUS_ACTIVE,
            'is_active' => 1,
            'approved_at' => now(),
        ]);

        // De eerste factuur aanmaken, vanaf de maand dat iemand lid is
        Contribution::createInvoice($member, membership_start_date($member->registration_date)->year);

        return back()->with('status', $member->fullName().' is goedgekeurd als lid.');
    }

    public function reject(Member $member)
    {
        abort_unless($member->status === Member::STATUS_PENDING, 404);

        $member->update(['status' => Member::STATUS_REJECTED]);

        return back()->with('status', 'De aanmelding van '.$member->fullName().' is afgewezen.');
    }

    public function markRefundPaid(Contribution $contribution)
    {
        abort_unless($contribution->amount < 0, 404);

        $contribution->update([
            'is_paid' => '1',
            'Pay_date' => today(),
        ]);

        return back()->with('status', 'De teruggave is gemarkeerd als uitbetaald.');
    }
}
