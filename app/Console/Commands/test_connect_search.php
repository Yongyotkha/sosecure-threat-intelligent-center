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
            // $hybrid_API_Key = "kpy0ibau846587b1lnemkw4k082be03bncw1bkz140a16b6cs64sk6uzf0498e3f";
            // $hybrid_url = "https://www.hybrid-analysis.com/api/v2/search/terms";
            // //$virustotal_url='https://www.virustotal.com/api/v3/domains/xlus0222uj81bxyf.xyz';
            // $headers = array(
            //      'api-key: '.$hybrid_API_Key,
            //      'accept: '.'application/json',
            //      'Content-Type: '.'application/x-www-form-urlencoded',
            //      'user-agent: '.'Falcon Sandbox',
            // );
            //'host'=>'151.101.2.110','domain'=>'151.101.2.110','url'=>'151.101.2.110','url'=>'151.101.2.110','similar_to'=>'151.101.2.110','context'=>'151.101.2.110'

            // $fields = array( 'host'=>'165.227.87.17');
            // $postvars = '';
            // foreach($fields as $key=>$value) {
            //     $postvars .= $key . "=" . $value . "&";
            //   }
            // // Send request to Server
            // $ch = curl_init($hybrid_url);
            // // To save response in a variable from server, set headers;
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($ch, CURLOPT_POSTFIELDS,$postvars);
            // // Get response
            // $response = curl_exec($ch);
            // curl_close($ch);  
            // echo($response);
            // $keyword="https://stackoverflow.com/questions/1755144/how-to-validate-domain-name-in-php";
            // $type ="";
            // if (preg_match("/^([a-f0-9]{64})$/", $keyword) == 1) {
            //     $type =  'SHA256';
            //  }else if(preg_match('/^[a-f0-9]{32}$/', $keyword)) {
            //     $type = 'MD5';
                
            //  }else if(preg_match('/^[0-9a-f]{40}$/i', $keyword)) {
            //     $type = 'SHA1';
                
            //  }
            //  else if(filter_var($keyword, FILTER_VALIDATE_IP)) {
            //     $type = 'IP';
                
            //  }
            //  else if(filter_var($keyword, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            //     $type = 'IP';
                
            //  }
            //  else if(filter_var($keyword, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
            //     $type = 'IP';
                
            //  }
            //  else if(filter_var($keyword, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
            //     $type = 'IP';
                
            //  }
            //  else if(preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $keyword) //valid chars check
            //  && preg_match("/^.{1,253}$/", $keyword) //overall length check
            //  && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $keyword)   ) {
            //     $type = 'Domain';
                
            //  }
            //  else if(preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$keyword)) {
            //     $type = 'URL';
                
            //  }
            //  else if(preg_match("/\b(?:(?:http?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$keyword)) {
            //     $type = 'URL';
                
            //  }else{

            //  }

            //OTX
            $otx_API_Key = "c69611682f6e13bfe36a9b3740dac840ce279d6d52b1b8c7c78eb097bee53688";
            $otx_url = "https://otx.alienvault.com/api/v1/indicators/file/f9f18153cabf61699d838407dddd4e937bb2efeb/analysis";
            $ch = curl_init();
            $headers = array(
                 'X-OTX-API-KEY: '.$otx_API_Key,
                'accept: '.'application/json',
                 'Content-Type: '.'application/x-www-form-urlencoded',
            );
            // Send request to Server
            $ch = curl_init($otx_url);
            // To save response in a variable from server, set headers;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  
           // echo($response);
           $myfile = fopen("/var/www/html/insight.sosecure.co.th/threat-intelligent-center/app/Console/Commands/search_oxt.txt", "w") or die("Unable to open file!");
$txt = $response;
fwrite($myfile, $txt);


fclose($myfile);
       // $this->info($type);
    }

}
