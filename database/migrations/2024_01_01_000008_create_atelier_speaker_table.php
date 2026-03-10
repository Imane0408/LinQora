<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('atelier_speaker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idAtelier')
                  ->constrained('ateliers', 'idAtelier')
                  ->cascadeOnDelete();
            $table->foreignId('idSpeaker')
                  ->constrained('speakers', 'idSpeaker')
                  ->cascadeOnDelete();
            $table->string('role')->nullable(); // principal | modérateur | invité
            $table->timestamps();
            $table->unique(['idAtelier', 'idSpeaker']);
        });
    }
    public function down(): void { Schema::dropIfExists('atelier_speaker'); }
};
