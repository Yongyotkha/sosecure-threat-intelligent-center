<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Modules\SiteSettings\Entities\SiteSettings;
use MongoDB\BSON\UTCDateTime;
use App\Entities\IndicatorSummaryYear;
use App\Entities\TransactionBatchjob;
class MDAdversaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDAdversaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MDAdersaries';

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
        $TransactionBatchjob_Update = TransactionBatchjob::where('mode','OTX_Adversaries')->first();
        $TransactionBatchjob_Update->progress = 2;
        $TransactionBatchjob_Update->transcation_date_start =date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
        $TransactionBatchjob_Update->save();

        ini_set('memory_limit', '-1');
        $otx_API_Key =  env('otx_API_Key', '');
        $otx_url = "https://otx.alienvault.com/otxapi/adversaries/?limit=20&page=1&sort=value&q=";

        $count =1;
        for ($x = 0; $x <= $count; $x++) {
            $count++;



                        $ch = curl_init();
                        $headers = array(
                            'X-OTX-API-KEY: '.$otx_API_Key,
                            'accept: '.'application/json',
                            'Content-Type: '.'application/x-www-form-urlencoded',
                        );
                        // Send request to Server
                        $ch = curl_init($otx_url);
                        // To save response in a variable from server, set headers;
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        // Get response
                        $response = curl_exec($ch);
                        curl_close($ch);  
                        $response = json_decode($response);
    
                        foreach(@$response->results as $key=>$valus){
                            $this->saveAdversaries($valus);
                                $otx_pulse_url="https://otx.alienvault.com/otxapi/pulses/?page1&limit=20&sort=-modified&q=adversary:".rawurlencode($valus->value);
                                $this->info($otx_pulse_url);
                                $count_pulse =1;
                                for ($x_pulse = 0; $x_pulse <= $count_pulse; $x_pulse++) {
                                         $count_pulse++;

                                        
                                                    $ch = curl_init();
                                                    $headers = array(
                                                        'X-OTX-API-KEY: '.$otx_API_Key,
                                                        'accept: '.'application/json',
                                                        'Content-Type: '.'application/x-www-form-urlencoded',
                                                    );
                                                    // Send request to Server
                                                    $ch = curl_init($otx_pulse_url);
                                                    // To save response in a variable from server, set headers;
                                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                                    // Get response
                                                    $response_pulse = curl_exec($ch);
                                                    curl_close($ch);  
                                                    $response_pulse = json_decode($response_pulse,true);
                                                    $this->info('   Pulse count:'.$response_pulse['count']);
                                                    foreach(@$response_pulse['results'] as $response_pulsekey=>$response_pulse_valus){
                                                        $this->info('    Pulse:'.$response_pulse_valus['name']);
                                                        $this->savePulse($response_pulse_valus,$valus->uuid,$valus->value);

                                                    }
                                                    if($response_pulse['next']){
                                                        $this->info($response_pulse['next']);
                                                        $otx_pulse_url = $response_pulse['next'];
                                                    }else{
                                                        $x_pulse  =  0;
                                                        $count_pulse = 0;
                                                    }
                                    }


                       }
                       if($response->next){
                                  $this->info($response->next);
                                    $otx_url = $response->next;
                       }else{
                        $x  =  0;
                        $count = 0;
                       }


          }
                 
    

          $TransactionBatchjob_Update = TransactionBatchjob::where('mode','OTX_Adversaries')->first();
          $TransactionBatchjob_Update->progress = 1;
          $TransactionBatchjob_Update->transcation_date_end =date("Y-m-d H:i:s");
          $TransactionBatchjob_Update->transcation_date  =date("Y-m-d H:i:s");
          $TransactionBatchjob_Update->save();


    }

    public function saveAdversaries($valueEvent)
    {
        $this->info($valueEvent->value);
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
        $tlpcolor = null;
        $tags = null;
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        $synonyms =[];
        $country = "";
        $uuid = $valueEvent->uuid;
        $value =$valueEvent->value;
        $description =@$valueEvent->description;
        if(!empty($valueEvent->meta)){
            $synonymss = [];
            if(@$valueEvent->meta->synonyms){
               $synonymss =  @$valueEvent->meta->synonyms;
            }
            foreach($synonymss as $key =>$value){
                    array_push($synonyms,$value);
            }
             @$country = $valueEvent->meta->country;
        }
        $synonyms_str = implode (",", $synonyms);
        $update_fx_otx_events = $col_fx_otx_events->updateOne(
            ['adversary_uuid' => $uuid],
            ['$set' => [
                'name' => $value,
                'description' => $description,
                'synonyms' =>  @$synonyms_str,
                'country' =>  $country,
                'updated_at' => $date_now ,
                'updated_by' => "system",
            ],
                '$setOnInsert' => [
                   'created_by' => "system",
                   'created_at' => $date_now ,
                ],
            ],
            ['upsert' => true]
        );
        return 0;
    }


    public function savePulse($value,$uuid,$adversary_name)
    { 
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $checkSuccess = true;
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
        $update_fx_otx_adversaries_related = $col_fx_otx_adversaries_related->updateOne(
            ['adversary_uuid' => $uuid,'pulse_id' => $value["id"]],
            ['$set' => [
                'updated_at' => $date_now ,
                'updated_by' => "system",
                'adversary_name' => $adversary_name,
                'pulse_name' => isset($value["name"])?$value["name"]:"",
            ],
                '$setOnInsert' => [
                   'created_by' => "system",
                   'created_at' => $date_now ,
                ],
            ],
            ['upsert' => true]
        );



                $pipeLine = array('pulse_id' => ['$regex'=>$value["id"], '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_events->count($pipeLine);
                $this->info('         Pulse Have Count :'.$dataWait['count'].'-------------------------------------------');
                if($dataWait['count'] == 0){
                                    try{

                                        $references = implode(', ' , isset($value["references"])?$value["references"]:[]);
                                        $tags = implode(', ' , isset($value["tags"])?$value["tags"]:[]);
                                        $industries = implode(', ' , isset($value["industries"])?$value["industries"]:[]);
                                        $malware_families = implode(', ', array_column(isset($value["malware_families"])?$value["malware_families"]:[] , 'display_name'));
                                        
                                        $update_fx_otx_events = $col_fx_otx_events->updateOne(
                                            ['pulse_id' => isset($value["id"])?$value["id"]:""],
                                            ['$set' => [
                                                'name' => isset($value["name"])?$value["name"]:"",
                                                'description' => isset($value["description"])?$value["description"]:"",
                                                'modified' => isset($value["modified"])?$value["modified"]:"",
                                                'created' => isset($value["created"])?$value["created"]:"",
                                                'public' => isset($value["public"])?$value["public"]:"",
                                                'TLP' => isset($value["TLP"])?$value["TLP"]:"",
                                                'indicator_count' => isset($value["indicator_count"])?$value["indicator_count"]:"",
                                                'is_modified' => isset($value["is_modified"])?$value["is_modified"]:"",
                                                'indicator_type_counts' => isset($value["indicator_type_counts"])?$value["indicator_type_counts"]:"",
                                                'references' => isset($references)?$references:"",
                                                'tags' => isset($tags)?$tags:"",
                                                'groups' => isset($groups)?$groups:"",
                                                'industries' => isset($industries)?$industries:"",
                                                'malware_families' => isset($malware_families)?$malware_families:"",
                                                'author_username' => isset($value["author"]["username"])?$value["author"]["username"]:"",
                                                'updated_at' => date("Y-m-d H:i:s"),
                                                'updated_by' => "system",
                                            ],
                                            '$setOnInsert' => [
                                                'transcation_id' => null,
                                                'status' => 1,
                                                'created_at' => date("Y-m-d H:i:s"),
                                                'created_by' => "system",
                                                'deleted_at' => null,
                                                'transaction_date' => date("Y-m-d"),
                                            ],
                                            ],
                                            ['upsert' => true]
                                        );

                                    } catch (Exception $e) {
                                        $checkSuccess = false;
                                    }
                 }

        

    }


}
