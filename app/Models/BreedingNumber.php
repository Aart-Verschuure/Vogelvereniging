<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BreedingNumber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'breeding_number',
        'date_of_issue',
    ];

    protected function casts(): array
    {
        return [
            'date_of_issue' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
