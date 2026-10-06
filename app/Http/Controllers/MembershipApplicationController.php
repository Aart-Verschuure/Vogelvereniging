<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipApplicationRequest;
use App\Models\Adress;
use App\Models\Member;
use App\Models\MemberType;
use App\Notifications\NewMembershipApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Opgave als lid via het digitale formulier op de openbare website.
 */
class MembershipApplicationController extends Controller
{
    public function create()
    {
        return view('membership.apply', [
            'memberTypes' => MemberType::with('prices')->orderBy('id')->get(),
            'startDate' => membership_start_date(today()),
            'months' => contribution_months(today()),
        ]);
    }

    public function store(StoreMembershipApplicationRequest $request)
    {
        $data = $request->validated();
        $isNbvvMember = $data['nbvv_member'] === '1';
        $memberType = MemberType::forApplicant($isNbvvMember, Carbon::parse($data['date_of_birth']));

        $member = DB::transaction(function () use ($data, $isNbvvMember, $memberType, $request) {
            $address = Adress::create([
                'street' => $data['street'],
                'house_number' => $data['house_number'],
                'postal_code' => strtoupper(str_replace(' ', '', $data['postal_code'])),
                'city' => $data['city'],
            ]);

            // De aanmelding komt in quarantaine (status pending) tot de administratie hem verwerkt
            return Member::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'nbvv_number' => $isNbvvMember ? $data['nbvv_number'] : null,
                'member_type_id' => $memberType->id,
                'address_id' => $address->id,
                'registration_date' => today(),
                'status' => Member::STATUS_PENDING,
                'is_active' => 0,
                'signature_name' => $data['signature_name'],
                'signed_at' => now(),
                'signature_ip' => $request->ip(),
            ]);
        });

        notify_administration(new NewMembershipApplication($member));

        return redirect()->route('membership.apply')->with('success', [
            'name' => $member->first_name,
            'type' => $memberType->name,
            'start_date' => membership_start_date($member->registration_date)->format('d-m-Y'),
            'months' => contribution_months($member->registration_date),
            'contribution' => member_contribution($member),
        ]);
    }
}
