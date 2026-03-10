<?php
namespace App\Jobs;

use App\Mail\ConfirmationInscription;
use App\Models\Inscription;
use App\Models\Utilisateur;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnvoyerEmailInscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60; // secondes entre les tentatives

    public function __construct(
        public readonly Inscription $inscription,
        public readonly Utilisateur $utilisateur
    ) {}

    public function handle(): void
    {
        Mail::to($this->utilisateur->email)
            ->send(new ConfirmationInscription($this->inscription, $this->utilisateur));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Échec envoi email inscription', [
            'inscription_id' => $this->inscription->idInscription,
            'email'          => $this->utilisateur->email,
            'erreur'         => $exception->getMessage(),
        ]);
    }
}
