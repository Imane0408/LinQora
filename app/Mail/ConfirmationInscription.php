<?php
namespace App\Mail;

use App\Models\Inscription;
use App\Models\Utilisateur;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationInscription extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Inscription $inscription,
        public readonly Utilisateur $utilisateur
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Confirmation d'inscription — {$this->inscription->evenement->titre}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-inscription',
            with: [
                'prenom'    => $this->utilisateur->prenom,
                'nom'       => $this->utilisateur->nom,
                'evenement' => $this->inscription->evenement,
                'ateliers'  => $this->inscription->ateliers,
                'codeQr'    => $this->inscription->codeQr,
                'statut'    => $this->inscription->statut,
            ]
        );
    }
}
