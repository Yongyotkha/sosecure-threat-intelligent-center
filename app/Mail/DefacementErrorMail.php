<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\WebDefacement\Entities\WebdefacmentSetting;

class DefacementErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $w;
    public $errorMessage;

    /**
     * Create a new message instance.
     *
     * @param WebdefacmentSetting $w
     * @param string $errorMessage
     */
    public function __construct(WebdefacmentSetting $w, $errorMessage)
    {
        $this->w = $w;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $sitename = $this->w->name ?? 'Unknown Site';
        return $this->subject("[ERROR] Web Defacement Check Failed - {$sitename}")
                    ->view('emails.defacement_error'); 
    }
}
