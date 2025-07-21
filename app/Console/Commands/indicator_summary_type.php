<?php

namespace App\Console\Commands;

use \Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use Artisan;
use App\IndicatorSummaryYear;
use App\Entities\TransactionBatchjob;
use Illuminate\Support\Facades\DB;


class indicator_summary_type extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:indicator_summary_type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FeedMD_To_SQL_Summary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        ini_set('memory_limit', '-1');
        $Transaction = TransactionBatchjob::where('mode', 'indicator_summary_type')->first();
        if (empty($Transaction)) {
            $data = array(
                'name' => 'Indicator Summary Type Update Every Date',
                'mode' => 'indicator_summary_type',
                'status' => 1,
                'progress' => 1,
                'transcation_date_start' => date("Y-m-d H:i:s"),
                'transcation_date' => date("Y-m-d H:i:s"),
                'transcation_date_end' => date("Y-m-d H:i:s"),
            );
            DB::table('transaction_batchjob')->insert($data);
            $Transaction = TransactionBatchjob::where('mode', 'indicator_summary_type')->first();
            print_r("Insert Transaction Log\n");
        }
        
        if ($Transaction->progress == 1) {
            $Transaction->progress = 2;
            $Transaction->transcation_date_start = date("Y-m-d H:i:s");
            $Transaction->transcation_date  = date("Y-m-d H:i:s");
            $Transaction->save();
            print_r("Start progress Transaction Log\n");
            $date_now = Carbon::now();
            $month = $date_now->month;
            $year = $date_now->year;
            $pipeline = [
                [
                    '$group' => [
                        '_id' => [
                            'month' => ['$month' => '$created_at'],
                            'year' => ['$year' => '$created_at'],
                            'type' => '$type',
                        ],
                        'COUNT(*)' => [
                            '$sum' => 1
                        ]
                    ]
                ],
                [
                    '$project' => [
                        'COUNT_Attr' => '$COUNT(*)',
                        'year' => '$_id.year',
                        'month' => '$_id.month',
                        'type' => '$_id.type',
                        '_id' => 0
                    ]
                ],
                [
                    '$sort' => [
                        'year' => -1,
                        'month' => -1,
                    ]
                ],
                [

                    '$match' => [
                        'year' => $year,
                        'month' => $month
                    ]
                ]


            ];

            $options = [
                'allowDiskUse' => TRUE
            ];
            $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;

            $countAttr = $col_fx_otx_indicator_detail->aggregate($pipeline, $options);

            $countAttr = $countAttr->toArray();
            print_r("Count: " . count($countAttr) . "\n");
            foreach ($countAttr as $v) {
                $Indicator_summary = IndicatorSummaryYear::where('month', $v->month)
                    ->where('year', $v->year)
                    ->where('industries_name', $v->type)
                    ->first();
                if (!empty($Indicator_summary)) {
                    $Indicator_summary->month = $v->month;
                    $Indicator_summary->year = $v->year;
                    $Indicator_summary->industries_name = $v->type;
                    $Indicator_summary->attribute_count = $v->COUNT_Attr;
                    $Indicator_summary->save();
                    print_r("Update " . $v->year . " " . $v->month . " " . $v->type . " " . $v->COUNT_Attr . "\n");
                } else {
                    $data = array(
                        'year' => $v->year,
                        'month' => $v->month,
                        'industries_name' => $v->type,
                        'attribute_count' => $v->COUNT_Attr,
                        'status' => 1,
                        'type' => 'attribute_type',
                    );
                    DB::table('indicator_summary_year')->insert($data);
                    print_r($data);
                }
            }

            $Transaction = TransactionBatchjob::where('mode', 'indicator_summary_type')->first();
            $Transaction->progress = 1;
            $Transaction->transcation_date_end = date("Y-m-d H:i:s");
            $Transaction->save();
            print_r("End progress Transaction Log\n");
        }
    }
}
