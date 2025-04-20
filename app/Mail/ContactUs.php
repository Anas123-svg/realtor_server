<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    public function __construct($emailData)
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
        return $this->view('emails.contact_us')
                    ->with([
                        'userName' => $this->emailData['userName'],
                        'userEmail' => $this->emailData['userEmail'],
                        'userPhone' => $this->emailData['userPhone'],
                        'userCountry' => $this->emailData['userCountry'],
                        'userCity' => $this->emailData['userCity'],
                        'userMessage' => $this->emailData['userMessage'],
                        'reason' => $this->emailData['reason'],
                    ])
                    ->subject('New Contact Message');
    }
}
