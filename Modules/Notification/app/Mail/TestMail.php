<?php

namespace Modules\Notification\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName = 'Usuario'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prueba de Correo - Sistema de Convocatorias CAS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'notification::emails.test',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
