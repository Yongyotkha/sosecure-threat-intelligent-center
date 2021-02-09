<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\ApiController;


use phpseclib\Net\SSH2;
use Exception;

use Modules\SiteSettings\Entities\SiteSettings;
use App\Entities\TF_Center_transaction_batchjob;

use App\Entities\Transaction_client_News;
use App\Entities\Transaction_client_webdefacment_data_check;
use App\Entities\Transaction_client_webdefacment_data_logs;
use App\Entities\Transaction_client_webdefacment_data_original;
use App\Entities\Transaction_client_webdefacment_image_mark;
use App\Entities\Transaction_client_webdefacment_setting;
use App\Entities\Transaction_client_data_datacve_mapping;
use App\Entities\Transaction_client_cve_assets;
use App\Entities\TFClient_R_s_s_news_categories;
use App\Entities\fx_transaction_client_news_categories;
use App\Entities\Transaction_site;
use App\Entities\Transaction_site_category;
use App\Entities\Transaction_client_categories;
use App\Entities\Transaction_users;
use App\Entities\Transaction_user_site;
use App\Entities\Transaction_roles;
use App\Entities\Transaction_permissions;
use App\Entities\Transaction_role_permissions;
use App\Entities\Transaction_model_has_roles;
use App\Entities\Transaction_profiles;
use App\Entities\Sites;
use App\Entities\Transaction_client_compromised_server;

use App\Entities\Transaction_client_asset;
use App\Entities\Transaction_client_asset_data;
use App\Entities\Transaction_client_credentials;
use App\Entities\Transaction_client_cpe;
class ApiTransferClients extends Controller
{

    private $ip = '127.0.0.1';
    private $mac = 'abcd';
    private $header = 'header';
    private $dbName = 'mysql';
    private $center_site_id = '0';


    protected function getTranferData(Request $request)
    {

      $ip = $this->ip;
      $mac = $this->mac;
      $header = $request->bearerToken();
      $code = $request->get('code');
      $Sites_get = Sites::where('code',$code)->where('active',1)->where('system_site_online',1)->where('start_active', '<=', date("Y-m-d H:i:s"))->where('end_active', ">=", date("Y-m-d H:i:s"))->first();

      if (!$Sites_get) {
         $dataout = [
            'connect' => 0,
            'result' => 0,
            'queryData' =>$Sites_get,
            'site_code_en' =>$code,
            'messageErr' => 'Your account has expired; please contact your system administrator',
        ];
        return response()->json($dataout); 
    }

    if ($header !=$Sites_get->public_key) {
      $dataout = [
        'connect' => 0,
        'result' => 0,
        'queryData' => array(),
        'site_code_en' =>$code,
        'messageErr' => 'Your account has expired; please contact your system administrator',
    ];
    return response()->json($dataout); 
}


$ip=$Sites_get->ip_key;
$mac=$Sites_get->mac_address_key;
$data = $request->data;
$dataDecode = encrypt_decrypt('decrypt', $data, $header, $ip, $mac);
$object_dataDecode = json_decode($dataDecode, FALSE);
// $dataout = [
//     'connect' => 0,
//     'result' => 0,
//     'queryData' => $object_dataDecode->tbName,
//     'site_code_en' =>$code,
//     'messageErr' => 'Your account has expired; please contact your system administrator',
// ];
// return response()->json($dataout); 



$TransactionClient = null;
$messageErr = '';
$connect = true;
$result = true;

$dataEncode = $code;
$dataDecode = $code;

if($dataDecode){
    $site = SiteSettings::where('code', $dataDecode)->first();

    if($site){
        $nameTable = $object_dataDecode->tbName;
        if ($nameTable == 'fx_transaction_client_news') {
            $model_getData = new Transaction_client_News;
            $modeInsert = 'fx_transaction_client_news';
            $nameBJ = 'Transaction Client client_news - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_news_categories') {
            $model_getData = new fx_transaction_client_news_categories;
            $modeInsert = 'fx_transaction_client_news_categories';
            $nameBJ = 'Transaction Client webdefacment_data_check - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_webdefacment_data_check') {
            $model_getData = new Transaction_client_webdefacment_data_check;
            $modeInsert = 'fx_transaction_client_webdefacment_data_check';
            $nameBJ = 'Transaction Client webdefacment_data_check - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_webdefacment_data_logs') {
            $model_getData = new Transaction_client_webdefacment_data_logs;
            $modeInsert = 'fx_transaction_client_webdefacment_data_logs';
            $nameBJ = 'Transaction Client webdefacment_data_logs - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_webdefacment_data_original') {
            $model_getData = new Transaction_client_webdefacment_data_original;
            $modeInsert = 'fx_transaction_client_webdefacment_data_original';
            $nameBJ = 'Transaction Client webdefacment_data_original - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_webdefacment_image_mark') {
            $model_getData = new Transaction_client_webdefacment_image_mark;
            $modeInsert = 'fx_transaction_client_webdefacment_image_mark';
            $nameBJ = 'Transaction Client webdefacment_image_mark - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_webdefacment_setting') {
            $model_getData = new Transaction_client_webdefacment_setting;
            $modeInsert = 'fx_transaction_client_webdefacment_setting';
            $nameBJ = 'Transaction Client webdefacment_setting - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_data_datacve_mapping') {
            $model_getData = new Transaction_client_data_datacve_mapping;
            $modeInsert = 'fx_transaction_client_data_datacve_mapping';
            $nameBJ = 'Transaction Client data_datacve_mapping - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_cve_assets') {
            $model_getData = new Transaction_client_cve_assets;
            $modeInsert = 'fx_transaction_client_cve_assets';
            $nameBJ = 'Transaction Client cve_assets - everyMinute()  Or Request';
        } else if ($nameTable == 'fx_transaction_client_categories') {
            $model_getData = new Transaction_client_categories;
            $modeInsert = 'fx_transaction_client_categories';
            $nameBJ = 'Transaction Client categories - everyMinute()  Or Request';
            
        }else if ($nameTable == 'fx_transaction_client_users') {
            $model_getData = new Transaction_users;
            $modeInsert = 'fx_transaction_client_users';
            $nameBJ = 'Transaction Client users - everyMinute()  Or Request';
            
        }else if ($nameTable == 'fx_transaction_client_users_site') {
            $model_getData = new Transaction_user_site;
            $modeInsert = 'fx_transaction_client_users_site';
            $nameBJ = 'Transaction Client users_site - everyMinute()  Or Request';
            
        }else if ($nameTable == 'fx_transaction_client_roles') {
            $model_getData = new Transaction_roles;
            $modeInsert = 'fx_transaction_client_roles';
            $nameBJ = 'Transaction Client roles - everyMinute()  Or Request';
            
        } else if ($nameTable == 'fx_transaction_client_permissions') {
            $model_getData = new Transaction_permissions;
            $modeInsert = 'fx_transaction_client_permissions';
            $nameBJ = 'Transaction Client permissions - everyMinute()  Or Request';
            
        }else if ($nameTable == 'fx_transaction_client_role_permissions') {
            $model_getData = new Transaction_role_permissions;
            $modeInsert = 'fx_transaction_client_role_permissions';
            $nameBJ = 'Transaction Client role_permissions - everyMinute()  Or Request';
            
        }else if ($nameTable == 'fx_transaction_client_model_has_roles') {
            $model_getData = new Transaction_model_has_roles;
            $modeInsert = 'fx_transaction_client_model_has_roles';
            $nameBJ = 'Transaction Client model_has_roles - everyMinute()  Or Request';
            
        }else if ($nameTable == 'fx_transaction_client_site') {
            $model_getData = new Transaction_site;
            $modeInsert = 'fx_transaction_client_site';
            $nameBJ = 'Transaction Client site - everyMinute()  Or Request';

        }else if ($nameTable == 'fx_transaction_client_profiles') {
            $model_getData = new Transaction_profiles;
            $modeInsert = 'fx_transaction_client_profiles';
            $nameBJ = 'Transaction Client profiles - everyMinute()  Or Request';

        }else if ($nameTable == 'fx_transaction_client_site_category') {
            $model_getData = new Transaction_site_category;
            $modeInsert = 'fx_transaction_client_site_category';
            $nameBJ = 'Transaction Client site_category - everyMinute()  Or Request';

        }else if ($nameTable == 'fx_transaction_client_compromised_server') {
            $model_getData = new Transaction_client_compromised_server;
            $modeInsert = 'fx_transaction_client_compromised_server';
            $nameBJ = 'Transaction Client  - everyMinute()  Or Request';

        }
        else if ($nameTable == 'fx_transaction_client_asset') {
            $model_getData = new Transaction_client_asset;
            $modeInsert = 'fx_transaction_client_asset';
            $nameBJ = 'Transaction asset r - everyMinute()  Or Request';

        }
        else if ($nameTable == 'fx_transaction_client_asset_data') {
            $model_getData = new Transaction_client_asset_data;
            $modeInsert = 'fx_transaction_client_asset_data';
            $nameBJ = 'Transaction asset_data  - everyMinute()  Or Request';

        }
        else if ($nameTable == 'fx_transaction_client_credentials') {
            $model_getData = new Transaction_client_credentials;
            $modeInsert = 'fx_transaction_client_credentials';
            $nameBJ = 'Transaction credentials server - everyMinute()  Or Request';

        }
        else if ($nameTable == 'fx_transaction_client_cpe') {
            $model_getData = new Transaction_client_cpe;
            $modeInsert = 'Transaction_client_cpe';
            $nameBJ = 'Transaction cpe - everyMinute()  Or Request';

        }
        else {
            $connect = false;
            $result = false;
        }

        if ($result == true) {
            $TransactionClient = new $model_getData;
            $TransactionClient->setConnection($this->dbName);
            $TransactionClient = $TransactionClient->where('site_id',$site->id)->where('status',1)->where('transaction_data_status',1)->with('get_transfer_client')->orderBy('id','asc')->get()->toArray();

            $TF_Center_transaction_batchjob = new TF_Center_transaction_batchjob;
            $TF_Center_transaction_batchjob->setConnection($this->dbName);
            $TF_Center_transaction_batchjob = $TF_Center_transaction_batchjob->where('mode', $modeInsert)->where('site_id', $site->id)->first();

            if(!$TF_Center_transaction_batchjob){
                $TF_Center_transaction_batchjob = new TF_Center_transaction_batchjob;
                $TF_Center_transaction_batchjob->status = 1;
                $TF_Center_transaction_batchjob->code = generator_uuid();
                $TF_Center_transaction_batchjob->mode = $modeInsert;
                $TF_Center_transaction_batchjob->site_id = $site->id;
            }
            $TF_Center_transaction_batchjob->name = $nameBJ;
            $TF_Center_transaction_batchjob->transcation_date = date('Y-m-d');
            $TF_Center_transaction_batchjob->progress = 2;
            $TF_Center_transaction_batchjob->transcation_date_start = date('Y-m-d H:i:s');
            $TF_Center_transaction_batchjob->save();

            $updater = new $model_getData;
            $updater->setConnection($this->dbName);
            $updater->where('site_id',$site->id)->where('status',1)->where('transaction_data_status',1)->update(['transaction_data_status' => 2]);
        }
    }else{
        $connect = false;
        $result = false;
    }
}else{
    $connect = false;
    $result = false;
}

$site_center = $dataDecode;


$dataEncode_send = encrypt_decrypt('encrypt', json_encode($TransactionClient), $header, $ip, $mac);



$dataout = [
    'connect' => $connect,
    'result' => $result,
    'queryData' => $dataEncode_send,
    'site_code_en' => $site_center,
    'messageErr' => $messageErr,
];
return response()->json($dataout); 
}

protected function get_encode(Request $request)
{
    $ip = '127.0.0.1';
    $mac = 'abcd';
    $header ='header';
    $site = $request->site_id;
    $dataEncode = encrypt_decrypt('encrypt', $site, $header,$ip,$mac);
        // $dataEncode = encrypt_decrypt('decrypt', $dataEncode, $header,$ip,$mac);
    $dataout = [
        'connect' => true,
        'result' => $dataEncode,
    ];

    return response()->json($dataout); 
}

protected function checkWebserverIP(Request $request)
{
    $ip = @$request->ip;
    $port = @$request->port;
    $user = @$request->user;
    $pass = @$request->password;
    $os = @$request->os;
    $checkConnect = null;
    $message = '';
    try {   
        if($os=="Linux"){
            $ssh = new SSH2($ip,$port);
            $ssh->setTimeout(60);

            if (!$ssh->login($user, $pass)) {
                $checkConnect = false;
                $message = 'no login';
            } else {
                $checkConnect = true;
                $message = 'success';
            }
        }else if($os=="Windows"){
            $checkConnect = false;
            $message = 'no make';
        }else{
            $checkConnect = false;
            $message = 'no os';
        }
    } catch (Exception $e) {
        $checkConnect = false;
        $message = $e->getMessage();
    }

    $dataout = [
        'webserverConnect' => $checkConnect,
        'message' => $message
    ];
    return response()->json($dataout); 
}

}
