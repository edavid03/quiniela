<?php

namespace App\Mail;

use App\Models\Liga;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LigaInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Liga $liga,
        public string $username,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Activá tu cuenta en {$this->liga->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invitation',
        );
    }
}
