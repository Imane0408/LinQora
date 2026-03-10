<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications_app', function (Blueprint $table) {
            $table->id('idNotification');
            $table->string('sujet');
            $table->text('contenu');
            $table->enum('statutEnvoi', ['en_attente', 'envoye', 'echoue'])->default('en_attente');
            $table->enum('type', ['email', 'push', 'interne'])->default('interne');
            $table->dateTime('dateCreation');
            $table->dateTime('dateLecture')->nullable();
            $table->boolean('lu')->default(false);
            $table->foreignId('idUtilisateur')
                  ->constrained('utilisateurs', 'idUtilisateur')
                  ->cascadeOnDelete();
            $table->foreignId('idRencontre')
                  ->nullable()
                  ->constrained('rencontres', 'idRencontre')
                  ->nullOnDelete();
            $table->foreignId('idEvenement')
                  ->nullable()
                  ->constrained('evenements', 'idEvenement')
                  ->nullOnDelete();
            $table->timestamps();
            $table->index(['idUtilisateur', 'lu']);
            $table->index('statutEnvoi');
        });
    }
    public function down(): void { Schema::dropIfExists('notifications_app'); }
};
