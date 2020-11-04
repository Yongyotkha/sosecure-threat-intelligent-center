<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TransactionTimeStampScans;
use Illuminate\Support\Facades\File;

class TransactionScanSSH extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:ssh';

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
        $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 0)->where('status', 1)->get();
        foreach($TransactionTimeStampScans as $TransactionTimeStampScan){
            $TransactionTimeStampScan->progress = 1;
            $TransactionTimeStampScan->save();

            $domain = $TransactionTimeStampScan->get_domain->domain;
            $current_1 = '';
            $current_2 = '';
            $cmd_1 = 'ssh -t root@10.104.0.12  /usr/spiderfoot/sf.py -m sfp_dnsbrute,sfp_dnsresolve,sfp_whois,sfp_crobat_api -s '.$domain.' -q -FAFFILIATE_DOMAIN_NAME,AFFILIATE_INTERNET_NAME,INTERNET_NAME';
            $cmd_2 = 'ssh -t root@10.104.0.12  "/usr/spiderfoot/sf.py -m sfp_dnsbrute,sfp_dnsresolve,sfp_whois,sfp_crobat_api,sfp_crt -s '.$domain.' -q -r -F IP_ADDRESS"';
            $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
            );
            flush();

            $process_1 = proc_open($cmd_1, $descriptorspec, $pipes, realpath('./'), array());
            if (is_resource($process_1)) {
                while ($s = fgets($pipes[1])) {
                    $current_1 .= $s;
                    echo $s;
                    flush();
                }
            }
            
            $path = public_path().'/files/scans/'.$TransactionTimeStampScan->get_site->code;
            File::makeDirectory($path, $mode = 0777, true, true);

            $file = $path.'/file_1.txt';
            file_put_contents($file, $current_1);

            $process_2 = proc_open($cmd_2, $descriptorspec, $pipes, realpath('./'), array());
            if (is_resource($process_2)) {
                while ($s = fgets($pipes[1])) {
                    $current_2 .= $s;
                    echo $s;
                    flush();
                }
            }

            $file = $path.'/file_2.txt';
            file_put_contents($file, $current_2);

            $TransactionTimeStampScan->progress = 2;
            $TransactionTimeStampScan->save();
        }
    }
}
