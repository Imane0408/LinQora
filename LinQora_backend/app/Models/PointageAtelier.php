<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointageAtelier extends Model
{
    use HasFactory;

    protected $table      = 'pointage_atelier';
    protected $primaryKey = 'idPointageAT';

    protected $fillable = ['heureScan', 'estPresent', 'idInscription', 'idAtelier', 'idOperateur'];

    protected $casts = [
        'heureScan'  => 'datetime',
        'estPresent' => 'boolean',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class, 'idInscription', 'idInscription');
    }

    public function atelier(): BelongsTo
    {
        return $this->belongsTo(Atelier::class, 'idAtelier', 'idAtelier');
    }

    public function operateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'idOperateur', 'idUtilisateur');
    }
}
