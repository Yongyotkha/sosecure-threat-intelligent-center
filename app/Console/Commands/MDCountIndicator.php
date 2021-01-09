<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Modules\SiteSettings\Entities\SiteSettings;
use MongoDB\BSON\UTCDateTime;
use App\Entities\IndicatorSummaryYear;

class MDCountIndicator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDCountIndicator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DarkWeb Feed';

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
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $options = [
            'allowDiskUse' => TRUE
        ];
        $pipeline = [
            [
                '$group' => [
                    '_id' => [
                        //'created_by' => '$created_by',
                        // 'created_at'=>['$month'=>'$entryTime','$year'=>'$entryTime']
                        'month'=>['$month'=>'$created_at'],
                        'year'=>['$year'=>'$created_at']
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
                    'type' => 'Attr',
                    '_id' => 0
                ]
            ],
            [
                '$sort' => [
                    'year' =>-1,
                    'month' => -1,
                ]
            ]
        ];
        $pipeline2 = [
            [
                '$group' => [
                    '_id' => [
                        'month'=>['$month'=>'$created_at'],
                        'year'=>['$year'=>'$created_at']
                    ],
                    'COUNT(*)' => [
                        '$sum' => 1
                    ]
                ]
            ],
            [
                '$project' => [
                    'COUNT_Event' => '$COUNT(*)',
                    'year' => '$_id.year',
                    'month' => '$_id.month',
                    'type' => 'Event',
                    '_id' => 0
                ]
            ],
            [
                '$sort' => [
                    'year' =>-1,
                    'month' => -1,
                ]
            ]
        ];
        
        $countAttr = $col_fx_otx_indicator_detail->aggregate($pipeline, $options);
        $countAttr = $countAttr->toArray();

        $countEvents = $col_fx_otx_events->aggregate($pipeline2, $options);
        $countEvents = $countEvents->toArray();
        
        foreach ( $countAttr as $value) {
            $IndicatorSummaryYear = IndicatorSummaryYear::where('year', $value["year"])
            ->where('month',  $value["month"])->first();
            if(!$IndicatorSummaryYear){
                $IndicatorSummaryYear = new IndicatorSummaryYear;
                $IndicatorSummaryYear->year = $value["year"];
                $IndicatorSummaryYear->month = $value["month"];
                $IndicatorSummaryYear->attribute_count =  $value["COUNT_Attr"];
                $IndicatorSummaryYear->event_count =  0;
                $IndicatorSummaryYear->status = 1;
                $IndicatorSummaryYear->save();

            }else{
                $IndicatorSummaryYear->attribute_count =  $value["COUNT_Attr"];
                $IndicatorSummaryYear->save();

            }
        }

        foreach ( $countEvents as $value) {
            $IndicatorSummaryYear = IndicatorSummaryYear::where('year', $value["year"])
            ->where('month',  $value["month"])->first();
            if(!$IndicatorSummaryYear){
                $IndicatorSummaryYear = new IndicatorSummaryYear;
                $IndicatorSummaryYear->year = $value["year"];
                $IndicatorSummaryYear->month = $value["month"];
                $IndicatorSummaryYear->attribute_count = 0;
                $IndicatorSummaryYear->event_count =  $value["COUNT_Event"];
                $IndicatorSummaryYear->status = 1;
                $IndicatorSummaryYear->save();

            }else{
                $IndicatorSummaryYear->event_count =  $value["COUNT_Event"];
                $IndicatorSummaryYear->save();

            }
        }
        print_r($countEvents);
        $this->info('END------------------------------------------------------------END');
    }



}
