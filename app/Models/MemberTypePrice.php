<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * De jaarprijs van een lidsoort, geldig vanaf een bepaald jaar.
 */
class MemberTypePrice extends Model
{
    protected $fillable = [
        'member_type_id',
        'year',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'float',
        ];
    }

    public function memberType(): BelongsTo
    {
        return $this->belongsTo(MemberType::class);
    }
}
