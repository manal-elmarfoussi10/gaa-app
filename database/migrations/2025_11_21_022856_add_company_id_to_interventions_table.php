<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            if (!Schema::hasColumn('interventions', 'company_id')) {
                $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            }
        });

        // Backfill company_id from clients via client_id
        $interventions = DB::table('interventions')->whereNull('company_id')->whereNotNull('client_id')->get();
        foreach ($interventions as $intervention) {
            $companyId = DB::table('clients')->where('id', $intervention->client_id)->value('company_id');
            if ($companyId) {
                DB::table('interventions')->where('id', $intervention->id)->update(['company_id' => $companyId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            if (Schema::hasColumn('interventions', 'company_id')) {
                $table->dropConstrainedForeignId('company_id');
            }
        });
    }
};
