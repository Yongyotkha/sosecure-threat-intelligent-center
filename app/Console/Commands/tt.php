<?php

namespace App\Console\Commands;

use App\DataLeakFeedTemp;
use Illuminate\Console\Command;

class tt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $DataLeakFeedTemp = DataLeakFeedTemp::all();
        var_dump($DataLeakFeedTemp);
    }
}
