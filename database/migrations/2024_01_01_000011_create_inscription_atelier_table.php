<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inscription_atelier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idInscription')
                  ->constrained('inscriptions', 'idInscription')
                  ->cascadeOnDelete();
            $table->foreignId('idAtelier')
                  ->constrained('ateliers', 'idAtelier')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['idInscription', 'idAtelier']);
        });
    }
    public function down(): void { Schema::dropIfExists('inscription_atelier'); }
};
