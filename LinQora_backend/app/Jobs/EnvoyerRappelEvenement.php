<?php
namespace App\Jobs;

use App\Mail\RappelEvenement;
use App\Models\Evenement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnvoyerRappelEvenement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Evenement $evenement) {}

    public function handle(): void
    {
        $inscrits = $this->evenement->inscriptions()
            ->with(['participant.utilisateur'])
            ->where('statut', 'confirme')
            ->get();

        foreach ($inscrits as $inscription) {
            Mail::to($inscription->participant->utilisateur->email)
                ->send(new RappelEvenement($inscription));
        }

        Log::info("Rappels envoyés pour l'événement #{$this->evenement->idEvenement} ({$inscrits->count()} participants)");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Échec envoi rappels événement', [
            'evenement_id' => $this->evenement->idEvenement,
            'erreur'       => $exception->getMessage(),
        ]);
    }
}
