<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
        return $this->view('emails.contact')
                    ->with([
                        'userName' => $this->emailData['userName'],
                        'userEmail' => $this->emailData['userEmail'],
                        'userPhone' => $this->emailData['userPhone'],
                        'userMessage' => $this->emailData['userMessage'],
                    ])
                    ->subject('New Contact Message');
    }
}
