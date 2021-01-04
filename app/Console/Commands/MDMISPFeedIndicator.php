<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;

class MDMISPFeedIndicator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDMISPFeedIndicator';

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
        ini_set('memory_limit', '-1');
        echo  app_path()."5555555555555";
        $output_filename_json = app_path()."/Console/Commands/temp/misp.json.ADMIN.json";
        $strJsonFileContents = file_get_contents($output_filename_json) or die("Error: Cannot create object");
        $json_o = json_decode($strJsonFileContents, true);

        
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_otx_event_stamp = $clientMD->sosecure_threatintelligent->fx_transaction_otx_event_stamp;
        $ins_fx_transaction_otx_event_stamp = $col_fx_transaction_otx_event_stamp->insertOne([
            'code' => generator_uuid(),
            'transaction_date' => date("Y-m-d"),
            'status' => 1,
            'created_at' => $date_now,
            'created_by' => "system",
            'updated_at' => $date_now ,
            'updated_by' => "system",
            'deleted_at' => null,
            'source' => "misp",
        ]);
        $col_fx_transaction_otx_indicator_stamp = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicator_stamp;
        $ins_fx_transaction_otx_indicator_stamp = $col_fx_transaction_otx_indicator_stamp->insertOne([
            'code' => generator_uuid(),
            'transaction_date' => date("Y-m-d"),
            'status' => 1,
            'created_at' => $date_now,
            'created_by' => "system",
            'updated_at' => $date_now ,
            'updated_by' => "system",
            'deleted_at' => null,
            'source' => "misp",
        ]);
        $stamp_event_id = $ins_fx_transaction_otx_event_stamp->getInsertedId();
        $stamp_indicator_id = $ins_fx_transaction_otx_indicator_stamp->getInsertedId();
        $this->saveJson($json_o,$stamp_event_id,$stamp_indicator_id);
        //echo json_encode($json_o["response"][0]["Event"]);
    }

    public function saveJson($json_o)
    {
        if(!empty($json_o["response"])){
            foreach ($json_o["response"] as $key => $valueEvent) {
                $this->$saveEvent($valueEvent["Event"]);
                $this->$saveRelatedEvent($valueEvent["Event"]);
                $this->$saveRelatedIndicator($valueEvent["Event"]);
            }
        }
    } 

    public function saveEvent($json_o)
    {
        if(!empty($json_o["response"])){
            foreach ($json_o["response"] as $key => $valueEvent) {
                
            }
        }
    }

    public function saveRelatedEvent($json_o)
    {
        if(!empty($json_o["response"])){
            foreach ($json_o["response"] as $key => $valueEvent) {
                
            }
        }
    }

    public function saveRelatedIndicator($json_o)
    {
        if(!empty($json_o["response"])){
            foreach ($json_o["response"] as $key => $valueEvent) {
                
            }
        }
    }
}
