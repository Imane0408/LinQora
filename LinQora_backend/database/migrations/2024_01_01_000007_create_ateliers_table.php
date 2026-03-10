<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ateliers', function (Blueprint $table) {
            $table->id('idAtelier');
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('categorie', ['public', 'vip', 'prive', 'autre'])->default('public');
            $table->dateTime('horaire');
            $table->integer('dureeMinutes')->default(60);
            $table->string('salle')->nullable();
            $table->integer('capacite')->nullable();
            $table->foreignId('idEvenement')
                  ->constrained('evenements', 'idEvenement')
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('idEvenement');
        });
    }
    public function down(): void { Schema::dropIfExists('ateliers'); }
};
