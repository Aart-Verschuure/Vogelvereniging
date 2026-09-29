<?php

namespace Database\Seeders;

use App\Models\MemberType;
use Illuminate\Database\Seeder;

class MemberTypeSeeder extends Seeder
{
    public function run(): void
    {
$types = [
            [
                'id'          => 1, 
                'name'        => 'Jeugdlid', 
                'description' => 'Leden jonger dan 18 jaar',
                'price'       => 18.00, // <-- Prijs toevoegen
            ],
            [
                'id'          => 2, 
                'name'        => 'Seniorlid', 
                'description' => 'Lidmaatschap waar de leden ouder dan 18 jaar zijn',
                'price'       => 36.00, // <-- Prijs toevoegen
            ],
            [
                'id'          => 3, 
                'name'        => 'Gastlid', 
                'description' => 'leden die niet bij de NBvV zijn aangesloten, maar wel lid willen worden van de vogelvereniging',
                'price'       => 18.00, // <-- Prijs toevoegen
            ],
        ];

        foreach ($types as $type) {
            MemberType::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}