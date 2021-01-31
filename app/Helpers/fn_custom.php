<?php

session_start();

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
use Modules\Users\Entities\UserSite;
use Modules\Users\Entities\model_has_roles;
use Illuminate\Support\Facades\Auth;


define("TYPE_WEB", 'center');//center , client
define("PAGINATE_NUM", 10);
define("DB_MONGO_01", 'mongodb://10.104.0.10:27017');
define("PATH_MY_IP_TF", 'http://127.0.0.2');
define("PATH_CENTER_IP_TF", 'http://127.0.0.2');
            
function get_role_custom() {
    $superadmin = 0;
    $client = 0;
    $site_support = 0;
    $site_admin = 0;
    $site_client = 0;
            if(Auth::check()) {
                $model_has_roles = model_has_roles::where('model_id',@Auth::user()->id)->first();
                $role_id = @$model_has_roles->role_id;
                // dd($role_id);

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(@$role_id == 1) {//if admin  | Auth::user()->hasRole('admin')
                    // dd(777);
                    $superadmin = 1;

                } else { //if notAdmin
                    // dd(888);
                    if(@Auth::user()->site_role_id && $site_id_arr) {
                        if(@$role_id == 6) {//support and admin
                            // dd(99);
                            $site_support = 1;
                            // $model = $model->whereIn('site_id', $site_id_arr);
                            // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

                        } else if(@$role_id == 4) {//not support and admin
                            $site_admin = 1;
                            // $model = $model->whereIn('site_id', $site_id_arr);
                            // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        } else if(@$role_id == 5) {//not support and admin
                            $site_client = 1;
                           
                        }
                    }

                    $client = 1;

                }
            }
            
                $data = [
                    "superadmin" => $superadmin,//center
                    "client" => $client,//center
                    "site_support" => $site_support,//site
                    "site_admin" => $site_admin,//site
                    "site_client" => $site_client,//site
                    "site_id_arr" => $site_id_arr//center,site
                ];
                return $data; 
            
}


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
            $html = '<span class="badge badge-secondary" style="background-color: #e64732;">'.$num_val.' CRITICAL</span>';
        } else if(strtolower($status_id) == strtolower("HIGH")) {
            $html = '<span class="badge badge-Warning" style="background-color: #fcc838;">'.$num_val.' HIGH</span>';
        } else if(strtolower($status_id) == strtolower("MEDIUM")) {
            $html = '<span class="badge badge-Warning" style="background-color: #ffe46d;">'.$num_val.' MEDIUM</span>';
        } else if(strtolower($status_id) == strtolower("LOW")) {
            $html = '<span class="badge badge-Success" style="background-color: #88ce4f;">'.$num_val.' LOW</span>';
        } else if(strtolower($status_id) == strtolower("NONE")) {
            $html = '<span class="badge badge-Success" style="background-color: #d3d3d3;">'.$num_val.' INFORMATION</span>';
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

function get_webdefacment_status($status_id,$color='') {
    $html = '';
    if($color == 'color') {
        if(strtolower($status_id) == strtolower("critical")) {
            $html = '<span class="dot critical"></span> Critical';
        } else if(strtolower($status_id) == strtolower("high")) {
            $html = '<span class="dot " style="background: #e64732 !important;"></span> High';
        } else if(strtolower($status_id) == strtolower("meduim")) {
            $html = '<span class="dot " style="background: #fcc838 !important;"></span> Medium';
        } else if(strtolower($status_id) == strtolower("normal")) {
            $html = '<span class="dot low"></span> Normal';
        } else if(strtolower($status_id) == strtolower("none")) {
            $html = '<span class="dot none"></span> None';
        }
    } else {
        if(strtolower($status_id) == strtolower("critical")) {
            $html = $status_id;
        } else if(strtolower($status_id) == strtolower("high")) {
            $html = $status_id;
        } else if(strtolower($status_id) == strtolower("meduim")) {
            $html = $status_id;
        } else if(strtolower($status_id) == strtolower("normal")) {
            $html = $status_id;
        } else if(strtolower($status_id) == strtolower("none")) {
            $html = $status_id;
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
                    $result .=  '<a href="'.route('indicators.link_tags', ['id' => $tag]).'">'.$tag.'</a> ,';
                } else if ($type == 'groups') {
                    $result .=  '<a href="'.route('indicators.link_group', ['id' => $tag]).'">'.$tag.'</a> ,';
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



function get_menu_html() {
    $html_all = '';

    $html_all .= '
    <nav class="nav-primary hidden-xs">
    <ul class="nav">
    ';

            $menu = [];
            $menu_html = '';
        if(isset($_SESSION["menu"])){
            // unset($_SESSION["lastname"]);
            $menu = $_SESSION["menu"];


            if($menu) {
                foreach($menu as $menu_val) {
                    $active = '';
                    $url = '#';
                    $check_menu_active = '';
                    $name_val = '';
                    
                    if($menu_val->url) {// url
                        if($menu_val->type_url == 'site_url') {
                            $url = site_url($menu_val->url);
                        } else if ($menu_val->type_url == 'route') {
                            $url = route($menu_val->url);
                        }
                    }

                    if($menu_val->check_menu_active) {// check active
                        if($menu_val->type_check_menu_active == 'langapp') {
                            $check_menu_active = langapp($menu_val->check_menu_active);
                        } else if ($menu_val->type_check_menu_active == '') {
                            $check_menu_active = $menu_val->check_menu_active;
                        }
                    }

                    if(@$page == $check_menu_active) {
                        $active = 'active';
                    }

                    if($menu_val->langapp) {//ชื่อเมนู
                        $name_val = langapp($menu_val->langapp);
                    }

                    if(@$menu_val->get_menu_sub) {


                        
                        $menu_sub_html = '';
                        foreach($menu_val->get_menu_sub as $menu_sub_val) {


                            $active_sub = '';
                            $url_sub = '#';
                            $check_menu_active_sub = '';
                            $name_val_sub = '';
                            

                            if($menu_sub_val->url) {// url
                                if($menu_sub_val->type_url == 'site_url') {
                                    $url_sub = site_url($menu_sub_val->url);
                                } else if ($menu_sub_val->type_url == 'route') {
                                    $url_sub = route($menu_sub_val->url);
                                }
                            }

                            if($menu_sub_val->check_menu_active) {// check active
                                if($menu_sub_val->type_check_menu_active == 'langapp') {
                                    $check_menu_active_sub = langapp($menu_sub_val->check_menu_active);
                                } else if ($menu_sub_val->type_check_menu_active == '') {
                                    $check_menu_active_sub = $menu_sub_val->check_menu_active;
                                }
                            }

                            if(@$page == $check_menu_active_sub) {
                                $active_sub = 'active';
                            }

                            if($menu_sub_val->langapp) {//ชื่อเมนู
                                $name_val_sub = langapp($menu_sub_val->langapp);
                            }




                            $menu_sub_html .= '<li class="'. $active_sub .'">
                                                    <a href="'. $url_sub .'">
                                                        <i class="'.@$menu_sub_val->icon.'"><b class="bg-info"></b></i>
                                                        <span>'.$name_val_sub.'</span>
                                                    </a>
                                                </li>';
                        }

                    }
                        if($menu_val->is_have_sub == 1) {//ถ้ามี sub menu
                            $is_have_sub = '<a href="'. $url .'" class="'. $active_sub .'">
                                                <i class="'.@$menu_val->icon.'"><b class="bg-info"></b></i>
                                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                                <i class="fas fa-angle-up text-active"></i></span>
                                                <span> '.$name_val.' </span>
                                            </a>
                                        <ul class="nav lt">'.$menu_sub_html.'</ul>
                                        ';
                        } else {
                            $is_have_sub = '<a href="'. $url .'" class="'. $active .'">
                                                <i class="'.@$menu_val->icon.'"><b class="bg-info"></b></i>
                                                    
                                                <span> '.$name_val.' </span>
                                            </a>';
                        }
                            

                    
           

                    $menu_html .=    '<li class="'. $active .'">
                                        '.$is_have_sub.'
                                      </li>';

                }

                $html_all .= $menu_html;
            }

        }

       

    $html_all .= '
            </ul>
        </nav>';

    echo $html_all;
}


function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
}

function encrypt_decrypt_version($action, $string, $ip, $mac) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'secret-key-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
    $secret_iv = 'secret-iv-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
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

function get_ip(){
    return '192.168.2.1';
}

function get_mac(){
    return 'fe80::8c98:dba3:69f3:2ecb%6';
}

function setEnvironmentValue($envKey, $envValue)
{
    $envFile = app()->environmentFilePath();
    $str = file_get_contents($envFile);

    $str .= "\n"; // In case the searched variable is in the last line without \n
    $keyPosition = strpos($str, "{$envKey}=");
    $endOfLinePosition = strpos($str, PHP_EOL, $keyPosition);
    $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
    $str = substr($str, 0, -1);

    $fp = fopen($envFile, 'w');
    fwrite($fp, $str);
    fclose($fp);
}

function get_word_leak_compromise($val, $type)
{
    $html = '';
    if($type == 'data_leak') {
        if($val == 'social') {
            $html = 'PUBLIC';
        } else if($val == 'darkweb_public') {
            $html = 'DARK WEB';
        }
        
    } else if($type == 'compromise') {
        if($val == 'compromise') {
            $html = 'PUBLIC';
        } else if($val == 'darkweb') {
            $html = 'DARK WEB';
        } else if($val == 'webserver') {
            $html = 'WEB SERVER';
        }
        
    } else {
        $html = '';
    }
    
    return $html;
}


?>
