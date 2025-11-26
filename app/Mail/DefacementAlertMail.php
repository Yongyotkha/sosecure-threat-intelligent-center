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
    public array $diff = [];
    public int $limit = 20;
    public ?string $viewUrl = null;
    public ?string $pdfPath = null; // ✅ เพิ่ม

    /**
     * @param WebdefacmentSetting $w
     * @param array<string,mixed> $diff
     * @param int $limit
     * @param string|null $viewUrl
     * @param string|null $pdfPath
     */
    public function __construct(WebdefacmentSetting $w, array $diff = [], $limit = 20, $viewUrl = null, $pdfPath = null)
    {
        $this->w = $w;
        $this->diff = $diff;
        $this->limit = (int) $limit;
        $this->viewUrl = $viewUrl;
        $this->pdfPath = $pdfPath; // ✅ เก็บ path ไฟล์ pdf ที่สร้างไว้
    }

    public function build()
    {
        $subjectName = $this->getSitename();

        $mail = $this->subject("[ALERT] Web Defacement - {$subjectName}")
            ->view('emails.defacement_alert')
            ->with([
                'w'       => $this->w,
                'diff'    => $this->diff,
                'limit'   => $this->limit,
                'viewUrl' => $this->viewUrl
            ]);

        // ✅ แนบ PDF ถ้ามีไฟล์จริง
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as' => 'Defacement_Report.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

    public function getSitename()
    {
        return $this->w->name ?? 'Unknown Site';
    }
}
