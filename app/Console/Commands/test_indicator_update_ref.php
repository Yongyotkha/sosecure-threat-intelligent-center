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
class test_indicator_update_ref extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test_indicator_update_ref';
    protected $description = 'test_indicator_update_ref';


    
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


        $site_id = 73;
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
     //   $start = date("Y-m-d").' 00:00:00';
        $end = date("Y-m-d").' 23:59:59';
        $start =  date("Y-m-d").' 00:00:00';
      //  $end = '2021-02-25'.' 23:59:59';
        $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($start)*1000);
        $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($end)*1000);

        // print_r($dateStart->toDateTime()->format(DATE_RSS));


        $where = array(
            //'feedtimepost' => ['$gt' => $time_stamp_search],
            //'feedcontent' => ['$regex'=>'PTT', '$options' => 'i'],
            //'pulse_id' => '6035a111d8311d7ab22b6fa3',
           // "\$gte"=>$date1,
           // "\$lt"=>$date2
          'updated_at' => ['$gte' => $dateStart,'$lt' => $dateEnd],
          'type' =>['$exists'=> false],
         // 'indicator' => [ '$exists'=> true, '$ne'=> null ] ,
           // 'feedcontent' => new \MongoDB\BSON\Regex($Site_keyword -> name),
           // 'feedcontent' => new \MongoDB\BSON\Regex('ทำไมแฟนผมเป็นแบบนี้'),
      );
    $cursor = $col_fx_otx_events_indicator_ref->find($where);   //This is the main line
    $docs = $cursor->toArray();
    $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
    foreach ($docs as  $value) {

      $query = [
        'indicator_id' => $value->indicator_id

    ];
    $cursor_2 = $col_fx_otx_indicator_detail->findOne($query);

    if ($cursor_2) {
        $newdata = array('$set' => array("indicator" => @$cursor_2['indicator_name'], "type" =>@$cursor_2['type']));
// specify the column name whose value is to be updated. If no such column than a new column is created with the same name.

        $condition = array('_id' => new \MongoDB\BSON\ObjectID($value->_id));
// specify the condition with column name. If no such column exist than no record will update

        if($col_fx_otx_events_indicator_ref->updateOne($condition, $newdata))
        {
            echo '<p style="color:green;">'.$value->_id.'-Record updated successfully</p>';
        }
        else
        {
            echo '<p style="color:red;">Error in update</p>';
        }
    }


}






}




}
