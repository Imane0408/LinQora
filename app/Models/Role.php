<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'idRole';
    protected $fillable   = ['nomRole', 'description'];

    // Constantes des rôles disponibles
    const SUPER_ADMIN      = 'super_admin';
    const ADMIN_ENTREPRISE = 'admin_entreprise';
    const GESTIONNAIRE     = 'gestionnaire';
    const PARTICIPANT      = 'participant';
    const OPERATEUR_SCAN   = 'operateur_scan';

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateur::class, 'idRole', 'idRole');
    }
}
