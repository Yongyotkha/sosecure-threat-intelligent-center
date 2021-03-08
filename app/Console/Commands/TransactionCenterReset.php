<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Console\Command;
use App\TransactionTimeStampScans;
use App\Log;
use Illuminate\Support\Facades\DB;
use App\LogSendTransaction;
class TransactionCenterReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TransactionCenterReset';
    protected $description = 'TransactionCenterReset';

    /**
     * The console command description.
     *
     * @var string
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
      //Reset Scan Domain  date("Y-m-d H:i:s ")
      TransactionTimeStampScans::where('status', '=', 1)->where('progress', '=', 3)->update(array('progress' => 0));
      $this->info('Reset Scan Domain successfully');

      Log::where('id', '>', 1)->delete();
      DB::statement("ALTER TABLE `fx_logs` AUTO_INCREMENT = 1;");
      $this->info('Delete Log successfully');


      LogSendTransaction::where('id', '>', 1)->delete();
      DB::statement("ALTER TABLE `fx_logs_sent_transaction` AUTO_INCREMENT = 1;");
      $this->info('Delete LogSendTransaction successfully');

     }

}
