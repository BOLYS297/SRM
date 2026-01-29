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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('agent')->after('password');
            $table->string('api_token', 64)->unique()->nullable()->after('remember_token');
            $table->foreignId('service_id')->nullable()->after('api_token')->constrained('services');
            $table->foreignId('etudiant_id')->nullable()->after('service_id')->constrained('etudiants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('etudiant_id');
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn(['role', 'api_token']);
        });
    }
};
