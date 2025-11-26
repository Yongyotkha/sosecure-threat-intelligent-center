<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DefacementAlertWebDownMail extends Mailable
{
    use Queueable, SerializesModels;

    public $webSetting; // ตัวแปรที่ใช้ส่งไปยัง view

    /**
     * Create a new message instance.
     *
     * @param  mixed  $webSetting
     * @return void
     */
    public function __construct($webSetting)
    {
        $this->webSetting = $webSetting;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('Building DefacementAlertWebDownMail for site: ' . ($this->webSetting->name ?? 'unknown'));

        return $this->subject('[ALERT] เว็บไซต์ไม่สามารถเข้าถึงได้')
            ->view('emails.defacement_alert_web_down')
            ->with(['setting' => $this->webSetting]);
    }
}
