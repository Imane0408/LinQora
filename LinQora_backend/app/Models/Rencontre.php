<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rencontre extends Model
{
    use HasFactory;

    protected $primaryKey = 'idRencontre';

    protected $fillable = [
        'creaneau', 'statut', 'message', 'feedback',
        'lieu', 'idProfilDemandeur', 'idProfilReceveur',
    ];

    protected $casts = ['creaneau' => 'datetime'];

    public function profilDemandeur(): BelongsTo
    {
        return $this->belongsTo(ProfilNetworking::class, 'idProfilDemandeur', 'idProfil');
    }

    public function profilReceveur(): BelongsTo
    {
        return $this->belongsTo(ProfilNetworking::class, 'idProfilReceveur', 'idProfil');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationApp::class, 'idRencontre', 'idRencontre');
    }

    public function scopeEnAttente($q) { return $q->where('statut', 'en_attente'); }
    public function scopeAccepte($q)   { return $q->where('statut', 'accepte'); }
}
