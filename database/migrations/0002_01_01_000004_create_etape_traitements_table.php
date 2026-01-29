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
        Schema::create('etape_traitements', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ordre_etape');
            $table->string('action');
            $table->dateTime('date_entree');
            $table->dateTime('date_sortie')->nullable();
            $table->text('observation')->nullable();
            $table->foreignId('requete_id')->constrained('requetes');
            $table->foreignId('service_id')->constrained('services');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etape_traitements');
    }
};
