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
        Schema::create('requetes', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_depot');
            $table->string('objet');
            $table->text('description')->nullable();
            $table->string('statut')->default('en_attente');
            $table->string('annee_depot');
            $table->string('filiere_depot');
            $table->string('niveau_depot');
            $table->foreignId('etudiant_id')->constrained('etudiants');
            $table->foreignId('type_requete_id')->constrained('types_requetes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requetes');
    }
};
