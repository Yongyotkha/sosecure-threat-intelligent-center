<?php

namespace App\Console\Commands;

use Crypt;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Storage;
use App\transcation_jobs_clients;

class site_setpermission_log_transaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:site_setpermission_log_transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'site_setpermission_log_transaction สำหรับแก้ปัญหา site ธกส ที่ยังแก้ปัญหา set permisstion ไม่ได้';

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
      
        transcation_jobs_clients::whereIn('id', ['589', '590', '591', '592', '593', '594', '595', '596', '597', '598'])->update( array('transaction_data_status'=>'1') );


    }



}
