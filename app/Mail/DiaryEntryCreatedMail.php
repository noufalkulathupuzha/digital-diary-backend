<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\DiaryEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiaryEntryCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DiaryEntry $entry)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have added an entry',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.diary-entry-created',
        );
    }
}
