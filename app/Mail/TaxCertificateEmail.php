<?php

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaxCertificateEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $business;
    public $fy;
    public $pdf;

    public function __construct($employee, Business $business, $fy, $pdf)
    {
        $this->employee = $employee;
        $this->business = $business;
        $this->fy = $fy;
        $this->pdf = $pdf;
    }

    public function build()
    {
        $subject = "Tax Deduction Certificate ($this->fy) - " . $this->business->name;

        return $this->subject($subject)
                    ->view('emails.tax_certificate') // We will create this simple view
                    ->attachData($this->pdf->output(), "Tax_Certificate_{$this->fy}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
    }
}