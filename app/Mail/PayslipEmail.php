<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class PayslipEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $payslip;
    public $business;
    public $pdf;

    /**
     * Create a new message instance.
     */
    public function __construct($payslip, $business, $pdf)
    {
        $this->payslip = $payslip;
        $this->business = $business;
        $this->pdf = $pdf;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Payslip for ' . $this->payslip->month . ', ' . $this->payslip->year,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payslip.notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdf->output(), 'Payslip-' . $this->payslip->month . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}