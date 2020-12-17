<?php

use App\Entities\Hook;
use App\Entities\Language;
use App\Entities\Local;
use App\Services\SvgFactory;
use Facades\App\Helpers\CurrencyConverter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Settings\Entities\Options;
use Modules\Tickets\Entities\Ticket;
use Modules\Timetracking\Entities\TimeEntry;
use Modules\Users\Entities\Profile;
use Modules\Users\Entities\User;
use Modules\SiteSettings\Entities\SiteSettings;
use Stringy\Stringy as S;

use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;


define("PAGINATE_NUM", 10);
define("DB_MONGO_01", 'mongodb://10.104.0.7:27017');


function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

// function get_name_scan_status($status_id) {
//     if($status_id == 0) {
//         $html = '<span class="badge badge-secondary">waiting</span>';
//     } else if($status_id == 1) {
//         $html = '<span class="badge badge-Warning" style="background-color: #ffc107;">scanning</span>';
//     } else if($status_id == 2) {
//         $html = '<span class="badge badge-Warning" style="background-color: #ffc107;">scanning</span>';
//     } else if($status_id == 3) {
//         $html = '<span class="badge badge-Success" style="background-color: #179f83;">complete</span>';
//     }
// }
function get_name_scan_status($status_id,$badg='') {
    $html = '';
    if($badg == 'badg') {
        if($status_id == 0) {
            $html = '<span class="badge badge-secondary">waiting</span>';
        } else if($status_id == 1) {
            $html = '<span class="badge badge-Warning" style="background-color: #ffc107;">scanning</span>';
        } else if($status_id == 2) {
            $html = '<span class="badge badge-Warning" style="background-color: #ffc107;">scanning</span>';
        } else if($status_id == 3) {
            $html = '<span class="badge badge-Success" style="background-color: #179f83;">complete</span>';
        }
    } else {
        if($status_id == 0) {
            $html = 'waiting';
        } else if($status_id == 1) {
            $html = 'scanning';
        } else if($status_id == 2) {
            $html = 'scanning';
        } else if($status_id == 3) {
            $html = 'complete';
        }
    }
    return $html;
}

function get_CVSS_Severity_status($num_val,$status_id,$badg='') {
    $html = '';
    if($badg == 'badg') {
        if(strtolower($status_id) == strtolower("CRITICAL")) {
            $html = '<span class="badge badge-secondary" style="background-color: #b30000;">'.$num_val.' CRITICAL</span>';
        } else if(strtolower($status_id) == strtolower("HIGH")) {
            $html = '<span class="badge badge-Warning" style="background-color: #d9534f;">'.$num_val.' HIGH</span>';
        } else if(strtolower($status_id) == strtolower("MEDIUM")) {
            $html = '<span class="badge badge-Warning" style="background-color: #ec971f;">'.$num_val.' MEDIUM</span>';
        } else if(strtolower($status_id) == strtolower("LOW")) {
            $html = '<span class="badge badge-Success" style="background-color: #bfff00;">'.$num_val.' LOW</span>';
        } else if(strtolower($status_id) == strtolower("NONE")) {
            $html = '<span class="badge badge-Success" style="background-color: #40ff00;">'.$num_val.' NONE</span>';
        }
    } else {
        if(strtolower($status_id) == strtolower("CRITICAL")) {
            $html = 'CRITICAL';
        } else if(strtolower($status_id) == strtolower("HIGH")) {
            $html = 'HIGH';
        } else if(strtolower($status_id) == strtolower("MEDIUM")) {
            $html = 'MEDIUM';
        } else if(strtolower($status_id) == strtolower("LOW")) {
            $html = 'LOW';
        } else if(strtolower($status_id) == strtolower("NONE")) {
            $html = 'NONE';
        }
    }
    return $html;
}


function generator_uuid(){
    return Str::uuid()->toString();
}

function generate_site_key_login() {
    $exists = true;
    while ($exists)
    {
        $code = str_random(30);
        $check = SiteSettings::where('site_key_login', $code)->count();
        if( $check == 0 )
        {
            $exists = false;
        }
    }
    return $code;
}

function generate_site_key_code() {
    $exists = true;
    while ($exists)
    {
        $code = str_random(30);
        $check = SiteSettings::where('site_key_code', $code)->count();
        if( $check == 0 )
        {
            $exists = false;
        }
    }
    return $code;
}

function encrypt_decrypt($action, $string, $public_key, $ip, $mac) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'secret-key-' . $public_key . $ip . $mac;
    $secret_iv = 'secret-iv-' . $public_key . $ip . $mac;
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if( $action == 'decrypt' ) {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function utf8_strlen($str) {
      $c = strlen($str);
            $l = 0;
      for ($i = 0; $i < $c; ++$i)
      {
         if ((ord($str[$i]) & 0xC0) != 0x80)
         {
            ++$l;
         }
      }
      return $l;
}

function explode_val($val,$type=null) {
    $result = '';
    if($val) {
        $val_arr = explode(",",$val);
        if($val_arr) {
            foreach($val_arr as $tag) {
                if($type == 'tags') {
                    $result .=  '<a href="#">'.$tag.'</a> ,';
                } else if ($type == 'groups') {
                    $result .=  '<a href="#">'.$tag.'</a> ,';
                } else {
                    $result .=  '<a href="#">'.$tag.'</a> ,';
                }
                
            }
            $result = rtrim($result,',');
        }
    } else {
        $result = '';
    }
    return $result;
}


function check_publish($val) {
    if($val==1) {
        $result = '<i class="fas fa-check"></i>';
    } else {
        $result = '';
    }
    return $result;
}
function check_last_status($val) {
    if($val== true) {
        $result = 'Modified';
    } else {
        $result = 'Created';
    }
    return $result;
}


function change_date_utc_to_thai($val) {
    // if($val== true) {
    //     $result = 'Modified';
    // } else {
    //     $result = 'Created';
    // }


    $tz = new \DateTimeZone('Asia/Bangkok');
    // $start = '2020-01-01 00:00:00';
    // $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($val)*1000);

    // print_r($dateStart->toDateTime()->format(DATE_RSS));
    $date_start = $val->toDateTime();

    $date_start->setTimezone($tz);

    $start = $date_start->format(DATE_ATOM);
    $return_date = date("Y-m-d H:i",strtotime($start));

    // echo $start;


    return $return_date;
}
