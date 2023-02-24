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
            return $this->subject('Notification New Data Leak | Threat inSights')->markdown('emails.template_email_compro');
            // return $this->subject('Notification New Data Leak | Threat Intelligent')->markdown('emails.template_email_compro');
        }else if($this->title == 'brand_abuse'){
            return $this->subject('Notification New Brand Abuse | Threat inSights')->markdown('emails.template_email_compro_brandabuse');
            // return $this->subject('Notification New Brand Abuse | Threat Intelligent')->markdown('emails.template_email_compro_brandabuse');
        }else{
            return $this->subject('Notification New Compromised | Threat inSights')->markdown('emails.template_email_compro');
            // return $this->subject('Notification New Compromised | Threat Intelligent')->markdown('emails.template_email_compro');
        }
        
    }
}
