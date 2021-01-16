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
    public $title;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $compromised, $title)
    {
        $this->compromised = $compromised;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->title == 'data_leak'){
            return $this->subject('Notification New Data Leak | Threat Intelligent Center')->markdown('emails.template_email_compro');
        }else{
            return $this->subject('Notification New Compromised | Threat Intelligent Center')->markdown('emails.template_email_compro');
        }
        
    }
}
