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
use Modules\Users\Entities\user_menu_permission;
use Modules\Users\Entities\user_menu_sub_permission;
use Modules\SiteSettings\Entities\site_menu_permission;
use Modules\SiteSettings\Entities\site_menu_sub_permission;
use App\Menu;
use App\Menu_sub;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Nexmo\Message\Shortcode\Alert;

define("TYPE_WEB", 'center'); //center , client
define("PAGINATE_NUM", 10);

define("DB_MONGO_01", 'mongodb://rwuser:$0$ecure-ABCDEFG@172.16.11.251:8635,172.16.11.171:8635/test?authSource=admin&replicaSet=replica');

define("PATH_MY_IP_TF", 'http://127.0.0.2');

define("PATH_CENTER_IP_TF", 'http://127.0.0.2');

function get_role_custom()
{
    $superadmin = 0;
    $client = 0;
    $site_support = 0;
    $site_admin = 0;
    $site_client = 0;

    $site_id_active = SiteSettings::select('id')->where('active', 1)->whereNull('deleted_at')->get()->pluck('id')->toArray();

    if (Auth::check()) {
        $model_has_roles = model_has_roles::where('model_id', @Auth::user()->id)->first();
        $role_id = @$model_has_roles->role_id;
        // dd($role_id);

        $site_id_arr = UserSite::select('site_id')->whereIn('site_id', $site_id_active)->where('user_id', @Auth::user()->id)->get();

        $SiteSettings = '';
        if (@$role_id == 1) { //if admin  | Auth::user()->hasRole('admin')
            // dd(777);
            $superadmin = 1;
            $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)->get();
        } else if (@$role_id == 2) { //if notAdmin  client
            $client = 1;
            if (@$site_id_arr) {
                $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                    ->whereIn('id', $site_id_arr) //['49', '56']
                    ->get();
            }
        } else if (@$role_id == 4) { //admin site
            $site_admin = 1;
            if (@$site_id_arr) {
                $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                    ->whereIn('id', $site_id_arr) //['49', '56']
                    ->get();
            }
        } else if (@$role_id == 5) { //client site
            $site_client = 1;
            if (@$site_id_arr) {
                $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                    ->whereIn('id', $site_id_arr) //['49', '56']
                    ->get();
            }
        } else if (@$role_id == 6) { //support site
            $site_support = 1;
            if (@$site_id_arr) {
                $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                    ->whereIn('id', $site_id_arr) //['49', '56']
                    ->get();
            }
        } else { //center client
            $client = 1;
            if (@$site_id_arr) {
                $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                    ->whereIn('id', $site_id_arr) //['49', '56']
                    ->get();
            }
        }
    }

    // $data = [
    //     "superadmin" => $superadmin,//center
    //     "client" => $client,//center
    //     "site_support" => $site_support,//site
    //     "site_admin" => $site_admin,//site
    //     "site_client" => $site_client,//site
    //     "site_id_arr" => $site_id_arr,//center,site
    //     "SiteSettings" => $SiteSettings//center,site
    // ];


    // if(Gate::check('dashboard')) {
    //    var_dump(123);
    //    exit();
    // }



    $data['superadmin'] = $superadmin; //center
    $data['client'] = $client; //center
    $data['site_support'] = $site_support; //center
    $data['site_admin'] = $site_admin; //center
    $data['site_client'] = $site_client; //center
    $data['site_id_arr'] = $site_id_arr; //center
    $data['SiteSettings'] = $SiteSettings; //center
    return $data;
}


function check_role_custom()
{
    $arr = [];
    $arr['dashboard'] = 0;
    $arr['assets'] = 0;
    $arr['news'] = 0;
    $arr['indicators'] = 0;
    $arr['vulnerabilities'] = 0;
    $arr['compromised'] = 0;
    $arr['data_leak'] = 0;
    $arr['web_defacement'] = 0;
    $arr['manage_users'] = 0;
    $arr['settings'] = 0;
    $arr['settings_categorys'] = 0;
    $arr['settings_sites'] = 0;
    $arr['settings_assets'] = 0;
    $arr['settings_rss'] = 0;
    $arr['settings_general'] = 0;
    $arr['monitoring'] = 0;
    $arr['monitoring_batch'] = 0;
    $arr['application_logs'] = 0;
    $arr['send_logs'] = 0;
    $arr['role_center'] = 0;
    $arr['role_site'] = 0;
    if (Auth::check()) {
        if (TYPE_WEB == 'center') {
            if (Gate::check('dashboard')) {
                // var_dump(123);
                // exit();
                $arr['dashboard'] = 1;
            }
            if (Gate::check('assets')) {
                $arr['assets'] = 1;
            }
            if (Gate::check('news')) {
                $arr['news'] = 1;
            }
            if (Gate::check('indicators')) {
                $arr['indicators'] = 1;
            }
            if (Gate::check('vulnerabilities')) {
                $arr['vulnerabilities'] = 1;
            }
            if (Gate::check('compromised')) {
                $arr['compromised'] = 1;
            }
            if (Gate::check('data_leak')) {
                $arr['data_leak'] = 1;
            }
            if (Gate::check('web_defacement')) {
                $arr['web_defacement'] = 1;
            }
            if (Gate::check('manage_users')) {
                $arr['manage_users'] = 1;
            }
            if (Gate::check('settings')) {
                $arr['settings'] = 1;
            }
            if (Gate::check('settings_categorys')) {
                $arr['settings_categorys'] = 1;
            }
            if (Gate::check('settings_sites')) {
                $arr['settings_sites'] = 1;
            }
            if (Gate::check('settings_assets')) {
                $arr['settings_assets'] = 1;
            }
            if (Gate::check('settings_rss')) {
                $arr['settings_rss'] = 1;
            }
            if (Gate::check('settings_general')) {
                $arr['settings_general'] = 1;
            }
            if (Gate::check('monitoring')) {
                $arr['monitoring'] = 1;
            }
            if (Gate::check('monitoring_batch')) {
                $arr['monitoring_batch'] = 1;
            }
            if (Gate::check('application_logs')) {
                $arr['application_logs'] = 1;
            }
            if (Gate::check('send_logs')) {
                $arr['send_logs'] = 1;
            }
            if (Gate::check('role_center')) {
                $arr['role_center'] = 1;
            }
            if (Gate::check('role_site')) {
                $arr['role_site'] = 1;
            }
        } else if (TYPE_WEB == 'client') {
            $model_has_roles = model_has_roles::where('model_id', @Auth::user()->id)->first();
            $role_id = @$model_has_roles->role_id;
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            $result_menu_permission = site_menu_permission::select('menu_code')->whereIn("site_id", @$site_id_arr)->where("deleted_at", null)->get()->pluck('menu_code')->toArray();
            $result_menu_sub_permission = site_menu_sub_permission::select('menu_sub_code')->whereIn("site_id", @$site_id_arr)->where("deleted_at", null)->get()->pluck('menu_sub_code')->toArray();
            $result_user_menu_permission = user_menu_permission::select('menu_code')->whereIn("site_id", @$site_id_arr)->where("user_id", @Auth::user()->id)->where("deleted_at", null)->whereIn('menu_code', $result_menu_permission)->get()->pluck('menu_code')->toArray();
            $result_user_menu_sub_permission = user_menu_sub_permission::select('menu_sub_code')->where("site_id", @$site_id_arr)->where("user_id", @Auth::user()->id)->where("deleted_at", null)->whereIn('menu_sub_code', $result_menu_sub_permission)->get()->pluck('menu_sub_code')->toArray();

            if ($role_id == 6) {
                if (in_array("45e03854-cc2c-485e-9ac0-81b0350bdec0", $result_menu_permission)) {
                    $arr['dashboard'] = 1;
                }
                if (in_array("556e3907-8b3f-4b32-9db4-a25ac9fbab06", $result_menu_permission)) {
                    $arr['assets'] = 1;
                }
                if (in_array("0a120651-fdfd-43e6-8369-bad5a713b8d5", $result_menu_permission)) {
                    $arr['news'] = 1;
                }
                if (in_array("89ecb70a-e693-4772-9789-a29666af3cd9", $result_menu_permission)) {
                    $arr['indicators'] = 1;
                }
                if (in_array("e37e3315-380e-4ba7-a4ad-0bcec9749e9a", $result_menu_permission)) {
                    $arr['vulnerabilities'] = 1;
                }
                if (in_array("854a1e60-9abf-4263-a187-60aec8cd4fb1", $result_menu_permission)) {
                    $arr['compromised'] = 1;
                }
                if (in_array("79b362a5-3789-445a-bd6c-846393ffd19d", $result_menu_permission)) {
                    $arr['data_leak'] = 1;
                }
                if (in_array("64f1c2af-94fa-460a-8a90-b9fd601193f9", $result_menu_permission)) {
                    $arr['web_defacement'] = 1;
                }
                if (in_array("6f580f26-8d46-452e-a84e-211b3f79268a", $result_menu_permission)) {
                    $arr['manage_users'] = 1;
                }
                if (in_array("8df567fb-33c3-4185-b1cd-ed618fe6ac29", $result_menu_permission)) {
                    $arr['settings'] = 1;
                }
                if (in_array("54735dbb-6987-4a7b-8aa3-121538aaad50", $result_menu_permission)) {
                    $arr['monitoring'] = 1;
                }
            } else {
                if (in_array("45e03854-cc2c-485e-9ac0-81b0350bdec0", $result_user_menu_permission)) {
                    $arr['dashboard'] = 1;
                }
                if (in_array("556e3907-8b3f-4b32-9db4-a25ac9fbab06", $result_user_menu_permission)) {
                    $arr['assets'] = 1;
                }
                if (in_array("0a120651-fdfd-43e6-8369-bad5a713b8d5", $result_user_menu_permission)) {
                    $arr['news'] = 1;
                }
                if (in_array("89ecb70a-e693-4772-9789-a29666af3cd9", $result_user_menu_permission)) {
                    $arr['indicators'] = 1;
                }
                if (in_array("e37e3315-380e-4ba7-a4ad-0bcec9749e9a", $result_user_menu_permission)) {
                    $arr['vulnerabilities'] = 1;
                }
                if (in_array("854a1e60-9abf-4263-a187-60aec8cd4fb1", $result_user_menu_permission)) {
                    $arr['compromised'] = 1;
                }
                if (in_array("79b362a5-3789-445a-bd6c-846393ffd19d", $result_user_menu_permission)) {
                    $arr['data_leak'] = 1;
                }
                if (in_array("64f1c2af-94fa-460a-8a90-b9fd601193f9", $result_user_menu_permission)) {
                    $arr['web_defacement'] = 1;
                }
                if (in_array("6f580f26-8d46-452e-a84e-211b3f79268a", $result_user_menu_permission)) {
                    $arr['manage_users'] = 1;
                }
                if (in_array("8df567fb-33c3-4185-b1cd-ed618fe6ac29", $result_user_menu_permission)) {
                    $arr['settings'] = 1;
                }
                if (in_array("54735dbb-6987-4a7b-8aa3-121538aaad50", $result_user_menu_permission)) {
                    $arr['monitoring'] = 1;
                }
            }
        }
    }
    return $arr;
}

function check_permission_site_custom($user_id)
{
    $arr = [];
    $arr['dashboard'] = 0;
    $arr['assets'] = 0;
    $arr['news'] = 0;
    $arr['indicators'] = 0;
    $arr['vulnerabilities'] = 0;
    $arr['compromised'] = 0;
    $arr['data_leak'] = 0;
    $arr['web_defacement'] = 0;
    $arr['manage_users'] = 0;
    $arr['settings'] = 0;
    $arr['settings_categorys'] = 0;
    $arr['settings_sites'] = 0;
    $arr['settings_assets'] = 0;
    $arr['settings_rss'] = 0;
    $arr['settings_general'] = 0;
    $arr['monitoring'] = 0;
    $arr['monitoring_batch'] = 0;
    $arr['application_logs'] = 0;
    $arr['send_logs'] = 0;
    $arr['role_center'] = 0;
    $arr['role_site'] = 0;

    $model_has_roles = model_has_roles::where('model_id', @$user_id)->first();
    $role_id = @$model_has_roles->role_id;
    $site_id_arr = UserSite::select('site_id')->where('user_id', @$user_id)->get();
    $result_menu_permission = site_menu_permission::select('menu_code')->whereIn("site_id", @$site_id_arr)->where("deleted_at", null)->get()->pluck('menu_code')->toArray();
    $result_menu_sub_permission = site_menu_sub_permission::select('menu_sub_code')->whereIn("site_id", @$site_id_arr)->where("deleted_at", null)->get()->pluck('menu_sub_code')->toArray();
    $result_user_menu_permission = user_menu_permission::select('menu_code')->whereIn("site_id", @$site_id_arr)->where("user_id", @$user_id)->where("deleted_at", null)->whereIn('menu_code', $result_menu_permission)->get()->pluck('menu_code')->toArray();
    $result_user_menu_sub_permission = user_menu_sub_permission::select('menu_sub_code')->where("site_id", @$site_id_arr)->where("user_id", @$user_id)->where("deleted_at", null)->whereIn('menu_sub_code', $result_menu_sub_permission)->get()->pluck('menu_sub_code')->toArray();

    if ($role_id == 6) {
        if (in_array("45e03854-cc2c-485e-9ac0-81b0350bdec0", $result_menu_permission)) {
            $arr['dashboard'] = 1;
        }
        if (in_array("556e3907-8b3f-4b32-9db4-a25ac9fbab06", $result_menu_permission)) {
            $arr['assets'] = 1;
        }
        if (in_array("0a120651-fdfd-43e6-8369-bad5a713b8d5", $result_menu_permission)) {
            $arr['news'] = 1;
        }
        if (in_array("89ecb70a-e693-4772-9789-a29666af3cd9", $result_menu_permission)) {
            $arr['indicators'] = 1;
        }
        if (in_array("e37e3315-380e-4ba7-a4ad-0bcec9749e9a", $result_menu_permission)) {
            $arr['vulnerabilities'] = 1;
        }
        if (in_array("854a1e60-9abf-4263-a187-60aec8cd4fb1", $result_menu_permission)) {
            $arr['compromised'] = 1;
        }
        if (in_array("79b362a5-3789-445a-bd6c-846393ffd19d", $result_menu_permission)) {
            $arr['data_leak'] = 1;
        }
        if (in_array("64f1c2af-94fa-460a-8a90-b9fd601193f9", $result_menu_permission)) {
            $arr['web_defacement'] = 1;
        }
        if (in_array("6f580f26-8d46-452e-a84e-211b3f79268a", $result_menu_permission)) {
            $arr['manage_users'] = 1;
        }
        if (in_array("8df567fb-33c3-4185-b1cd-ed618fe6ac29", $result_menu_permission)) {
            $arr['settings'] = 1;
        }
        if (in_array("54735dbb-6987-4a7b-8aa3-121538aaad50", $result_menu_permission)) {
            $arr['monitoring'] = 1;
        }
    } else {
        if (in_array("45e03854-cc2c-485e-9ac0-81b0350bdec0", $result_user_menu_permission)) {
            $arr['dashboard'] = 1;
        }
        if (in_array("556e3907-8b3f-4b32-9db4-a25ac9fbab06", $result_user_menu_permission)) {
            $arr['assets'] = 1;
        }
        if (in_array("0a120651-fdfd-43e6-8369-bad5a713b8d5", $result_user_menu_permission)) {
            $arr['news'] = 1;
        }
        if (in_array("89ecb70a-e693-4772-9789-a29666af3cd9", $result_user_menu_permission)) {
            $arr['indicators'] = 1;
        }
        if (in_array("e37e3315-380e-4ba7-a4ad-0bcec9749e9a", $result_user_menu_permission)) {
            $arr['vulnerabilities'] = 1;
        }
        if (in_array("854a1e60-9abf-4263-a187-60aec8cd4fb1", $result_user_menu_permission)) {
            $arr['compromised'] = 1;
        }
        if (in_array("79b362a5-3789-445a-bd6c-846393ffd19d", $result_user_menu_permission)) {
            $arr['data_leak'] = 1;
        }
        if (in_array("64f1c2af-94fa-460a-8a90-b9fd601193f9", $result_user_menu_permission)) {
            $arr['web_defacement'] = 1;
        }
        if (in_array("6f580f26-8d46-452e-a84e-211b3f79268a", $result_user_menu_permission)) {
            $arr['manage_users'] = 1;
        }
        if (in_array("8df567fb-33c3-4185-b1cd-ed618fe6ac29", $result_user_menu_permission)) {
            $arr['settings'] = 1;
        }
        if (in_array("54735dbb-6987-4a7b-8aa3-121538aaad50", $result_user_menu_permission)) {
            $arr['monitoring'] = 1;
        }
    }


    return $arr;
}


function check_permission_site_custom_api($user_id, $menu)
{
    try {
        $arr = [];
        $arr['dashboard'] = 0;
        $arr['assets'] = 0;
        $arr['news'] = 0;
        $arr['indicators'] = 0;
        $arr['vulnerabilities'] = 0;
        $arr['compromised'] = 0;
        $arr['data_leak'] = 0;
        $arr['brand_abuse'] = 0;
        $arr['web_defacement'] = 0;
        $arr['manage_users'] = 0;
        $arr['settings'] = 0;
        $arr['settings_categorys'] = 0;
        $arr['settings_sites'] = 0;
        $arr['settings_assets'] = 0;
        $arr['settings_rss'] = 0;
        $arr['settings_general'] = 0;
        $arr['monitoring'] = 0;
        $arr['monitoring_batch'] = 0;
        $arr['application_logs'] = 0;
        $arr['send_logs'] = 0;
        $arr['role_center'] = 0;
        $arr['role_site'] = 0;
        $arr['phishing_detection'] = 0;

        $model_has_roles = model_has_roles::where('model_id', @$user_id)->first();
        $role_id = @$model_has_roles->role_id;
        $site_id_arr = UserSite::select('site_id')->where('user_id', @$user_id)->get();
        $result_menu_permission = site_menu_permission::select('menu_code')->whereIn("site_id", @$site_id_arr)->where("deleted_at", null)->get()->pluck('menu_code')->toArray();
        $result_menu_sub_permission = site_menu_sub_permission::select('menu_sub_code')->whereIn("site_id", @$site_id_arr)->where("deleted_at", null)->get()->pluck('menu_sub_code')->toArray();
        $result_user_menu_permission = user_menu_permission::select('menu_code')->whereIn("site_id", @$site_id_arr)->where("user_id", @$user_id)->where("deleted_at", null)->whereIn('menu_code', $result_menu_permission)->get()->pluck('menu_code')->toArray();
        // $result_user_menu_sub_permission = user_menu_sub_permission::select('menu_sub_code')->where("site_id", $site_id_arr)->where("user_id", $user_id)->where("deleted_at", null)->whereIn('menu_sub_code',$result_menu_sub_permission)->get()->pluck('menu_sub_code')->toArray();

        if ($role_id == 6) {
            if (in_array("45e03854-cc2c-485e-9ac0-81b0350bdec0", $result_menu_permission)) {
                $arr['dashboard'] = 1;
            }
            if (in_array("556e3907-8b3f-4b32-9db4-a25ac9fbab06", $result_menu_permission)) {
                $arr['assets'] = 1;
            }
            if (in_array("0a120651-fdfd-43e6-8369-bad5a713b8d5", $result_menu_permission)) {
                $arr['news'] = 1;
            }
            if (in_array("89ecb70a-e693-4772-9789-a29666af3cd9", $result_menu_permission)) {
                $arr['indicators'] = 1;
            }
            if (in_array("e37e3315-380e-4ba7-a4ad-0bcec9749e9a", $result_menu_permission)) {
                $arr['vulnerabilities'] = 1;
            }
            if (in_array("854a1e60-9abf-4263-a187-60aec8cd4fb1", $result_menu_permission)) {
                $arr['compromised'] = 1;
            }
            if (in_array("79b362a5-3789-445a-bd6c-846393ffd19d", $result_menu_permission)) {
                $arr['data_leak'] = 1;
            }
            if (in_array("64f1c2af-94fa-460a-8a90-b9fd601193f9", $result_menu_permission)) {
                $arr['web_defacement'] = 1;
            }
            if (in_array("6f580f26-8d46-452e-a84e-211b3f79268a", $result_menu_permission)) {
                $arr['manage_users'] = 1;
            }
            if (in_array("8df567fb-33c3-4185-b1cd-ed618fe6ac29", $result_menu_permission)) {
                $arr['settings'] = 1;
            }
            if (in_array("54735dbb-6987-4a7b-8aa3-121538aaad50", $result_menu_permission)) {
                $arr['monitoring'] = 1;
            }
            if (in_array("z9b362a5-3789-445a-bd6c-846393ffd19x", $result_menu_permission)) {
                $arr['brand_abuse'] = 1;
            }
            if (in_array("556e3907-5n3f-4b32-9db4-a25dv9fbab08", $result_menu_permission)) {
                $arr['phishing_detection'] = 1;
            }
        } else {
            if (in_array("45e03854-cc2c-485e-9ac0-81b0350bdec0", $result_user_menu_permission)) {
                $arr['dashboard'] = 1;
            }
            if (in_array("556e3907-8b3f-4b32-9db4-a25ac9fbab06", $result_user_menu_permission)) {
                $arr['assets'] = 1;
            }
            if (in_array("0a120651-fdfd-43e6-8369-bad5a713b8d5", $result_user_menu_permission)) {
                $arr['news'] = 1;
            }
            if (in_array("89ecb70a-e693-4772-9789-a29666af3cd9", $result_user_menu_permission)) {
                $arr['indicators'] = 1;
            }
            if (in_array("e37e3315-380e-4ba7-a4ad-0bcec9749e9a", $result_user_menu_permission)) {
                $arr['vulnerabilities'] = 1;
            }
            if (in_array("854a1e60-9abf-4263-a187-60aec8cd4fb1", $result_user_menu_permission)) {
                $arr['compromised'] = 1;
            }
            if (in_array("79b362a5-3789-445a-bd6c-846393ffd19d", $result_user_menu_permission)) {
                $arr['data_leak'] = 1;
            }
            if (in_array("64f1c2af-94fa-460a-8a90-b9fd601193f9", $result_user_menu_permission)) {
                $arr['web_defacement'] = 1;
            }
            if (in_array("6f580f26-8d46-452e-a84e-211b3f79268a", $result_user_menu_permission)) {
                $arr['manage_users'] = 1;
            }
            if (in_array("8df567fb-33c3-4185-b1cd-ed618fe6ac29", $result_user_menu_permission)) {
                $arr['settings'] = 1;
            }
            if (in_array("54735dbb-6987-4a7b-8aa3-121538aaad50", $result_user_menu_permission)) {
                $arr['monitoring'] = 1;
            }
            if (in_array("z9b362a5-3789-445a-bd6c-846393ffd19x", $result_user_menu_permission)) {
                $arr['brand_abuse'] = 1;
            }
            if (in_array("556e3907-5n3f-4b32-9db4-a25dv9fbab08", $result_user_menu_permission)) {
                $arr['phishing_detection'] = 1;
            }
        }


        return $arr[$menu];
    } catch (\Exception $e) {
        return 0;
    }
}

function check_goto_menu($Menu_permission_site = null, $type = 'center')
{
    $url = '';
    if (!empty($Menu_permission_site)) {
        $menu_id = $Menu_permission_site[0];
        // $menu_id = 3;
        $menu = Menu::where('id', $menu_id)->where('deleted_at', null)->where('active', 1)->orderBy('order', 'asc')->first();
        if ($menu) {
            if ($type == 'center') {
                if ($menu->type_url == 'site_url') {
                    $url = $menu->url;
                } else if ($menu->type_url == 'route') {
                    // $url = route($menu->url_client);
                    // $url_arr = explode("/",$url);
                    // $url = $url_arr[2];
                    $url_length = strlen(url(''));
                    $urlfull_length = strlen(route($menu->url));
                    // $url = stripos(route($menu->url_client), "/");
                    // $url = substr(route($menu->url_client),0,$url_arr[2]+1);
                    $url = substr(route($menu->url), $url_length, $urlfull_length);
                }
            } else {
                if ($menu->type_url == 'site_url') {
                    $url = $menu->url_client;
                } else if ($menu->type_url == 'route') {
                    // $url = route($menu->url_client);
                    // $url_arr = explode("/",$url);
                    // $url = $url_arr[2];
                    $url_length = strlen(url(''));
                    $urlfull_length = strlen(route($menu->url_client));
                    // $url = stripos(route($menu->url_client), "/");
                    // $url = substr(route($menu->url_client),0,$url_arr[2]+1);
                    $url = substr(route($menu->url_client), $url_length, $urlfull_length);
                }
            }
        }
    }
    return $url;
}


function gen_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
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
function get_name_scan_status($status_id, $badg = '')
{
    $html = '';
    if ($badg == 'badg') {
        if ($status_id == 0) {
            $html = '<span class="badge badge-secondary">waiting</span>';
        } else if ($status_id == 1) {
            $html = '<span class="badge badge-Warning" style="background-color: #ffc107;">scanning</span>';
        } else if ($status_id == 2) {
            $html = '<span class="badge badge-Warning" style="background-color: #ffc107;">scanning</span>';
        } else if ($status_id == 3) {
            $html = '<span class="badge badge-Success" style="background-color: #179f83;">complete</span>';
        }
    } else {
        if ($status_id == 0) {
            $html = 'waiting';
        } else if ($status_id == 1) {
            $html = 'scanning';
        } else if ($status_id == 2) {
            $html = 'scanning';
        } else if ($status_id == 3) {
            $html = 'complete';
        }
    }
    return $html;
}

function get_CVSS_Severity_status($num_val, $status_id, $badg = '')
{

    if (empty($status_id) || strtolower($status_id) == 'none') {
        $status_id = getSeverityFromScore($num_val);
    }

    $html = '';
    if ($badg == 'badg') {
        if (strtolower($status_id) == strtolower("CRITICAL")) {
            $html = '<span class="badge badge-secondary" style="background-color: #e64732;">' . $num_val . ' CRITICAL</span>';
        } else if (strtolower($status_id) == strtolower("HIGH")) {
            $html = '<span class="badge badge-Warning" style="background-color: #fcc838;">' . $num_val . ' HIGH</span>';
        } else if (strtolower($status_id) == strtolower("MEDIUM")) {
            $html = '<span class="badge badge-Warning" style="background-color: #f2ff15;color:#333;">' . $num_val . ' MEDIUM</span>';
        } else if (strtolower($status_id) == strtolower("LOW")) {
            $html = '<span class="badge badge-Success" style="background-color: #88ce4f;">' . $num_val . ' LOW</span>';
        } else if (strtolower($status_id) == strtolower("NONE")) {
            $html = '<span class="badge badge-Success" style="background-color: #00dcff;">' . $num_val . ' INFORMATION</span>';
        }
    } else {
        if (strtolower($status_id) == strtolower("CRITICAL")) {
            $html = 'CRITICAL';
        } else if (strtolower($status_id) == strtolower("HIGH")) {
            $html = 'HIGH';
        } else if (strtolower($status_id) == strtolower("MEDIUM")) {
            $html = 'MEDIUM';
        } else if (strtolower($status_id) == strtolower("LOW")) {
            $html = 'LOW';
        } else if (strtolower($status_id) == strtolower("NONE")) {
            $html = 'NONE';
        }
    }
    return $html;
}

function getSeverityFromScore($score)
{
    if ($score) {
        if ($score >= 9.0) return 'CRITICAL';
        if ($score >= 7.0) return 'HIGH';
        if ($score >= 4.0) return 'MEDIUM';
        if ($score > 0.0) return 'LOW';
    }
    return 'NONE';
}

function get_webdefacment_status($status_id, $color = '')
{
    $html = '';
    $key = strtolower((string) $status_id);

    if ($color == 'color') {
        if ($key === 'critical' || $key === 'high') {
            $html = '<span class="dot " style="background: #e64732 !important;"></span> High';
            if ($key === 'critical') {
                $html = '<span class="dot critical"></span> Critical';
            }
        } else if ($key === 'medium') {
            $html = '<span class="dot " style="background: #fcc838 !important;"></span> Medium';
        } else if ($key === 'normal') {
            $html = '<span class="dot low"></span> Normal';
        } else if ($key === 'down') {
            $html = '<span class="dot " style="background: #6c757d !important;"></span> Down';
        } else if ($key === 'error') {
            $html = '<span class="dot " style="background: #dc3545 !important;"></span> Error';
        } else if ($key === 'skipped') {
            $html = '<span class="dot " style="background: #adb5bd !important;"></span> Skipped';
        } else if ($key === 'none') {
            $html = '<span class="dot none"></span> None';
        }
    } else {
        if (in_array($key, ['critical', 'high', 'medium', 'normal', 'down', 'error', 'skipped', 'none'], true)) {
            $html = $status_id;
        }
    }
    return $html;
}


function generator_uuid()
{
    return Str::uuid()->toString();
}

function generate_site_key_login()
{
    $exists = true;
    while ($exists) {
        $code = str_random(30);
        $check = SiteSettings::where('site_key_login', $code)->count();
        if ($check == 0) {
            $exists = false;
        }
    }
    return $code;
}

function generate_site_key_code()
{
    $exists = true;
    while ($exists) {
        $code = str_random(30);
        $check = SiteSettings::where('site_key_code', $code)->count();
        if ($check == 0) {
            $exists = false;
        }
    }
    return $code;
}

function encrypt_decrypt($action, $string, $public_key, $ip, $mac)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'secret-key-' . $public_key . $ip . $mac;
    $secret_iv = 'secret-iv-' . $public_key . $ip . $mac;
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function utf8_strlen($str)
{
    $c = strlen($str);
    $l = 0;
    for ($i = 0; $i < $c; ++$i) {
        if ((ord($str[$i]) & 0xC0) != 0x80) {
            ++$l;
        }
    }
    return $l;
}

function explode_val($val, $type = null)
{
    $result = '';
    if ($val) {
        $val_arr = explode(",", $val);
        if ($val_arr) {
            $result .= '<div>';
            foreach ($val_arr as $tag) {
                if ($type == 'tags') {
                    $result .=  '<a href="' . route('indicators.link_tags', ['id' => $tag]) . '">' . $tag . '</a> ,';
                } else if ($type == 'groups') {
                    $result .=  '<a href="' . route('indicators.link_group', ['id' => $tag]) . '">' . $tag . '</a> ,';
                } else {
                    $result .=  '<a href="#">' . $tag . '</a> ,';
                }
            }
            $result .= '</div>';
            $result = rtrim($result, ',');
        }
    } else {
        $result = '';
    }
    return $result;
}


function check_publish($val)
{
    if ($val == 1) {
        $result = '<i class="fas fa-check"></i>';
    } else {
        $result = '';
    }
    return $result;
}
function check_last_status($val)
{
    if ($val == true) {
        $result = 'Modified';
    } else {
        $result = 'Created';
    }
    return $result;
}


function change_date_utc_to_thai($val)
{
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
    $return_date = date("Y-m-d H:i", strtotime($start));

    // echo $start;


    return $return_date;
}

function change_date_thai_tummai($val)
{

    if (is_string($val)) {
        $time = strtotime($val);
        $newformat = date('Y-m-d H:i', $time);

        $return_date = $newformat;
    } else {
        $tz = new \DateTimeZone('Asia/Bangkok');

        $date_start = $val->toDateTime();
        $date_start->setTimezone($tz);

        $re_date = $date_start->format(DATE_ATOM);
        $return_date = date("Y-m-d H:i", strtotime($re_date));
    }

    return $return_date;
}



function get_menu_html()
{
    $html_all = '';

    $html_all .= '
    <nav class="nav-primary hidden-xs">
    <ul class="nav">
    ';

    $menu = [];
    $menu_html = '';
    if (isset($_SESSION["menu"])) {
        // unset($_SESSION["lastname"]);
        $menu = $_SESSION["menu"];


        if ($menu) {
            foreach ($menu as $menu_val) {
                $active = '';
                $url = '#';
                $check_menu_active = '';
                $name_val = '';

                if ($menu_val->url) { // url
                    if ($menu_val->type_url == 'site_url') {
                        $url = site_url($menu_val->url);
                    } else if ($menu_val->type_url == 'route') {
                        $url = route($menu_val->url);
                    }
                }

                if ($menu_val->check_menu_active) { // check active
                    if ($menu_val->type_check_menu_active == 'langapp') {
                        $check_menu_active = langapp($menu_val->check_menu_active);
                    } else if ($menu_val->type_check_menu_active == '') {
                        $check_menu_active = $menu_val->check_menu_active;
                    }
                }

                if (@$page == $check_menu_active) {
                    $active = 'active';
                }

                if ($menu_val->langapp) { //ชื่อเมนู
                    $name_val = langapp($menu_val->langapp);
                }

                if (@$menu_val->get_menu_sub) {



                    $menu_sub_html = '';
                    foreach ($menu_val->get_menu_sub as $menu_sub_val) {


                        $active_sub = '';
                        $url_sub = '#';
                        $check_menu_active_sub = '';
                        $name_val_sub = '';


                        if ($menu_sub_val->url) { // url
                            if ($menu_sub_val->type_url == 'site_url') {
                                $url_sub = site_url($menu_sub_val->url);
                            } else if ($menu_sub_val->type_url == 'route') {
                                $url_sub = route($menu_sub_val->url);
                            }
                        }

                        if ($menu_sub_val->check_menu_active) { // check active
                            if ($menu_sub_val->type_check_menu_active == 'langapp') {
                                $check_menu_active_sub = langapp($menu_sub_val->check_menu_active);
                            } else if ($menu_sub_val->type_check_menu_active == '') {
                                $check_menu_active_sub = $menu_sub_val->check_menu_active;
                            }
                        }

                        if (@$page == $check_menu_active_sub) {
                            $active_sub = 'active';
                        }

                        if ($menu_sub_val->langapp) { //ชื่อเมนู
                            $name_val_sub = langapp($menu_sub_val->langapp);
                        }




                        $menu_sub_html .= '<li class="' . $active_sub . '">
                                                    <a href="' . $url_sub . '">
                                                        <i class="' . @$menu_sub_val->icon . '"><b class="bg-info"></b></i>
                                                        <span>' . $name_val_sub . '</span>
                                                    </a>
                                                </li>';
                    }
                }
                if ($menu_val->is_have_sub == 1) { //ถ้ามี sub menu
                    $is_have_sub = '<a href="' . $url . '" class="' . $active_sub . '">
                                                <i class="' . @$menu_val->icon . '"><b class="bg-info"></b></i>
                                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                                <i class="fas fa-angle-up text-active"></i></span>
                                                <span> ' . $name_val . ' </span>
                                            </a>
                                        <ul class="nav lt">' . $menu_sub_html . '</ul>
                                        ';
                } else {
                    $is_have_sub = '<a href="' . $url . '" class="' . $active . '">
                                                <i class="' . @$menu_val->icon . '"><b class="bg-info"></b></i>
                                                    
                                                <span> ' . $name_val . ' </span>
                                            </a>';
                }





                $menu_html .=    '<li class="' . $active . '">
                                        ' . $is_have_sub . '
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
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

function encrypt_decrypt_version($action, $string, $ip, $mac)
{
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = 'secret-key-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
    $secret_iv = 'secret-iv-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
    // hash
    $key = hash('sha256', $secret_key);

    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }
    return $output;
}

function get_ip()
{
    return '192.168.2.1';
}

function get_mac()
{
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
    // Log::info($val);
    $html = '';
    if ($type == 'data_leak') {
        if ($val == 'social') {
            $html = 'PUBLIC';
        } else if ($val == 'darkweb_public') {
            $html = 'DARK WEB';
        } else if ($val == 'credential' || $val == 'Credential') {
            $html = 'CREDENTIAL';
        }
    } else if ($type == 'brand_abuse') {
        if ($val == 'social') {
            $html = 'PUBLIC';
        } else if ($val == 'darkweb_public') {
            $html = 'DARK WEB';
        }
    } else if ($type == 'compromise') {
        if ($val == 'compromise') {
            $html = 'PUBLIC';
        } else if ($val == 'darkweb') {
            $html = 'DARK WEB';
        } else if ($val == 'webserver') {
            $html = 'WEB SERVER';
        } else if ($val == 'agent') {
            $html = 'AGENT';
        } else if ($val == 'network') {
            $html = 'NETWORK';
        }
    } else {
        $html = '';
    }

    return $html;
}

function check_permission403()
{
    Auth::logout();
    abort(403, 'Unauthorized action.');
}

/**
 * Remove leftover Chrome/Puppeteer temp profiles under the system temp dir.
 * Puppeteer creates puppeteer_dev_profile-* when userDataDir is not set;
 * scraper.js uses wdfm_chrome_* for controlled profiles.
 * Only removes dirs older than $maxAgeSeconds to avoid races with concurrent jobs.
 *
 * @param int $maxAgeSeconds
 * @param int $maxRemove Max directories to remove per call (keeps job latency bounded)
 * @return int Number of directories removed
 */
function cleanup_stale_chrome_tmp_profiles($maxAgeSeconds = 3600, $maxRemove = 50)
{
    $tmp = sys_get_temp_dir();
    $patterns = [
        $tmp . DIRECTORY_SEPARATOR . 'puppeteer_dev_profile-*',
        $tmp . DIRECTORY_SEPARATOR . 'wdfm_chrome_*',
    ];

    $now = time();
    $removed = 0;

    foreach ($patterns as $pattern) {
        $dirs = glob($pattern);
        if ($dirs === false) {
            continue;
        }

        foreach ($dirs as $dir) {
            if ($removed >= $maxRemove) {
                return $removed;
            }
            if (!is_dir($dir)) {
                continue;
            }

            $mtime = @filemtime($dir);
            if ($mtime === false || ($now - $mtime) < (int) $maxAgeSeconds) {
                continue;
            }

            if (remove_directory_recursive($dir)) {
                $removed++;
            }
        }
    }

    return $removed;
}

/**
 * Recursively delete a directory (best-effort).
 */
function remove_directory_recursive($dir)
{
    if (!is_dir($dir)) {
        return false;
    }

    // Prefer OS rm for large Chrome profile trees
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        @exec('rm -rf ' . escapeshellarg($dir));
        return !is_dir($dir);
    }

    try {
        $items = @scandir($dir);
        if ($items === false) {
            return false;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                remove_directory_recursive($path);
            } else {
                @unlink($path);
            }
        }
        return @rmdir($dir);
    } catch (\Throwable $e) {
        return false;
    }
}
