<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TransactionTimeStampScans;
use App\TransactionScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class TransactionSaveScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:saveScan';

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
        $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 2)->where('status', 1)->get();
        foreach($TransactionTimeStampScans as $TransactionTimeStampScan){
            $path = public_path().'/files/scans/'.$TransactionTimeStampScan->get_site->code.'/'.$TransactionTimeStampScan->get_domain->code;
            $array = explode("\n", file_get_contents($path.'/looking_for_subdomain.txt'));
            $arrays = [];
            $arrays_final = [];
            $arrays_last_final = [];
            foreach ($array as $item) {
                $arrays[] = explode("\t", $item);
            }
            foreach($arrays as $data){
                $arrays_final[] = $data;
            }
            foreach($arrays_final as $item){
                $arrays = [];
                foreach($item as $data){
                    if(!empty($data)){
                        $arrays[] = trim($data);
                    }
                }
                $arrays_last_final[] = $arrays;
            }
            $arrays_last_final = array_filter($arrays_last_final);
            array_pop($arrays_last_final);
            foreach($arrays_last_final as $item){
                $TransactionScans = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                ->where('domain_id', $TransactionTimeStampScan->domain_id)
                ->where('module', $item[0])
                ->where('data_type', $item[1])
                ->where('raw_data', $item[2])
                ->first();
                if(!empty($TransactionScans)){
                    $TransactionScans -> updated_at = Carbon::now();
                }else{
                    $CreateTransactionScans = new TransactionScans;
                    $CreateTransactionScans -> created_by = $TransactionTimeStampScan -> created_by;
                    $CreateTransactionScans -> site_id = $TransactionTimeStampScan->site_id;
                    $CreateTransactionScans -> domain_id = $TransactionTimeStampScan->domain_id;
                    $CreateTransactionScans -> module = $item[0];
                    $CreateTransactionScans -> data_type = $item[1];
                    $CreateTransactionScans -> raw_data = $item[2];
                    $CreateTransactionScans -> status = 1;
                    $CreateTransactionScans -> save();
                }
            }
        }
    }
}
