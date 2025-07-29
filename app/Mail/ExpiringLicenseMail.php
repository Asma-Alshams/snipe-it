<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiringLicenseMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($params, $threshold, $isIndividual = false)
    {
        $this->licenses = $params;
        $this->threshold = $threshold;
        $this->isIndividual = $isIndividual;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        $subject = $this->isIndividual 
            ? trans('mail.Your_License_Expiring_Alert')
            : trans('mail.Expiring_Licenses_Report');

        return new Envelope(
            from: $from,
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $template = $this->isIndividual 
            ? 'notifications.markdown.report-expiring-licenses-individual'
            : 'notifications.markdown.report-expiring-licenses';

        return new Content(
            markdown: $template,
            with: [
                'licenses'  => $this->licenses,
                'threshold'  => $this->threshold,
                'isIndividual' => $this->isIndividual,
            ]
        );
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
