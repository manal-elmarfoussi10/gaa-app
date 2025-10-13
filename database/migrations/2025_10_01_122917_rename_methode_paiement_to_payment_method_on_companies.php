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
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('methode_paiement', 'payment_method');
        });
    }
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('payment_method', 'methode_paiement');
        });
    }
};
