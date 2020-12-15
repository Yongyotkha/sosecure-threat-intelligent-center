<?php

namespace App\Console\Commands;
use MongoDB\BSON\UTCDateTime;
use Exception;
use Illuminate\Console\Command;

class OTXMDFeedType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:OTXMDFeedType';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        
        try {
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
            $OTX_KEY = env("OTX_KEY", "");
            $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $clientHttp = new \GuzzleHttp\Client();
            $bodyData = $clientHttp->request(
                'GET',
                'https://otx.alienvault.com/api/v1/pulses/indicators/types',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-type' => 'application/json',
                        'X-OTX-API-KEY' => $OTX_KEY,
                    ]
                ]
            )->getBody();
            $otxFeedType = json_decode($bodyData, true);

            $collectionStamp = $clientMD->sosecure_threatintelligent->fx_otx_type_stamp;
            $insertOneResult = $collectionStamp->insertOne([
                'code' => generator_uuid(),
                'transaction_date' => date("Y-m-d"),
                'status' => 1,
                'created_at' => $date_now,
                'created_by' => "system",
                'updated_at' => $date_now,
                'updated_by' => "system",
                'deleted_at' => null,
            ]);
        } catch (Exception $e) {
            $error["Exception"] = $e;
        }

        if(!isset($error["Exception"])){
                $collection = $clientMD->sosecure_threatintelligent->fx_otx_type;
                foreach ($otxFeedType["detail"] as $value) {
                    try {
                        $updateResult = $collection->updateOne(
                            ['name' => $value["name"]],
                            ['$set' => [
                                'updated_at' => $date_now,
                                'updated_by' => "system",
                                'slug' => $value["slug"],
                                'description' => $value["description"],
                                'transcation_id' => $insertOneResult->getInsertedId(),
                            ],
                                '$setOnInsert' => [
                                    'code' => generator_uuid(),
                                    'remark' => "system",
                                    'element_count' => 0,
                                    'status' => 1,
                                    'created_at' => $date_now,
                                    'created_by' => "system",
                                    'deleted_at' => null,
                                ],
                            ],
                            ['upsert' => true]
                        );
                    } catch (Exception $e) {
                        $error["Exception"] = $e;
                    }
                }
        }

        if(isset($error["Exception"])){
            $this->info("OTX FEED TYPE ERROR SOME CONTENT");
        }else{
            $updateResult2 = $collectionStamp->updateOne(
                ['_id' => $insertOneResult->getInsertedId()],
                ['$set' => ['status' => 2]]
            );
            $this->info("OTX FEED TYPE SUCCESS");
        }
       
    }
}
