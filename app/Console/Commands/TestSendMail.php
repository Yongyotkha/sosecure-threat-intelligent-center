<?php

namespace App\Console\Commands;

use App\Mail\TestSend;
use Illuminate\Console\Command;
use Modules\Deals\Entities\Deal;
use Modules\Estimates\Entities\Estimate;
use Modules\Expenses\Entities\Expense;
use Modules\Invoices\Entities\Invoice;
use Modules\Leads\Entities\Lead;
use Modules\Payments\Entities\Payment;
use Modules\Tasks\Entities\Task;
use Modules\Tickets\Entities\Ticket;
use Modules\Timetracking\Entities\TimeEntry;
use Modules\Users\Emails\DailyDigestMail;
use Modules\Users\Entities\User;
use Modules\Webhook\Jobs\Xrun;


class TestSendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:testSendMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Daily Summary';

    protected $summary;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Mail::to('romrun.2k@gmail.com')->send(new TestSend());
        $this->info('Daily summary sent successfully');
    }
    
    
}
