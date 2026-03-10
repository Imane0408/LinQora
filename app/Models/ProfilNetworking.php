<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilNetworking extends Model
{
    use HasFactory;

    protected $table      = 'profil_networking';
    protected $primaryKey = 'idProfil';

    protected $fillable = [
        'poste', 'linkedin', 'objectifs', 'photo',
        'disponibilites', 'actif', 'idParticipant', 'idEvenement',
    ];

    protected $casts = [
        'disponibilites' => 'array',
        'actif'          => 'boolean',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'idParticipant', 'idParticipant');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'idEvenement', 'idEvenement');
    }

    public function rencontresDemandees(): HasMany
    {
        return $this->hasMany(Rencontre::class, 'idProfilDemandeur', 'idProfil');
    }

    public function rencontresRecues(): HasMany
    {
        return $this->hasMany(Rencontre::class, 'idProfilReceveur', 'idProfil');
    }
}
