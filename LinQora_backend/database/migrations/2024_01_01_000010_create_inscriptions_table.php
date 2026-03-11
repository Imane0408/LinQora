<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id('idInscription');
            $table->dateTime('dateInscr');
            $table->boolean('aPaye')->default(false);
            $table->dateTime('datePaiement')->nullable();
            $table->string('methodePaiement')->nullable();
            $table->boolean('presentGlobal')->default(false);
            $table->string('codeQr')->unique();
            $table->enum('statut', ['en_attente', 'confirme', 'annule', 'refuse'])->default('en_attente');
            $table->boolean('networkingActif')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('idEvenement')
                  ->constrained('evenements', 'idEvenement')
                  ->cascadeOnDelete();
            $table->foreignId('idParticipant')
                  ->constrained('participants', 'idParticipant')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['idEvenement', 'idParticipant']);
            $table->index(['idEvenement', 'statut']);
            $table->index('codeQr');
        });
    }
    public function down(): void { Schema::dropIfExists('inscriptions'); }
};
