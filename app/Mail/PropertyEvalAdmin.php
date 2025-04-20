<?php
// app/Mail/PropertyEvalAdmin.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PropertyEvalAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
        return $this->subject('New Property Evaluation Request')
                    ->view('emails.propertyEvalAdmin');
    }
}
