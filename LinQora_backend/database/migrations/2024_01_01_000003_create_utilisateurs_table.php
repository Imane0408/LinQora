<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('idUtilisateur');
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('motDePasse');
            $table->string('avatar')->nullable();
            $table->boolean('actif')->default(true);
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->foreignId('idEntreprise')
                  ->nullable()
                  ->constrained('entreprises', 'idEntreprise')
                  ->nullOnDelete();
            $table->foreignId('idRole')
                  ->constrained('roles', 'idRole');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['idEntreprise', 'idRole']);
        });
    }
    public function down(): void { Schema::dropIfExists('utilisateurs'); }
};
