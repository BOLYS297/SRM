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
        Schema::table('etudiants', function (Blueprint $table) {
            if (!Schema::hasColumn('etudiants', 'filiere')) {
                $table->string('filiere', 20)->nullable()->after('email');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'code_departement')) {
                $table->string('code_departement', 20)->nullable()->after('type_service');
                $table->index('code_departement', 'services_code_departement_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'code_departement')) {
                $table->dropIndex('services_code_departement_idx');
                $table->dropColumn('code_departement');
            }
        });

        Schema::table('etudiants', function (Blueprint $table) {
            if (Schema::hasColumn('etudiants', 'filiere')) {
                $table->dropColumn('filiere');
            }
        });
    }
};
