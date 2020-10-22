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
