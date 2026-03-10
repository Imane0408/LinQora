<?php
namespace App\Mail;

use App\Models\Inscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RappelEvenement extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Inscription $inscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⏰ Rappel — {$this->inscription->evenement->titre} commence bientôt !",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rappel-evenement',
            with: [
                'inscription' => $this->inscription,
                'evenement'   => $this->inscription->evenement,
                'participant' => $this->inscription->participant->utilisateur,
            ]
        );
    }
}
