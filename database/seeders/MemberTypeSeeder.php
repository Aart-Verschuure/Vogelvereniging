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
                'name' => MemberType::JEUGDLID,
                'description' => 'Leden tot en met het jaar waarin ze 18 worden',
                'price' => 18.00,
            ],
            [
                'name' => MemberType::SENIORLID,
                'description' => 'Lidmaatschap voor leden vanaf het jaar nadat ze 18 zijn geworden',
                'price' => 36.00,
            ],
            [
                'name' => MemberType::GASTLID,
                'description' => 'leden die niet bij de NBvV zijn aangesloten, maar wel lid willen worden van de vogelvereniging',
                'price' => 18.00,
            ],
        ];

        foreach ($types as $type) {
            $memberType = MemberType::updateOrCreate(['name' => $type['name']], ['description' => $type['description']]);

            // De prijs geldt vanaf het huidige jaar
            $memberType->prices()->updateOrCreate(['year' => now()->year], ['price' => $type['price']]);
        }
    }
}
