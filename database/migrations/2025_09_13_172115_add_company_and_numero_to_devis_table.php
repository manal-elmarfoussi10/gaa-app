<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon; // <-- import Carbon here

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add the numero column if it doesn't exist
        if (! Schema::hasColumn('devis', 'numero')) {
            Schema::table('devis', function (Blueprint $table) {
                $table->string('numero', 20)->nullable()->after('date_devis');
            });
        }

        // 2) Backfill numero for existing rows that don't have one
        $rows = DB::table('devis')->whereNull('numero')->orderBy('id')->get();
        foreach ($rows as $row) {
            $companyId = (int) ($row->company_id ?? 0);
            $d         = $row->date_devis ? Carbon::parse($row->date_devis) : now();
            $year      = $d->format('Y');
            $month     = $d->format('m');

            // Find next free SSMMYYYY for that company+month
            $seq = 1;
            do {
                $numero = str_pad($seq, 2, '0', STR_PAD_LEFT) . $month . $year;
                $exists = DB::table('devis')
                    ->where('company_id', $companyId)
                    ->where('numero', $numero)
                    ->exists();
                $seq++;
            } while ($exists && $seq <= 99);

            DB::table('devis')->where('id', $row->id)->update(['numero' => $numero]);
        }

        // 3) Create a unique index on (company_id, numero)
        // If it already exists, this will error; wrap in try/catch to be idempotent.
        try {
            Schema::table('devis', function (Blueprint $table) {
                $table->unique(['company_id', 'numero'], 'devis_company_numero_unique');
            });
        } catch (\Throwable $e) {
            // index already exists — ignore
        }
    }

    public function down(): void
    {
        // Drop the unique index and the numero column
        try {
            Schema::table('devis', function (Blueprint $table) {
                $table->dropUnique('devis_company_numero_unique');
            });
        } catch (\Throwable $e) {
            // ignore if it didn't exist
        }

        if (Schema::hasColumn('devis', 'numero')) {
            Schema::table('devis', function (Blueprint $table) {
                $table->dropColumn('numero');
            });
        }
    }
};