<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('signed_pdf_path')
                      ->comment('Image de la signature manuscrite du client (stockée en public/...)');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('logo')
                      ->comment('Signature du représentant légal de la société');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasColumn('clients', 'signature_path')) {
                $table->dropColumn('signature_path');
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'signature_path')) {
                $table->dropColumn('signature_path');
            }
        });
    }
};