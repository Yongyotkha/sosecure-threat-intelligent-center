<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use Artisan;
//lib
use GuzzleHttp\Client as HttpClient;
use phpseclib\Net\SSH2;
//model
use App\Entities\CompromisedServer;
use App\Entities\CompromisedFileOriginal;
use App\Entities\CompromisedFileCheck;
use App\DataLeakFeed;
use App\DataLeakSocialRef;

class FeedCompromisedServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:FeedCompromisedServer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FeedCompromisedServer';
    private $hashingAlgorithm  = 'md5';
    private $timeOutMain  = 59;
    private $timeOutSub  = 1200;
    private $pathToSave  = "";
    private $urlLimit  = 3;
    private $url = PATH_CENTER_IP_TF.'/api/v1/centerinto-transfer/updateTFBatchJob';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pathToSave = base_path()."/storage/compromised/webserver/";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("--Start Process--");
        $passBody = [
            'modeFor' => 'wait',
            'modeInsert' => 'Compromised_scan_webserver',
            'nameBJ' => 'Compromised Scan Webserver - DailyAt(\'06:00\')  Or Request',
            'sitecode' => config('app.site_code'),
        ];
        $httpData = $this->reconnnect($this->url, $passBody, $this->urlLimit);

        $CompromisedServer = CompromisedServer::where('active', '1')->whereNull('deleted_at')->get(); 
        if($CompromisedServer){
            foreach ($CompromisedServer as $server) {
                try{

                    if($server->os=="Linux"){
                        $this->info("Process: "."ip - ".$server->ip." site - ".$server->site_id." serverID - ".$server->id);
                        $ip = $server->ip;
                        $port = $server->port;
                        $user = $server->user;
                        $pass = $server->password;
                        $ssh = new SSH2($ip,$port);
                        $ssh->setTimeout($this->timeOutMain);
                        $return_value = null;
                        if (!$ssh->login($user, $pass)) {
                            $return_value = null;
                        } else {
                            $server_path = $server->path;
                            // $cmd = "find /var/www/html/threat-intelligent-center/threat-intelligent-clinet-online -type f \( -iname \*.php -o -iname \*.js \) -print0 | xargs -0 ls -lh --time-style=\"+%Y-%m-%d %H:%M:%S\"";
                            
                            $server_search_extenstion = "";
                            if(!empty($server->file_extension)){
                                $search_extensions = explode(",", $server->file_extension);
                                $server_search_extenstion .= "\( ";
                                foreach ($search_extensions as $search_extension) {
                                    $server_search_extenstion .= "-iname \*".$search_extension." -o ";
                                }
                                $server_search_extenstion = rtrim($server_search_extenstion, "-o ");
                                $server_search_extenstion .= " \)";


                            }
                            //$cmd = "find ".$server_path." -type f \( -iname \*.php -o -iname \*.js \) -print0 | xargs -0 ls -lh --time-style=\"+%Y-%m-%d %H:%M:%S\"";
                            $cmd2 = "find ".$server_path." -type f ".$server_search_extenstion." -print0 | xargs -0 ls -lh --time-style=\"+%Y-%m-%d %H:%M:%S\"";
                            //echo $cmd2;
                            $return_value = $ssh->exec($cmd2);

                            $this->FileSaveDetail($server,$ssh,$return_value);
                        }
                    }else{

                        $output=null;
                        $retval=null;
                      // exec("docker run -it vpershin93/winexe-docker build/winexe -U Administrator%Apss*2020 //150.95.91.205 'where /r C:\boy *.js *.css' ", $output, $retval);
                      //  echo "Returned with status $retval and output:\n";
                      //  print_r($output);

                        $output=null;
                        $retval=null;
                        exec("docker run -it vpershin93/winexe-docker build/winexe -U Administrator%Apss*2020 //150.95.91.205 'forfiles /M jquery-ui.js /C 'cmd /c echo @fdate @ftime' ", $output, $retval);
                        echo "Returned with status $retval and output:\n";
                        print_r($output);

         
//

                    }

                } catch (Exception $e) {
                    $this->info("line : ".$e->getLine()." Error :".$e->getMessage());
                }
            }
        }
        $this->info("--Start ScanPython--");
        $commandArtisan = 'app:FeedCompromisedScan';
        Artisan::call($commandArtisan);
        $commandArtisan = 'app:TFCenterTransfer_Compromised_Server';
        Artisan::call($commandArtisan);
        $commandArtisan = 'app:TFCenterTransfer_center_data_leak_feed';
        Artisan::call($commandArtisan);
        
        $passBody = [
            'modeFor' => 'done',
            'modeInsert' => 'Compromised_scan_webserver',
            'nameBJ' => 'Compromised Scan Webserver - DailyAt(\'06:00\')  Or Request',
            'sitecode' => config('app.site_code'),
        ];
        $httpData = $this->reconnnect($this->url, $passBody, $this->urlLimit);

        $this->info("--END Process--");
        // $ip = '10.104.0.7';
        // $port = '22';
        // $user = 'sosecure';
        // $pass = '$0$ecure-!@#$%^&*()';
        // $ssh = new \phpseclib\Net\SSH2($ip,$port);
        // $ssh->setTimeout(59);
        // if (!$ssh->login($user, $pass)) {
        //     $return_value = null;
        // } else {
        //     $cmd = "find /var/www/html/threat-intelligent-center/threat-intelligent-clinet-online -type f \( -iname \*.php -o -iname \*.js \) -print0 | xargs -0 ls -lh --time-style=\"+%Y-%m-%d %H:%M:%S\"";
        //     $return_value = $ssh->exec($cmd);
        // }
        // $line = $this->user_exec_mod($return_value)[0];
        // $detail = $this->get_exec_subdetail($line,$ssh);
        // echo json_encode($detail);

    }

    private  function FileSaveDetail($server,$ssh,$return_value) {
        $lines = $this->user_exec_mod($return_value);
        $updateAll = new CompromisedFileOriginal;
        $updateAll->where('compromised_server_id', $server->id)->update(['file_status' => 4]);
        // $loop = 0;
        // $stop = 10+1;

        $checkPythonScan = true;

        foreach ($lines as $line) {

            try{
                // $loop++;
                // if($loop<$stop){
                $rand = rand(0,30000);
                $rand = $rand+30000;
                usleep($rand);
                $detail = $this->get_exec_subdetail($line,$ssh);

                if(!empty($detail)){
                    $CompromisedFileOriginal = new CompromisedFileOriginal;
                    $CompromisedFileOriginal = $CompromisedFileOriginal->where('compromised_server_id', $server->id)->where('file_path', $detail["file_path"])->where('site_id', $server->site_id)->first();
                    $this->info("Detail : " .json_encode($detail["file_path"]));
                    if (!$CompromisedFileOriginal){
                        $CompromisedFileOriginal = new CompromisedFileOriginal;
                        $CompromisedFileOriginal->code = generator_uuid();
                        $CompromisedFileOriginal->compromised_server_id = $server->id;
                        $CompromisedFileOriginal->file_name = $detail["file_name"];
                        $CompromisedFileOriginal->file_extension = $detail["file_extenstion"];
                        $CompromisedFileOriginal->file_path = $detail["file_path"];
                        $CompromisedFileOriginal->file_size = $detail["file_size"];
                        $CompromisedFileOriginal->file_modified = $detail["file_modified"];
                        $CompromisedFileOriginal->file_hash = $detail["file_hash"];
                        $CompromisedFileOriginal->file_status = 1;
                        $CompromisedFileOriginal->active = 1;
                        $CompromisedFileOriginal->site_id = $server->site_id;
                        $CompromisedFileOriginal->save();
                        $status_active = $this->checkAlgorithmAndSave($server,$detail,$CompromisedFileOriginal->code);
                    }else{

                        if($CompromisedFileOriginal->file_modified==$detail["file_modified"]){
                            $CompromisedFileOriginal->file_status = 2;
                            $CompromisedFileOriginal->save();
                        }else{
                            if($CompromisedFileOriginal->file_hash!=$detail["file_hash"]){
                                $CompromisedFileOriginal->file_size = $detail["file_size"];
                                $CompromisedFileOriginal->file_modified = $detail["file_modified"];
                                $CompromisedFileOriginal->file_hash = $detail["file_hash"];
                                $CompromisedFileOriginal->file_status = 3;
                                $CompromisedFileOriginal->save();
                                $status_active = $this->checkAlgorithmAndSave($server,$detail,$CompromisedFileOriginal->code);
                            }else{
                                $CompromisedFileOriginal->file_status = 2;
                                $CompromisedFileOriginal->save();
                                
                            }
                        }
                        $status_active = $this->checkAlgorithmAndSave($server,$detail,$CompromisedFileOriginal->code);
                    }
                }
                // }else{
                //     break;
                // }

            } catch (Exception $e) {
                $this->info("line : ".$e->getLine()." Error :".$e->getMessage());
            }
            
        }
    }

    private function user_exec_backup($shell,$cmd) {
        //$output = user_exec($shell,$cmd);
        //$connection = ssh2_connect('shell.example.com', 22);
        // ssh2_auth_password($connection,$user,$pass);
        // $shell = ssh2_shell($connection,"bash");

        //$output = user_exec($shell,$cmd);
        // echo json_encode($output);
        // fclose($shell);
        fwrite($shell,$cmd . "\n");
        $output = "";
        $start = false;
        $start_time = time();
        $max_time = 2; //time in seconds
        while(((time()-$start_time) < $max_time)) {
          $line = fgets($shell);
          if(!strstr($line,$cmd)) {
            if(preg_match('/\[start\]/',$line)) {
              $start = true;
          }elseif(preg_match('/\[end\]/',$line)) {
              return $output;
          }elseif($start){
              $output[] = $line;
          }
      }
  }
}

private  function pythonCheck($server,$ssh) {
    $pathSave = $this->pathToSave.$server->id."/";
    if (!file_exists($pathSave)) {
        mkdir($pathSave, 0777, true);
    }
    $ssh->setTimeout($this->timeOutSub);
        //$cmd = "cd /python_scanner/yara-scanner/ && python3 yara_main.py --scan-dir '/python_scanner/file_webshell/' -r";
        // $cmd = "cd /python_scanner/yara-scanner/ && python3 yara_main.py --scan-dir '/python_scanner/yara-scanner/' -r";
    $cmd = "cd /python_scanner/yara-scanner/ && python3 yara_main.py --scan-dir '".$pathSave."' -r";
    $output = @$ssh->exec($cmd);
    $this->info($output);
    return 0;
}

private  function checkAlgorithmAndSave($server,$detail,$codename) {
    $pathSave = $this->pathToSave.$server->id."/";
    if (!file_exists($pathSave)) {
        mkdir($pathSave, 0777, true);
    }
    $file = $pathSave.$codename.$detail["file_extenstion"];
    file_put_contents($file, $detail["file_content"]);
    chmod($file, 0777);

        // if(!is_file($file)){
        //     file_put_contents($file, $detail["file_content"]);
        // }

        // $blacklistKeywords = $server->blacklist_keyword;
        // $blackListFoundString = array();
        // if (!empty($blacklistKeywords)) {
        //     //$totalConfig += 1;
        //     $blackListFound = $this->trackKeyWords($detail["file_content"], $blacklistKeywords,[]);
        //     if (count($blackListFound) > 0){
        //         foreach($blackListFound as $value) {
        //             array_push($blackListFoundString,$value["key"]."(Position:[".$value["value"]."])");
        //         }
        //         $this->saveBlacklistKeyword($server,$detail,'webserver','keyword',$blackListFoundString);
        //     }
        // }

        //echo json_encode($blackListFoundString);

    return 0;
}

private  function savePythonScan_backup($server,$detail,$feedtype,$keyword,$blackListFoundString) {
        $implode_blacklist = implode (",", $blackListFoundString);//insert
        $CompromisedFileCheck = CompromisedFileCheck::where('compromised_server_id', $server->id)->where('defacement_type', $keyword)->where('file_path', $detail["file_path"])->where('site_id',$server->site_id)->first(); 
        $insertLeak = true;
        //1 path มีได้กี่ check
        if(!$CompromisedFileCheck){
            $CompromisedFileCheck = new CompromisedFileCheck;
            $CompromisedFileCheck->code = generator_uuid();
            $CompromisedFileCheck->compromised_server_id = $server->id;
            $CompromisedFileCheck->file_name = $detail["file_name"];
            $CompromisedFileCheck->file_extension = $detail["file_extenstion"];
            $CompromisedFileCheck->file_path = $detail["file_path"];
            $CompromisedFileCheck->file_size = $detail["file_size"];
            $CompromisedFileCheck->file_modified = $detail["file_modified"];
            $CompromisedFileCheck->defacement_type = $keyword;
            $CompromisedFileCheck->defacment_description = $implode_blacklist;
            $CompromisedFileCheck->file_status = 1;
            $CompromisedFileCheck->active = 1;
            $CompromisedFileCheck->site_id = $server->site_id;
            $CompromisedFileCheck->save();
        }else{
            if($CompromisedFileCheck->file_status==2){
                //no update
                $insertLeak = false;
            }else if($CompromisedFileCheck->file_status==1){
                //status จะเป็น 2 เมือ่ไร
                $CompromisedFileCheck->file_size = $detail["file_size"];
                $CompromisedFileCheck->defacment_description = $implode_blacklist;
                $CompromisedFileCheck->site_id = $server->site_id;
                // $CompromisedFileCheck->file_status = 2;
                $CompromisedFileCheck->save();
            }

            

        }

        if($insertLeak){
            $DataLeakFeedCheck = DataLeakFeed::where('data_id', $CompromisedFileCheck->id)
            ->where('temp_id', $server->id)
            ->where('feel_type', $keyword)
            ->where('feedcontent',$server->site_id)->first();

            if(!$DataLeakFeedCheck){
                $DataLeakFeedCheck = new DataLeakFeed;
                $DataLeakFeedCheck->code = generator_uuid();
                $DataLeakFeedCheck->data_id =$CompromisedFileCheck->id;
                $DataLeakFeedCheck->temp_id =  $server->id;
                $DataLeakFeedCheck->sourceid = 1000;
                $DataLeakFeedCheck->keyword = $keyword;
                $DataLeakFeedCheck->source_name = $detail["file_path"];
                // $DataLeakFeedCheck->feedtimepost = $detail["file_modified"];
                $DataLeakFeedCheck->feedtimepost = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedtimestamp = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedcontent = $implode_blacklist;
                $DataLeakFeedCheck->status = 1;
                $DataLeakFeedCheck->feel_type = $feedtype;
                $DataLeakFeedCheck->view = 0;
                $DataLeakFeedCheck->save();
            }

            $DataLeakSocialRefCheck = DataLeakSocialRef::where('data_leak_feed_id', $DataLeakFeedCheck->id)
            ->where('site_id', $server->site_id)
            ->where('temp_id', $server->id)->first();
            if(!$DataLeakSocialRefCheck){
                $DataLeakSocialRefCheck = new DataLeakSocialRef;
                $DataLeakSocialRefCheck->code = generator_uuid();
                $DataLeakSocialRefCheck->temp_id = $server->id;
                $DataLeakSocialRefCheck->data_leak_feed_id =  $DataLeakFeedCheck->id;
                $DataLeakSocialRefCheck->site_id = $server->site_id;
                $DataLeakSocialRefCheck->keyword = $keyword ;
                $DataLeakSocialRefCheck->status = 1;
                $DataLeakSocialRefCheck->feel_type = $feedtype;
                $DataLeakSocialRefCheck->view = 0;
                $DataLeakSocialRefCheck->save();

            }
        }
        return 0;
    }

    private  function saveBlacklistKeyword($server,$detail,$feedtype,$keyword,$blackListFoundString) {
        $implode_blacklist = implode (",", $blackListFoundString);//insert
        $CompromisedFileCheck = CompromisedFileCheck::where('compromised_server_id', $server->id)->where('defacement_type', $keyword)->where('file_path', $detail["file_path"])->where('site_id',$server->site_id)->first(); 
        $insertLeak = true;
        //1 path มีได้กี่ check
        if(!$CompromisedFileCheck){
            $CompromisedFileCheck = new CompromisedFileCheck;
            $CompromisedFileCheck->code = generator_uuid();
            $CompromisedFileCheck->compromised_server_id = $server->id;
            $CompromisedFileCheck->file_name = $detail["file_name"];
            $CompromisedFileCheck->file_extension = $detail["file_extenstion"];
            $CompromisedFileCheck->file_path = $detail["file_path"];
            $CompromisedFileCheck->file_size = $detail["file_size"];
            $CompromisedFileCheck->file_modified = $detail["file_modified"];
            $CompromisedFileCheck->defacement_type = $keyword;
            $CompromisedFileCheck->defacment_description = $implode_blacklist;
            $CompromisedFileCheck->file_status = 1;
            $CompromisedFileCheck->active = 1;
            $CompromisedFileCheck->site_id = $server->site_id;
            $CompromisedFileCheck->save();
        }else{
            if($CompromisedFileCheck->file_status==2){
                //no update
                $insertLeak = false;
            }else if($CompromisedFileCheck->file_status==1){
                //status จะเป็น 2 เมือ่ไร
                $CompromisedFileCheck->file_size = $detail["file_size"];
                $CompromisedFileCheck->defacment_description = $implode_blacklist;
                $CompromisedFileCheck->site_id = $server->site_id;
                // $CompromisedFileCheck->file_status = 2;
                $CompromisedFileCheck->save();
            }
        }

        if($insertLeak){
            $DataLeakFeedCheck = DataLeakFeed::where('data_id', $CompromisedFileCheck->id)
            ->where('temp_id', $server->id)
            ->where('feel_type', $keyword)
            ->where('feedcontent',$server->site_id)->first();

            if(!$DataLeakFeedCheck){
                $DataLeakFeedCheck = new DataLeakFeed;
                $DataLeakFeedCheck->code = generator_uuid();
                $DataLeakFeedCheck->data_id =$CompromisedFileCheck->id;
                $DataLeakFeedCheck->temp_id =  $server->id;
                $DataLeakFeedCheck->sourceid = 1000;
                $DataLeakFeedCheck->keyword = $keyword;
                $DataLeakFeedCheck->source_name = $detail["file_path"];
                // $DataLeakFeedCheck->feedtimepost = $detail["file_modified"];
                $DataLeakFeedCheck->feedtimepost = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedtimestamp = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedcontent = $implode_blacklist;
                $DataLeakFeedCheck->status = 1;
                $DataLeakFeedCheck->feel_type = $feedtype;
                $DataLeakFeedCheck->view = 0;
                $DataLeakFeedCheck->save();
            }

            $DataLeakSocialRefCheck = DataLeakSocialRef::where('data_leak_feed_id', $DataLeakFeedCheck->id)
            ->where('site_id', $server->site_id)
            ->where('temp_id', $server->id)->first();
            if(!$DataLeakSocialRefCheck){
                $DataLeakSocialRefCheck = new DataLeakSocialRef;
                $DataLeakSocialRefCheck->code = generator_uuid();
                $DataLeakSocialRefCheck->temp_id = $server->id;
                $DataLeakSocialRefCheck->data_leak_feed_id =  $DataLeakFeedCheck->id;
                $DataLeakSocialRefCheck->site_id = $server->site_id;
                $DataLeakSocialRefCheck->keyword = $keyword ;
                $DataLeakSocialRefCheck->status = 1;
                $DataLeakSocialRefCheck->feel_type = $feedtype;
                $DataLeakSocialRefCheck->view = 0;
                $DataLeakSocialRefCheck->save();

            }
        }
        return 0;
    }

    private  function user_exec_mod($text) {
        $output = explode("\n", $text);
        return $output;
    }

    private  function get_exec_subdetail($line,$ssh) {
        //$split_line = explode(" ", $line);
        $split_line = preg_split('/\s+/', $line);
        $output = null;
        $ssh->setTimeout($this->timeOutMain);
        // if(!empty($split_line)&&count($split_line)==8){
        if(count($split_line)>7){
            $output["file_size"] = $this->ConvertUserStrToBytes($split_line[4]);
            $output["file_path"] = implode("",array_slice($split_line, 7));
            $output["file_modified"] = $split_line[5]." ".$split_line[6];

            $pathInfo = pathinfo($output["file_path"]);
            $output["file_name"] = $pathInfo["basename"];
            $output["file_extenstion"] = ".".$pathInfo["extension"];

            $cmd = "cat ".$output["file_path"];
            $output["file_content"] = $ssh->exec($cmd);
            $output["file_hash"] = hash($this->hashingAlgorithm, $output["file_content"]);
        }else{
            // $this->info("Sub Error".$line.json_encode($split_line));
        }
            // $split_point = "/";
            // $stringpos = strrpos($output["file_path"], $split_point, -1)+1;
            // $output["file_name"] = substr($output["file_path"],$stringpos);
            // $split_point = ".";
            // $stringpos = strrpos($output["file_name"], $split_point, -1);
            // if ($stringpos === false) { // note: three equal signs
            //     // not found...
            //     $output["file_extenstion"] = "";
            // }else{
            //     $output["file_extenstion"] = substr($output["file_name"],$stringpos);
            // }


            // $this->info($output["file_name"]);
        // }
        return $output;
    }

    private function ConvertUserStrToBytes($str)
    {
        $str = trim($str);
        $num = (double)$str;
        if (strtoupper(substr($str, -1)) == "B")  $str = substr($str, 0, -1);
        switch (strtoupper(substr($str, -1)))
        {
            case "P":  $num *= 1024;
            case "T":  $num *= 1024;
            case "G":  $num *= 1024;
            case "M":  $num *= 1024;
            case "K":  $num *= 1024;
        }

        return $num;
    }

    private function trackKeyWords($webContent, $blacklistKeywords,$Keyword_checks)
    {
        $keywordOK =array();
        $keywords = explode(',', $blacklistKeywords);
        foreach ($keywords as $keyword) {
            $trackFound = $this->CheckKeyword($webContent,$keyword);
            if (count($trackFound) > 0)
            {
                if (count($Keyword_checks) > 0)
                {
                    $filtereds = array();
                    $rows = $Keyword_checks;
                    foreach($rows as $index => $columns) {
                        foreach($columns as $key => $value) {
                            if ($key == 'key' && $value == $keyword) {
                                $filtereds[] = $columns;
                            }
                        }
                    }

                    foreach ($trackFound as $position)
                    {
                        //ถ้ามีให้หาตำแหน่ง

                        if (!in_array($position, array_column($filtereds, 'value')))
                        {
                            //ไม่มีอยู่ใน ignore
                            array_push($keywordOK, array("key"=>$keyword,"value"=>$position));
                        }
                    }

                }else
                {
                    foreach ($trackFound as $position)
                    {
                        array_push($keywordOK, array("key"=>$keyword,"value"=>$position));
                        
                    }
                }
            }
        }

        return $keywordOK;
    }

    private function CheckKeyword($html,$needle){
        $lastPos = 0;
        $positions = array();

        while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }
        return $positions;
    }

    public function reconnnect($url, $passBody, $limit)
    {
        $_OTX_KEY = env("OTX_KEY", "");
        $_clientHttp = new HttpClient;
        $_reconnect = 0;
        $_otxReconnect = true;
        $_dataOut["result"] = null;
        $_dataOut["success"] = false;
        $_sleeptime = rand(0, 2000);
        while ($_otxReconnect && $_reconnect < $limit) {
            try {
                $_bodyData = $_clientHttp->request(
                    'POST',
                    $url,
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-type' => 'application/json',
                        ],
                        'delay' => $_sleeptime, //millisec == ms
                        'timeout' => 59, //sec == 100sec
                        'verify' => false,
                        'body' => json_encode($passBody),
                    ]
                )->getBody();
                $_dataOut["result"] = json_decode($_bodyData, true);
                $_dataOut["success"] = true;
                $_otxReconnect = false;
                //echo "  Pass : " . $_reconnect;
            } catch (Exception $e) {
                echo "  Fail : " . $e->getMessage();
            }
            $_reconnect++;
        }
        return $_dataOut;
    }
}
