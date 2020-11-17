<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TransactionTimeStampScans;
use App\TransactionScans;
use App\TranSactionScanTemps;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\DataScans;

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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/looking_for_subdomain.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/looking_for_domain_name.txt';
                        $CreateTransactionScanTemps -> save();
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }

                // try {
                //     //full_DNS_recon_all_detail
                //     $array = explode("\n", file_get_contents($path.'/full_DNS_recon_all_detail.txt'));
                //     $arrays = [];
                //     $arrays_final = [];
                //     $arrays_last_final = [];
                //     foreach ($array as $item) {
                //         $arrays[] = explode("\t", $item);
                //     }
                //     foreach($arrays as $data){
                //         $arrays_final[] = $data;
                //     }
                //     foreach($arrays_final as $item){
                //         $arrays = [];
                //         foreach($item as $data){
                //             if(!empty($data)){
                //                 $arrays[] = trim($data);
                //             }
                //         }
                //         $arrays_last_final[] = $arrays;
                //     }
                //     $arrays_last_final = array_filter($arrays_last_final);
                //     array_pop($arrays_last_final);
                //     foreach($arrays_last_final as $item){
                //         $TransactionScanTemps = TransactionScanTemps::where('site_id', $TransactionTimeStampScan->site_id)
                //         ->where('domain_id', $TransactionTimeStampScan->domain_id)
                //         ->where('module', $item[0])
                //         ->where('data_type', $item[1])
                //         ->where('raw_data', $item[2])
                //         ->first();
                //         if(!empty($TransactionScanTemps)){
                //             $TransactionScanTemps -> status = 2;
                //             $TransactionScanTemps -> updated_at = Carbon::now();
                //             $TransactionScanTemps -> save();
                //         }else{
                //             $CreateTransactionScanTemps = new TransactionScanTemps;
                //             $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                //             $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                //             $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                //             $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                //             $CreateTransactionScanTemps -> module = $item[0];
                //             $CreateTransactionScanTemps -> data_type = $item[1];
                //             $CreateTransactionScanTemps -> raw_data = $item[2];
                //             $CreateTransactionScanTemps -> status = 1;
                //             $CreateTransactionScanTemps -> save();
                //         }
                //     }
                // } catch (\Throwable $th) {
                //     //throw $th;
                // }

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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> referent = $item[2];
                        $CreateTransactionScanTemps -> raw_data = $item[3];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/DNS_recon_filter_for_ip_v4.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> referent = $item[2];
                        $CreateTransactionScanTemps -> raw_data = $item[3];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/DNS_recon_filter_for_ip_v6.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/looking_for_hijack_subdomain.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/looking_for_compromised_email.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/looking_for_all_compromised.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/threat_intel_and_blacklist_lookups.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/scraping_names_emails_and_phone_number.txt';
                        $CreateTransactionScanTemps -> save();
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
                        $CreateTransactionScanTemps = new TranSactionScanTemps;
                        $CreateTransactionScanTemps -> code = Str::uuid()->toString();
                        $CreateTransactionScanTemps -> created_by = $TransactionTimeStampScan -> created_by;
                        $CreateTransactionScanTemps -> site_id = $TransactionTimeStampScan->site_id;
                        $CreateTransactionScanTemps -> domain_id = $TransactionTimeStampScan->domain_id;
                        $CreateTransactionScanTemps -> module = $item[0];
                        $CreateTransactionScanTemps -> data_type = $item[1];
                        $CreateTransactionScanTemps -> raw_data = $item[2];
                        $CreateTransactionScanTemps -> status = 1;
                        $CreateTransactionScanTemps -> path = '/port_scanner.txt';
                        $CreateTransactionScanTemps -> save();
                    }
                } catch (\Throwable $th) {
                    //throw $th;
                }

                $TransactionScans = TransactionScans::select('status_progrress')
                ->where('site_id', $TransactionTimeStampScan->site_id)
                ->where('domain_id', $TransactionTimeStampScan->domain_id)->get();
                if($TransactionScans){
                    foreach($TransactionScans as $data){
                        $data -> status = 0;
                        $data -> status_progrress = 0;
                        $data -> save();
                    }    
                }
                
                $TransactionScanTemps = TranSactionScanTemps::where('site_id', $TransactionTimeStampScan->site_id)
                ->where('domain_id', $TransactionTimeStampScan->domain_id)->get();
                foreach($TransactionScanTemps as $data){
                    $TransactionScans_save = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                    ->where('domain_id', $TransactionTimeStampScan->domain_id)
                    ->where('module', $data->module)
                    ->where('data_type', $data->data_type)
                    ->where('referent', $data->referent)
                    ->where('raw_data', $data->raw_data)
                    ->first();
                    if($TransactionScans_save){
                        $TransactionScans_save -> status = 1;
                        $TransactionScans_save -> status_progrress = 1;
                        $TransactionScans_save -> save();
                    }else{
                        $CreateTransactionScan = new TransactionScans;
                        $CreateTransactionScan -> code = $data->code;
                        $CreateTransactionScan -> created_by = $data->created_by;
                        $CreateTransactionScan -> site_id = $data->site_id;
                        $CreateTransactionScan -> domain_id = $data->domain_id;
                        $CreateTransactionScan -> module = $data->module;
                        $CreateTransactionScan -> data_type = $data->data_type;
                        $CreateTransactionScan -> referent = $data->referent;
                        $CreateTransactionScan -> raw_data = $data->raw_data;
                        $CreateTransactionScan -> status = 2;
                        $CreateTransactionScan -> path = $data->path;
                        $CreateTransactionScan -> status_progrress = 1;
                        $CreateTransactionScan -> save();
                    }
                    $data -> delete();
                }

                $TransactionScans_elemtnts = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                ->where('domain_id', $TransactionTimeStampScan->domain_id)->count();

                $TransactionTimeStampScan->elements = $TransactionScans_elemtnts;
                $TransactionTimeStampScan->progress = 3;
                $TransactionTimeStampScan->path = '/files/scans/'.$TransactionTimeStampScan->get_site->code.'/'.$TransactionTimeStampScan->get_domain->code;
                $TransactionTimeStampScan->save();

                

                $DataScans = DataScans::where('site_id', $TransactionTimeStampScan->site_id)
                ->where('domain_id', $TransactionTimeStampScan->domain_id)->get();
                if($DataScans){
                    DataScans::where('site_id', $TransactionTimeStampScan->site_id)
                    ->where('domain_id', $TransactionTimeStampScan->domain_id)->delete();

                    $TransactionScans_data_scan = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                    ->where('domain_id', $TransactionTimeStampScan->domain_id)
                    ->get();
                    if($TransactionScans_data_scan){
                        foreach($TransactionScans_data_scan as $data){
                            $DataScans_data_type = DataScans::where('site_id', $TransactionTimeStampScan->site_id)
                            ->where('domain_id', $TransactionTimeStampScan->domain_id)
                            ->where('data_type', $data->data_type)
                            ->first();
                            if(!$DataScans_data_type){
                                $DataScans_save = new DataScans;
                                $DataScans_save -> code = Str::uuid()->toString();
                                $DataScans_save -> site_id = $TransactionTimeStampScan->site_id;
                                $DataScans_save -> domain_id = $TransactionTimeStampScan->domain_id;
                                $DataScans_save -> data_type = $data->data_type;
                                $DataScans_save -> total = 1;
                                $DataScans_save -> save();
                            }else{
                                $DataScans_data_type -> total = ($DataScans_data_type -> total + 1);
                                $DataScans_data_type -> save();
                            }
                        }
                    }
                }

            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
