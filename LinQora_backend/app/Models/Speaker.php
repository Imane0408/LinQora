<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Speaker extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'idSpeaker';

    protected $fillable = [
        'nomComplet', 'photo', 'bio', 'titre',
        'organisation', 'linkedin', 'twitter', 'email', 'idEntreprise',
    ];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'idEntreprise', 'idEntreprise');
    }

    public function ateliers(): BelongsToMany
    {
        return $this->belongsToMany(
            Atelier::class, 'atelier_speaker', 'idSpeaker', 'idAtelier'
        )->withPivot('role')->withTimestamps();
    }
}
