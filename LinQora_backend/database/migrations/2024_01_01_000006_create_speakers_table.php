<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('speakers', function (Blueprint $table) {
            $table->id('idSpeaker');
            $table->string('nomComplet');
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('titre')->nullable();
            $table->string('organisation')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('twitter')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('idEntreprise')
                  ->constrained('entreprises', 'idEntreprise')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('speakers'); }
};
