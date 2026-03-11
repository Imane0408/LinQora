<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atelier extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'idAtelier';

    protected $fillable = [
        'titre', 'description', 'categorie', 'horaire',
        'dureeMinutes', 'salle', 'capacite', 'idEvenement',
    ];

    protected $casts = ['horaire' => 'datetime'];

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'idEvenement', 'idEvenement');
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(
            Speaker::class, 'atelier_speaker', 'idAtelier', 'idSpeaker'
        )->withPivot('role')->withTimestamps();
    }

    public function inscriptions(): BelongsToMany
    {
        return $this->belongsToMany(
            Inscription::class, 'inscription_atelier', 'idAtelier', 'idInscription'
        )->withTimestamps();
    }

    public function pointages(): HasMany
    {
        return $this->hasMany(PointageAtelier::class, 'idAtelier', 'idAtelier');
    }

    public function getPlacesRestantesAttribute(): ?int
    {
        if ($this->capacite === null) return null;
        return max(0, $this->capacite - $this->inscriptions()->count());
    }
}
