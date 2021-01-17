<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Entities\CompromisedFileCheck;
use App\Http\Controllers\Api\ApiController;
use Exception;
use Illuminate\Http\Request;
use Modules\SiteSettings\Entities\SiteSettings;

class ApiTransferCenterInsert extends Controller
{
    private $ip = '127.0.0.1';
    private $mac = 'abcd';
    private $header = 'header';
    private $dbName = 'mysql';

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
                                if (!empty($dataTable["get_transfer"][$pkey]) && $dataTable["transaction_mode"] == 'insert' || $dataTable["transaction_mode"] == 'update') {
                                    if (empty($findOne)) {
                                        $findOne = new $model_insert;
                                        $findOne->setConnection($this->dbName);
                                        $findOne->transfer_site_id = $site->id;
                                        $findOne->transfer_data_id = $dataTable["get_transfer"][$pkey];
                                        foreach ($dataTable["get_transfer"] as $key => $subValue) {
                                            if ($key != $pkey&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
                                                $findOne->{$key} = $subValue;
                                            }
    
                                        }
    
                                        $findOne->save();
    
                                    } else {
                                        $findOne->transfer_site_id = $site->id;
                                        $findOne->transfer_data_id = $dataTable["get_transfer"][$pkey];
                                        foreach ($dataTable["get_transfer"] as $key => $subValue) {
                                            if ($key != $pkey&&$key!='transfer_site_id'&&$key!='transfer_data_id') {
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
