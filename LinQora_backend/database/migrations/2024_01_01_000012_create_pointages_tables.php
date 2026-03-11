<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Pointage entrée événement
        Schema::create('pointage_ev', function (Blueprint $table) {
            $table->id('idPointageEv');
            $table->dateTime('heureEntree');
            $table->dateTime('heureSortie')->nullable();
            $table->boolean('estPresent')->default(true);
            $table->foreignId('idInscription')
                  ->constrained('inscriptions', 'idInscription')
                  ->cascadeOnDelete();
            $table->foreignId('idOperateur')
                  ->nullable()
                  ->constrained('utilisateurs', 'idUtilisateur')
                  ->nullOnDelete();
            $table->timestamps();
            $table->index('idInscription');
        });

        // Pointage par atelier
        Schema::create('pointage_atelier', function (Blueprint $table) {
            $table->id('idPointageAT');
            $table->dateTime('heureScan');
            $table->boolean('estPresent')->default(true);
            $table->foreignId('idInscription')
                  ->constrained('inscriptions', 'idInscription')
                  ->cascadeOnDelete();
            $table->foreignId('idAtelier')
                  ->constrained('ateliers', 'idAtelier')
                  ->cascadeOnDelete();
            $table->foreignId('idOperateur')
                  ->nullable()
                  ->constrained('utilisateurs', 'idUtilisateur')
                  ->nullOnDelete();
            $table->timestamps();
            $table->unique(['idInscription', 'idAtelier']);
            $table->index('idAtelier');
        });
    }
    public function down(): void {
        Schema::dropIfExists('pointage_atelier');
        Schema::dropIfExists('pointage_ev');
    }
};
