<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

    class Member extends Model
    {
        use HasFactory, SoftDeletes;

        protected $fillable = [
            'first_name',
            'last_name',
            'date_of_birth',
            'member_type_id',
            'email',
            'password',
            'is_active',
            'nbvv_number',
        ];

        protected static function booted(): void
        {
            static::creating(function (Member $member) {
                // Als er nog geen member_type_id is opgegeven, berekenen we het
                if (empty($member->member_type_id) && !empty($member->date_of_birth)) {
                    $age = Carbon::parse($member->date_of_birth)->age;

                    $member->member_type_id = match (true) {
                        $age < 18   => 1, // Jeugdlid
                        $age >= 65  => 3, // Senior
                        default     => 2, // Gewoon lid
                    };
                }
            });
        }
    }