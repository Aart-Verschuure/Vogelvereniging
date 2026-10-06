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
        Schema::table('members', function (Blueprint $table) {
            // Gastleden zijn geen lid van de NBvV en hebben dus geen NBvV-nummer
            $table->string('nbvv_number')->nullable()->change();

            $table->string('phone')->nullable()->after('email');
            $table->date('registration_date')->nullable()->after('nbvv_number'); // Datum van opgave als lid, hierop wordt de contributie berekend
            $table->string('status')->default('active')->after('registration_date'); // pending (quarantaine), active, rejected, cancelled
            $table->timestamp('approved_at')->nullable()->after('status'); // Wanneer de administratie de aanmelding heeft goedgekeurd

            // Digitale handtekening: vinkje + getypte naam, met tijdstip en IP-adres als bewijs
            $table->string('signature_name')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_ip', 45)->nullable();

            // Afmelding
            $table->timestamp('cancellation_requested_at')->nullable(); // Wanneer het lid zich heeft afgemeld
            $table->date('membership_end_date')->nullable(); // Vanaf deze datum is iemand geen lid meer
            $table->text('cancellation_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'registration_date',
                'status',
                'approved_at',
                'signature_name',
                'signed_at',
                'signature_ip',
                'cancellation_requested_at',
                'membership_end_date',
                'cancellation_reason',
            ]);
        });
    }
};
