<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Api\ApiController;
use Exception;
use Illuminate\Http\Request;

use App\Entities\CompromisedFileCheck;
use Modules\SiteSettings\Entities\SiteSettings;
use App\Entities\TF_Center_transaction_batchjob;

use App\Entities\TF_Center_data_leak_feed_temp;
use App\Entities\TF_Center_data_leak_socail_ref_temp;
use App\Entities\TF_Center_data_datacve_mapping;
use App\Entities\TF_Center_data_leak_feed;
use App\Entities\TF_Center_data_leak_socail_ref;


class ApiTransferCenterInsert extends Controller
{
    private $ip = '127.0.0.1';
    private $mac = 'abcd';
    private $header = 'header';
    private $dbName = 'mysql';

    protected function insertToRef(Request $request)
    {
        $ip = $this->ip;
        $mac = $this->mac;
        $header = $this->header;
        $code = $request->site_code_en;
        $dataEncode = $code;
        $dataDecode = encrypt_decrypt('decrypt', $dataEncode, $header, $ip, $mac);
        $messageErr = '';
        $connect = true;
        $result = true;
        $arrUpdate = array();
        if ($dataDecode) {
            try {
                $site = SiteSettings::where('code', $dataDecode)->first();
                if ($site) {
                    $nameTable = $request->tbName;
                    $dataTables = $request->queryData;

                    if ($nameTable == 'fx_transaction_center_data_leak_feed_temp') {
                        $pkey_main = 'id';
                        $pkey_sub = 'id';
                        $refkey_sub = 'data_leak_feed_id';
                        $model_main = new TF_Center_data_leak_feed_temp;
                        $model_sub = new TF_Center_data_leak_socail_ref_temp;
                    } else if ($nameTable == 'fx_transaction_center_data_leak_feed') {
                        $pkey_main = 'id';
                        $pkey_sub = 'id';
                        $refkey_sub = 'data_leak_feed_id';
                        $model_main = new TF_Center_data_leak_feed;
                        $model_sub = new TF_Center_data_leak_socail_ref;
                    } else {
                        $connect = false;
                        $result = false;
                    }

                    if ($result == true) {
                        foreach ($dataTables as $dataTable) {

                            // $transfer_data_id = $dataTable["get_transfer"]["id"];
                            if(!empty($dataTable["transaction_id"])||!empty($dataTable["transaction_id_ref"])){

                                $findOne_main = new $model_main;
                               
                                $findOne_main->setConnection($this->dbName);
                                

                                $findOne_main = $findOne_main->where('transfer_site_id', $site->id)->where('transfer_data_id', $dataTable["transaction_id"])->first();
                                if (isset($dataTable["get_transfer"][$pkey_main]) && ($dataTable["transaction_mode"] == 'insert' || $dataTable["transaction_mode"] == 'update')) {
                                    if (empty($findOne_main)) {
                                        $findOne_main = new $model_main;
                                        $findOne_main->setConnection($this->dbName);
                                        $findOne_main->transfer_site_id = $site->id;
                                        $findOne_main->transfer_data_id = $dataTable["get_transfer"][$pkey_main];
                                        foreach ($dataTable["get_transfer"] as $key => $subValue) {
                                            if ($key != $pkey_main&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
                                                if($subValue==''){
                                                    $subValue=null;
                                                }
                                                $findOne_main->{$key} = $subValue;
                                            }
    
                                        }
    
                                        $findOne_main->save();
    
                                    } else {
                                        $findOne_main->transfer_site_id = $site->id;
                                        $findOne_main->transfer_data_id = $dataTable["get_transfer"][$pkey_main];
                                        foreach ($dataTable["get_transfer"] as $key => $subValue) {
                                            if ($key != $pkey_main&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
                                                if($subValue==''){
                                                    $subValue=null;
                                                }
                                                $findOne_main->{$key} = $subValue;
                                            }
    
                                        }
                                        $findOne_main->save();
                                    }

                                    
                                } else if ($dataTable["transaction_mode"] == 'delete') {
                                    if (!empty($dataTable["transaction_id"])&&!empty($findOne_main)) {
                                        $findOne_main->delete();
                                    }

                                    if (!empty($dataTable["transaction_id_ref"])) {
                                        $findOne_sub = new $model_sub;
                                        $findOne_sub->setConnection($this->dbName);
                                        $findOne_sub = $findOne_sub->where('transfer_site_id', $site->id)->where('transfer_data_id', $dataTable["transaction_id_ref"])->first();
                                        if (!empty($findOne_sub)) {
                                            $findOne_sub->delete();
                                        }
                                    }
                                    
                                }

                                if(!empty($findOne_main) && ($dataTable["transaction_mode"] == 'insert' || $dataTable["transaction_mode"] == 'update')){
                                    $findOne_sub = new $model_sub;
                                    $findOne_sub->setConnection($this->dbName);
                                    if (isset($dataTable["get_transfer_ref"][$pkey_sub])) {
                                        $findOne_sub = $findOne_sub->where($refkey_sub, $findOne_main->{$pkey_main})->where('transfer_site_id', $site->id)->where('transfer_data_id', $dataTable["transaction_id_ref"])->first();
                                        if (empty($findOne_sub)) {
                                            $findOne_sub = new $model_sub;
                                            $findOne_sub->setConnection($this->dbName);
                                            $findOne_sub->transfer_site_id = $site->id;
                                            $findOne_sub->transfer_data_id = $dataTable["get_transfer_ref"][$pkey_sub];
                                            $findOne_sub->{$refkey_sub} = $findOne_main->{$pkey_main};
                                            foreach ($dataTable["get_transfer_ref"] as $key => $subValue) {
                                                if ($key != $refkey_sub&&$key != $pkey_sub&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
                                                    if($subValue==''){
                                                        $subValue=null;
                                                    }
                                                    $findOne_sub->{$key} = $subValue;
                                                }
        
                                            }
        
                                            $findOne_sub->save();
        
                                        } else {
                                            $findOne_sub->transfer_site_id = $site->id;
                                            $findOne_sub->transfer_data_id = $dataTable["get_transfer_ref"][$pkey_sub];
                                            $findOne_sub->{$refkey_sub} = $findOne_main->{$pkey_main};
                                            foreach ($dataTable["get_transfer_ref"] as $key => $subValue) {
                                                if ($key != $refkey_sub&&$key != $pkey_sub&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
                                                    if($subValue==''){
                                                        $subValue=null;
                                                    }
                                                    $findOne_sub->{$key} = $subValue;
                                                }
        
                                            }
                                            $findOne_sub->save();
                                        }

                                    }
                                }

                                $arrUpdate[] = $dataTable["id"];
                            }
                            
                        }
                    }
                } else {
                    $connect = false;
                    $result = false;
                }
            } catch (Exception $e) {
                $connect = false;
                $result = false;
                $messageErr = "line : ".$e->getLine()." Error :".$e->getMessage();
            }

        } else {
            $connect = false;
            $result = false;
        }

        $dataout = [
            'connect' => $connect,
            'result' => $result,
            'returnUpdate' => $arrUpdate,
            'messageErr' => $messageErr,
        ];
        return response()->json($dataout);
    }

    protected function insertToNoRef(Request $request)
    {
        $ip = $this->ip;
        $mac = $this->mac;
        $header = $this->header;
        $code = $request->site_code_en;
        $dataEncode = $code;
        $dataDecode = encrypt_decrypt('decrypt', $dataEncode, $header, $ip, $mac);
        $messageErr = '';
        $connect = true;
        $result = true;
        $arrUpdate = array();
        if ($dataDecode) {
            try {
                $site = SiteSettings::where('code', $dataDecode)->first();
                if ($site) {
                    $nameTable = $request->tbName;
                    $dataTables = $request->queryData;

                    if ($nameTable == 'fx_transaction_center_compromised_files_check') {
                        $pkey = 'id';
                        $model_insert = new CompromisedFileCheck;
                    } else if ($nameTable == 'fx_transaction_center_compromised_files_check2') {
                        $pkey = 'id';
                        $model_insert = new CompromisedFileCheck;
                    } else {
                        $connect = false;
                        $result = false;
                    }
                    if ($result == true) {
                        foreach ($dataTables as $dataTable) {

                            // $transfer_data_id = $dataTable["get_transfer"]["id"];
                            if(!empty($dataTable["transaction_id"])){
                                $findOne = new $model_insert;
                                $findOne->setConnection($this->dbName);
                                $findOne = $findOne->where('transfer_site_id', $site->id)->where('transfer_data_id', $dataTable["transaction_id"])->first();
                                if (isset($dataTable["get_transfer"][$pkey]) && ($dataTable["transaction_mode"] == 'insert' || $dataTable["transaction_mode"] == 'update')) {
                                    if (empty($findOne)) {
                                        $findOne = new $model_insert;
                                        $findOne->setConnection($this->dbName);
                                        $findOne->transfer_site_id = $site->id;
                                        $findOne->transfer_data_id = $dataTable["get_transfer"][$pkey];
                                        foreach ($dataTable["get_transfer"] as $key => $subValue) {
                                            if ($key != $pkey&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
                                                if($subValue==''){
                                                    $subValue=null;
                                                }
                                                $findOne->{$key} = $subValue;
                                            }
    
                                        }
    
                                        $findOne->save();
    
                                    } else {
                                        $findOne->transfer_site_id = $site->id;
                                        $findOne->transfer_data_id = $dataTable["get_transfer"][$pkey];
                                        foreach ($dataTable["get_transfer"] as $key => $subValue) {
                                            if ($key != $pkey&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
                                                if($subValue==''){
                                                    $subValue=null;
                                                }
                                                $findOne->{$key} = $subValue;
                                            }
    
                                        }
                                        $findOne->save();
                                    }
                                } else if ($dataTable["transaction_mode"] == 'delete') {
                                    if (!empty($findOne)) {
                                        $findOne->delete();
                                    }
                                }
                                $arrUpdate[] = $dataTable["id"];
                            }
                            
                        }
                    }
                } else {
                    $connect = false;
                    $result = false;
                }
            } catch (Exception $e) {
                $connect = false;
                $result = false;
                $messageErr = "line : ".$e->getLine()." Error :".$e->getMessage();
            }

        } else {
            $connect = false;
            $result = false;
        }

        $dataout = [
            'connect' => $connect,
            'result' => $result,
            'returnUpdate' => $arrUpdate,
            'messageErr' => $messageErr,
        ];
        return response()->json($dataout);
    }

    protected function updateIsFix_fx_data_datacve_mapping(Request $request)
    {
        $ip = $this->ip;
        $mac = $this->mac;
        $header = $this->header;
        $connect = true;

        $code = $request->site_code_en;
        $statusCVE = $request->statusCVE;
        $transaction_id = $request->transaction_id;

        $dataEncode = $code;
        $dataDecode = encrypt_decrypt('decrypt', $dataEncode, $header, $ip, $mac);
        $site = SiteSettings::where('code', $dataDecode)->first();
        
        if($site){
            $TF_Center_data_datacve_mapping = TF_Center_data_datacve_mapping::where('site_id', $site->id)->where('id', $transaction_id)->first();
            if($TF_Center_data_datacve_mapping){
                $TF_Center_data_datacve_mapping->is_fix = $statusCVE;
                $TF_Center_data_datacve_mapping->save();
            }
        }else{
            $connect = false;
        }
        $dataout = [
            'connect' => true,
        ];

        return response()->json($dataout);
    }

    protected function updateBatchJob(Request $request)
    {
        $modeFor = $request->modeFor;
        $modeInsert = $request->modeInsert;
        $nameBJ = $request->nameBJ;

        $sitecode = $request->sitecode;
        $site = SiteSettings::where('code', $sitecode)->first();
       
        if($site){
            if($modeFor=='wait'||$modeFor=='done'){
                $TF_Center_transaction_batchjob = TF_Center_transaction_batchjob::where('mode', $modeInsert)->where('site_id', $site->id)->first();
                if(!$TF_Center_transaction_batchjob){
                    $TF_Center_transaction_batchjob = new TF_Center_transaction_batchjob;
                    $TF_Center_transaction_batchjob->status = 1;
                    $TF_Center_transaction_batchjob->code = generator_uuid();
                    $TF_Center_transaction_batchjob->mode = $modeInsert;
                    $TF_Center_transaction_batchjob->site_id = $site->id;
                }
                $TF_Center_transaction_batchjob->name = $nameBJ;
                
                $TF_Center_transaction_batchjob->transcation_date = date('Y-m-d');
                
                if($modeFor=='wait'){
                    $TF_Center_transaction_batchjob->progress = 0;
                    $TF_Center_transaction_batchjob->transcation_date_start = date('Y-m-d H:i:s');
                }else if($modeFor=='done'){
                    $TF_Center_transaction_batchjob->progress = 1;
                    $TF_Center_transaction_batchjob->transcation_date_end = date('Y-m-d H:i:s');
                }
                $TF_Center_transaction_batchjob->save();
            }
        }
        
        $dataout = [
            'connect' => true,
        ];

        return response()->json($dataout);
    }

    protected function get_encode(Request $request)
    {
        $ip = '127.0.0.1';
        $mac = 'abcd';
        $header = 'header';
        $site = [
            'site_id' => $request->site_id,
        ];
        $dataEncode = encrypt_decrypt('encrypt', json_encode($site), $header, $ip, $mac);
        // $dataEncode = encrypt_decrypt('decrypt', $dataEncode, $header,$ip,$mac);
        $dataout = [
            'connect' => true,
            'result' => $dataEncode,
        ];

        return response()->json($dataout);
    }

}
