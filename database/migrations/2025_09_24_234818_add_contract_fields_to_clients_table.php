<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // contract_pdf_path
        if (! Schema::hasColumn('clients', 'contract_pdf_path')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('contract_pdf_path')->nullable()->after('precision');
            });
        }

        // Prefer "contract_signed_pdf_path", but if you already have "signed_pdf_path" we will keep it.
        $hasSignedLegacy = Schema::hasColumn('clients', 'signed_pdf_path');
        $hasSignedNew    = Schema::hasColumn('clients', 'contract_signed_pdf_path');

        if (! $hasSignedLegacy && ! $hasSignedNew) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('contract_signed_pdf_path')->nullable()->after('contract_pdf_path');
            });
        }

        // yousign request id (v3)
        // Codebase often uses "yousign_request_id". If you used "yousign_signature_request_id" before,
        // keep it; otherwise create "yousign_request_id".
        $hasSrIdNew    = Schema::hasColumn('clients', 'yousign_request_id');
        $hasSrIdLegacy = Schema::hasColumn('clients', 'yousign_signature_request_id');

        if (! $hasSrIdNew && ! $hasSrIdLegacy) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('yousign_request_id')->nullable()->index()->after(
                    Schema::hasColumn('clients', 'contract_signed_pdf_path') ? 'contract_signed_pdf_path' :
                    (Schema::hasColumn('clients', 'signed_pdf_path') ? 'signed_pdf_path' : 'contract_pdf_path')
                );
            });
        }

        // yousign_document_id
        if (! Schema::hasColumn('clients', 'yousign_document_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('yousign_document_id')->nullable()->after(
                    Schema::hasColumn('clients', 'yousign_request_id') ? 'yousign_request_id' : 'yousign_signature_request_id'
                );
            });
        }

        // signed_at
        if (! Schema::hasColumn('clients', 'signed_at')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->timestamp('signed_at')->nullable()->after('yousign_document_id');
            });
        }
    }

    public function down(): void
    {
        // Only drop columns if they exist, to be safe.
        if (Schema::hasColumn('clients', 'contract_pdf_path')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('contract_pdf_path');
            });
        }

        if (Schema::hasColumn('clients', 'contract_signed_pdf_path')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('contract_signed_pdf_path');
            });
        }

        // We won’t drop 'signed_pdf_path' (legacy) automatically.
        // Uncomment if you’re sure you don’t use it anymore:
        // if (Schema::hasColumn('clients', 'signed_pdf_path')) {
        //     Schema::table('clients', function (Blueprint $table) {
        //         $table->dropColumn('signed_pdf_path');
        //     });
        // }

        if (Schema::hasColumn('clients', 'yousign_request_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex(['yousign_request_id']);
                $table->dropColumn('yousign_request_id');
            });
        }

        if (Schema::hasColumn('clients', 'yousign_signature_request_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex(['yousign_signature_request_id']);
                $table->dropColumn('yousign_signature_request_id');
            });
        }

        if (Schema::hasColumn('clients', 'yousign_document_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('yousign_document_id');
            });
        }

        if (Schema::hasColumn('clients', 'signed_at')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('signed_at');
            });
        }
    }
};