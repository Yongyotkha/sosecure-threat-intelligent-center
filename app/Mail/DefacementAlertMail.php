<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\WebDefacement\Entities\WebdefacmentSetting;

class DefacementAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public WebdefacmentSetting $w;

    /**
     * รับข้อมูล webdefacment setting
     */
    public function __construct(WebdefacmentSetting $w)
    {
        $this->w = $w;
    }

    public function build()
    {
        $subjectName = $this->w->name ?? 'Unknown Site';

        return $this->subject("[ALERT] Web Defacement - {$subjectName}")
            ->view('emails.defacement_alert') 
            ->with([
                'w' => $this->w,
            ]);
    }

    public function getSitename(){
        return $this->w->name ?? 'Unknown Site';
    }
}
