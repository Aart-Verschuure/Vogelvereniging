<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Database\Seeder;

class ContributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Elk lid krijgt een factuur voor dit jaar, berekend vanaf de maand waarin hij daadwerkelijk lid is
        foreach (Member::with('memberType')->get() as $member) {
            $invoice = Contribution::createInvoice($member, now()->year);

            if ($invoice && fake()->boolean()) {
                $invoice->update(['is_paid' => '1']);
            }
        }
    }
}
