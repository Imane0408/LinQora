<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('participants', function (Blueprint $table) {
            $table->id('idParticipant');
            $table->string('telephone')->nullable();
            $table->string('organisation')->nullable();
            $table->foreignId('idUtilisateur')
                  ->constrained('utilisateurs', 'idUtilisateur')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->index('idUtilisateur');
        });
    }
    public function down(): void { Schema::dropIfExists('participants'); }
};
