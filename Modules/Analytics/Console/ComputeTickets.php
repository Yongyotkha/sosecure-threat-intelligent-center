<?php

namespace Modules\Analytics\Console;

use Illuminate\Console\Command;
use Modules\Tickets\Entities\Ticket;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ComputeTickets extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'analytics:tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to calculate ticket reports.';

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
        $this->total();
        $this->open();
        $this->closed();
        $this->thisMonth();
        $this->avgResponse();
        $this->yearlyClosed();
        $this->yearlyOpen();
        $this->yearlyTotal();
        $this->info('Tickets reports calculated successfully');
    }
    protected function total()
    {
        \App\Entities\Computed::updateOrCreate(
            ['key' => 'tickets_total'],
            ['value' => Ticket::count()]
        );
    }
    protected function open()
    {
        \App\Entities\Computed::updateOrCreate(
            ['key' => 'tickets_open'],
            ['value' => Ticket::pending()->count()]
        );
    }
    protected function closed()
    {
        \App\Entities\Computed::updateOrCreate(
            ['key' => 'tickets_closed'],
            ['value' => Ticket::closed()->count()]
        );
    }
    protected function thisMonth()
    {
        \App\Entities\Computed::updateOrCreate(
            ['key' => 'tickets_this_month'],
            ['value' => Ticket::whereYear('created_at', now()->format('Y'))->whereMonth('created_at', now()->format('n'))->count()]
        );
    }
    protected function avgResponse()
    {
        \App\Entities\Computed::updateOrCreate(
            ['key' => 'tickets_avg_response'],
            ['value' => Ticket::whereNotNull('closed_at')->avg('resolution_time')]
        );
    }

    protected function yearlyClosed()
    {
        $year = chartYear();
        for ($i = 1; $i < 13; $i++) {
            \App\Entities\Computed::updateOrCreate(
                ['key' => 'tickets_closed_'.$i.'_'.$year],
                ['value' => Ticket::closed()->whereYear('created_at', $year)->whereMonth('created_at', $i)->count()]
            );
        }
    }
    protected function yearlyOpen()
    {
        $year = chartYear();
        for ($i = 1; $i < 13; $i++) {
            \App\Entities\Computed::updateOrCreate(
                ['key' => 'tickets_pending_'.$i.'_'.$year],
                ['value' => Ticket::pending()->whereYear('created_at', $year)->whereMonth('created_at', (string)$i)->count()]
            );
        }
    }
    protected function yearlyTotal()
    {
        $year = chartYear();
        for ($i = 1; $i < 13; $i++) {
            \App\Entities\Computed::updateOrCreate(
                ['key' => 'tickets_'.$i.'_'.$year],
                ['value' => Ticket::whereYear('created_at', $year)->whereMonth('created_at', (string)$i)->count()]
            );
        }
    }
}
