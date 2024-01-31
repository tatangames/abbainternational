<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorreoTokenPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    public $dataArray;

    public function __construct($dataArray)
    {
        $this->dataArray = $dataArray;
    }

    /**
     * Get the message envelope.
     */


    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'correos.recuperarpasswordadmin',
        );
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->view('correos.recuperarpasswordadmin')
            ->subject("Recuperar contraseña - Abba")
            ->with($this->dataArray);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
