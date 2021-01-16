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
use App\DataLeakFeedTemp;
use App\leak_socail_ref_temp;

use App\Entities\Transaction_center_compromised_files_check;
use App\Entities\Transaction_center_data_leak_feed_temp;
use App\Entities\Transaction_center_data_leak_socail_ref_temp;

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
    private $pathPythonScan  = "cd /python_scanner/yara-scanner/ && python3 yara_main.py --scan-file";
    // private $pathPythonScan  = "bash -c cd /python_scanner/yara-scanner && python3 yara_main.py --scan-file";
    private $pathToSave  = "";
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
        // $output = shell_exec("cd /python_scanner/yara-scanner/ && python3 yara_main.py --scan-file '/var/www/html/threat-intelligent-center/threat-intelligent-center/storage/compromised/webserver/1/e0763fe7-22f8-4502-b580-7a6584cc5f6b.php'");
        // $savePythonScan = $this->searchError('/var/www/html/threat-intelligent-center/threat-intelligent-center/storage/compromised/webserver/1/e0763fe7-22f8-4502-b580-7a6584cc5f6b.php',$output);
        // echo json_encode($savePythonScan);
        // return 0;
        $CompromisedServer = CompromisedServer::where('active', '1')->whereNull('deleted_at')->get(); 
        if($CompromisedServer){
            foreach ($CompromisedServer as $server) {
                try{
                    
                    // $ip = $server->ip;
                    // $port = $server->port;
                    // $user = $server->user;
                    // $pass = $server->password;
                    // $ssh = new SSH2($ip,$port);
                    // $ssh->setTimeout($this->timeOutSub);
                    // $return_value = null;
                    // if (!$ssh->login($user, $pass)) {
                    //     $return_value = null;
                    // } else {
                    //     $this->scanFolder($server,$ssh);
                    // }

                    $ssh = null;
                    $this->scanFolder($server,$ssh);
                } catch (Exception $e) {
                    echo "line : ".$e->getLine()." Error :".$e->getMessage()."\r\n";
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
                        // $this->info($dir_file);
                        if($CompromisedFileOriginalCheck){
                            $pythonCheck_output = $this->pythonCheck($ssh,$dir_file);
                            $savePythonScan = $this->searchError($dir_file,$pythonCheck_output);
                            if(!empty($savePythonScan)){
                                echo "Scan Found: ".$CompromisedFileOriginalCheck->file_path." -IN- ".$dir_folder." --->> " .json_encode($savePythonScan)."\r\n";
                                $this->savePythonScan($server,$CompromisedFileOriginalCheck,$savePythonScan,'scanner','webserver');
                            }
                        }
                        unlink($dir_file);
                    }
                }
                echo "Scan END: ".$dir_folder."\r\n";
                closedir($dh);
            }
            rmdir($dir_folder);
        }
        return 0;
    }

    private  function pythonCheck($ssh,$dir_file) {
        $cmd = $this->pathPythonScan." '".$dir_file."'";
        $output = shell_exec($cmd);
        // $output = @$ssh->exec($cmd);
        return $output;
    }

    private  function savePythonScan($server,$CompromisedFileOriginalCheck,$savePythonScan,$keyword,$feel_type) {
        $CompromisedFileCheck = CompromisedFileCheck::where('compromised_server_id', $server->id)->where('defacement_type', $keyword)->where('file_path', $CompromisedFileOriginalCheck->file_path)->where('site_id',$server->site_id)->first(); 
        $insertLeak = true;
        if(!$CompromisedFileCheck){
            $CompromisedFileCheck = new CompromisedFileCheck;
            $CompromisedFileCheck->code = generator_uuid();
            $CompromisedFileCheck->compromised_server_id = $server->id;
            $CompromisedFileCheck->file_name = $CompromisedFileOriginalCheck->file_name;
            $CompromisedFileCheck->file_extension = $CompromisedFileOriginalCheck->file_extension;
            $CompromisedFileCheck->file_path = $CompromisedFileOriginalCheck->file_path;
            $CompromisedFileCheck->file_size = $CompromisedFileOriginalCheck->file_size;
            $CompromisedFileCheck->file_modified = $CompromisedFileOriginalCheck->file_modified;
            $CompromisedFileCheck->defacement_type = $keyword;
            $CompromisedFileCheck->defacment_description = $savePythonScan;
            $CompromisedFileCheck->file_status = 1;
            $CompromisedFileCheck->active = 1;
            $CompromisedFileCheck->site_id = $server->site_id;
            $CompromisedFileCheck->save();

            $Transaction_center_compromised_files_check = new Transaction_center_compromised_files_check;
            $Transaction_center_compromised_files_check -> site_id = $server->site_id;
            $Transaction_center_compromised_files_check -> transaction_id = $CompromisedFileCheck->id;
            $Transaction_center_compromised_files_check -> transaction_mode = 'insert';
            $Transaction_center_compromised_files_check -> transaction_data_status = 1;
            $Transaction_center_compromised_files_check -> status = 1;
            $Transaction_center_compromised_files_check -> save();
        }else{
            if($CompromisedFileCheck->file_status==2){
                //no update
                $insertLeak = false;
            }else if($CompromisedFileCheck->file_status==1){
                $CompromisedFileCheck->file_size = $CompromisedFileOriginalCheck->file_size;
                $CompromisedFileCheck->defacment_description = $savePythonScan;
                $CompromisedFileCheck->site_id = $server->site_id;
                $CompromisedFileCheck->save();

                $Transaction_center_compromised_files_check = new Transaction_center_compromised_files_check;
                $Transaction_center_compromised_files_check -> site_id = $server->site_id;
                $Transaction_center_compromised_files_check -> transaction_id = $CompromisedFileCheck->id;
                $Transaction_center_compromised_files_check -> transaction_mode = 'update';
                $Transaction_center_compromised_files_check -> transaction_data_status = 1;
                $Transaction_center_compromised_files_check -> status = 1;
                $Transaction_center_compromised_files_check -> save();
            }

            
        }

        if($insertLeak){
            $DataLeakFeedCheck = DataLeakFeedTemp::where('source_name', $CompromisedFileOriginalCheck->file_path)
            ->where('feedcontent', $savePythonScan)
            ->where('keyword', $keyword)
            ->where('feed_type', $feel_type)->first();
            if(!$DataLeakFeedCheck){
                $DataLeakFeedCheck = new DataLeakFeedTemp;
                $DataLeakFeedCheck->code = null;
                $DataLeakFeedCheck->data_id = $CompromisedFileCheck->id;
                $DataLeakFeedCheck->data_id = null;
                $DataLeakFeedCheck->sourceid = 1000;
                $DataLeakFeedCheck->keyword = $keyword;
                $DataLeakFeedCheck->source_name = $CompromisedFileOriginalCheck->file_path;
                // $DataLeakFeedCheck->feedtimepost = $detail["file_modified"];
                $DataLeakFeedCheck->feedtimepost = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedtimestamp = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedcontent = $savePythonScan;
                $DataLeakFeedCheck->status = 1;
                $DataLeakFeedCheck->feed_type = $feel_type;
                $DataLeakFeedCheck->approve = 0;
                $DataLeakFeedCheck->save();
                // $id_DataLeakFeedCheck = $DataLeakFeedCheck->id;

                $Transaction_center_compromised_files_check = new Transaction_center_data_leak_feed_temp;
                $Transaction_center_compromised_files_check -> site_id = $server->site_id;
                $Transaction_center_compromised_files_check -> transaction_id = $DataLeakFeedCheck->id;
                $Transaction_center_compromised_files_check -> transaction_mode = 'insert';
                $Transaction_center_compromised_files_check -> transaction_data_status = 1;
                $Transaction_center_compromised_files_check -> status = 1;
                $Transaction_center_compromised_files_check -> save();
            }else{
                // $id_DataLeakFeedCheck = $DataLeakFeedCheck->id;
                $DataLeakFeedCheck->source_name = $CompromisedFileOriginalCheck->file_path;
                $DataLeakFeedCheck->feedtimepost = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedtimestamp = date("Y-m-d H:i:s");
                $DataLeakFeedCheck->feedcontent = $savePythonScan;
                $DataLeakFeedCheck->save();

                $Transaction_center_compromised_files_check = new Transaction_center_data_leak_feed_temp;
                $Transaction_center_compromised_files_check -> site_id = $server->site_id;
                $Transaction_center_compromised_files_check -> transaction_id = $DataLeakFeedCheck->id;
                $Transaction_center_compromised_files_check -> transaction_mode = 'update';
                $Transaction_center_compromised_files_check -> transaction_data_status = 1;
                $Transaction_center_compromised_files_check -> status = 1;
                $Transaction_center_compromised_files_check -> save();

            }

            $DataLeakSocialRefCheck = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedCheck->id)
            ->where('site_id', $server->site_id."")->first();
            if(!$DataLeakSocialRefCheck){
                $DataLeakSocialRefCheck = new leak_socail_ref_temp;
                $DataLeakSocialRefCheck->code = null;
                $DataLeakSocialRefCheck->data_leak_feed_id =  $DataLeakFeedCheck->id;
                $DataLeakSocialRefCheck->site_id = $server->site_id;
                $DataLeakSocialRefCheck->keyword = $keyword;
                $DataLeakSocialRefCheck->status = 1;
                $DataLeakSocialRefCheck->view = null;
                $DataLeakSocialRefCheck->save();

                $Transaction_center_compromised_files_check = new Transaction_center_data_leak_socail_ref_temp;
                $Transaction_center_compromised_files_check -> site_id = $server->site_id;
                $Transaction_center_compromised_files_check -> transaction_id = $DataLeakSocialRefCheck->id;
                $Transaction_center_compromised_files_check -> transaction_mode = 'insert';
                $Transaction_center_compromised_files_check -> transaction_data_status = 1;
                $Transaction_center_compromised_files_check -> status = 1;
                $Transaction_center_compromised_files_check -> save();
                
            }else{
                $DataLeakSocialRefCheck->keyword = $keyword;
                $DataLeakSocialRefCheck->save();

                $Transaction_center_compromised_files_check = new Transaction_center_data_leak_socail_ref_temp;
                $Transaction_center_compromised_files_check -> site_id = $server->site_id;
                $Transaction_center_compromised_files_check -> transaction_id = $DataLeakSocialRefCheck->id;
                $Transaction_center_compromised_files_check -> transaction_mode = 'update';
                $Transaction_center_compromised_files_check -> transaction_data_status = 1;
                $Transaction_center_compromised_files_check -> status = 1;
                $Transaction_center_compromised_files_check -> save();
            }
        }
        return 0;
    }

    private  function user_exec_mod($text) {
        $output = explode("\n", $text);
        return $output;
    }


    private  function searchError($dir_file,$pythonCheck_output) {
        $output = null;
        $arrayError = $this->user_exec_mod($pythonCheck_output);//insert
        foreach ($arrayError as $value) {
            $splitError = $this->get_exec_subpython($dir_file,$value);
            if($splitError["result"]){
                $output = $output.$splitError["value_err"].",";
            }
        }
        $output = rtrim($output,",");
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

                $myText = $split_detail["value_err"];
                // $myText = preg_replace('/(?<='.preg_quote(' :"').').*?(?='.preg_quote('"').')/', ',', $myText);
                $myText = preg_replace('/'.preg_quote('] :"').'.*?'.preg_quote('"').'/', '],', $myText);
                $split_detail["value_err"] = rtrim($myText,",");;
                
                // $split_point = "]";
                // $stringpos = strrpos($split_detail["value_err"], $split_point)+1;
                // $split_detail["value_err"] = substr($split_detail["value_err"],0,$stringpos);


            }else{
                $split_detail["value_err"] = $split_first;
            }
        }

        return $split_detail;
    }

}
