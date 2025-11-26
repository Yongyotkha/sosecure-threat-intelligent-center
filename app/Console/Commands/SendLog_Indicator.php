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

use Illuminate\Support\Facades\Log;

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
        // Get Setting Site ก่อนว่าควรจะมี Site ใหนทีจะ send log
        // $site_id =73;
        // $Logs_setting_data = Logs_setting::->where('type','indicator')->first();

        $logs_setting_content = Sites::select('logs_setting.content', 'logs_setting.site_id')
            ->join('logs_setting', 'site.id', '=', 'logs_setting.site_id')
            ->where('site.active', 1)
            ->where('logs_setting.type', 'indicator')
            ->get();

        // print_r($logs_setting_content);
        // Log::info($logs_setting_content);
        // return;

        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;

        $options = ['allowDiskUse' => TRUE];

        $tz = new \DateTimeZone('Asia/Bangkok');
        $start = date("Y-m-d") . ' 00:00:00';
        $end = date("Y-m-d") . ' 23:59:59';

        $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($start) * 1000);
        $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($end) * 1000);

        $where = [
            'updated_at' => ['$gte' => $dateStart, '$lt' => $dateEnd],
        ];

        $cursor = $col_fx_otx_indicator_detail->find($where);
        $docs = $cursor->toArray();

        foreach ($docs as $data) {

            // ดึงข้อมูลอ้างอิงของ indicator_id จาก ref table
            $where_ref = ['indicator_id' => $data->indicator_id];
            $cursor_ref = $col_fx_otx_events_indicator_ref->find($where_ref);
            $docs_ref = $cursor_ref->toArray();

            foreach ($docs_ref as $key => $value_ref) {

                // ดึงข้อมูล event โดยใช้ pulse_id จาก ref table
                $where_event = ['pulse_id' => $value_ref->pulse_id];
                $cursor_event = $col_fx_otx_events->find($where_event);
                $docs_event = $cursor_event->toArray();

                foreach ($docs_event as $docs_eventvalue) {

                    if (strpos($docs_eventvalue->name, 'Public DNS') !== false) {
                        continue;
                    }

                    $attribute_score = $value_ref->attribute_score ?? '';
                    $attribute_severity = $value_ref->attribute_serverity ?? ($value_ref->attribute_serverity ?? '');
                    // log::info($attribute_severity);

                    foreach ($logs_setting_content as $logs_setting_content_data) {

                        if ($logs_setting_content_data->content) {

                            $format_str = $logs_setting_content_data->content;

                            // Date/time
                            $format_str = str_replace("[[M]]", date("M"), $format_str);
                            $format_str = str_replace("[[m]]", date("m"), $format_str);
                            $format_str = str_replace("[[Y]]", date("Y"), $format_str);
                            $format_str = str_replace("[[y]]", date("y"), $format_str);
                            $format_str = str_replace("[[d]]", date("d"), $format_str);
                            $format_str = str_replace("[[D]]", date("D"), $format_str);
                            $format_str = str_replace("[[h:i:s]]", date("h:i:s"), $format_str);
                            $format_str = str_replace("[[H:i:s]]", date("H:i:s"), $format_str);

                            // Event data
                            $format_str = str_replace("[[Event name]]", $docs_eventvalue->name ?? '', $format_str);
                            $format_str = str_replace("[[Event Description]]", $docs_eventvalue->description ?? '', $format_str);

                            $format_str = str_replace("[[Attribute Type]]", $data->type ?? '', $format_str);
                            $format_str = str_replace("[[Attribute Name]]", $data->indicator_name ?? '', $format_str);

                            $format_str = str_replace("[[Attribute Score]]", $attribute_score ?? "", $format_str);
                            $format_str = str_replace("[[Attribute Severity]]", $attribute_severity ?: "", $format_str);

                            // $placeholders = ["[[Attribute Serverity]]", "[[Attribute Severity]]"];

                            // $format_str = str_replace($placeholders, $attribute_severity ?: "", $format_str);



                            $format_str = str_replace("[[Tags]]", $docs_eventvalue->tags, $format_str);
                            $format_str = str_replace("[[Attribute DateTime]]", change_date_utc_to_thai($docs_eventvalue->modified), $format_str);

                            // Log::info($format_str);
                            $Logs_sent_transaction_save = new Logs_sent_transaction;
                            $Logs_sent_transaction_save->site_id = $logs_setting_content_data->site_id;
                            $Logs_sent_transaction_save->content = $format_str;
                            $Logs_sent_transaction_save->type = 'indicator';


                            if ($logs_setting_content_data->site_id == 85) {
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
                                // Log::info('🟢 MOCK SEND: ' . $message);
                            } else {
                                $Logs_sent_transaction_save->transaction_status = 1;
                                //$Logs_sent_transaction_save->created_at = date("yyyy-MM-dd H:i:s"); 
                                $Logs_sent_transaction_save->save();



                                sleep(1);
                                $message     = $format_str;
                            }
                            // echo $format_str;
                            // log::info($format_str);
                        }
                    }
                }
            }
        }

        // 🟨 ส่วนข้างล่างที่ comment ไว้เดิม — เก็บไว้ตามเดิมเลย
        /*
    $format_str = str_replace("[[Tags]]", $docs_eventvalue->tags, $format_str);
    $format_str = str_replace("[[Attribute DateTime]]", change_date_utc_to_thai($docs_eventvalue->modified), $format_str);

    // Log::info($format_str);

    // $Logs_sent_transaction_save = new Logs_sent_transaction;
    // $Logs_sent_transaction_save->site_id = $logs_setting_content_data->site_id;
    // $Logs_sent_transaction_save->content = $format_str ;
    // $Logs_sent_transaction_save->type = 'indicator' ;

    // if($logs_setting_content_data->site_id == 85){
    //     $Logs_sent_transaction_save->transaction_status = 3;
    //     $Logs_sent_transaction_save->save(); 
    //     $server_ip   = "172.16.11.147";
    //     $server_port = "1521";
    //     sleep(1);
    //     $message     = $format_str;
    //     if ($socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
    //         socket_sendto($socket, $message, strlen($message), 0, $server_ip, $server_port);
    //     }
    // } else {
    //     $Logs_sent_transaction_save->transaction_status = 1;
    //     $Logs_sent_transaction_save->save(); 
    //     sleep(1);
    //     $message = $format_str;
    // }
    */
    }
}
