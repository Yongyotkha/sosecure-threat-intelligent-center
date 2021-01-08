<?php

namespace App\Console\Commands;

use App\DataLeakFeedTemp;
use App\DataLeakSocial;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;
use App\leak_socail_ref_temp;
use Modules\SiteSettings\Entities\Site_keywords;

class data_leak_social extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:data_leak_social';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'concention MomngoDB';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       // require 'C://xampp//htdocs//threat-intelligent-center//vendor//autoload.php'; // include Composer's autoloader]
       // $mongo = new \MongoDB\Client('mongodb://10.104.0.10:27017');
// Manager Class
        // $manager = new \MongoDB\Driver\Manager("mongodb://10.104.0.10:27017");


        // $start = new \MongoDB\BSON\UTCDateTime(strtotime('2020-01-01 00:00:00'));
        // $end = new \MongoDB\BSON\UTCDateTime(strtotime('2020-12-31 23:59:59'));
        $tz = new \DateTimeZone('Asia/Bangkok');
        $start = '2020-01-01 00:00:00';
        $end = '2020-12-31 23:59:59';
        $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($start)*1000);
        $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($end)*1000);

        // print_r($dateStart->toDateTime()->format(DATE_RSS));
        $date_start = $dateStart->toDateTime();
        $date_end = $dateEnd->toDateTime();

        $date_start->setTimezone($tz);
        $date_end->setTimezone($tz);

        $start = $date_start->format(DATE_ATOM);
        $end = $date_end->format(DATE_ATOM);

        echo $start;
        echo $end;
        // // Query Class
        // $query = new \MongoDB\Driver\Query(array('feedtimestamp' => array('$gt' => $start, '$lte' => $end)));

        // // Output of the executeQuery will be object of MongoDB\Driver\Cursor class
        // $cursor = $manager->executeQuery('social.Feed', $query);

        // Convert cursor to Array and print result
        $mongo_url = 'mongodb://10.104.0.10:27017';
        $client = new \MongoDB\Client($mongo_url);
        $db_name = 'social';
        $db = $client->$db_name;
        $collection = $db->Feed;

        // $where = array(
        //     'feedtimestamp' => array('$gt' => $start, '$lt' => $end),
        // );

        $time_stamp_search = new \MongoDB\BSON\UTCDateTime(Carbon::now('UTC')->subDays(1));

        //echo json_encode($time_stamp_search);
        
        $where = array(
            'feedtimepost' => ['$gt' => $time_stamp_search],
            //'feedcontent' => ['$regex'=>'PTT', '$options' => 'i'],
            //'sourceid' => 1,
        );
        
        
        $cursor = $collection->find($where, ['sort' => ['feedtimepost' => -1]]);   //This is the main line
        $docs = $cursor->toArray();

        $Site_keywords = Site_keywords::where('status', 1)->where('deleted_at', null)->where('type', 'social')->get();
        foreach($docs as $data){
            $source = DataLeakSocial::select('source', 'tag')->find($data -> sourceid);
            $site_id = '';
            $keyword = '';
            
            foreach($Site_keywords as $item){
                // if (strpos($data -> feedcontent, $item -> name) !== false) {
                //     $keyword .= $item -> name . ',';
                //     $site_id .= $item -> site_id . ',';
                // }
                $pattern = "/\b".$item -> name."\b/";
                if (preg_match($pattern, $data -> feedcontent)) {
                    $site_id .= $item -> site_id . ',';
                    $keyword .= $item -> name . ',';
                    
                    echo $data -> feedcontent;
                    $this->info("LINE");
                    echo $item -> name;
                  
                }else{

                }
            }
            $site_id = rtrim($site_id, ",");
            $keyword = rtrim($keyword, ",");
           
            try {
                if(!empty($keyword)){
                    $DataLeakFeedTemp = DataLeakFeedTemp::where('data_id', $data -> _id)->where('sourceid', $data -> sourceid)
                        ->where('keyword', $keyword)->where('feedlink', $data -> feedlink)->first();
                    if (!$DataLeakFeedTemp){
                        $DataLeakFeedTemp = new DataLeakFeedTemp();
                        $DataLeakFeedTemp -> data_id = $data -> _id;
                        $DataLeakFeedTemp -> sourceid = $data -> sourceid;
                        $DataLeakFeedTemp -> keyword = $keyword;
                        $DataLeakFeedTemp -> source_name = $source -> source;
                        $DataLeakFeedTemp -> tag = $source -> tag;
                        $DataLeakFeedTemp -> feedtimepost = Carbon::now();
                        $DataLeakFeedTemp -> feedcontent = $data -> feedcontent;
                        $DataLeakFeedTemp -> feedtimestamp = Carbon::now();
                        $DataLeakFeedTemp -> feedlink = $data -> feedlink;
                        $DataLeakFeedTemp -> feeduser = $data -> feeduser;
                        $DataLeakFeedTemp -> save();
                    }
                }
                if(!empty($site_id) && !empty($keyword)){
                    $find_leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp -> id)->where('site_id', $site_id)
                    ->where('keyword', $keyword)->first();
                    if (!$find_leak_socail_ref_temp){
                        $leak_socail_ref_temp = new leak_socail_ref_temp();
                        $leak_socail_ref_temp -> data_leak_feed_id = $DataLeakFeedTemp -> id;
                        $leak_socail_ref_temp -> site_id = $site_id;
                        $leak_socail_ref_temp -> keyword = $keyword;
                        $leak_socail_ref_temp -> status = 1;
                        $leak_socail_ref_temp -> save();
                    }
                    echo "SAVE";
                }
            }
            catch (\Exception $ex) {
                echo "ERROR ERROR";
                echo json_encode($ex->getMessage());
            }

        } 
    }
}
