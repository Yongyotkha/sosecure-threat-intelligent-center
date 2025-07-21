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
class indicator_log extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:indicator_log';
    protected $description = 'indicator_log';


    
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


        $site_id =85;
        $Logs_setting_data = Logs_setting::where('site_id',$site_id)->where('type','indicator')->first();

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

         print_r($dateStart->toDateTime()->format(DATE_RSS));


        $where = array(
            //'feedtimepost' => ['$gt' => $time_stamp_search],
            //'feedcontent' => ['$regex'=>'PTT', '$options' => 'i'],
            'indicator_id' => "2863709669",
           // "\$gte"=>$date1,
           // "\$lt"=>$date2
            //'updated_at' => ['$gte' => $dateStart,'$lt' => $dateEnd],
           // 'feedcontent' => new \MongoDB\BSON\Regex($Site_keyword -> name),
           // 'feedcontent' => new \MongoDB\BSON\Regex('à¸—à¸³à¹„à¸¡à¹à¸Ÿà¸™à¸œà¸¡à¹€à¸›à¹‡à¸™à¹à¸šà¸šà¸™à¸µà¹‰'),
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
           // 'feedcontent' => new \MongoDB\BSON\Regex('à¸—à¸³à¹„à¸¡à¹à¸Ÿà¸™à¸œà¸¡à¹€à¸›à¹‡à¸™à¹à¸šà¸šà¸™à¸µà¹‰'),
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
           // 'feedcontent' => new \MongoDB\BSON\Regex('à¸—à¸³à¹„à¸¡à¹à¸Ÿà¸™à¸œà¸¡à¹€à¸›à¹‡à¸™à¹à¸šà¸šà¸™à¸µà¹‰'),
        );
    $cursor_event = $col_fx_otx_events->find($where_event);   //This is the main line
    $docs_event = $cursor_event->toArray();
   // print_r($docs_event);

    foreach ($docs_event as $docs_eventkey => $docs_eventvalue) {
        # code...

        if ($Logs_setting_data) {
         $format_str = $Logs_setting_data->content;
         if ($format_str) {

            $format_str = str_replace("[[M]]",date("M"),$format_str);
            $format_str = str_replace("[[m]]",date("m"),$format_str);
            $format_str = str_replace("[[Y]]",date("Y"),$format_str);
            $format_str = str_replace("[[y]]",date("y"),$format_str);
            $format_str = str_replace("[[d]]",date("d"),$format_str);
            $format_str = str_replace("[[D]]",date("D"),$format_str);
            $format_str = str_replace("[[h:i:s]]",date("h:i:s"),$format_str);
            $format_str = str_replace("[[H:i:s]]",date("H:i:s"),$format_str);

            $format_str = str_replace("[[Event name]]", $docs_eventvalue->name,$format_str);
            $format_str = str_replace("[[Attribute Type]]", $data -> type,$format_str);
            $format_str = str_replace("[[Attribute Name]]", $data -> indicator_name,$format_str);
            $format_str = str_replace("[[Tags]]", $docs_eventvalue->tags,$format_str);
            $format_str = str_replace("[[Attribute DateTime]]", change_date_utc_to_thai($docs_eventvalue->modified),$format_str);
            $Logs_sent_transaction_save = new Logs_sent_transaction;
            $Logs_sent_transaction_save->site_id = $site_id;
            $Logs_sent_transaction_save->content = $format_str ;
            $Logs_sent_transaction_save->type = 'indicator' ;
            $Logs_sent_transaction_save->transaction_status = 1;
               //$Logs_sent_transaction_save->created_at = date("yyyy-MM-dd H:i:s"); 
            $Logs_sent_transaction_save->save(); 
            echo $format_str;
        }
    }

}










}

}


}


}
