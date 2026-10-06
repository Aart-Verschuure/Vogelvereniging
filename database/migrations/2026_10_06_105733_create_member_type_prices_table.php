<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prijzen per lidsoort per jaar. Een prijswijziging geldt altijd vanaf een volgend jaar,
     * zodat het lopende jaar en de historie niet veranderen.
     */
    public function up(): void
    {
        Schema::create('member_type_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_type_id')->constrained('member_types')->cascadeOnDelete();
            $table->unsignedSmallInteger('year'); // Vanaf dit jaar geldt de prijs
            $table->decimal('price', 8, 2); // Jaarprijs
            $table->timestamps();

            $table->unique(['member_type_id', 'year']);
        });

        // Bestaande prijzen overnemen als prijs voor het huidige jaar
        $year = (int) date('Y');
        foreach (DB::table('member_types')->get() as $type) {
            DB::table('member_type_prices')->insert([
                'member_type_id' => $type->id,
                'year' => $year,
                'price' => $type->price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('member_types', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_types', function (Blueprint $table) {
            $table->integer('price')->default(0);
        });

        Schema::dropIfExists('member_type_prices');
    }
};
