<?php

namespace Modules\SiteSettings\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SiteCreateUserMail extends Mailable implements ShouldQueue
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
        return $this->from(get_option('company_email'), get_option('company_name'))
            ->subject('Create Site User '.now()->toFormattedDateString())
            ->markdown('emails.create_site_user');
    }
}
