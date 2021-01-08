<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WebDefacementDataCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:WebDefacementDataCheck {url} {port} {site_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebDefacementDataCheck';

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
        $url= $this->argument('url');
        $port=$this->argument('port');
        $site_id =$this->argument('site_id');
        $result = array();
        if ($this->is_url($url)) {



            $parse = parse_url($url);
        $host = $parse['host']; // prints 'google.com'
        $result_checkDomainOnline=  $this->checkDomainOnline($host,$port);
        if ($result_checkDomainOnline == 1) {
            $result["Result"] = 1;
            $result["domain"] = $host;

            $result_checkDomainHeaders=   $this->checkDomainHeaders($url,1);
            $result_URL_404=   $this->URL_404($url);
            $result["DomainHeaders"] = $result_checkDomainHeaders;
            $result["Is_URL_404"] = $result_URL_404;
            $result["message"] = "";

        }else{
            $result["Result"] = 0;
            $result["messes "] = "The website is not online.";
        }
    }else{
        $result["Result"] = 0;
        $result["message"] = "The url is not formatted.";
    }


    // print_r($result);
    
    // $result_json_e = 'test';
    $result_json_e = json_encode($result);
    // $result_json_e = 'test';
    // var_dump($result_json_e);

    // var_dump($result);
    // print_r($result);


  
        // $data = [
        //     "Result" => $result["Result"],
        // ];
        // echo $result["Result"];

        // $test = '{
        //     "employees":[
        //       {"firstName":"John", "lastName":"Doe"},
        //       {"firstName":"Anna", "lastName":"Smith"},
        //       {"firstName":"Peter", "lastName":"Jones"}
        //     ]
        // }';

        // $test = 55;


        echo $result_json_e;
    


}
function is_url($uri){
    if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri)){
      return $uri;
  }
  else{
    return false;
}
}


function checkDomainOnline($domain,$port) {
    $domain = str_replace("https://","",$domain);
    $domain =str_replace("http://","",$domain);
    $host = $domain;
    if($socket =@ fsockopen($host, $port, $errno, $errstr, 30)) {
        fclose($socket);
        return 1;
    } else {
        return 0;
    }

}
function checkDomainHeaders($url,$format=0)
{
    $url=parse_url($url);
    $end = "\r\n\r\n";
    $fp = fsockopen($url['host'], (empty($url['port'])?80:$url['port']), $errno, $errstr, 30);
    if ($fp)
    {
        $out  = "GET / HTTP/1.1\r\n";
        $out .= "Host: ".$url['host']."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $var  = '';
        fwrite($fp, $out);
        while (!feof($fp))
        {
            $var.=fgets($fp, 1280);
            if(strpos($var,$end))
                break;
        }
        fclose($fp);

        $var=preg_replace("/\r\n\r\n.*\$/",'',$var);
        $var=explode("\r\n",$var);
        if($format)
        {
            foreach($var as $i)
            {
                if(preg_match('/^([a-zA-Z -]+): +(.*)$/',$i,$parts))
                    $v[$parts[1]]=$parts[2];
            }
            return $v;
        }
        else
            return $var;
    }

}
function URL_404($url) {
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
