<?php

namespace App\Console\Commands;

use Crypt;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Storage;

class test_connect_search extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test_connect_search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check application license';

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




        //x-acloud=======================================================
            // $ibmcloud_API_Key = "d4b45ba9-4a1f-4127-bb72-1a01ab26a4b9";
            // $ibmcloud_API_Key_Password = "95d8e0cd-0f34-45dc-9c6c-aa490fcb0415";
            // $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/ipr/190.187.248.117";
            // $ch = curl_init();
            // header('Content-type: application/json');
            // curl_setopt($ch, CURLOPT_URL,$ibmcloud_url);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // curl_setopt($ch, CURLOPT_USERPWD, "$ibmcloud_API_Key:$ibmcloud_API_Key_Password");
            // $result = curl_exec($ch);
            // $response = $result;
            // curl_close($ch);  
            // echo($result);

// 
        // //virustotal=======================================================
        //     $virustotal_API_Key = "8ed71053d254aa99c9a79b73c6f3223cac762c2c77628d075e62ec506a538267";
        //     $virustotal_url = "https://www.virustotal.com/api/v3/ip_addresses/190.187.248.117";
        //     //$virustotal_url='https://www.virustotal.com/api/v3/domains/xlus0222uj81bxyf.xyz';
        //     $headers = array(
        //          'X-Apikey: '.$virustotal_API_Key
        //     );
        //     // Send request to Server
        //     $ch = curl_init($virustotal_url);
        //     // To save response in a variable from server, set headers;
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //     // Get response
        //     $response = curl_exec($ch);
        //     curl_close($ch);  
        //     echo($response);


              //virustotal=======================================================
            $hybrid_API_Key = "kpy0ibau846587b1lnemkw4k082be03bncw1bkz140a16b6cs64sk6uzf0498e3f";
            $hybrid_url = "https://www.hybrid-analysis.com/api/v2/search/terms";
            //$virustotal_url='https://www.virustotal.com/api/v3/domains/xlus0222uj81bxyf.xyz';
            $headers = array(
                 'api-key: '.$hybrid_API_Key,
                 'accept: '.'application/json',
                 'Content-Type: '.'application/x-www-form-urlencoded',
                 'user-agent: '.'Falcon Sandbox',
            );

            $fields = array( 'domain'=>'151.101.2.110');
            $postvars = '';
            foreach($fields as $key=>$value) {
                $postvars .= $key . "=" . $value . "&";
              }
            // Send request to Server
            $ch = curl_init($hybrid_url);
            // To save response in a variable from server, set headers;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$postvars);
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  
            echo($response);

        $this->info('Purchase verified successfully');
    }

}
