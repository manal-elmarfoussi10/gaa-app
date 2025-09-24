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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('statut_gsauto')->nullable(); // draft|sent|viewed|signed|failed
            $table->string('yousign_procedure_id')->nullable();
            $table->string('yousign_file_id')->nullable();
            $table->timestamp('signed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'statut_gsauto',
                'yousign_procedure_id',
                'yousign_file_id',
                'signed_at',
            ]);
        });
    }
};
