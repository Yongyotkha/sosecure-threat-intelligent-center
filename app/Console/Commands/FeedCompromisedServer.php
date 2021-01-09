<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
//lib
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

    /**
     * Create a new command instance.
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

        $CompromisedServer = CompromisedServer::where('active', '1')->whereNull('deleted_at')->get(); 
        if($CompromisedServer){
            foreach ($CompromisedServer as $server) {
                try{
                    $ip = $server->ip;
                    $port = $server->port;
                    $user = $server->user;
                    $pass = $server->password;
                    $ssh = new SSH2($ip,$port);
                    $ssh->setTimeout(59);
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
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

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
        // $datail = $this->get_exec_subdetail($line,$ssh);
        // echo json_encode($datail);

    }

    private  function FileSaveDetail($server,$ssh,$return_value) {
        $lines = $this->user_exec_mod($return_value);
        $updateAll = new CompromisedFileOriginal;
        $updateAll->where('compromised_server_id', $server->id)->update(['file_status' => 4]);
        $loop = 0;
        $stop = 10+1;

        echo json_encode($server->file_extension." : ");
        foreach ($lines as $line) {

            $loop++;
            if($loop<$stop){
                echo json_encode($loop);
                $rand = rand(0,30000);
                $rand = $rand+30000;
                usleep($rand);
                $datail = $this->get_exec_subdetail($line,$ssh);
                $CompromisedFileOriginal = new CompromisedFileOriginal;
                $CompromisedFileOriginal = $CompromisedFileOriginal->where('compromised_server_id', $server->id)->where('file_path', $datail["file_path"])->where('site_id', $server->site_id)->first();
                if($datail["file_name"]!=''){
                    if (!$CompromisedFileOriginal){
                        $CompromisedFileOriginal = new CompromisedFileOriginal;
                        $CompromisedFileOriginal->code = generator_uuid();
                        $CompromisedFileOriginal->compromised_server_id = $server->id;
                        $CompromisedFileOriginal->file_name = $datail["file_name"];
                        $CompromisedFileOriginal->file_extension = $datail["file_extenstion"];
                        $CompromisedFileOriginal->file_path = $datail["file_path"];
                        $CompromisedFileOriginal->file_size = $datail["file_size"];
                        $CompromisedFileOriginal->file_modified = $datail["file_modified"];
                        $CompromisedFileOriginal->file_hash = $datail["file_hash"];
                        $CompromisedFileOriginal->file_status = 1;
                        $CompromisedFileOriginal->active = 1;
                        $CompromisedFileOriginal->site_id = $server->site_id;
                        $CompromisedFileOriginal->save();
                    }else{
                        $status_active = $this->checkAlgorithmAndSave($server,$datail);
                        if($CompromisedFileOriginal->file_modified==$datail["file_modified"]){
                            $CompromisedFileOriginal->file_status = 2;
                            $CompromisedFileOriginal->save();
                        }else{
                            if($CompromisedFileOriginal->file_hash!=$datail["file_hash"]){
                                $CompromisedFileOriginal->file_size = $datail["file_size"];
                                $CompromisedFileOriginal->file_modified = $datail["file_modified"];
                                $CompromisedFileOriginal->file_hash = $datail["file_hash"];
                                $CompromisedFileOriginal->file_status = 3;
                                
                                $CompromisedFileOriginal->save();
                            }else{
                                $CompromisedFileOriginal->file_status = 2;
                                $CompromisedFileOriginal->save();
                            }
                        }
                    }
                }
            }else{
                break;
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

    private  function checkAlgorithmAndSave($server,$datail) {
        //implode (",", $blackListFoundString);//insert
        $blacklistKeywords = $server->blacklist_keyword;
        $blackListFoundString = array();
        //$totalConfig = 0;

        if (!empty($blacklistKeywords)) {
            //$totalConfig += 1;
            $blackListFound = $this->trackKeyWords($datail["file_content"], $blacklistKeywords,[]);
            if (count($blackListFound) > 0){
                foreach($blackListFound as $value) {
                    array_push($blackListFoundString,$value["key"]."(Position:[".$value["value"]."])");
                }
            }


        }

        echo json_encode($blackListFoundString);
        return 0;
    }

    private  function saveBlacklistKeyword($server,$detail,$keyword,$blackListFoundString) {

        $implode_blacklist = implode (",", $blackListFoundString);//insert
        $CompromisedFileCheck = CompromisedFileCheck::where('compromised_server_id', $server->id)->where('defacement_type', $keyword)->where('file_path', $detail["file_path"])->where('site_id',$server->site_id)->get(); 
        $insertLeak = true;
        //1 path มีได้กี่ check
        if(!$CompromisedFileCheck){
            $CompromisedFileCheck = new CompromisedFileOriginal;
            $CompromisedFileCheck->code = generator_uuid();
            $CompromisedFileCheck->compromised_server_id = $server->id;
            $CompromisedFileCheck->file_name = $datail["file_name"];
            $CompromisedFileCheck->file_extension = $datail["file_extenstion"];
            $CompromisedFileCheck->file_path = $datail["file_path"];
            $CompromisedFileCheck->file_size = $datail["file_size"];
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
                $CompromisedFileCheck->file_size = $datail["file_size"];
                $CompromisedFileCheck->defacment_description = $implode_blacklist;
                $CompromisedFileCheck->site_id = $server->site_id;
                // $CompromisedFileCheck->file_status = 2;
                $CompromisedFileCheck->save();
            }

            if($insertLeak){
                $DataLeakFeedCheck = DataLeakFeed::where('data_id', $CompromisedFileCheck->id)
                ->where('temp_id', $server->id)
                ->where('feel_type', $keyword)
                ->where('feedcontent',$server->site_id)->get();
                if(!$DataLeakFeedCheck){

                }

                $DataLeakSocialRefCheck = DataLeakSocialRef::where('data_leak_feed_id', $DataLeakFeedCheck->id)
                ->where('site_id', $server->site_id)
                ->where('temp_id', $server->id)->get();
                if(!$DataLeakSocialRefCheck){

                }
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
        if(!empty($split_line)&&count($split_line)==8){
            $output["file_size"] = $this->ConvertUserStrToBytes(@$split_line[4]);
            $output["file_path"] = @$split_line[7];
            $output["file_modified"] = @$split_line[5]." ".@$split_line[6];
            $split_point = "/";
            $stringpos = strrpos($output["file_path"], $split_point, -1)+1;
            $output["file_name"] = substr($output["file_path"],$stringpos);
            $split_point = ".";
            $stringpos = strrpos($output["file_name"], $split_point, -1);
            $output["file_extenstion"] = substr($output["file_name"],$stringpos);
            $cmd = "cat ".$output["file_path"];
            $output["file_content"] = @$ssh->exec($cmd);
            $output["file_hash"] = hash($this->hashingAlgorithm, $output["file_content"]);
            $this->info($output["file_name"]);
        }
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
}
