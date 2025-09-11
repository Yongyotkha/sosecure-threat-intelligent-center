<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\MailProgressController;
use App\Http\Controllers\MISPFeedController;

// Route::get('/', 'Welcome@index')->middleware(['auth'])->name('index');
// Route::get('/', 'Welcome@index')->name('index_salepage');

// Route::get('/clientlogin', 'Auth\LoginController@login');

Route::get('/clientlogin', 'Welcome@clientlogin');
Route::get('/test', 'Welcome@test');

Auth::routes(['verify' => true, 'register' => settingEnabled('allow_client_registration')]);

Route::get('/redirect/{provider}', 'SocialAuthController@redirectToProvider');
Route::get('/callback/{provider}', 'SocialAuthController@handleProviderCallback');

Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/backlogin', 'Auth\LoginController@backlogin')->name('backlogin');

Route::get('error/403', 'ErrorController@fourZeroThree')->name('error.403');

Route::get('setlocale/{locale}', 'LocaleController@setLang')->name('setLanguage');
Route::get('setview/{type}/{view}', 'ViewTypeController@setView')->name('set.view.type');
Route::get('setcalendar/{view}', 'ViewTypeController@activeCalendar')->name('set.calendar.type');

Route::post('license/verify', 'LicenseController@verify')->name('app.license');

Route::group(['middleware' => config('updater.middleware')], function () {
    Route::get('update/check', 'UpdaterController@check');
    Route::get('updater/version', 'UpdaterController@getCurrentVersion');
    Route::get('update/run', 'UpdaterController@update');
});

Route::get('invite', 'InviteController@invite')->name('invite');
Route::get('tell-friend', 'TellFriendController@share')->name('tell.friend');
Route::post('tell-friend', 'TellFriendController@invite')->name('invite.friend');
Route::post('invite', 'InviteController@process')->name('invite.process');
Route::post('bulk-archive', 'ArchiveController@archive')->name('archive.process');
Route::get('accept/{token}', 'InviteController@accept')->name('invite.accept');
Route::post('accepted', 'InviteController@accepted')->name('invite.accepted');

Route::post('auth/2fa', 'TwoFactorAuthController@authenticate')->name('2fa.auth')->middleware('2fa');
Route::post('/system/reset/2fa', 'TwoFactorAuthController@reset_2fa');
Route::get('reset/2fa', 'TwoFactorAuthController@reset')->name('2fa.reset');

Route::get('cron/schedule/{token}', 'ScheduleController@run')->name('artisan.schedule')->middleware('demo');

Route::any('search', 'SearchController@search')->name('search.app');
Route::any('newSearch', 'SearchController@searchPage')->name('search.page');
Route::post('newSearchAPI', 'SearchController@searchAPI')->name('search.newSearchAPI');
Route::any('search/{mode}', 'SearchController@search')->name('search.lookup');
Route::post('loadSearchAPI', 'SearchController@loadSearchAPI');

Route::get('support', 'SupportController@ticket')->name('support.ticket')->middleware('cors');
Route::post('stripe/webhook', '\Laravel\Cashier\Http\Controllers\WebhookController@handleWebhook');



Route::get('phpinfo', function () {
    $dss = 15;
    if ($dss = 15) {
        print_r($dd);
    }
});

Route::get('emailtest', 'Welcome@emailtest');

Route::get('phishing_detection/login-1', function () {
    return view('demo_login');
})->name('phishing_detection_login-1');

Route::get('phishing_detection/login-2', function () {
    return view('demo_login');
})->name('phishing_detection_login-2');

Route::get('/feed/indicators.csv', 'SearchController@sslBlacklist')->name('search.sslBlacklist');

Route::get('/feeds/manifest.json', [MISPFeedController::class, 'generateDirectoryManifest']);
Route::get('/feeds/{uuid}.json', [MISPFeedController::class, 'generateJsonFeed']);
Route::get('/feeds/{uuid}/manifest.json', [MISPFeedController::class, 'generateManifest']);
// // Route::get('/feeds/list', [MISPFeedController::class, 'listFeeds']);
// Route::get('/feeds/list', [MISPFeedController::class, 'listFeeds'])
//     ->middleware(['api.token']);

Route::prefix('feeds')
    ->middleware(['throttle:300,1', 'api.token']) 
    ->group(function () {
        // Route::get('manifest.json',           [MISPFeedController::class, 'generateDirectoryManifest'])->name('feeds.manifest');
        // Route::get('{uuid}.json',             [MISPFeedController::class, 'generateJsonFeed'])->name('feeds.event');
        // Route::get('{uuid}/manifest.json',    [MISPFeedController::class, 'generateManifest'])->name('feeds.event.manifest');
        Route::get('list',                    [MISPFeedController::class, 'listFeeds'])->name('feeds.list');
    });



Route::get('/preview-defacement-alert', function () {
    $w = (object)[
        'name' => 'John Doe',
        'url' => 'example.com',
        'status_val' => 'High',
        'datetime' => now()->format('Y-m-d H:i:s'),
        'image_last' => '',
        'user_agent' => 'cloudflare',
        'site_id' => '85',
        'created_at' => now()->format('Y-m-d H:i:s'),
        'last_online' => now()->format('Y-m-d H:i:s'),
        'updated_at' => now()->format('Y-m-d H:i:s'),
        'hash' => 'Test_hash020202023202012012',
        'filesize_new' => '162424',
        'element' => 'Test_element',
        'code' => 'c96e6741-8915-4c6a-8de2-2eb6a52fa91f',
        'id' => '186',
        'image_last' => '/images/webdefacment_mages/77/69/77_69_Defacement_Now.png',
        'hashper' => '100',
        'filesizeper' => '20',
        'elementper' => '60',
        'imageper' => '30',
        'blacklistper' => '0',
        'domain' => 'example.com',
    ];

    return view('emails.defacement_alert', [
        'w' => $w,
    ]);
});

Route::get('/preview-news', function () {
    $news = [
        'public_date' => now()->format('Y-m-d H:i:s'),
        'title_th'    => 'Test_title_th',
        'detail_th'   => '    BitLockMove มี 2 โหมดหลักคือโหมด Enumeration ที่ใช้ API ที่ไม่เป็นทางการจาก winsta.dll เพื่อสำรวจ Session ผู้ใช้งานจากระยะไกลโดยไม่ต้องเปิด Remote Desktop และโหมด Attack ที่เริ่มจากการเปิดใช้ Remote Registry บนเครื่องเป้าหมาย แล้วสร้างคีย์ CLSID ภายใต้ InProcServer32 เพื่อบังคับให้ผู้ใช้งานเรียกใช้ BDEUILauncher เมื่อกระบวนการของ BitLocker ถูกเรียกจะทำการโหลด DLL ของผู้ไม่ประสงค์ดีแทน Component จากนั้นรันโค้ด ซึ่งยากต่อการถูกตรวจจับ

    เทคนิคดังกล่าวถึงจะมีความซับซ้อน แต่องค์กรสามารถตรวจจับได้ในหลายจุด ตัวอย่างเช่น การเปิดใช้ Remote Registry (Event ID 7040), การเปลี่ยนแปลง Registry (Event ID 4657, 4660, 4663), และการโหลด DLL หรือรัน Process ที่ผิดปกติ ตัวอย่างเช่น BaaUpdate.exe หรือ BdeUISrv.exe จาก svchost.exe นอกจากนี้ยังควรตรวจสอบการเรียกใช้ API ที่ไม่เป็นทางการ

จาก winsta.dll และตั้งกฎ SIGMA เพื่อแจ้งเตือนพฤติกรรมที่น่าสงสัย

ความเสี่ยงและผลกระทบจากเหตุการณ์นี้ <br>

- Privilege Escalation: หากผู้ใช้งานที่ถูกเจาะระบบมีสิทธิ์สูง ตัวอย่างเช่น Domain Admin โค้ดอันตรายจะถูกรันภายใต้สิทธิ์นั้น <br>

- Lateral Movement: ผู้ไม่ประสงค์ดีสามารถขยายการเข้าถึงไปยังเครื่องอื่นในเครือข่ายได้<br>

- การหลีกเลี่ยงระบบตรวจจับ: ใช้ส่วนประกอบของ Windows ที่ถูกต้อง ทำให้หลบเลี่ยง EDR/AV ได้ง่าย<br>

พฤติกรรมที่ควรเฝ้าระวัง (Indicators of Compromise / Anomalies)<br>
- Step one',
        'get_cate'    => ['get_cate_name' => ['name' => 'Cybernews']],
        'code' => '167',
    ];

    $newsObj = (object) $news;
    // หรือ: $newsObj = new \Illuminate\Support\Fluent($news);

    return view('emails.template_email_new_news', [
        'news' => ['news' => $newsObj],
    ]);
});

Route::get('/preview-hash', function () {
    $id = [
        'id'=> '167',
    ];

    return view('components.hash-display', [
        'id' => ['id' => $id],
    ]);
});
