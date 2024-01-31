<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorreoPasswordMail extends Mailable
{

    // * UTILIZADO PARA APLICACION MOVIL


    use Queueable, SerializesModels;
    public $dataArray;
    public $sujeto;

    public function __construct($dataArray, $sujeto)
    {
        $this->dataArray = $dataArray;
        $this->sujeto = $sujeto;
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
            view: 'correos.vistarecuperarpassword',
        );
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->view('correos.vistarecuperarpassword')
            ->subject($this->sujeto . " - Abba")
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
