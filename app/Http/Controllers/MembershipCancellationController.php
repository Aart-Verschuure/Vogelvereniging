<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipCancellationRequest;
use App\Models\Contribution;
use App\Models\Member;
use App\Notifications\MembershipCancelled;
use Illuminate\Support\Facades\DB;

/**
 * Afmelding als lid via het digitale formulier op de openbare website.
 */
class MembershipCancellationController extends Controller
{
    public function create()
    {
        return view('membership.cancel', [
            'endDate' => membership_end_date(today()),
            'months' => contribution_months(today()),
        ]);
    }

    public function store(StoreMembershipCancellationRequest $request)
    {
        $data = $request->validated();

        $member = Member::with('memberType')
            ->where('email', $data['email'])
            ->whereDate('date_of_birth', $data['date_of_birth'])
            ->whereIn('status', [Member::STATUS_ACTIVE, Member::STATUS_PENDING])
            ->first();

        if (! $member) {
            return back()->withInput()->withErrors([
                'email' => 'We kunnen geen lid vinden met dit e-mailadres en deze geboortedatum.',
            ]);
        }

        $endDate = membership_end_date(today());

        // Het bedrag voor de resterende maanden, op basis van de factuur van dat jaar
        $refund = member_refund($member, today());
        $invoice = $member->invoices()->where('year', $endDate->year)->first();
        $invoiceIsPaid = $invoice?->isPaid() ?? false;

        DB::transaction(function () use ($member, $data, $endDate, $refund, $invoice, $invoiceIsPaid) {
            $member->update([
                'status' => Member::STATUS_CANCELLED,
                'is_active' => 0,
                'cancellation_requested_at' => now(),
                'membership_end_date' => $endDate,
                'cancellation_reason' => $data['cancellation_reason'] ?? null,
            ]);

            if ($refund <= 0) {
                return;
            }

            if ($invoiceIsPaid) {
                // Al betaald: de teruggave wordt opgeslagen als negatieve contributie die nog uitbetaald moet worden
                Contribution::create([
                    'member_id' => $member->id,
                    'year' => $endDate->year,
                    'member_type_id' => $invoice->member_type_id,
                    'amount' => -$refund,
                    'is_paid' => '0',
                    'Pay_date' => $endDate,
                ]);
            } else {
                // Nog niet betaald: de factuur wordt verlaagd tot de maanden dat iemand nog lid is
                $invoice->update([
                    'amount' => round($invoice->amount - $refund, 2),
                    'months' => invoice_months($member, $endDate->year),
                ]);
            }
        });

        notify_administration(new MembershipCancelled($member, $refund, $invoiceIsPaid));

        return redirect()->route('membership.cancel')->with('success', [
            'name' => $member->first_name,
            'end_date' => $endDate->format('d-m-Y'),
            'refund' => $invoiceIsPaid ? $refund : 0.0,
            'credited' => $invoiceIsPaid ? 0.0 : $refund,
        ]);
    }
}
