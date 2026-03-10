<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Evenement extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'idEvenement';

    protected $fillable = [
        'titre', 'description', 'mode', 'dateDebut', 'dateFin',
        'lieu', 'lienEnLigne', 'banniere', 'paquettePdf',
        'estPayant', 'prix', 'networkingActif', 'statut', 'slug',
        'capaciteMax', 'idEntreprise', 'idGestionnaire',
    ];

    protected $casts = [
        'dateDebut'       => 'datetime',
        'dateFin'         => 'datetime',
        'estPayant'       => 'boolean',
        'networkingActif' => 'boolean',
        'prix'            => 'decimal:2',
    ];

    // Génération automatique du slug à la création
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($e) {
            if (empty($e->slug)) {
                $e->slug = Str::slug($e->titre) . '-' . Str::random(6);
            }
        });
    }

    // ── Relations ─────────────────────────────────────────────────────────────
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'idEntreprise', 'idEntreprise');
    }

    public function gestionnaire(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'idGestionnaire', 'idUtilisateur');
    }

    public function ateliers(): HasMany
    {
        return $this->hasMany(Atelier::class, 'idEvenement', 'idEvenement');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'idEvenement', 'idEvenement');
    }

    public function profilsNetworking(): HasMany
    {
        return $this->hasMany(ProfilNetworking::class, 'idEvenement', 'idEvenement');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationApp::class, 'idEvenement', 'idEvenement');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopePublie($q)                  { return $q->where('statut', 'publie'); }
    public function scopeParEntreprise($q, int $id)  { return $q->where('idEntreprise', $id); }

    // ── Accesseurs calculés ───────────────────────────────────────────────────
    public function getNombreInscritsAttribute(): int
    {
        return $this->inscriptions()->where('statut', 'confirme')->count();
    }

    public function getNombrePresentsAttribute(): int
    {
        return $this->inscriptions()->where('presentGlobal', true)->count();
    }

    public function getTauxParticipationAttribute(): float
    {
        $i = $this->nombre_inscrits;
        return $i > 0 ? round(($this->nombre_presents / $i) * 100, 2) : 0;
    }
}
