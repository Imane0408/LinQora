<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entreprise extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'idEntreprise';
    protected $fillable   = ['nom', 'logo', 'email', 'telephone', 'adresse', 'actif'];
    protected $casts      = ['actif' => 'boolean'];

    public function utilisateurs(): HasMany
    {
        return $this->hasMany(Utilisateur::class, 'idEntreprise', 'idEntreprise');
    }

    public function evenements(): HasMany
    {
        return $this->hasMany(Evenement::class, 'idEntreprise', 'idEntreprise');
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(Speaker::class, 'idEntreprise', 'idEntreprise');
    }
}
