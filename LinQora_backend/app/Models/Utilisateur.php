<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'idUtilisateur';

    protected $fillable = [
        'nom', 'prenom', 'email', 'motDePasse',
        'avatar', 'actif', 'idEntreprise', 'idRole',
    ];

    protected $hidden = ['motDePasse', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'actif'             => 'boolean',
        'motDePasse'        => 'hashed',
    ];

    // Sanctum utilise ce champ comme mot de passe
    public function getAuthPassword(): string
    {
        return $this->motDePasse;
    }

    // ── Relations ─────────────────────────────────────────────────────────────
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'idRole', 'idRole');
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'idEntreprise', 'idEntreprise');
    }

    public function participant(): HasOne
    {
        return $this->hasOne(Participant::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationApp::class, 'idUtilisateur', 'idUtilisateur');
    }

    // ── Helpers rôle ──────────────────────────────────────────────────────────
    public function isSuperAdmin(): bool     { return $this->role->nomRole === Role::SUPER_ADMIN; }
    public function isAdminEntreprise(): bool { return $this->role->nomRole === Role::ADMIN_ENTREPRISE; }
    public function isGestionnaire(): bool   { return $this->role->nomRole === Role::GESTIONNAIRE; }
    public function isParticipant(): bool    { return $this->role->nomRole === Role::PARTICIPANT; }
    public function isOperateurScan(): bool  { return $this->role->nomRole === Role::OPERATEUR_SCAN; }
    public function hasRole(string $r): bool { return $this->role->nomRole === $r; }

    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}
