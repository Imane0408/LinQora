<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'idInscription';

    protected $fillable = [
        'dateInscr', 'aPaye', 'datePaiement', 'methodePaiement',
        'presentGlobal', 'codeQr', 'statut', 'networkingActif',
        'notes', 'idEvenement', 'idParticipant',
    ];

    protected $casts = [
        'dateInscr'       => 'datetime',
        'datePaiement'    => 'datetime',
        'aPaye'           => 'boolean',
        'presentGlobal'   => 'boolean',
        'networkingActif' => 'boolean',
    ];

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'idEvenement', 'idEvenement');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'idParticipant', 'idParticipant');
    }

    public function ateliers(): BelongsToMany
    {
        return $this->belongsToMany(
            Atelier::class, 'inscription_atelier', 'idInscription', 'idAtelier'
        )->withTimestamps();
    }

    public function pointageEv(): HasOne
    {
        return $this->hasOne(PointageEv::class, 'idInscription', 'idInscription');
    }

    public function pointagesAtelier(): HasMany
    {
        return $this->hasMany(PointageAtelier::class, 'idInscription', 'idInscription');
    }
}
