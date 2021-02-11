<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;
use App\Entities\Sites;
use App\Entities\Data_datacve_mapping;
use App\Entities\Logs_setting;
use App\Entities\CVE_assets;
use App\Entities\Logs_sent_transaction;
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

     //   echo date("M d H:i:s");
        $created_From = date("yyyy-MM-dd 00:00:00");
        $created_To = date("yyyy-MM-dd 23:59:59");
        $Data_datacve_mapping_data =    Data_datacve_mapping::get();
        foreach ($Data_datacve_mapping_data as $key => $value) {
            print_r($value);
            $Logs_setting_data = Logs_setting::where('site_id',$value->site_id)->where('type','cve')->first();
            if ($Logs_setting_data) {
             $format_str = $Logs_setting_data->content;
             if ($format_str) {

                $CVE_assets_data = CVE_assets::where('id',$value->cveven_id)->first();
                $format_str = str_replace("[[M]]",date("M"),$format_str);
                $format_str = str_replace("[[m]]",date("m"),$format_str);
                $format_str = str_replace("[[Y]]",date("Y"),$format_str);
                $format_str = str_replace("[[y]]",date("y"),$format_str);
                $format_str = str_replace("[[d]]",date("d"),$format_str);
                $format_str = str_replace("[[D]]",date("D"),$format_str);
                $format_str = str_replace("[[h:i:s]]",date("h:i:s"),$format_str);
                $format_str = str_replace("[[H:i:s]]",date("H:i:s"),$format_str);

                $format_str = str_replace("[[Vendor]]", $CVE_assets_data->vendor,$format_str);
                $format_str = str_replace("[[Title]]", $CVE_assets_data->title,$format_str);
                $format_str = str_replace("[[Version]]", $CVE_assets_data->version,$format_str);
                $format_str = str_replace("[[Edtion]]", $CVE_assets_data->edition,$format_str);
                if (strpos($format_str, '[[Site]]') !== false) {
                    $Sites_data = Sites::where('id',$value->site_id)->first();
                    $format_str = str_replace("[[Site]]", $Sites_data->name,$format_str);
                }
                $format_str = str_replace("[[IP]]", $CVE_assets_data->IP,$format_str);
                $format_str = str_replace("[[Hostname]]", $CVE_assets_data->Hostname,$format_str);
                $format_str = str_replace("[[Vuln ID]]", $value->namecve,$format_str);
                $format_str = str_replace("[[Published]]", $value->published,$format_str);
                $format_str = str_replace("[[Modified]]", $value->modified,$format_str);
                $format_str = str_replace("[[CVSS core]]", $value->cvss_score,$format_str);
                $format_str = str_replace("[[CVSS Severity]]", $value->severity,$format_str);
                $format_str = str_replace("[[Transaction]]", $value->created_at->format('Y-m-d H:i:s'),$format_str);
                $format_str = str_replace("[[Description]]", $value->description,$format_str);
                $Logs_sent_transaction_save = new Logs_sent_transaction;
                $Logs_sent_transaction_save->site_id = $value->site_id;
                $Logs_sent_transaction_save->content = $format_str ;
                $Logs_sent_transaction_save->type = 'cve' ;
                $Logs_sent_transaction_save->transaction_status = 3 ;
               //$Logs_sent_transaction_save->created_at = date("yyyy-MM-dd H:i:s"); 
                $Logs_sent_transaction_save->save(); 
            }
        }



    }
//[[M]] [[d]] [[H:i:s]] Sosecure CEF:0|Sosecure|Threat inSight|1.0|100|Vulnerability|| [[IP]] [[Hostname]] [[Vendor]] [[Title]] [[Version]] [[Edtion]] [[Vuln ID]] [[CVSS Severity]]


//         $Assets_list = [];
//         $Assets_data = Assets::where('status',1)->get();
//         foreach ($Assets_data as $key => $value) {
//            $AssetsData_data = AssetsData::where('site_id',$value->site_id)->where('asset_id',$value->id)->where('status',1)->get();
//            $Domain_list = [];
//            $IP_List =[];
//            foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
//             if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
//                 //Domain
//                 array_push($Domain_list, $AssetsData_datavalue);

//             }elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
//                 //IP Asset
//                array_push($IP_List, $AssetsData_datavalue);

//            }else{

//            }
//        }


//     foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
//             $CPR_string ="";
//             $CPE_Data = CPE::where('asset_id',$IP_Listvalue->id)->get();
//             $CPE_List = array();
//             foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
//                 array_push($CPE_List, $CPE_Datavalue->result .' : '.$CPE_Datavalue->os_type);

//             }
//             if (count($CPE_List) > 0) {
//                 $CPR_string = implode(' | ', (array) $CPE_List);
//             }

//             if (count($Domain_list) == 0) {
//             $Assets_data_list = array();
//             $Assets_data_list['id'] = $IP_Listvalue->id;
//             $Assets_data_list['code'] = $IP_Listvalue->code;
//             $Assets_data_list['site_id'] = $IP_Listvalue->site_id;
//             $Assets_data_list['status'] = $IP_Listvalue->status;
//             $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
//             $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
//             $Assets_data_list['domain'] = "";
//             $Assets_data_list['ip'] = $IP_Listvalue->value;
//             $Assets_data_list['CPE'] = $CPR_string;

//             array_push($Assets_list, $Assets_data_list);

//         }else{

//             foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
//                 $Assets_data_list = array();
//                 $Assets_data_list['id'] = $IP_Listvalue->id;
//                 $Assets_data_list['code'] = $IP_Listvalue->code;
//                 $Assets_data_list['site_id'] = $IP_Listvalue->site_id;
//                 $Assets_data_list['status'] = $IP_Listvalue->status;
//                 $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
//                 $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
//                 $Assets_data_list['domain'] = $Domain_listvalue->value;
//                 $Assets_data_list['ip'] = $IP_Listvalue->value;
//                 $Assets_data_list['CPE'] = $CPR_string;
//                 array_push($Assets_list, $Assets_data_list);

//             }
//         }


//     }


// }
// print_r($Assets_list);

}


}
