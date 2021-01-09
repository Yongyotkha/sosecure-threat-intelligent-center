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
            ->where('month',  $value["month"])->where('type',  'summary_year')->first();
            if(!$IndicatorSummaryYear){
                $IndicatorSummaryYear = new IndicatorSummaryYear;
                $IndicatorSummaryYear->year = $value["year"];
                $IndicatorSummaryYear->month = $value["month"];
                $IndicatorSummaryYear->attribute_count =  $value["COUNT_Attr"];
                $IndicatorSummaryYear->event_count =  0;
                $IndicatorSummaryYear->type =  'summary_year';
                $IndicatorSummaryYear->status = 1;
                $IndicatorSummaryYear->save();

            }else{
                $IndicatorSummaryYear->attribute_count =  $value["COUNT_Attr"];
               
                $IndicatorSummaryYear->save();

            }
        }

        foreach ( $countEvents as $value) {
            $IndicatorSummaryYear = IndicatorSummaryYear::where('year', $value["year"])
            ->where('month',  $value["month"])->where('type',  'summary_year')->first();
            if(!$IndicatorSummaryYear){
                $IndicatorSummaryYear = new IndicatorSummaryYear;
                $IndicatorSummaryYear->year = $value["year"];
                $IndicatorSummaryYear->month = $value["month"];
                $IndicatorSummaryYear->attribute_count = 0;
                $IndicatorSummaryYear->event_count =  $value["COUNT_Event"];
                $IndicatorSummaryYear->type =  'summary_year';
                $IndicatorSummaryYear->status = 1;
                $IndicatorSummaryYear->save();

            }else{
                $IndicatorSummaryYear->event_count =  $value["COUNT_Event"];
                
                $IndicatorSummaryYear->save();

            }
        }
  

        $pipeline3 = [
            [
                '$group' => [
                    '_id' => [
                        'type' => '$type'
                    ],
                    'COUNT(*)' => [
                        '$sum' => 1
                    ]
                ]
            ],
            [
                '$project' => [
                    'COUNT_AttrType' => '$COUNT(*)',
                    'type' => 'AttrType',
                    'thisType' => '$_id.type',
                    '_id' => 0
                ]
            ]
        ];

        $countTypeAttr = $col_fx_otx_indicator_detail->aggregate($pipeline3, $options);
        $countTypeAttr = $countTypeAttr->toArray();
        foreach ( $countTypeAttr as $value) {
            $IndicatorSummaryYear = IndicatorSummaryYear::where('type_name', $value["thisType"])
            ->where('type',  'summary_attr_type')->first();
            if(!$IndicatorSummaryYear){
                $IndicatorSummaryYear = new IndicatorSummaryYear;
                $IndicatorSummaryYear->attribute_count = $value["COUNT_AttrType"];
                $IndicatorSummaryYear->type =  'summary_attr_type';
                $IndicatorSummaryYear->type_name = $value["thisType"];
                $IndicatorSummaryYear->status = 1;
                $IndicatorSummaryYear->save();
            }else{
                $IndicatorSummaryYear->attribute_count = $value["COUNT_AttrType"];
                $IndicatorSummaryYear->save();

            }
        }

        // $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        $date_sub1 = 4;
        $date_now_sub1 = new UTCDateTime( Carbon::now('UTC')->subDays($date_sub1));
        $query = array(
            'created_at' => ['$gt' =>  $date_now_sub1],
        );

        $query2 = array( 
            'created_at' => ['$gt' =>  $date_now_sub1],
        );
        $Attr_count_current = (int)$col_fx_otx_indicator_detail->count($query);
        $Event_count_current = (int)$col_fx_otx_events->count($query2);
        $IndicatorSummaryYear = IndicatorSummaryYear::where('type',  'summary_current')->first();
        if(!$IndicatorSummaryYear){
            $IndicatorSummaryYear = new IndicatorSummaryYear;
            $IndicatorSummaryYear->attribute_count = $Attr_count_current;
            $IndicatorSummaryYear->event_count =  $Event_count_current;
            $IndicatorSummaryYear->status = 1;
            $IndicatorSummaryYear->save();
        }else{
            $IndicatorSummaryYear->attribute_count = $Attr_count_current;
            $IndicatorSummaryYear->event_count =  $Event_count_current;
            $IndicatorSummaryYear->save();

        }
        $this->info('END------------------------------------------------------------END');
    }



}
