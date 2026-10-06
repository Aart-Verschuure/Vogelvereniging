<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Http\Request;

/**
 * Facturen voor de contributie. Het systeem bepaalt het factuurbedrag, zie Contribution::createInvoice().
 */
class ContributionController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->query('jaar') ?: now()->year);
        $status = in_array($request->query('status'), ['open', 'betaald', 'alle'], true) ? $request->query('status') : 'alle';

        $invoices = Contribution::invoices()
            ->with(['member', 'memberType'])
            ->where('year', $year)
            ->when($status === 'open', fn ($q) => $q->where('is_paid', '0'))
            ->when($status === 'betaald', fn ($q) => $q->where('is_paid', '1'))
            ->orderBy('invoice_number')
            ->get();

        return view('invoices.index', [
            'invoices' => $invoices,
            'year' => $year,
            'status' => $status,
            'years' => range(now()->year + 1, now()->year - 4),
            'missingCount' => $this->membersWithoutInvoice($year)->count(),
        ]);
    }

    /**
     * Facturen aanmaken voor alle leden die in dat jaar lid zijn en nog geen factuur hebben.
     */
    public function storeForYear(Request $request)
    {
        $year = (int) $request->validate([
            'year' => ['required', 'integer', 'min:'.(now()->year - 4), 'max:'.(now()->year + 1)],
        ])['year'];

        $created = $this->membersWithoutInvoice($year)
            ->map(fn (Member $member) => Contribution::createInvoice($member, $year))
            ->filter()
            ->count();

        return redirect()->route('invoices.index', ['jaar' => $year])
            ->with('status', $created === 1 ? 'Er is 1 factuur aangemaakt.' : "Er zijn {$created} facturen aangemaakt.");
    }

    /**
     * Een factuur aanmaken voor één lid.
     */
    public function store(Request $request, Member $member)
    {
        $year = (int) $request->validate([
            'year' => ['required', 'integer', 'min:'.(now()->year - 4), 'max:'.(now()->year + 1)],
        ])['year'];

        if ($member->invoices()->where('year', $year)->exists()) {
            return back()->withErrors(['invoice' => $member->fullName().' heeft al een factuur voor '.$year.'.']);
        }

        if (! in_array($member->status, [Member::STATUS_ACTIVE, Member::STATUS_CANCELLED], true)) {
            return back()->withErrors(['invoice' => 'Alleen goedgekeurde leden krijgen een factuur.']);
        }

        $invoice = Contribution::createInvoice($member, $year);

        if (! $invoice) {
            return back()->withErrors(['invoice' => $member->fullName().' is in '.$year.' geen lid, er is geen factuur aangemaakt.']);
        }

        return redirect()->route('invoices.show', $invoice)->with('status', 'Factuur '.$invoice->invoice_number.' is aangemaakt.');
    }

    /**
     * De factuur, op te slaan als PDF via de printfunctie van de browser.
     */
    public function show(Contribution $contribution)
    {
        abort_unless($contribution->amount > 0, 404);

        return view('invoices.show', [
            'invoice' => $contribution->load(['member.address', 'memberType']),
        ]);
    }

    public function markPaid(Contribution $contribution)
    {
        $contribution->update(['is_paid' => '1']);

        return back()->with('status', 'Factuur '.$contribution->invoice_number.' is gemarkeerd als betaald.');
    }

    /**
     * Goedgekeurde (en afgemelde) leden die in het jaar lid zijn en nog geen factuur hebben.
     */
    private function membersWithoutInvoice(int $year)
    {
        return Member::with('memberType')
            ->whereIn('status', [Member::STATUS_ACTIVE, Member::STATUS_CANCELLED])
            ->whereDoesntHave('invoices', fn ($q) => $q->where('year', $year))
            ->get()
            ->filter(fn (Member $member) => invoice_months($member, $year) > 0);
    }
}
