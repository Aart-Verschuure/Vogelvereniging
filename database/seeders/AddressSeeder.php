<?php

namespace Database\Seeders;

use App\Models\Adress;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Maak een vast adres aan (handig voor testen)
        Adress::updateOrCreate(
            ['id' => 1],
            [
                'street'      => 'Kerkstraat',
                'house_number'=> '12',
                'postal_code' => '1234 AB',
                'city'        => 'Bodegraven',
            ]
        );

        // 2. Optioneel: Maak extra willekeurige adressen aan via de factory
        if (class_exists(\Database\Factories\AdressFactory::class)) {
            Adress::factory()->count(10)->create();
        }
    }
}