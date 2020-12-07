<?php

namespace App\Console\Commands;

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
        $manager = new \MongoDB\Driver\Manager("mongodb://10.104.0.10:27017");


        $start = new \MongoDB\BSON\UTCDateTime(strtotime('2020-01-01 00:00:00'));
         $end = new \MongoDB\BSON\UTCDateTime(strtotime('2020-12-31 23:59:59'));
        // Query Class
        $query = new \MongoDB\Driver\Query(array('feedtimestamp' => array('$gt' => $start, '$lte' => $end)));

        // Output of the executeQuery will be object of MongoDB\Driver\Cursor class
        $cursor = $manager->executeQuery('social.Feed', $query);

        // Convert cursor to Array and print result
        print_r($cursor->toArray());
       echo $start;
    }
}
