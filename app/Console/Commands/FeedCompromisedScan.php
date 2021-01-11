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

class FeedCompromisedScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:FeedCompromisedScan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FeedCompromisedServer';
    private $hashingAlgorithm  = 'md5';
    private $timeOutMain  = 59;
    private $timeOutSub  = 180;
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
        $CompromisedServer = CompromisedServer::where('active', '1')->whereNull('deleted_at')->get(); 
        if($CompromisedServer){
            foreach ($CompromisedServer as $server) {
                try{
                    $ip = $server->ip;
                    $port = $server->port;
                    $user = $server->user;
                    $pass = $server->password;
                    $ssh = new SSH2($ip,$port);
                    $ssh->setTimeout($this->timeOutSub);
                    $return_value = null;
                    if (!$ssh->login($user, $pass)) {
                        $return_value = null;
                    } else {
                        $this->scanFolder($server,$ssh);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        

    }
    
    private  function scanFolder($server,$ssh) {
        $dir_folder = $this->pathToSave.$server->id."/";
        if (is_dir($dir_folder)) {
            if ($dh = opendir($dir_folder)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") {
                        $dir_file = $dir_folder . $file;
                        $pathinfo_file = pathinfo($dir_file);
                        $CompromisedFileOriginalCheck = CompromisedFileOriginal::where('code', $pathinfo_file["filename"])->first();
                        $this->info($dir_file);
                        if($CompromisedFileOriginalCheck){
                            $pythonCheck_output = $this->pythonCheck($ssh,$dir_file);
                            $savePythonScan = $this->searchError($dir_file,$pythonCheck_output);
                            if(!empty($savePythonScan)){

                            }
                        }else{
                            $pythonCheck_output = $this->pythonCheck($ssh,$dir_file);
                            $savePythonScan = $this->searchError($dir_file,$pythonCheck_output);
                        }
                        // unlink($somefile);
                    }
                }
                $this->info("--END--");
                closedir($dh);
            }
        }
        return 0;
    }

    private  function pythonCheck($ssh,$dir_file) {
        $cmd = "cd /python_scanner/yara-scanner/ && python3 yara_main.py --scan-file '".$dir_file."'";
        $output = @$ssh->exec($cmd);
        return $output;
    }

    private  function savePythonScan($server,$CompromisedFileOriginalCheck,$pythonCheck_output,$keyword) {
        $CompromisedFileCheck = CompromisedFileCheck::where('compromised_server_id', $server->id)->where('defacement_type', $keyword)->where('file_path', $CompromisedFileOriginalCheck->file_path)->where('site_id',$server->site_id)->first(); 
        $insertLeak = true;
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

        // if(false){
        //     $DataLeakFeedCheck = DataLeakFeed::where('data_id', $CompromisedFileCheck->id)
        //     ->where('temp_id', $server->id)
        //     ->where('feel_type', $keyword)
        //     ->where('feedcontent',$server->site_id)->first();
           
        //     if(!$DataLeakFeedCheck){
        //         $DataLeakFeedCheck = new DataLeakFeed;
        //         $DataLeakFeedCheck->code = generator_uuid();
        //         $DataLeakFeedCheck->data_id =$CompromisedFileCheck->id;
        //         $DataLeakFeedCheck->temp_id =  $server->id;
        //         $DataLeakFeedCheck->sourceid = 1000;
        //         $DataLeakFeedCheck->keyword = $keyword;
        //         $DataLeakFeedCheck->source_name = $detail["file_path"];
        //         // $DataLeakFeedCheck->feedtimepost = $detail["file_modified"];
        //         $DataLeakFeedCheck->feedtimepost = date("Y-m-d H:i:s");
        //         $DataLeakFeedCheck->feedtimestamp = date("Y-m-d H:i:s");
        //         $DataLeakFeedCheck->feedcontent = $implode_blacklist;
        //         $DataLeakFeedCheck->status = 1;
        //         $DataLeakFeedCheck->feel_type = $feedtype;
        //         $DataLeakFeedCheck->view = 0;
        //         $DataLeakFeedCheck->save();
        //     }

        //     $DataLeakSocialRefCheck = DataLeakSocialRef::where('data_leak_feed_id', $DataLeakFeedCheck->id)
        //     ->where('site_id', $server->site_id)
        //     ->where('temp_id', $server->id)->first();
        //     if(!$DataLeakSocialRefCheck){
        //         $DataLeakSocialRefCheck = new DataLeakSocialRef;
        //         $DataLeakSocialRefCheck->code = generator_uuid();
        //         $DataLeakSocialRefCheck->temp_id = $server->id;
        //         $DataLeakSocialRefCheck->data_leak_feed_id =  $DataLeakFeedCheck->id;
        //         $DataLeakSocialRefCheck->site_id = $server->site_id;
        //         $DataLeakSocialRefCheck->keyword = $keyword ;
        //         $DataLeakSocialRefCheck->status = 1;
        //         $DataLeakSocialRefCheck->feel_type = $feedtype;
        //         $DataLeakSocialRefCheck->view = 0;
        //         $DataLeakSocialRefCheck->save();

        //     }
        // }
        return 0;
    }

    private  function user_exec_mod($text) {
        $output = explode("\n", $text);
        return $output;
    }


    private  function searchError($dir_file,$pythonCheck_output) {
        $output = array();
        $arrayError = $this->user_exec_mod($pythonCheck_output);//insert
        foreach ($arrayError as $value) {
            $splitError = $this->get_exec_subpython($dir_file,$value);
            if($splitError["result"]){
                $output [] = $splitError["value_err"];
            }
        }
        return $output;
    }

    private  function get_exec_subpython($dir_file,$line) {
        $split_detail["value_err"] = "";
        $split_detail["result"] = false;
        $split_detail["mark"] = substr($line,0,3);

        if($split_detail["mark"]=='[*]'){
            $split_detail["result"] = true;
            $split_first = substr($line,4);
            $searchRe = " in \"".$dir_file."\"";
            $pos = strpos($split_first, $searchRe);
            if ($pos !== false) {
                $split_detail["value_err"] = substr_replace($split_first, "", $pos, strlen($searchRe));
            }else{
                $split_detail["value_err"] = $split_first;
            }
        }

        return $split_detail;
    }

}
