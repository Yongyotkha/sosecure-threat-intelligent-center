<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\transcation_jobs_clients;
use App\cpe_data;

class TransactionCheckAssetMappingCPE extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TransactionCheckAssetMappingCPE {site_id} {OS_Type} {IP} {UserName} {Password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TransactionCheckAssetMappingCPE';

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
        $site_id= $this->argument('site_id');
        $IP=$this->argument('IP');
        $Password =$this->argument('Password');
        $UserName =$this->argument('UserName');
        $OS_Type =$this->argument('OS_Type');

        $result = array();
        $result["Result"] = 0;
        $result["message"] = "TPlease try again.";
        $result["mode"] = "";
        $result["result"] = "";
        $result["os_name"] = "";
        $result["cpe"] = "";
        try {
           $TransactionBatchjob_check = transcation_jobs_clients::where('site_id',$site_id)->where('status','1')->where('mode','asset_check_cpe')->first();
           if (!$TransactionBatchjob_check) {
            $TransactionBatchjob_save = new transcation_jobs_clients;
            $TransactionBatchjob_save->site_id = $site_id;
            $TransactionBatchjob_save->mode = 'asset_check_cpe';
            $TransactionBatchjob_save->status = 1;
            $TransactionBatchjob_save->transaction_data_status = 3;
            $TransactionBatchjob_save->created_at = date("Y-m-d H:i:s");
            $TransactionBatchjob_save->save();
            $TransactionBatchjob_check = transcation_jobs_clients::where('site_id',$site_id)->where('status','1')->where('mode','asset_check_cpe')->first();
        }
        $job_key = $this-> generateRandomString();
        for ($i=0; $i <= 30 ; $i++) { 
            if ($TransactionBatchjob_check->transaction_data_status ==3) {
                $result["Result"] = 2;
                $result["message"] = "";
                $TransactionBatchjob_check->transaction_data_status =1;
                $TransactionBatchjob_check->job_key =$job_key;
                $TransactionBatchjob_check->data ='{"os_type":"'.$OS_Type.'","IP":"'.$IP.'","UserName":"'.$UserName.'","Password":"'.$Password.'"}';
                $TransactionBatchjob_check->save();
                break;

            }else{
             $TransactionBatchjob_check = transcation_jobs_clients::where('site_id',$site_id)->where('status','1')->where('mode','asset_check_cpe')->first();
             if ($i == 30) {
                $result["Result"] = 0;
                $result["message"] = "There are other processes running. Please try again.";
                break;
            }

        }
    }


    if ($result["Result"] == 2) {

        do {
            if ($i < 1000) {

                break;
            }

            $TransactionBatchjob_check = transcation_jobs_clients::where('site_id',$site_id)->where('job_key',$job_key)->where('status','1')->where('mode','asset_check_cpe')->first();
            if ($TransactionBatchjob_check->transaction_data_status ==3) {

                $json = '{"Result":1,"message":"","os_type":"linux","data_all":"Static hostname: threat-insight
                Icon name: computer-vm
                Chassis: vm
                Machine ID: 2ebc13a1fe40472f970a55a9ca21aa37
                Boot ID: 70477747ef53417896940da6453f80e1
                Virtualization: vmware
                Operating System: Ubuntu 20.04.1 LTS
                Kernel: Linux 5.4.0-65-generic
                Architecture: x86-64
                ","data_version":"Ubuntu 20.04.1 LTS"}';





                $result["Result"] = 1;
                $result["message"] = "";


                $result["mode"] = "";
                $result["result"] =  json_decode($json, true);
                $result["os_name"] = "";
                $result["cpe"] = "";
                break;
            }

            sleep(10);
        } while (0);


    }
    $json = '{"Result":1,"message":"1","os_type":"linux","data_all":"Static hostname: threat-insight Icon name: computer-vm Chassis: vm Machine ID: 2ebc13a1fe40472f970a55a9ca21aa37 Boot ID: 70477747ef53417896940da6453f80e1 Virtualization: vmware Operating System: Ubuntu 20.04.1 LTS Kernel: Linux 5.4.0-65-generic","data_version":"Ubuntu 20.04.1 LTS"}';

    $data_json = json_decode($json);
    // var_dump($data_json);
    // print_r($data_json);
    if ($data_json->Result==1) {
        $result["mode"] = "1";
        $result["result"] =  "tatic hostname: threat-insight Icon name: computer-vm Chassis: vm Machine ID: 2ebc13a1fe40472f970a55a9ca21aa37 Boot ID: 70477747ef53417896940da6453f80e1 Virtualization: vmware Operating System: Ubuntu 20.04.1 LTS Kernel: Linux 5.4.0-65-generic";
        $result["os_name"] = "Ubuntu 20.04.1 LTS";
        $result["cpe"] = "cpe:2.3:o:canonical:ubuntu_linux:8.04:*:*:*:-:*:*:*";
    }


    $result["Result"] = 1;
    $result["message"] = "";




} catch (Exception $e) {
   $result["Result"] = 0;
   $result["message"] = $e->getMessage();
}





$result_json_e = json_encode($result);
echo $result_json_e;



}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

}
