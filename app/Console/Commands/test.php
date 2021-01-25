<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';
    protected $description = 'test';


    
    public function __construct()
    {
     parent::__construct();
        // $this->site_code = config('app.site_code');
        // $this->site_mode = config('app.mode');
        // $this->header = config('app.site_key');
        // $this->urlCenterData = $this->urlCenterData.'?code='.$this->site_code;
        // $this->ip =exec("hostname -I");
        // $this->mac = exec("cat /sys/class/net/ens33/address");
 }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $Assets_list = [];
        $Assets_data = Assets::where('status',1)->get();
        foreach ($Assets_data as $key => $value) {
           $AssetsData_data = AssetsData::where('site_id',$value->site_id)->where('asset_id',$value->id)->where('status',1)->get();
           $Domain_list = [];
           $IP_List =[];
           foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
            if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                //Domain
                array_push($Domain_list, $AssetsData_datavalue);

            }elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                //IP Asset
               array_push($IP_List, $AssetsData_datavalue);

           }else{

           }
       }


    foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
            $CPR_string ="";
            $CPE_Data = CPE::where('asset_id',$IP_Listvalue->id)->get();
            $CPE_List = array();
            foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                array_push($CPE_List, $CPE_Datavalue->result .' : '.$CPE_Datavalue->os_type);
                
            }
            if (count($CPE_List) > 0) {
                $CPR_string = implode(' | ', (array) $CPE_List);
            }

            if (count($Domain_list) == 0) {
            $Assets_data_list = array();
            $Assets_data_list['id'] = $IP_Listvalue->id;
            $Assets_data_list['code'] = $IP_Listvalue->code;
            $Assets_data_list['site_id'] = $IP_Listvalue->site_id;
            $Assets_data_list['status'] = $IP_Listvalue->status;
            $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
            $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
            $Assets_data_list['domain'] = "";
            $Assets_data_list['ip'] = $IP_Listvalue->value;
            $Assets_data_list['CPE'] = $CPR_string;

            array_push($Assets_list, $Assets_data_list);

        }else{

            foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                $Assets_data_list = array();
                $Assets_data_list['id'] = $IP_Listvalue->id;
                $Assets_data_list['code'] = $IP_Listvalue->code;
                $Assets_data_list['site_id'] = $IP_Listvalue->site_id;
                $Assets_data_list['status'] = $IP_Listvalue->status;
                $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
                $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
                $Assets_data_list['domain'] = $Domain_listvalue->value;
                $Assets_data_list['ip'] = $IP_Listvalue->value;
                $Assets_data_list['CPE'] = $CPR_string;
                array_push($Assets_list, $Assets_data_list);

            }
        }


    }


}
print_r($Assets_list);

}


}
