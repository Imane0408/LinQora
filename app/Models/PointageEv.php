<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointageEv extends Model
{
    use HasFactory;

    protected $table      = 'pointage_ev';
    protected $primaryKey = 'idPointageEv';

    protected $fillable = ['heureEntree', 'heureSortie', 'estPresent', 'idInscription', 'idOperateur'];

    protected $casts = [
        'heureEntree' => 'datetime',
        'heureSortie' => 'datetime',
        'estPresent'  => 'boolean',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'idInscription', 'idInscription');
    }

    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'idOperateur', 'idUtilisateur');
    }
}
