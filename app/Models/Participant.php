<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $primaryKey = 'idParticipant';
    protected $fillable   = ['telephone', 'organisation', 'idUtilisateur'];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'idParticipant', 'idParticipant');
    }

    public function profilsNetworking(): HasMany
    {
        return $this->hasMany(ProfilNetworking::class, 'idParticipant', 'idParticipant');
    }
}
