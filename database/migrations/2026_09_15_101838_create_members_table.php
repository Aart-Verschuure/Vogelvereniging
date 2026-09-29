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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_type_id')->constrained('member_types'); // elk lid heeft maar 1 lidmaatschapstype
            $table->foreignId('address_id')->constrained('adresses'); // elk lid heeft maar 1 adres
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth'); // Geboortedatum van het lid om te bepalen welke leeftijdscategorie het lid valt (junior or senior)
            $table->string('nbvv_number')->unique(); // het nummer wat het lid van de nbvv gekregen heeft, dit nummer is uniek en wordt gebruikt om het lid te identificeren
            $table->integer('is_active')->default(1); // 1 = actief, 0 = inactief, dit veld wordt gebruikt om te bepalen of het lidmaatschap actief is of niet, bijvoorbeeld als het lid zijn lidmaatschap opzegt of als het lid verhuist is
            $table->string('email')->nullable(); // Email van het lid, dit veld is optioneel en kan gebruikt worden om het lid te contacteren
            $table->string('password')->nullable(); // Wachtwoord van het lid, dit veld is optioneel en kan gebruikt worden om het lid toegang te geven tot de website
            $table->timestamps();
            $table->softDeletes(); // Voeg soft delete kolom toe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
