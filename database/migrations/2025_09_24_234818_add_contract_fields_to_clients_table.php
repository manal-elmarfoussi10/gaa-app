<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // paths in storage (storage/app/…)
            $table->string('contract_pdf_path')->nullable()->after('precision');   // non signé
            $table->string('signed_pdf_path')->nullable()->after('contract_pdf_path');

            // Yousign identifiers (v3)
            $table->string('yousign_signature_request_id')->nullable()->index()->after('signed_pdf_path');
            $table->string('yousign_document_id')->nullable()->after('yousign_signature_request_id');

            // If you want to track signed date (if not already present)
            $table->timestamp('signed_at')->nullable()->after('yousign_document_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'contract_pdf_path',
                'signed_pdf_path',
                'yousign_signature_request_id',
                'yousign_document_id',
                'signed_at',
            ]);
        });
    }
};