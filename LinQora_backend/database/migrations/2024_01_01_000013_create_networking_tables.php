<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Profil networking d'un participant pour un événement donné
        Schema::create('profil_networking', function (Blueprint $table) {
            $table->id('idProfil');
            $table->string('poste')->nullable();
            $table->string('linkedin')->nullable();
            $table->text('objectifs')->nullable();
            $table->string('photo')->nullable();
            $table->json('disponibilites')->nullable();
            $table->boolean('actif')->default(true);
            $table->foreignId('idParticipant')
                  ->constrained('participants', 'idParticipant')
                  ->cascadeOnDelete();
            $table->foreignId('idEvenement')
                  ->constrained('evenements', 'idEvenement')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['idParticipant', 'idEvenement']);
        });

        // Rencontres one-to-one entre deux profils networking
        Schema::create('rencontres', function (Blueprint $table) {
            $table->id('idRencontre');
            $table->dateTime('creaneau');
            $table->enum('statut', ['en_attente', 'accepte', 'refuse', 'annule', 'termine'])
                  ->default('en_attente');
            $table->text('message')->nullable();
            $table->text('feedback')->nullable();
            $table->string('lieu')->nullable();
            $table->foreignId('idProfilDemandeur')
                  ->constrained('profil_networking', 'idProfil')
                  ->cascadeOnDelete();
            $table->foreignId('idProfilReceveur')
                  ->constrained('profil_networking', 'idProfil')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->index(['idProfilDemandeur', 'statut']);
            $table->index(['idProfilReceveur', 'statut']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('rencontres');
        Schema::dropIfExists('profil_networking');
    }
};
