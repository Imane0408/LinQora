<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationApp extends Model
{
    use HasFactory;

    protected $table      = 'notifications_app';
    protected $primaryKey = 'idNotification';

    protected $fillable = [
        'sujet', 'contenu', 'statutEnvoi', 'type', 'dateCreation',
        'dateLecture', 'lu', 'idUtilisateur', 'idRencontre', 'idEvenement',
    ];

    protected $casts = [
        'dateCreation' => 'datetime',
        'dateLecture'  => 'datetime',
        'lu'           => 'boolean',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'idUtilisateur', 'idUtilisateur');
    }

    public function rencontre(): BelongsTo
    {
        return $this->belongsTo(Rencontre::class, 'idRencontre', 'idRencontre');
    }

    public function evenement(): BelongsTo
    {
        return $this->belongsTo(Evenement::class, 'idEvenement', 'idEvenement');
    }

    public function marquerCommeLu(): void
    {
        $this->update(['lu' => true, 'dateLecture' => now()]);
    }
}
