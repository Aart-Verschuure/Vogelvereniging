<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Een factuur voor de contributie (positief bedrag) of een teruggave na afmelding (negatief bedrag).
 */
class Contribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'invoice_number',
        'year',
        'member_type_id',
        'months',
        'yearly_price',
        'amount',
        'is_paid',
        'Pay_date',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'months' => 'integer',
            'yearly_price' => 'float',
            'amount' => 'float',
            'Pay_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function memberType(): BelongsTo
    {
        return $this->belongsTo(MemberType::class)->withTrashed();
    }

    /**
     * Alleen facturen, geen teruggaves.
     */
    public function scopeInvoices(Builder $query): Builder
    {
        return $query->where('amount', '>', 0);
    }

    public function isPaid(): bool
    {
        return $this->is_paid === '1';
    }

    /**
     * Maak de factuur aan voor een lid in een bepaald jaar. Het systeem bepaalt het bedrag.
     * Geeft null terug als er al een factuur is, of als het lid in dat jaar geen lid is.
     */
    public static function createInvoice(Member $member, int $year): ?self
    {
        $member->loadMissing('memberType');

        if ($member->invoices()->where('year', $year)->exists()) {
            return null;
        }

        $months = invoice_months($member, $year);
        if ($months === 0) {
            return null;
        }

        $memberType = member_type_for_year($member, $year);

        // Een jeugdlid dat in een nieuw jaar seniorlid wordt, krijgt ook die lidsoort
        if ($year >= now()->year && $member->member_type_id !== $memberType->id) {
            $member->update(['member_type_id' => $memberType->id]);
        }

        // Te betalen vanaf de eerste dag dat iemand dat jaar lid is
        $start = $member->registration_date ? membership_start_date($member->registration_date) : null;
        $dueDate = $start && $start->year === $year ? $start : Carbon::create($year, 1, 1);

        return DB::transaction(fn () => self::create([
            'member_id' => $member->id,
            'invoice_number' => self::nextInvoiceNumber($year),
            'year' => $year,
            'member_type_id' => $memberType->id,
            'months' => $months,
            'yearly_price' => $memberType->priceForYear($year),
            'amount' => invoice_amount($member, $year),
            'is_paid' => '0',
            'Pay_date' => $dueDate,
        ]));
    }

    /**
     * Factuurnummers lopen per jaar door: 2026-0001, 2026-0002, ...
     */
    public static function nextInvoiceNumber(int $year): string
    {
        $count = self::withTrashed()->where('invoice_number', 'like', $year.'-%')->count();

        return sprintf('%d-%04d', $year, $count + 1);
    }
}
