<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TransactionTimeStampScans;
use App\TransactionScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
        try {
            $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 2)->where('status', 1)->get();
            foreach($TransactionTimeStampScans as $TransactionTimeStampScan){
                $path = public_path().'/files/scans/'.$TransactionTimeStampScan->get_site->code.'/'.$TransactionTimeStampScan->get_domain->code;

                try {
                    // looking_for_subdomain
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //looking_for_domain_name
                    $array = explode("\n", file_get_contents($path.'/looking_for_domain_name.txt'));
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //DNS_recon_filter_for_ip_v4
                    $array = explode("\n", file_get_contents($path.'/DNS_recon_filter_for_ip_v4.txt'));
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
                        ->where('referent', $item[2])
                        ->where('raw_data', $item[3])
                        ->first();
                        if(!empty($TransactionScans)){
                            $TransactionScans -> updated_at = Carbon::now();
                        }else{
                            $CreateTransactionScans = new TransactionScans;
                            $CreateTransactionScans -> code = Str::uuid()->toString();
                            $CreateTransactionScans -> created_by = $TransactionTimeStampScan -> created_by;
                            $CreateTransactionScans -> site_id = $TransactionTimeStampScan->site_id;
                            $CreateTransactionScans -> domain_id = $TransactionTimeStampScan->domain_id;
                            $CreateTransactionScans -> module = $item[0];
                            $CreateTransactionScans -> data_type = $item[1];
                            $CreateTransactionScans -> referent = $item[2];
                            $CreateTransactionScans -> raw_data = $item[3];
                            $CreateTransactionScans -> status = 1;
                            $CreateTransactionScans -> save();
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //DNS_recon_filter_for_ip_v6
                    $array = explode("\n", file_get_contents($path.'/DNS_recon_filter_for_ip_v6.txt'));
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
                        ->where('referent', $item[2])
                        ->where('raw_data', $item[3])
                        ->first();
                        if(!empty($TransactionScans)){
                            $TransactionScans -> updated_at = Carbon::now();
                        }else{
                            $CreateTransactionScans = new TransactionScans;
                            $CreateTransactionScans -> code = Str::uuid()->toString();
                            $CreateTransactionScans -> created_by = $TransactionTimeStampScan -> created_by;
                            $CreateTransactionScans -> site_id = $TransactionTimeStampScan->site_id;
                            $CreateTransactionScans -> domain_id = $TransactionTimeStampScan->domain_id;
                            $CreateTransactionScans -> module = $item[0];
                            $CreateTransactionScans -> data_type = $item[1];
                            $CreateTransactionScans -> referent = $item[2];
                            $CreateTransactionScans -> raw_data = $item[3];
                            $CreateTransactionScans -> status = 1;
                            $CreateTransactionScans -> save();
                        }
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //looking_for_hijack_subdomain
                    $array = explode("\n", file_get_contents($path.'/looking_for_hijack_subdomain.txt'));
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //looking_for_compromised_email
                    $array = explode("\n", file_get_contents($path.'/looking_for_compromised_email.txt'));
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //looking_for_all_compromised
                    $array = explode("\n", file_get_contents($path.'/looking_for_all_compromised.txt'));
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //threat_intel_and_blacklist_lookups
                    $array = explode("\n", file_get_contents($path.'/threat_intel_and_blacklist_lookups.txt'));
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                   //scraping_names_emails_and_phone_number
                    $array = explode("\n", file_get_contents($path.'/scraping_names_emails_and_phone_number.txt'));
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }

                try {
                    //port_scanner
                    $array = explode("\n", file_get_contents($path.'/port_scanner.txt'));
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
                            $CreateTransactionScans -> code = Str::uuid()->toString();
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
                } catch (\Throwable $th) {
                    //throw $th;
                }
                
                $TransactionTimeStampScan->progress = 3;
                $TransactionTimeStampScan->save();
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
