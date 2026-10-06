<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberType extends Model
{
    use HasFactory, SoftDeletes;

    // Koppeling naar de tabel in het meervoud (optioneel, maar Laravel pakt 'member_types' standaard al goed op)
    protected $table = 'member_types';

    // Lidsoorten waarvan de leeftijd bepaalt welke van de twee iemand is
    public const JEUGDLID = 'Jeugdlid';

    public const SENIORLID = 'Seniorlid';

    public const GASTLID = 'Gastlid';

    protected $fillable = [
        'name',
        'description',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(MemberTypePrice::class)->orderBy('year');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * De jaarprijs die geldt in een bepaald jaar: de laatst vastgestelde prijs tot en met dat jaar.
     */
    public function priceForYear(int $year): ?float
    {
        $prices = $this->relationLoaded('prices') ? $this->prices : $this->prices()->get();

        return $prices->where('year', '<=', $year)->sortByDesc('year')->first()?->price;
    }

    public function currentPrice(): ?float
    {
        return $this->priceForYear(now()->year);
    }

    /**
     * Een aangekondigde prijswijziging voor een komend jaar, of null als er niets is aangekondigd.
     */
    public function announcedPrice(): ?MemberTypePrice
    {
        $prices = $this->relationLoaded('prices') ? $this->prices : $this->prices()->get();

        return $prices->where('year', '>', now()->year)->sortBy('year')->first();
    }

    /**
     * Is dit Jeugdlid of Seniorlid? Daarvoor bepaalt de leeftijd de lidsoort.
     */
    public function isAgeBased(): bool
    {
        return in_array($this->name, [self::JEUGDLID, self::SENIORLID], true);
    }

    /**
     * Bepaal het lidmaatschapstype voor een nieuwe aanmelding.
     * Geen lid van de NBvV => Gastlid, anders op basis van leeftijd Jeugdlid of Seniorlid.
     */
    public static function forApplicant(bool $isNbvvMember, Carbon $dateOfBirth): self
    {
        $name = match (true) {
            ! $isNbvvMember => self::GASTLID,
            is_youth_in_year($dateOfBirth, now()->year) => self::JEUGDLID,
            default => self::SENIORLID,
        };

        return static::where('name', $name)->firstOrFail();
    }
}
