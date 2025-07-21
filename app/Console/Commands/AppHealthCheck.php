<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Clients\Entities\Client;
use Modules\Deals\Entities\Deal;
use Modules\Expenses\Entities\Expense;
use Modules\Invoices\Entities\Invoice;
use Modules\Projects\Entities\Project;
use Modules\Users\Entities\Profile;
use Modules\Users\Entities\User;

class AppHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan database for potential orphan records';

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
        // TODO complete checks
        $this->checkOrpanRecords();
        $this->info('Application health checked successfully');
    }

    protected function checkOrpanRecords()
    {
        $this->invoicesWithoutClient();
        $this->expensesWithoutProject();
        $this->clientsWithoutContact();
        $this->dealsWithoutContact();
        $this->contactWithoutClient();
    }

    protected function invoicesWithoutClient()
    {
        foreach (Invoice::select('id', 'client_id', 'reference_no')->whereNull('archived_at')->get() as $invoice) {
            if (Client::select('id')->whereId($invoice->client_id)->count() === 0) {
                $invoice->delete();
                $this->info($invoice->reference_no . ' has been removed');
            }
        }
    }
    protected function contactWithoutClient()
    {
        foreach (Profile::select('id', 'user_id', 'company')->where('company', '>', 0)->get() as $profile) {
            if (Client::select('id')->where('id', $profile->company)->count() === 0) {
                $profile->update(['company' => 0]);
                $this->info($profile->user->name . ' company has been fixed');
            }
        }
    }

    protected function expensesWithoutProject()
    {
        foreach (Expense::select('project_id', 'code')->where('project_id', '>', 0)->get() as $expense) {
            if (Project::select('id')->whereId($expense->project_id)->count() === 0) {
                $expense->update(['project_id' => 0]);
                $this->info($expense->code . ' is attached to a missing project ID ' . $expense->project_id);
            }
        }
    }

    protected function clientsWithoutContact()
    {
        foreach (Client::select('id', 'primary_contact')->get() as $client) {
            if (User::select('id')->whereId($client->primary_contact)->count() === 0 && $client->primary_contact > 0) {
                $client->update(['primary_contact' => null]);
                $this->info($client->id . ' is attached to a missing contact');
            }
        }
    }

    protected function dealsWithoutContact()
    {
        foreach (Deal::select('id', 'contact_person', 'title')->get() as $deal) {
            if (User::select('id')->whereId($deal->contact_person)->count() === 0 && $deal->contact_person > 0) {
                $deal->update(['contact_person' => null]);
                $this->info($deal->title . ' is attached to a missing contact');
            }
        }
    }
}
