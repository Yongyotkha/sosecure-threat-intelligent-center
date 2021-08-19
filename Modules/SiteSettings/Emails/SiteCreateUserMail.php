<?php

namespace Modules\SiteSettings\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
// implements ShouldQueue
class SiteCreateUserMail extends Mailable
{
    use Queueable, SerializesModels;
    public $summary;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $summary)
    {
        $this->summary = $summary;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('view.name');
        // return $this->from(get_option('company_email'), get_option('company_name'))
        //     // ->subject('Create Site User '.now()->toFormattedDateString())
        //     ->subject('Create Site User')
        //     ->markdown('emails.create_site_user');


            $mail = $this->subject('Notification Register | Threat inSight')
            // $mail = $this->subject('Notification Register | Threat Intelligent')
            // ->from('sales@dsure.net', 'Dsure')//glorysociety@gmail.com
            // ->view('backEnd.template_email.send_massage_data_mailto_org_ins');
            ->markdown('emails.template_email_create_site_user');

            // if (File::exists(public_path($part_file).'/Application_Form_Insurance.xlsx')) {
            //     $mail->attach(public_path($part_file).'/Application_Form_Insurance.xlsx');
            //     $this -> Application_Form_Insurance_text = 'ใบแจ้งประกัน';
            //     // $mail->attach(public_path($part_file).'/'.$this->NO_ID->path , [
            //     //         'as' => $this->NO_ID->path,
            //     //         'mime' => get_mimetype($this->NO_ID->path),
            //     // ]);
            // }

            // if (File::exists(public_path($part_file).'/'.$this->NO_ID->file_name)) {
            //     $mail->attach(public_path($part_file).'/'.$this->NO_ID->file_name);
            //     $this -> NO_ID_text = 'บัตรประจำตัวประชาชน';
            //     // $mail->attach(public_path($part_file).'/'.$this->NO_ID->path , [
            //     //         'as' => $this->NO_ID->path,
            //     //         'mime' => get_mimetype($this->NO_ID->path),
            //     // ]);
            // }

            // if($this->LISNO) {
            //     foreach($this->LISNO as $liso_item) {

            //         if (File::exists(public_path($part_file).'/'.$liso_item->file_name)) {
            //             $mail->attach(public_path($part_file).'/'.$liso_item->file_name);
            //             $this -> LISNO_text = 'ทะเบียนรถยนต์';
            //         }
            //     }
            // }

            // if($this->sys_file) {
            //     foreach($this->sys_file as $sys_file_item) {

            //         if (File::exists(public_path($part_file).'/'.$sys_file_item->file_name)) {
            //             $mail->attach(public_path($part_file).'/'.$sys_file_item->file_name);
            //             $this -> sys_file_text = 'รูปภาพที่อัพโหลด';
            //         }
            //     }
            // }



        return $mail;


    }
}
