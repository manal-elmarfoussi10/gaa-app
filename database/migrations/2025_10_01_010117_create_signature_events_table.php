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
        Schema::create('signature_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable(); // lien vers client
            $table->string('event_name');   // ex: signature_request.done, signer.done
            $table->string('yousign_request_id')->nullable();
            $table->string('yousign_document_id')->nullable();
            $table->json('payload')->nullable(); // stocker toutes les données brutes envoyées par Yousign
            $table->timestamps();
    
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_events');
    }
};
