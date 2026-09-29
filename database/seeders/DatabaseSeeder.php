<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use App\Models\MemberType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'beheerder Aart',
            'email' => 'beheer@vogelvereniging.nl',
            'password' => bcrypt('Password123'),
        ]);

        Member::factory()->create([
            'first_name' => 'Aart',
            'last_name' => 'de Vogelaar',
            'email' => 'deVogelaar@ietwat.com',
            'date_of_birth' => '1980-01-01',
            'nbvv_number' => '123456',
            'is_active' => true,
            'password' => bcrypt('Password123'),
            'member_type_id' => 1,
            'address_id' => 1,
        ]); 

        Member::factory()->create([
            'first_name' => 'Bert',
            'last_name' => 'van Herkingen',
            'email' => 'vanHerkingen@vogelaar.nl',
            'date_of_birth' => '2010-01-01',
            'nbvv_number' => '654321',
            'is_active' => true,
            'password' => bcrypt('Password123'),
            'member_type_id' => 2,
            'address_id' => 1,
        ]);

        Member::factory(10)->create();

        // Gebruik hier MemberType (zonder 's')
        MemberType::firstOrCreate(
            ['name' => 'Volwassen lid'],
            [
                'description' => 'Leden vanaf 18 jaar (inclusief NBvV lidmaatschap)',
                'price' => 36.00,
            ]
        );

        MemberType::firstOrCreate(
            ['name' => 'Jeugd lid'],
            [
                'description' => 'Leden tot 18 jaar (inclusief NBvV lidmaatschap)',
                'price' => 18.00,
            ]
        );

        MemberType::firstOrCreate(
            ['name' => 'Gastlid'],
            [
                'description' => 'Gastleden van alle leeftijden (geen NBvV lidmaatschap)',
                'price' => 18.00,
            ]
        );
    }
}