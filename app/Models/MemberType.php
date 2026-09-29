<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberType extends Model
{
    use HasFactory, SoftDeletes;

    // Koppeling naar de tabel in het meervoud (optioneel, maar Laravel pakt 'member_types' standaard al goed op)
    protected $table = 'member_types';

    protected $fillable = [
        'name',
        'description',
        'price',
    ];
}