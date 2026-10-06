<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Adress;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\MemberType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Ledenbeheer door de beheerders.
 */
class MemberController extends Controller
{
    // Filters voor de status in het ledenoverzicht
    public const STATUS_FILTERS = [
        'lid' => 'Actief lid',
        'geen_lid' => 'Geen lid',
        'quarantaine' => 'In quarantaine',
        'alle' => 'Alle',
    ];

    /**
     * Overzicht van alle leden, te doorzoeken op naam, lidsoort, actieve status of kweeknummer.
     */
    public function index(Request $request)
    {
        $filters = [
            'naam' => trim((string) $request->query('naam')),
            'lidsoort' => $request->query('lidsoort'),
            'status' => array_key_exists($request->query('status'), self::STATUS_FILTERS) ? $request->query('status') : 'lid',
            'kweeknummer' => trim((string) $request->query('kweeknummer')),
        ];

        $members = Member::with(['memberType', 'address', 'breedingNumbers'])
            // Elk woord moet in de voor- of achternaam voorkomen, zodat "Jan Jansen" ook werkt
            ->when($filters['naam'] !== '', function ($query) use ($filters) {
                foreach (preg_split('/\s+/', $filters['naam']) as $word) {
                    $query->where(fn ($q) => $q->where('first_name', 'like', "%{$word}%")->orWhere('last_name', 'like', "%{$word}%"));
                }
            })
            ->when($filters['lidsoort'], fn ($query, $typeId) => $query->where('member_type_id', $typeId))
            ->when($filters['kweeknummer'] !== '', fn ($query) => $query->whereHas(
                'breedingNumbers',
                fn ($q) => $q->where('breeding_number', 'like', "%{$filters['kweeknummer']}%")
            ))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            // De actieve status hangt af van de ingangs- en einddatum, daarom filteren we die hier
            ->filter(fn (Member $member) => match ($filters['status']) {
                'lid' => $member->isCurrentMember(),
                'geen_lid' => ! $member->isCurrentMember() && $member->status !== Member::STATUS_PENDING,
                'quarantaine' => $member->status === Member::STATUS_PENDING,
                default => true,
            });

        return view('members.index', [
            'members' => $members,
            'filters' => $filters,
            'memberTypes' => MemberType::orderBy('name')->get(),
            'statusFilters' => self::STATUS_FILTERS,
        ]);
    }

    public function create()
    {
        return view('members.create', [
            'member' => new Member(['registration_date' => today()]),
            'memberTypes' => MemberType::orderBy('name')->get(),
        ]);
    }

    /**
     * Een beheerder voegt een lid toe. Dat lid is direct goedgekeurd (geen quarantaine).
     */
    public function store(MemberRequest $request)
    {
        $data = $request->validated();

        $member = DB::transaction(function () use ($data) {
            $address = Adress::create($this->addressData($data));

            $member = Member::create([
                ...$this->memberData($data),
                'address_id' => $address->id,
                'status' => Member::STATUS_ACTIVE,
                'is_active' => 1,
                'approved_at' => now(),
            ]);

            $this->applyAgeRule($member);

            if (! empty($data['create_invoice'])) {
                Contribution::createInvoice($member, membership_start_date($member->registration_date)->year);
            }

            return $member;
        });

        return redirect()->route('members.show', $member)->with('status', $member->fullName().' is toegevoegd als lid.');
    }

    public function show(Member $member)
    {
        $member->load(['memberType', 'address', 'breedingNumbers', 'contributions' => fn ($q) => $q->orderByDesc('year')->orderBy('id')]);

        return view('members.show', [
            'member' => $member,
            'invoiceYears' => [now()->year, now()->year + 1],
        ]);
    }

    public function edit(Member $member)
    {
        return view('members.edit', [
            'member' => $member->load('address'),
            'memberTypes' => MemberType::orderBy('name')->get(),
        ]);
    }

    public function update(MemberRequest $request, Member $member)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $member) {
            if ($member->address) {
                $member->address->update($this->addressData($data));
            } else {
                $member->update(['address_id' => Adress::create($this->addressData($data))->id]);
            }

            $member->update($this->memberData($data));
            $this->applyAgeRule($member);
        });

        return redirect()->route('members.show', $member)->with('status', 'De gegevens van '.$member->fullName().' zijn opgeslagen.');
    }

    /**
     * Een lid verwijderen (soft-delete): het lid verdwijnt uit de overzichten, maar de gegevens
     * en facturen blijven bewaard.
     */
    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('status', $member->fullName().' is verwijderd.');
    }

    /**
     * Jeugdlid of Seniorlid hangt af van de leeftijd: in het jaar dat iemand 18 wordt is hij nog jeugdlid.
     */
    private function applyAgeRule(Member $member): void
    {
        $member->load('memberType');
        $type = member_type_for_year($member, now()->year);

        if ($type->id !== $member->member_type_id) {
            $member->update(['member_type_id' => $type->id]);
        }
    }

    private function addressData(array $data): array
    {
        return [
            'street' => $data['street'],
            'house_number' => $data['house_number'],
            'postal_code' => strtoupper(str_replace(' ', '', $data['postal_code'])),
            'city' => $data['city'],
        ];
    }

    private function memberData(array $data): array
    {
        return [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'member_type_id' => $data['member_type_id'],
            'nbvv_number' => $data['nbvv_number'] ?? null,
            'registration_date' => $data['registration_date'],
        ];
    }
}
