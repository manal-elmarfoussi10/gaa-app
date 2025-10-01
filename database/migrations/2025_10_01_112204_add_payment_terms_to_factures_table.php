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
        Schema::table('factures', function (Blueprint $table) {
            $table->string('payment_method')->nullable();        // Exemple: Virement bancaire
            $table->string('payment_iban')->nullable();          // IBAN
            $table->string('payment_bic')->nullable();           // BIC
            $table->decimal('penalty_rate', 6, 3)->nullable();   // Exemple: 10.000 (%)
            $table->text('payment_terms_text')->nullable();      // Texte libre
            $table->date('due_date')->nullable();                // Date d’échéance
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_iban',
                'payment_bic',
                'penalty_rate',
                'payment_terms_text',
                'due_date',
            ]);
        });
    }
};
