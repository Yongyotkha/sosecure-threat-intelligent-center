<?php

namespace App\Console\Commands;

use App\DataLeakSocial;
use App\Entities\Sites;
use Illuminate\Console\Command;
use App\LogPhishing;
use App\LogURLFeed;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Modules\SiteSettings\Entities\Site_keywords;
use Illuminate\Support\Str;

class TransactionURLFeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TransactionURLFeed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TransactionURLFeed';

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
        $dataLeakSocial = DataLeakSocial::where('type', 2)->where('status', 1)->where('transaction_status', 1)->get();
        foreach($dataLeakSocial as $data){

            $data -> transaction_status = 2;
            $data -> save();

            $site_id = [];
            if(empty($data -> site_id)){
                $site_id = Sites::where('active',1)
                ->where('system_site_online',1)
                ->where('start_active', '<=', date("Y-m-d H:i:s"))
                ->where('end_active', ">=", date("Y-m-d H:i:s"))
                ->pluck('id');
            }else{
                $site_id[] = $data -> site_id;
            }

            foreach($site_id as $site){
                $keywords = Site_keywords::select('name')->where('site_id', $site)->where('deleted_at', null)->get();
                foreach($keywords as $keyword){
                    $url = $data -> url;
                    $url_404 = $this -> URL_404($url);
                    if($url_404 == 0){
                        $get_html = $this -> getHtml($url);
                        if(!empty($get_html)){
                            $checkKeyword = $this -> CheckKeyword($get_html, $keyword -> name);
                            if(!empty($checkKeyword)){
                                $code = Str::uuid();
                                $pathFile = '/files/logs_url_feed/'.$code.'.html';
                                File::put(public_path() . $pathFile, $get_html);
                                
                                $LogURLFeed = new LogURLFeed();
                                $LogURLFeed -> code = $code;
                                $LogURLFeed -> url = $url;
                                $LogURLFeed -> keyword = $keyword -> name;
                                $LogURLFeed -> detection = $checkKeyword;
                                $LogURLFeed -> path_file = $pathFile;
                                $LogURLFeed -> site_id = $site;
                                $LogURLFeed -> status = 1;
                                $LogURLFeed -> save();

                                $this -> info('Found Keyword');
                            }        
                            $this -> error('Not Found Keyword');
                        }
                    }
                }
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
