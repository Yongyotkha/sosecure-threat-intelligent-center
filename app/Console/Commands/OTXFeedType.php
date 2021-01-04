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
use App\Entities\OtxIndicatiorType;

class OTXFeedType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:OTXFeedType';

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
        $client = new \GuzzleHttp\Client();
        $bodyData   = $client->request(
            'GET',
            'https://otx.alienvault.com/api/v1/pulses/indicators/types',
            [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-type' => 'application/json',
                    'X-OTX-API-KEY' => $OTX_KEY,
                ],
                'delay' => 200, //millisec == 1sec
                'timeout' => 59, //sec == 100sec
            ]
        )->getBody();
        $otxFeedType = json_decode($bodyData,true);

        foreach ($otxFeedType["detail"] as $value) {
            $checkOTXType = OtxIndicatiorType::where('name',$value["name"]);
            if($checkOTXType->count()<1){
                try {
                    $inputOTXType = new OtxIndicatiorType;
                    $inputOTXType -> code = generator_uuid();
                    $inputOTXType -> slug = $value["slug"];
                    $inputOTXType -> name = $value["name"];
                    $inputOTXType -> description = $value["description"];
                    $inputOTXType -> remark = "system";
                    $inputOTXType -> element_count = 0;
                    $inputOTXType -> status = 1;
                    $inputOTXType -> created_by = "system";
                    $inputOTXType -> updated_by = "system";
                    $inputOTXType->save();
                } catch (Exception $e) {
                    $error["Exception"] = $e;
                } catch (\Throwable $ex) {
                    $error["Throwable"] = $ex;
                }
            }
        }
       
        //$this->info("SUCCESS FULLY");
    }
}
