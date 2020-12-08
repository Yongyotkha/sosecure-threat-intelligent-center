<?php

namespace App\Console\Commands;

use App\DataLeakFeedTemp;
use App\DataLeakSocial;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;

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
        $where = array(
            'sourceid' => 1,
        );
        $cursor = $collection->find([], ['sort' => ['feedtimepost' => -1], 'limit' => 1000]);   //This is the main line
        $docs = $cursor->toArray();
        foreach($docs as $data){
            $source = DataLeakSocial::select('source')->find($data -> sourceid);
            $DataLeakFeedTemp = new DataLeakFeedTemp();
            $DataLeakFeedTemp -> data_id = $data -> _id;
            $DataLeakFeedTemp -> sourceid = $data -> sourceid;
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
}
