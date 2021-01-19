<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

use App\Entities\CompromisedFileCheck;
use App\Entities\TF_Client_R_s_s_news;
use App\Entities\TF_Client_webdefacment_data_check;
use App\Entities\TF_Client_webdefacment_data_logs;
use App\Entities\TF_Client_webdefacment_data_original;
use App\Entities\TF_Client_webdefacment_image_mark;
use App\Entities\TF_Client_webdefacment_setting;
use App\Entities\TF_Client_cve_assets;
use App\Entities\TF_Client_data_datacve_mapping;

class ApiTransferClientInsert extends Controller
{
    private $ip = '127.0.0.1';
    private $mac = 'abcd';
    private $header = 'header';
    private $dbName = 'dummyDatabase';

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
        if ($dataDecode||$dataDecode===0) {
            try {
                    $site = new StdClass();
                    $site->id = $dataDecode;
                    $nameTable = $request->tbName;
                    $dataTables = $request->queryData;

                    if ($nameTable == 'fx_transaction_center_data_leak_feed_temp') {
                        $pkey_main = 'id';
                        $pkey_sub = 'id';
                        $refkey_sub = 'data_leak_feed_id';
                        $model_main = new TF_Center_data_leak_feed_temp;
                        $model_sub = new TF_Center_data_leak_socail_ref_temp;
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

                                if($findOne_main && ($dataTable["transaction_mode"] == 'insert' || $dataTable["transaction_mode"] == 'update')){
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

        // $dataDecode = false;

        if ($dataDecode||$dataDecode === 0) {
            try {
                $siteID = $dataDecode;
                $nameTable = $request->tbName;
                $dataTables = $request->queryData;

                if ($nameTable == 'fx_transaction_client_news') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_R_s_s_news;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_check') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_data_check;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_logs') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_data_logs;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_original') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_data_original;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_image_mark') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_image_mark;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_setting') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_setting;
                } else if ($nameTable == 'fx_transaction_client_cve_assets') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_cve_assets;
                } else if ($nameTable == 'fx_transaction_client_data_datacve_mapping') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_data_datacve_mapping;
                } else {
                    $connect = false;
                    $result = false;
                }
                
                if ($result == true) {

                    if (!empty($dataTables)) {
                        foreach ($dataTables as $dataTable) {

                            // $transfer_data_id = $dataTable["get_transfer_client"]["id"];
                            if (!empty($dataTable["transaction_id"])) {
                                $findOne = new $model_insert;
                                $findOne->setConnection($this->dbName);
                                $findOne = $findOne->where('transfer_site_id', $siteID)->where('transfer_data_id', $dataTable["transaction_id"])->first();
                                
                                if (isset($dataTable["get_transfer_client"][$pkey]) && ($dataTable["transaction_mode"] == 'insert' || $dataTable["transaction_mode"] == 'update')) {
                                    if (empty($findOne)) {
                                        $findOne = new $model_insert;
                                        $findOne->setConnection($this->dbName);
                                        $findOne->transfer_site_id = $siteID;
                                        $findOne->transfer_data_id = $dataTable["get_transfer_client"][$pkey];
                                        foreach ($dataTable["get_transfer_client"] as $key => $subValue) {
                                            if ($key != $pkey && $key != 'transfer_site_id' && $key != 'transfer_data_id') {
                                                if($subValue==''){
                                                    $subValue=null;
                                                }
                                                $findOne->{$key} = $subValue;
                                            }

                                        }

                                        $findOne->save();

                                    } else {
                                        $findOne->transfer_site_id = $siteID;
                                        $findOne->transfer_data_id = $dataTable["get_transfer_client"][$pkey];
                                        foreach ($dataTable["get_transfer_client"] as $key => $subValue) {
                                            if ($key != $pkey && $key != 'transfer_site_id' && $key != 'transfer_data_id') {
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
                }
            } catch (Exception $e) {
                $connect = false;
                $result = false;
                $messageErr = "line : " . $e->getLine() . " Error :" . $e->getMessage();
            }
        }


        $dataout = [
            'connect' => $connect,
            'result' => $result,
            'returnUpdate' => $arrUpdate,
            'messageErr' => $messageErr,
        ];
        return response()->json($dataout);
    }


    protected function insertToNoRefWithID(Request $request)
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

        // $dataDecode = false;

        if ($dataDecode||$dataDecode === 0) {
            try {
                $siteID = $dataDecode;
                $nameTable = $request->tbName;
                $dataTables = $request->queryData;

                if ($nameTable == 'fx_transaction_client_news') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_R_s_s_news;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_check') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_data_check;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_logs') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_data_logs;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_original') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_data_original;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_image_mark') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_image_mark;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_setting') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_webdefacment_setting;
                } else if ($nameTable == 'fx_transaction_client_cve_assets') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_cve_assets;
                } else if ($nameTable == 'fx_transaction_client_data_datacve_mapping') {
                    $pkey = 'id';
                    $model_insert = new TF_Client_data_datacve_mapping;
                } else {
                    $connect = false;
                    $result = false;
                }
                if ($result == true) {

                    if (!empty($dataTables)) {
                        foreach ($dataTables as $dataTable) {

                            // $transfer_data_id = $dataTable["get_transfer_client"]["id"];
                            if (!empty($dataTable["transaction_id"])) {
                                $findOne = new $model_insert;
                                $findOne->setConnection($this->dbName);
                                // $findOne = $findOne->where($pkey, $dataTable["get_transfer_client"][$pkey])->where('transfer_site_id', $siteID)->where('transfer_data_id', $dataTable["get_transfer_client"][$pkey])->first();
                                $findOne = $findOne->where($pkey, $dataTable["transaction_id"])->first();

                                if (!empty($dataTable["get_transfer_client"][$pkey]) && $dataTable["transaction_mode"] == 'insert' || $dataTable["transaction_mode"] == 'update') {
                                    if (empty($findOne)) {
                                        $findOne = new $model_insert;
                                        $findOne->setConnection($this->dbName);
                                        $findOne->transfer_site_id = $siteID;
                                        $findOne->transfer_data_id = $dataTable["get_transfer_client"][$pkey];
                                        foreach ($dataTable["get_transfer_client"] as $key => $subValue) {
                                            if ( $key != 'transfer_site_id' && $key != 'transfer_data_id') {
                                                if($subValue==''){
                                                    $subValue=null;
                                                }
                                                $findOne->{$key} = $subValue;
                                            }

                                        }

                                        $findOne->save();

                                    } else {
                                        
                                        $findOne->transfer_site_id = $siteID;
                                        $findOne->transfer_data_id = $dataTable["get_transfer_client"][$pkey];
                                        foreach ($dataTable["get_transfer_client"] as $key => $subValue) {
                                            if ($key != 'transfer_site_id' && $key != 'transfer_data_id') {
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
                }
            } catch (Exception $e) {
                $connect = false;
                $result = false;
                $messageErr = "line : " . $e->getLine() . " Error :" . $e->getMessage();
            }
        }
        $dataout = [
            'connect' => $connect,
            'result' => $result,
            'returnUpdate' => $arrUpdate,
            'messageErr' => $messageErr,
        ];
        return response()->json($dataout);
    }
}
