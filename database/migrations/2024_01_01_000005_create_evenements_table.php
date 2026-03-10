<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id('idEvenement');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('mode', ['presentiel', 'en_ligne', 'hybride'])->default('presentiel');
            $table->dateTime('dateDebut');
            $table->dateTime('dateFin');
            $table->string('lieu')->nullable();
            $table->string('lienEnLigne')->nullable();
            $table->string('banniere')->nullable();
            $table->string('paquettePdf')->nullable();
            $table->boolean('estPayant')->default(false);
            $table->decimal('prix', 10, 2)->nullable();
            $table->boolean('networkingActif')->default(false);
            $table->enum('statut', ['brouillon', 'publie', 'archive', 'annule'])->default('brouillon');
            $table->string('slug')->unique();
            $table->integer('capaciteMax')->nullable();
            $table->foreignId('idEntreprise')
                  ->constrained('entreprises', 'idEntreprise')
                  ->cascadeOnDelete();
            $table->foreignId('idGestionnaire')
                  ->constrained('utilisateurs', 'idUtilisateur');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['idEntreprise', 'statut']);
            $table->index('dateDebut');
        });
    }
    public function down(): void { Schema::dropIfExists('evenements'); }
};
