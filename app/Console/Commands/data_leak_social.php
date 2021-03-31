<?php

namespace App\Console\Commands;

use App\DataLeakFeedTemp;
use App\DataLeakSocial;
use App\Entities\TransactionBatchjob;
use App\leak_socail_ref_temp;
use Carbon\Carbon;
use Illuminate\Console\Command;
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

        $TransactionBatchjob_Update = TransactionBatchjob::where('mode', 'data_leak_social')->first();
        $TransactionBatchjob_Update->progress = 2;
        $TransactionBatchjob_Update->transcation_date_start = date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->transcation_date = date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->save();
        // require 'C://xampp//htdocs//threat-intelligent-center//vendor//autoload.php'; // include Composer's autoloader]
        // $mongo = new \MongoDB\Client('mongodb://10.104.0.10:27017');
        // Manager Class
        // $manager = new \MongoDB\Driver\Manager("mongodb://10.104.0.10:27017");

        // $start = new \MongoDB\BSON\UTCDateTime(strtotime('2020-01-01 00:00:00'));
        // $end = new \MongoDB\BSON\UTCDateTime(strtotime('2020-12-31 23:59:59'));
        $tz = new \DateTimeZone('Asia/Bangkok');
        $start = date("Y-m-d") . ' 00:00:00';
        $end = date("Y-m-d") . ' 23:59:59';
        // $start = '2020-01-01'.' 00:00:00';
        //  $end = date("Y-m-d").' 23:59:59';
        $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($start) * 1000);
        $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($end) * 1000);

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
        $mongo_url = config('app.DB_MONGO_DEV');
        $client = new \MongoDB\Client($mongo_url);
        $db_name = 'social';
        $db = $client->$db_name;
        $collection = $db->Feed;

        // $where = array(
        //     'feedtimestamp' => array('$gt' => $start, '$lt' => $end),
        // );

        $time_stamp_search = new \MongoDB\BSON\UTCDateTime(Carbon::now('UTC')->subDays(1));
        $Site_keywords = Site_keywords::where('status', 1)->where('deleted_at', null)->where('type', 'social')->get();
        foreach ($Site_keywords as $key => $Site_keyword) {

            $where = array(
                //'feedtimepost' => ['$gt' => $time_stamp_search],
                //'feedcontent' => ['$regex'=>'PTT', '$options' => 'i'],
                //'sourceid' => 1,
                // "\$gte"=>$date1,
                // "\$lt"=>$date2
                'feedtimepost' => ['$gte' => $dateStart, '$lt' => $dateEnd],
                'feedcontent' => new \MongoDB\BSON\Regex($Site_keyword->name),
                // 'feedcontent' => new \MongoDB\BSON\Regex('ทำไมแฟนผมเป็นแบบนี้'),
            );

            $cursor = $collection->find($where); //This is the main line
            $docs = $cursor->toArray();

            foreach ($docs as $data) {

                $source = DataLeakSocial::select('source', 'tag')->find($data->sourceid);
                $site_id = '';
                $keyword = '';

                foreach ($Site_keywords as $item) {
                    // if (strpos($data -> feedcontent, $item -> name) !== false) {
                    //     $keyword .= $item -> name . ',';
                    //     $site_id .= $item -> site_id . ',';
                    // }
                    //  echo $item -> name;
                    // $pattern = "/\b".$item -> name."\b/";
                    //   $pattern ='/\b('..$item -> name..'\w+)\b/';
                    //  $this->comment($pattern);
                    // if (preg_match($pattern, $data -> feedcontent)) {
                    if ($Site_keyword->name == $item->name) {
                        $site_id .= $item->site_id . ',';
                        $keyword .= $item->name . ',';

                        echo $data->feedcontent;
                        $this->info("LINE");
                        echo $item->name;

                    } else {

                    }
                }
                $site_id = rtrim($site_id, ",");
                $keyword = rtrim($keyword, ",");
                // print_r($data->feedlink);exit();
                try {
                    if (!empty($keyword)) {
                        $DataLeakFeedTemp = DataLeakFeedTemp::where('feedlink', $data->feedlink)->first();
                        // $DataLeakFeedTemp = DataLeakFeedTemp::all();
                        // var_dump($DataLeakFeedTemp);exit();
                        if (!$DataLeakFeedTemp) {
                            $DataLeakFeedTemp = new DataLeakFeedTemp();
                            $DataLeakFeedTemp->data_id = $data->_id;
                            $DataLeakFeedTemp->sourceid = $data->sourceid;
                            $DataLeakFeedTemp->keyword = $keyword;
                            $DataLeakFeedTemp->source_name = @$source->source;
                            $DataLeakFeedTemp->tag = @$source->tag;
                            $DataLeakFeedTemp->feedtimepost = Carbon::now();
                            $DataLeakFeedTemp->feedcontent = $data->feedcontent;
                            $DataLeakFeedTemp->feedtimestamp = Carbon::now();
                            $DataLeakFeedTemp->feedlink = $data->feedlink;
                            $DataLeakFeedTemp->feeduser = $data->feeduser;
                            $DataLeakFeedTemp->save();
                        }
                    }
                    if (!empty($site_id) && !empty($keyword)) {
                        $find_leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->where('site_id', $site_id)
                            ->where('keyword', $keyword)->first();
                        if (!$find_leak_socail_ref_temp) {
                            $leak_socail_ref_temp = new leak_socail_ref_temp();
                            $leak_socail_ref_temp->data_leak_feed_id = $DataLeakFeedTemp->id;
                            $leak_socail_ref_temp->site_id = $site_id;
                            $leak_socail_ref_temp->keyword = $keyword;
                            $leak_socail_ref_temp->status = 1;
                            $leak_socail_ref_temp->save();
                        }
                        echo "SAVE";
                    }
                } catch (\Exception $ex) {
                    echo "ERROR ERROR";
                    echo json_encode($ex->getMessage());
                }

                try {
                        $datetime_subdays3 = Carbon::now()->subDays(3);
                        $datetime_subdays3 = date("Y-m-d H:i:s",strtotime($datetime_subdays3));
                        $DataLeakFeedTemp = DataLeakFeedTemp::where('created_at', '<', $datetime_subdays3)->delete();
                        if (!$DataLeakFeedTemp) {
   
                        }
                    
                    
                        $find_leak_socail_ref_temp = leak_socail_ref_temp::where('created_at', '<', $datetime_subdays3)->delete();
                        if (!$find_leak_socail_ref_temp) {

                        }
                    
                } catch (\Exception $ex) {
                    echo "ERROR ERROR";
                    echo json_encode($ex->getMessage());
                }

            }
        }
        $TransactionBatchjob_Update = TransactionBatchjob::where('mode', 'data_leak_social')->first();
        $TransactionBatchjob_Update->progress = 1;
        $TransactionBatchjob_Update->transcation_date_end = date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->transcation_date = date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->save();
    }
}
