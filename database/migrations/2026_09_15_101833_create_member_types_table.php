<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // junior, senior, gastlid
            $table->string('description'); // Overige informatie over het lidmaatschapstype
            $table->integer('price'); // Prijs van het lidmaatschapstype
            $table->timestamps();
            $table->softDeletes(); // Voeg soft delete kolom toe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_types');
    }
};
