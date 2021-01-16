<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompromisedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $compromised;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $compromised)
    {
        $this->compromised = $compromised;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Notification New Compromised | Threat Intelligent Center')->markdown('emails.template_email_compro');
    }
}
