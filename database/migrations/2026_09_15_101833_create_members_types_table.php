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
        Schema::create('members_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // junior, senior, gastlid
            $table->string('description'); // Overige informatie over het lidmaatschapstype
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members_types');
    }
};
