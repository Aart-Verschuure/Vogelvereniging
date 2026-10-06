<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'member_type_id',
        'address_id',
        'email',
        'password',
        'is_active',
        'nbvv_number',
        'phone',
        'registration_date',
        'status',
        'approved_at',
        'signature_name',
        'signed_at',
        'signature_ip',
        'cancellation_requested_at',
        'membership_end_date',
        'cancellation_reason',
    ];

    // Statussen van een lidmaatschap
    public const STATUS_PENDING = 'pending';     // Aangemeld, in quarantaine tot de administratie het verwerkt

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled'; // Afgemeld

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'registration_date' => 'date',
            'approved_at' => 'datetime',
            'signed_at' => 'datetime',
            'cancellation_requested_at' => 'datetime',
            'membership_end_date' => 'date',
        ];
    }

    public function memberType(): BelongsTo
    {
        return $this->belongsTo(MemberType::class)->withTrashed();
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Adress::class);
    }

    public function fullName(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Is iemand op dit moment lid?
     * Na goedkeuring pas vanaf de ingangsdatum, en na afmelding nog tot de einddatum.
     */
    public function isCurrentMember(): bool
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => ! $this->registration_date || membership_start_date($this->registration_date)->lte(today()),
            self::STATUS_CANCELLED => $this->membership_end_date?->gt(today()) ?? false,
            default => false,
        };
    }

    /**
     * Omschrijving van de lidmaatschapsstatus voor het ledenoverzicht.
     */
    public function statusLabel(): string
    {
        return match (true) {
            $this->status === self::STATUS_PENDING => 'In quarantaine',
            $this->status === self::STATUS_REJECTED => 'Afgewezen',
            $this->status === self::STATUS_CANCELLED && $this->isCurrentMember() => 'Opgezegd, lid tot '.$this->membership_end_date->format('d-m-Y'),
            $this->status === self::STATUS_CANCELLED => 'Geen lid meer sinds '.$this->membership_end_date?->format('d-m-Y'),
            ! $this->isCurrentMember() => 'Goedgekeurd, lid per '.membership_start_date($this->registration_date)->format('d-m-Y'),
            default => 'Lid',
        };
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    /**
     * Facturen (contributies met een positief bedrag), zonder teruggaves.
     */
    public function invoices(): HasMany
    {
        return $this->contributions()->where('amount', '>', 0);
    }

    public function breedingNumbers(): HasMany
    {
        return $this->hasMany(BreedingNumber::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            // Als er nog geen member_type_id is opgegeven, bepalen we het op basis van leeftijd
            if (empty($member->member_type_id) && ! empty($member->date_of_birth)) {
                $name = is_youth_in_year(Carbon::parse($member->date_of_birth), now()->year)
                    ? MemberType::JEUGDLID
                    : MemberType::SENIORLID;

                $member->member_type_id = MemberType::where('name', $name)->value('id');
            }
        });
    }
}
