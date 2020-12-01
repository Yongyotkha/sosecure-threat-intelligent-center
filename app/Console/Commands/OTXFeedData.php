<?php

namespace App\Console\Commands;

use Exception;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Entities\OtxIndicatiorStamp;
use App\Entities\OtxIndicatiorData;


class OTXFeedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:OTXFeedData';

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
        $OTX_KEY = env("OTX_KEY","");

        $inputOTXStamp = new OtxIndicatiorStamp;
        $inputOTXStamp -> code = generator_uuid();
        $inputOTXStamp -> transaction_date = date("Y-m-d H:i:s");
        $inputOTXStamp -> status = 1;
        $inputOTXStamp -> created_by = "system";
        $inputOTXStamp -> updated_by = "system";
        $inputOTXStamp->save();

        $client = new \GuzzleHttp\Client();
        $otxModifiedDate =  gmdate("c", time() - 60 * 60 * 24 *2) ;
        $bodyData   = $client->request(
            'GET',
            'https://otx.alienvault.com/api/v1/indicators/export?modified_since='.$otxModifiedDate,
            [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-type' => 'application/json',
                    'X-OTX-API-KEY' => $OTX_KEY,
                ]]
        )->getBody();
        $otxFeedData = json_decode($bodyData,true);
        $otxFeedDataCheck = true;

        while ($otxFeedDataCheck) {

            foreach ($otxFeedData["results"] as $value) {
                try {
                    echo json_encode($value["id"]);
                    $inputOTXData = OtxIndicatiorData::updateOrCreate(
                        [
                            'id' => $value["id"]
                        ],
                        [
                            'created_by' => "system",
                            'indicatior' => $value["indicator"],
                            'type' => $value["type"],
                            'tile' => $value["title"],
                            'desciption' => $value["description"],
                            'content' => $value["content"],
                            'status' => 1,
                            'updated_by' => "system",
                            'transaction_date' => $inputOTXStamp->transaction_date,
                            'transcation_id' => $inputOTXStamp->id,
                        ]
                    );
                } catch (Exception $e) {
                    $error["Exception"] = $e;
                } catch (\Throwable $ex) {
                    $error["Throwable"] = $ex;
                }
            }

            if(isset($otxFeedData["next"])){
                $bodyData   = $client->request(
                    'GET',
                    $otxFeedData["next"],
                    [
                        'headers' => [
                            'Accept'       => 'application/json',
                            'Content-type' => 'application/json',
                            'X-OTX-API-KEY' => $OTX_KEY,
                        ]]
                )->getBody();
                $otxFeedData = json_decode($bodyData,true);
                $otxFeedDataCheck = true;
            }else{
                $otxFeedDataCheck = false;
            }
            
        }
        $this->info("SUCCESS FULLY");
    }
}
