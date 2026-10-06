<?php

namespace Database\Seeders;

use App\Models\Member;
use Database\Factories\MemberFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Een specifiek testlid aanmaken
        Member::create([
            'first_name' => 'Aart',
            'last_name' => 'de Vogelaar',
            'date_of_birth' => '1980-01-01',
            'email' => 'deVogelaar@ietwat.com',
            'password' => Hash::make('wachtwoord123'),
            'nbvv_number' => '123456',
            'is_active' => true,
            'registration_date' => '2026-01-01',
            'address_id' => 1, // Verwijst naar het adres in AddressSeeder
            'member_type_id' => 2, // Verwijst naar 'Seniorlid' in MemberTypeSeeder
        ]);

        // 2. Optioneel: Meerdere willekeurige leden genereren via de factory
        if (class_exists(MemberFactory::class)) {
            Member::factory()->count(20)->create();
        }
    }
}
