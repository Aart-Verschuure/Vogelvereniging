<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Een contributie is een factuur (positief bedrag) of een teruggave na afmelding (negatief bedrag).
     */
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('member_id');
            $table->unsignedSmallInteger('year')->nullable()->after('invoice_number'); // Contributiejaar
            $table->foreignId('member_type_id')->nullable()->after('year')->constrained('member_types'); // Lidsoort waarvoor gefactureerd is
            $table->unsignedTinyInteger('months')->nullable()->after('member_type_id'); // Aantal maanden waarover betaald wordt
            $table->decimal('yearly_price', 8, 2)->nullable()->after('months'); // Jaarprijs die gebruikt is
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('member_type_id');
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['invoice_number', 'year', 'months', 'yearly_price']);
        });
    }
};
