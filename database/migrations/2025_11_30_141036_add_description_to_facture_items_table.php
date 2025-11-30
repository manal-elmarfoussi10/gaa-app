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
        Schema::table('facture_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('produit');
            $table->decimal('taux_tva', 5, 2)->nullable()->after('remise');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facture_items', function (Blueprint $table) {
            $table->dropColumn(['description', 'taux_tva']);
        });
    }
};
