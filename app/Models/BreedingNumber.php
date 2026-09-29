<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BreedingNumber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'breeding_number',
        'issue_year',
    ];
}