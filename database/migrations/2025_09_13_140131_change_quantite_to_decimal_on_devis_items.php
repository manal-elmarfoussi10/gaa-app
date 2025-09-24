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
    Schema::table('devis_items', function (Blueprint $table) {
        $table->decimal('quantite', 10, 3)->change();
        $table->decimal('prix_unitaire', 12, 2)->change();
        $table->decimal('taux_tva', 5, 2)->change();
        $table->decimal('remise', 5, 2)->default(0)->change();
    });
}

public function down(): void
{
    Schema::table('devis_items', function (Blueprint $table) {
        $table->integer('quantite')->change();
        $table->integer('prix_unitaire')->change();
        $table->integer('taux_tva')->change();
        $table->integer('remise')->change();
    });
}
};
