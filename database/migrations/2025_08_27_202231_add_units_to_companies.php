<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Add the column (no AFTER)
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'units')) {
                $table->integer('units')->default(0);
            }
        });

        // 2) Optional backfill from users.units (pick the rule you want)
        // Here: take MAX(units) per company; change to MIN or admin-specific if needed.
        // Note: SQLite doesn't support UPDATE with JOIN, so we do it in PHP
        $companies = DB::table('companies')->get();
        foreach ($companies as $company) {
            $maxUnits = DB::table('users')
                ->where('company_id', $company->id)
                ->max('units') ?? 0;
            DB::table('companies')
                ->where('id', $company->id)
                ->update(['units' => $maxUnits]);
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'units')) {
                $table->dropColumn('units');
            }
        });
    }
};
