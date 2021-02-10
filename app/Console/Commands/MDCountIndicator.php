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

        $date_sub1 = 1;
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
            $IndicatorSummaryYear->type =  'summary_current';
            $IndicatorSummaryYear->save();
        }else{
            $IndicatorSummaryYear->attribute_count = $Attr_count_current;
            $IndicatorSummaryYear->event_count =  $Event_count_current;
            $IndicatorSummaryYear->save();

        }



        $query = array(
            
        );

        $query2 = array( 
            
        );

        $Attr_count_all = (int)$col_fx_otx_indicator_detail->count($query);
        $Event_count_all = (int)$col_fx_otx_events->count($query2);
        $IndicatorSummaryYear = IndicatorSummaryYear::where('type',  'summary_all')->first();
        if(!$IndicatorSummaryYear){
            $IndicatorSummaryYear = new IndicatorSummaryYear;
            $IndicatorSummaryYear->attribute_count = $Attr_count_all;
            $IndicatorSummaryYear->event_count =  $Event_count_all;
            $IndicatorSummaryYear->type =  'summary_all';
            $IndicatorSummaryYear->status = 1;
            $IndicatorSummaryYear->save();
        }else{
            $IndicatorSummaryYear->attribute_count = $Attr_count_all;
            $IndicatorSummaryYear->event_count =  $Event_count_all;
            $IndicatorSummaryYear->save();

        }
        

        $month_sub1 = 1;
        $date_now_sub1 = new UTCDateTime( Carbon::now('UTC')->subMonths($month_sub1)->firstOfMonth());
        $pipeline = [
            [
                '$match' => [
                    'created_at'  => ['$gt' =>  $date_now_sub1],
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'year'=>['$month'=>'$created_at'],
                        'month' => ['$dayOfMonth'=>'$created_at'],
                        
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
                '$match' => [
                    'created_at'  => ['$gt' =>  $date_now_sub1],
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'year'=>['$month'=>'$created_at'],
                        'month'=>['$dayOfMonth'=>'$created_at'],
                        
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
        
        $updateall = IndicatorSummaryYear::where('type',  'summary_month')->update(['event_count' => 0,
        'attribute_count' => 0
        ]);

        foreach ( $countAttr as $value) {
            $IndicatorSummaryYear = IndicatorSummaryYear::where('year', $value["year"]==(int)date('m')?2:1)
            ->where('month',  $value["month"])->where('type',  'summary_month')->first();
            if(!$IndicatorSummaryYear){
                $IndicatorSummaryYear = new IndicatorSummaryYear;
                $IndicatorSummaryYear->year = $value["year"]==(int)date('m')?2:1;
                $IndicatorSummaryYear->month = $value["month"];
                $IndicatorSummaryYear->attribute_count =  $value["COUNT_Attr"];
                $IndicatorSummaryYear->event_count =  0;
                $IndicatorSummaryYear->type =  'summary_month';
                $IndicatorSummaryYear->status = 1;
                $IndicatorSummaryYear->save();

            }else{
                $IndicatorSummaryYear->attribute_count =  $value["COUNT_Attr"];
                $IndicatorSummaryYear->save();

            }
        }

        foreach ( $countEvents as $value) {
            $IndicatorSummaryYear = IndicatorSummaryYear::where('year', $value["year"]==(int)date('m')?2:1)
                ->where('month',  $value["month"])->where('type',  'summary_month')->first();
            if(!$IndicatorSummaryYear){
                $IndicatorSummaryYear = new IndicatorSummaryYear;
                $IndicatorSummaryYear->year = $value["year"]==(int)date('m')?2:1;
                $IndicatorSummaryYear->month = $value["month"];
                $IndicatorSummaryYear->attribute_count = 0;
                $IndicatorSummaryYear->event_count =  $value["COUNT_Event"];
                $IndicatorSummaryYear->type =  'summary_month';
                $IndicatorSummaryYear->status = 1;
                $IndicatorSummaryYear->save();
            }else{
                $IndicatorSummaryYear->event_count =  $value["COUNT_Event"];
                $IndicatorSummaryYear->save();

            }
        }


        if(false){
            //backup code
            // join type
            $options = [
                'allowDiskUse' => TRUE
            ];
    
            $pipeline = [
                [
                    '$match' => [
                        'pulse_id'  => 'misp_1020',
                    ]
                ]
                ,
                [
                    '$lookup' => [
                        'localField' => 'indicator_id',
                        'from' => 'fx_otx_indicator_detail',
                        'foreignField' => 'indicator_id',
                        'as' => 'b'
                    ]
                ]
                ,
                [
                    '$group' => [
                        '_id' => [
                            'type' => '$b.type'
                        ],
                        'COUNT(*)' => [
                            '$sum' => 1
                        ]
                    ]
                ],
                [
                    '$project' => [
                        'COUNT_AttrType' => '$COUNT(*)',
                        'thisType' => '$_id.type',
                        '_id' => 0,
                    ]
                ]
                ,
            ];
            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
            $cursor = $col_fx_otx_events_indicator_ref->aggregate($pipeline, $options)->toArray();
        }
        
        if(false){
            //backup code
            $pulseID = '5ff56a70c101904b55634d56'."";
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;

            $findOne_col_fx_otx_events = $col_fx_otx_events->findOne(array('pulse_id' => $pulseID));
            $query2 = [
                '$and' =>   
                    [
                        ['pulse_id' => $pulseID],
                        ['is_count_attr' => ['$exists' => true]],
                    ]
            ];
            $find_col_fx_otx_events_indicator_ref_2 = $col_fx_otx_events_indicator_ref->count($query2);

            if($find_col_fx_otx_events_indicator_ref_2==$findOne_col_fx_otx_events["indicator_count"]){
                //count corrrect
                $query = [
                    '$and' =>   
                        [
                            ['pulse_id' => $pulseID],
                            ['is_count_attr' => ['$exists' => false]],
                        ]
                ];
                $find_col_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->find($query)->toArray();
                $countAttrArray = $findOne_col_fx_otx_events["indicator_type_counts"];
                $countAttrAll = $findOne_col_fx_otx_events["indicator_count"];
                foreach ($find_col_fx_otx_events_indicator_ref as $key => $value) {
                    $findOne_col_fx_otx_indicator_detail = $col_fx_otx_indicator_detail->findOne(array('indicator_id' => $value["indicator_id"]));
                    $countAttrAll++;
                    if(!empty($findOne_col_fx_otx_indicator_detail)){
                        if (isset($countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]])) {
                            $countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]] = $countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]] + 1;
                        } else {
                            $countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]] = 1;
                        }
                    }
                }

                $updateResult_col_fx_otx_events = $col_fx_otx_events->updateOne(
                    ['pulse_id' => $pulseID],
                    ['$set' => 
                        [
                            'indicator_count' =>  $countAttrAll,
                            'indicator_type_counts' => $countAttrArray,
                        ],
                    ]
                );

                $col_fx_otx_events_indicator_ref->updateMany(
                    $query,
                    array('$set' => array("is_count_attr" => 1))
                );
            }else{
                //count false
                $query = [
                    'pulse_id' => $pulseID
                ];
                $find_col_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->find($query)->toArray();
                $countAttrArray = array();
                $countAttrAll = 0;
                foreach ($find_col_fx_otx_events_indicator_ref as $key => $value) {
                    $findOne_col_fx_otx_indicator_detail = $col_fx_otx_indicator_detail->findOne(array('indicator_id' => $value["indicator_id"]));
                    $countAttrAll++;
                    if(!empty($findOne_col_fx_otx_indicator_detail)){
                        // echo $findOne_col_fx_otx_indicator_detail["type"];
                        if (isset($countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]])) {
                            $countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]] = $countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]] + 1;
                        } else {
                            $countAttrArray[$findOne_col_fx_otx_indicator_detail["type"]] = 1;
                        }
                    }
                }

                $updateResult_col_fx_otx_events = $col_fx_otx_events->updateOne(
                    ['pulse_id' => $pulseID],
                    ['$set' => 
                        [
                            'indicator_count' =>  $countAttrAll,
                            'indicator_type_counts' => $countAttrArray,
                        ],
                    ]
                );

                $col_fx_otx_events_indicator_ref->updateMany(
                    $query,
                    array('$set' => array("is_count_attr" => 1))
                );

            }
        }


        $this->info('END------------------------------------------------------------END');
    }



}
