<?php

namespace App\Console\Commands;

use Crypt;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Storage;
use Modules\Assets\Entities\OSType;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\Assets_port;
class Asset_Scan_Port extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:Asset_Scan_Port';

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



         $SiteSettings_data =   SiteSettings::where('active',1)->get();
         foreach($SiteSettings_data as $SiteSettings_data_key => $SiteSettings_data_value)
         {
            
                        $Assets_data = Assets::where('status', 1)->where('site_id', $SiteSettings_data_value->id)->get();
                        $OsType = OSType::get()->keyBy('id')->toArray();
                        $SiteSettings = SiteSettings::withTrashed()->get()->keyBy('id')->toArray();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id', $value->site_id)->where('asset_id', $value->id)->get();
                            $Domain_list = [];
                            $IP_List = [];
                            foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                    //Domain
                                    array_push($Domain_list, $AssetsData_datavalue);

                                } elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                    //IP Asset
                                    array_push($IP_List, $AssetsData_datavalue);
                                    $this->info($AssetsData_datavalue->value);

                                    $cmd = 'ssh -t root@10.104.0.12  nmap sosecure.co.th';
                                    $current_port = ''; 
                                    $current_port_line_port = 0; 
                                    $descriptorspec = array(
                                      0 => array("pipe", "r"),
                                      1 => array("pipe", "w"),
                                      2 => array("pipe", "w")
                                  );
                                  flush();
                             
                                  
                                  for ($i=1; $i <= 3; $i++) { 
                                   try {
                                    $process_looking_for_subdomain_securitytrails  = proc_open($cmd, $descriptorspec, $pipes, realpath('./'), array());
                                    if (is_resource($process_looking_for_subdomain_securitytrails)) {
                                        while ($s = fgets($pipes[1])) {
                                            if(strpos($s, 'PORT     STATE SERVICE') !== false || $current_port_line_port >0){
                                               $current_port_line_port++;
                                               $current_port .= $s;
                                          
                                           } else{
                                           
                                           }
                                            flush();
                                        }
                                    }
                                    break;
                                   } catch (\Throwable $th) {
                                   }
                               }
                               $count = 0;
                               foreach(preg_split("/((\r?\n)|(\r\n?))/", $current_port) as $line){
                                $count++;
                                if($count > 1){

                                   $pizza  = $line;
                                   $this->info($pizza);
                                   $pieces = explode("/", $pizza);
                                   if(count($pieces) > 0){
                                    $port = $pieces[0];
                                    $Assets_port_data =  Assets_port::where('asset_id',$value->site_id)->where('asset_name',$AssetsData_datavalue->value)->where('port',$port)->first();
                                    if($Assets_port_data){
                                        $Assets_port_data->port = $port;
                                        $Assets_port_data->status =1;
                                        $Assets_port_data->updated_at =date("Y-m-d H:i:s");
                                        $Assets_port_data->save(); 
                                    }else{
                                        $Assets_port_data  = new Assets_port;
                                        $Assets_port_data->asset_id = $value->site_id;
                                        $Assets_port_data->asset_name = $AssetsData_datavalue->value;
                                        $Assets_port_data->port = $port;
                                        $Assets_port_data->status =1;
                                        $Assets_port_data->created_at =date("Y-m-d H:i:s");
                                        $Assets_port_data->save(); 

                                    }
                                 
 

                                   }
                                











                                   if(!$line){
                                      break;
                                         
                                   }
                             
                                }
                               
                             } 












                                   
                                } else {

                                }
                            }
                        
                        }
            }


          




    }

}
