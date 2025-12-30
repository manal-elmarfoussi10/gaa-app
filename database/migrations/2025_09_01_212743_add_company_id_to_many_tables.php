<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add company_id where missing
        $tables = [
            // tables that have client_id (easy backfill from clients)
            'devis',
            'factures',
            'expenses',
            'photos',
            'rdvs',
            'stocks',
            'emails',                 // if you tie emails to a client_id
            'conversations',          // if you tie conversations to a client_id

            // tables that depend on a parent (we’ll backfill via the parent)
            'avoirs',                 // via factures
            'paiements',              // via factures (or clients if you use that)
            'bons_de_commande',      // via bons_de_commande
            'facture_items',          // via factures
            'devis_items',            // via devis

            // optional – only include if your Produits are per-company (not global)
            // 'produits',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'company_id')) {
                    $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
                }
            });
        }

        // 2) Backfill from clients where possible
        // Generic helper: backfill tables that have client_id -> clients.company_id
        $withClient = [
            'devis', 'factures', 'expenses', 'photos', 'rdvs', 'stocks',
            'emails', 'conversations',
            'bons_de_commande',
        ];
        foreach ($withClient as $t) {
            if (Schema::hasColumn($t, 'client_id')) {
                // SQLite doesn't support UPDATE with JOIN, so we do it in PHP
                $records = DB::table($t)->whereNull('company_id')->whereNotNull('client_id')->get();
                foreach ($records as $record) {
                    $companyId = DB::table('clients')->where('id', $record->client_id)->value('company_id');
                    if ($companyId) {
                        DB::table($t)->where('id', $record->id)->update(['company_id' => $companyId]);
                    }
                }
            }
        }

        // Backfill avoirs via factures
        if (Schema::hasTable('avoirs') && Schema::hasColumn('avoirs', 'facture_id')) {
            $avoirs = DB::table('avoirs')->whereNull('company_id')->whereNotNull('facture_id')->get();
            foreach ($avoirs as $avoir) {
                $companyId = DB::table('factures')->where('id', $avoir->facture_id)->value('company_id');
                if ($companyId) {
                    DB::table('avoirs')->where('id', $avoir->id)->update(['company_id' => $companyId]);
                }
            }
        }

        // Backfill paiements via factures (adjust if you use client_id instead)
        if (Schema::hasTable('paiements')) {
            if (Schema::hasColumn('paiements', 'facture_id')) {
                $paiements = DB::table('paiements')->whereNull('company_id')->whereNotNull('facture_id')->get();
                foreach ($paiements as $paiement) {
                    $companyId = DB::table('factures')->where('id', $paiement->facture_id)->value('company_id');
                    if ($companyId) {
                        DB::table('paiements')->where('id', $paiement->id)->update(['company_id' => $companyId]);
                    }
                }
            } elseif (Schema::hasColumn('paiements', 'client_id')) {
                $paiements = DB::table('paiements')->whereNull('company_id')->whereNotNull('client_id')->get();
                foreach ($paiements as $paiement) {
                    $companyId = DB::table('clients')->where('id', $paiement->client_id)->value('company_id');
                    if ($companyId) {
                        DB::table('paiements')->where('id', $paiement->id)->update(['company_id' => $companyId]);
                    }
                }
            }
        }

        // facture_items via factures
        if (
            Schema::hasTable('facture_items')
            && Schema::hasColumn('facture_items', 'facture_id')
        ) {
            $items = DB::table('facture_items')->whereNull('company_id')->whereNotNull('facture_id')->get();
            foreach ($items as $item) {
                $companyId = DB::table('factures')->where('id', $item->facture_id)->value('company_id');
                if ($companyId) {
                    DB::table('facture_items')->where('id', $item->id)->update(['company_id' => $companyId]);
                }
            }
        }

        // devis_items via devis
        if (
            Schema::hasTable('devis_items')
            && Schema::hasColumn('devis_items', 'devis_id')
        ) {
            $items = DB::table('devis_items')->whereNull('company_id')->whereNotNull('devis_id')->get();
            foreach ($items as $item) {
                $companyId = DB::table('devis')->where('id', $item->devis_id)->value('company_id');
                if ($companyId) {
                    DB::table('devis_items')->where('id', $item->id)->update(['company_id' => $companyId]);
                }
            }
        }

        // produits (only if produits are per-company in your business rules)
        // if (Schema::hasTable('produits')) {
        //     // If you can derive produit->company_id from somewhere, do it here.
        //     // Otherwise leave null and set on creation with the trait.
        // }
    }

    public function down(): void
    {
        $tables = [
            'devis','factures','expenses','photos','rdvs','stocks','intervention_photos',
            'emails','conversations','conversation_threads',
            'avoirs','paiements','bons_de_commande',
            'facture_items','devis_items',
            // 'produits',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'company_id')) {
                    $table->dropConstrainedForeignId('company_id');
                }
            });
        }
    }
};