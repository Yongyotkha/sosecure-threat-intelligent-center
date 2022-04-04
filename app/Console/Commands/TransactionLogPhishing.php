<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\LogPhishing;
use Carbon\Carbon;

class TransactionLogPhishing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TransactionLogPhishing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TransactionLogPhishing';

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
        $logPhishing = LogPhishing::where('transaction_status', 1)->get();
        foreach($logPhishing as $data){

            $data -> start_date = Carbon::now();
            $data -> transaction_status = 2;
            $data -> save();

            $url = $data -> url;
            $url_404 = $this -> URL_404($url);
            if($url_404 == 0){
                $this -> info(200);

                $get_html = $this -> getHtml($url);
                if(!empty($get_html)){
                    $checkKeyword = $this -> CheckKeyword($get_html, 'type="password"');
                    if(!empty($checkKeyword)){
                        $found = 1;
                        $data -> score = 10;
                    }else{
                        $data -> score = 0;
                        $found = 0;
                    }

                   
                    $data -> url_is_work = 1;
                    $data -> is_found = $found;
                    $data -> save();

                    $this -> info('Found Status ' . $found);
                }else{
                    
                    $this -> error('Not Found HTML!');

                    $data -> url_is_work = 0;
                    $data -> save();

                }
            }else{

                $this -> error(404);

                $data -> url_is_work = 0;
                $data -> save();
            }

            $data -> transaction_status = 3;
            $data -> save();
        }
        
    }

    private function getHtml($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    function CheckKeyword($html,$needle){
        $lastPos = 0;
        $positions = array();
    
        while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }
        return $positions;
    }

    private function URL_404($url) {
        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
    
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);
    
        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
    
        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode >= 200 && $httpCode < 300) {
            return 0;
        } else {
            return 1;
        }
    }
}
