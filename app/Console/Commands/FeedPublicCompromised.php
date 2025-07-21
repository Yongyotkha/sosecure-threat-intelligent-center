<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Modules\SiteSettings\Entities\SiteSettings;


class FeedPublicCompromised extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:FeedPublicCompromised';

    /**
     * The console command description.
     *
     * @var string
     */


    protected $description = 'FeedPublicCompromised';
    private $url = 'https://haveibeenpwned.com/api/v3/breaches';
    private $key = '92aa5d8fe2f54692994a51a29d75356d';
    private $urlLimit = 3;
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

        $SiteSettings = SiteSettings::where('active', '1')->whereNull('deleted_at')->with('get_domains_default');
        $SiteSettings = $SiteSettings->get()->toArray();
        $SiteSettings = $SiteSettings[0];

        foreach ($SiteSettings as $value) {
            try{
                $domain = $value["get_domains_default"][0]["domain"];
                $site_id = $value["id"];
                

                $response = $this->perform_query($domain,$site_id);

            } catch (Exception $e) {


                echo "Fail handle : " . $e->getMessage();


            }
        }

        print_r($SiteSettings);
        return 0;


        $this->info('END------------------------------------------------------------END');
    }

    public function perform_query($domain,$site_id)
    {
        $url = $this->url.'?domain='.$domain;
        $urlLimit = $this->urlLimit;
        $httpData = $this->reconnnect($url, $urlLimit);
        
    }

    public function reconnnect($url, $limit)
    {
        $_clientHttp = new Client();
        $_reconnect = 0;
        $_otxReconnect = true;
        $_dataOut["result"] = "";
        $_dataOut["success"] = false;
        $_sleeptime = rand(0,2000); 
        while ($_otxReconnect && $_reconnect < $limit) {
            sleep(2);
            try {
                $_bodyData = $_clientHttp->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-type' => 'application/json',
                            'hibp-api-key' => $this->key
                        ],
                        'delay' => $_sleeptime, //millisec == 1sec
                        'timeout' => 59, //sec == 100sec
                    ]
                )->getBody();
                $_dataOut["result"] = json_decode($_bodyData);
                $_dataOut["success"] = true;
                $_otxReconnect = false;
            } catch (Exception $e) {
                // $_dataOut["exception"] = $e->getMessage();
                echo "  Fail : " . $e->getMessage();
            }
            $_reconnect++;
        }
        return $_dataOut;
    }
}
