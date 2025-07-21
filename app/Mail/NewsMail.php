<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewsMail extends Mailable
{
    use Queueable, SerializesModels;
    public $news;
    public $mail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $news)
    {
        $this->news = $news;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Cyber News Update | Threat inSights')->markdown('emails.template_email_new_news');
        // $this->subject('Cyber News Update | Threat Intelligent')->markdown('emails.template_email_new_news');
    }
}
