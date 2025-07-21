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


use App\leak_socail_ref_temp;
use Modules\SiteSettings\Entities\Site_keywords;
use App\Entities\TransactionBatchjob;
use App\DataLeakFeedTemp;
use App\DataLeakSocial;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
class SendLog_Indicator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:SendLog_Indicator';
    protected $description = 'SendLog_Indicator';


    
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


        //Get Setting Site ก่อนว่าควรจะมี Site ใหนทีจะ send log
      //  $site_id =73;
      //  $Logs_setting_data = Logs_setting::->where('type','indicator')->first();

        $logs_setting_content =  Sites::select('logs_setting.content','logs_setting.site_id')
        ->join('logs_setting', 'site.id', '=', 'logs_setting.site_id')
        ->where('site.active', 1)->where('logs_setting.type','indicator')
        ->get();
        print_r($logs_setting_content);

        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
        $options = [
            'allowDiskUse' => TRUE
        ];




        // $start = new \MongoDB\BSON\UTCDateTime(strtotime('2020-01-01 00:00:00'));
        // $end = new \MongoDB\BSON\UTCDateTime(strtotime('2020-12-31 23:59:59'));
        $tz = new \DateTimeZone('Asia/Bangkok');
        $start = date("Y-m-d").' 00:00:00';
        $end = date("Y-m-d").' 23:59:59';
        //$start = '2021-03-06'.' 12:00:00';
        //$end = '2021-03-06'.' 13:59:59';
        $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($start)*1000);
        $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($end)*1000);

        // print_r($dateStart->toDateTime()->format(DATE_RSS));


        $where = array(
            //'feedtimepost' => ['$gt' => $time_stamp_search],
            //'feedcontent' => ['$regex'=>'PTT', '$options' => 'i'],
            //'sourceid' => 1,
           // "\$gte"=>$date1,
           // "\$lt"=>$date2
            'updated_at' => ['$gte' => $dateStart,'$lt' => $dateEnd],
           // 'feedcontent' => new \MongoDB\BSON\Regex($Site_keyword -> name),
           // 'feedcontent' => new \MongoDB\BSON\Regex('ทำไมแฟนผมเป็นแบบนี้'),
        );
    $cursor = $col_fx_otx_indicator_detail->find($where);   //This is the main line
    $docs = $cursor->toArray();
    foreach($docs as $data){

        $where_ref = array(
            //'feedtimepost' => ['$gt' => $time_stamp_search],
            //'feedcontent' => ['$regex'=>'PTT', '$options' => 'i'],
            'indicator_id' => $data -> indicator_id,
           // "\$gte"=>$date1,
           // "\$lt"=>$date2
           // 'updated_at' => ['$gte' => $dateStart,'$lt' => $dateEnd],
           // 'feedcontent' => new \MongoDB\BSON\Regex($Site_keyword -> name),
           // 'feedcontent' => new \MongoDB\BSON\Regex('ทำไมแฟนผมเป็นแบบนี้'),
        );
    $cursor_ref = $col_fx_otx_events_indicator_ref->find($where_ref);   //This is the main line
    $docs_ref = $cursor_ref->toArray();
    foreach ($docs_ref as $key => $value_ref) {

        $where_event = array(
            //'feedtimepost' => ['$gt' => $time_stamp_search],
            //'feedcontent' => ['$regex'=>'PTT', '$options' => 'i'],
            'pulse_id' => $value_ref -> pulse_id,
           // 'modified' => ['$gte' => $dateStart,'$lt' => $dateEnd],
           // "\$gte"=>$date1,
           // "\$lt"=>$date2
           // 'updated_at' => ['$gte' => $dateStart,'$lt' => $dateEnd],
           // 'feedcontent' => new \MongoDB\BSON\Regex($Site_keyword -> name),
           // 'feedcontent' => new \MongoDB\BSON\Regex('ทำไมแฟนผมเป็นแบบนี้'),
        );
    $cursor_event = $col_fx_otx_events->find($where_event);   //This is the main line
    $docs_event = $cursor_event->toArray();
   // print_r($docs_event);

    foreach ($docs_event as $docs_eventkey => $docs_eventvalue) {
        # code...
        if(strpos($docs_eventvalue->name, 'Public DNS') !== false){

        }else{

                    foreach($logs_setting_content as  $logs_setting_content_key => $logs_setting_content_data ){
                            if ($logs_setting_content_data->content) {
                            $format_str = $logs_setting_content_data->content;
                            if ($format_str) {

                             //   $format_str    ="[[M]] [[d]] [[H:i:s]] CEF:0|Threat inSights||Threat inSights||1.0|100|[[Event name]]|2|src=[[Value of Attacker IP Address]] cn1Label=[[Tage]] cn1=[[Value of Tage]]";


                            

                                $format_str = str_replace("[[M]]",date("M"),$format_str);
                                $format_str = str_replace("[[m]]",date("m"),$format_str);
                                $format_str = str_replace("[[Y]]",date("Y"),$format_str);
                                $format_str = str_replace("[[y]]",date("y"),$format_str);
                                $format_str = str_replace("[[d]]",date("d"),$format_str);
                                $format_str = str_replace("[[D]]",date("D"),$format_str);
                                $format_str = str_replace("[[h:i:s]]",date("h:i:s"),$format_str);
                                $format_str = str_replace("[[H:i:s]]",date("H:i:s"),$format_str);




                                $format_str = str_replace("[[Event name]]", $docs_eventvalue->name,$format_str);

                                $format_str = str_replace("[[Event Description]]", $docs_eventvalue->description,$format_str);


                                $format_str = str_replace("[[Attribute Type]]", $data -> type,$format_str);
                                $format_str = str_replace("[[Attribute Name]]", $data -> indicator_name,$format_str);
                                $format_str = str_replace("[[Tags]]", $docs_eventvalue->tags,$format_str);
                                $format_str = str_replace("[[Attribute DateTime]]", change_date_utc_to_thai($docs_eventvalue->modified),$format_str);
                                $Logs_sent_transaction_save = new Logs_sent_transaction;
                                $Logs_sent_transaction_save->site_id = $logs_setting_content_data->site_id;
                                $Logs_sent_transaction_save->content = $format_str ;
                                $Logs_sent_transaction_save->type = 'indicator' ;


                                if($logs_setting_content_data->site_id == 85){
                                    $Logs_sent_transaction_save->transaction_status = 3;
                                    //$Logs_sent_transaction_save->created_at = date("yyyy-MM-dd H:i:s"); 
                                    $Logs_sent_transaction_save->save(); 
    
    
                                    $server_ip   = "172.16.11.147";
                                    $server_port = "1521";
                                    sleep(1);
                                    $message     = $format_str;
                                    if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
                                        socket_sendto($socket, $message, strlen($message), 0, $server_ip, $server_port);
                                    }
            
                                }else{
                                    $Logs_sent_transaction_save->transaction_status = 1;
                                    //$Logs_sent_transaction_save->created_at = date("yyyy-MM-dd H:i:s"); 
                                    $Logs_sent_transaction_save->save(); 
    
    
                             
                                    sleep(1);
                                    $message     = $format_str;
            

                                }
                              

                             


                                echo $format_str;
                            }
                        }


                    }
    }

}










}

}


}


}
