<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adress extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'adresses';

    protected $fillable = [
        'street',
        'house_number',
        'postal_code',
        'city',
    ];
}