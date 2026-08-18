<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Bookmark;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocialRef;
use App\Entities\IndicatorSummaryYear;
use App\leak_socail_ref_temp;
use App\R_s_s_news;
use App\ReadCategories;
use App\ReadNews;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\OSType;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Users\Entities\UserSite;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Yajra\DataTables\DataTables;
use Modules\Users\Entities\User;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentStatDaily;
use App\Mail\DefacementAlertMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;
use App\Console\Commands\compareImages;

class ApiWebdefacementController extends ApiController
{

    public function web_defacement_load_card(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $keywords = $data['data']['keywords'];
                    $datatype = $data['data']['datatype'];
                    $site = $data['data']['site'];
                    $level = $data['data']['level'];
                    $search = $data['data']['search'];
                    $url = $data['data']['url'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $html = '';


                    $modal = WebdefacmentSetting::where("active", 1)->whereNull("deleted_at");

                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if (@$get_role_custom_first['superadmin'] == 1) {

                    } else if (@$get_role_custom_first['client'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    } else if (@$get_role_custom_first['site_support'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    } else if (@$get_role_custom_first['site_admin'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    } else if (@$get_role_custom_first['site_client'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }


                    if ($search == 1) {
                        if ($keywords) {
                            $modal = $modal->where('name', 'LIKE', '%' . $keywords . '%')
                                ->orWhere('url', 'LIKE', '%' . $keywords . '%');
                        }
                        if ($datatype) {
                            // dd($datatype);

                            $modal = $modal->whereIn('status_val', $datatype);
                            // dd($modal);
                        }


                    }

                    if ($site) {
                        $modal = $modal->where('site_id', '=', $site);

                    }

                    if ($level) {
                        if ($level == 'High') {
                            $modal = $modal->where('status_val', 'High');
                        } else if ($level == 'Normal') {
                            $modal = $modal->where('status_val', 'Normal');
                        } else if ($level == 'Medium') {
                            $modal = $modal->where('status_val', 'Medium');
                        }
                    }

                    $modal = $modal->get();


                    foreach ($modal as $key) {
                        $status = strtolower($key->status_val);

                        $data_chk = [];

                        $data_chk = WebdefacmentDataCheck::getData($key->id);


                        // 🧩 Generate card HTML
                        try {
                            $html .= view(
                                'webdefacement::components.webdefacement_card_client',
                                compact('key', 'url')
                            )->render();
                        } catch (\Throwable $th) {
                        }


                        // ⛳ Data for chart

                        $id[] = [
                            'id' => $key->id,
                            'detection_score_all' => $data_chk->percent_all ?? 0,
                            'hash_percent' => $data_chk->hash_percent ?? 0,
                            'filesize_percent' => $data_chk->filesize_percent ?? 0,
                            'element_percent' => $data_chk->element_percent ?? 0,
                            'image_percent' => $data_chk->image_percent ?? 0,
                            'blacklist_percent' => $data_chk->keyword_percent ?? 0,
                            'score' => ($data_chk->score ?? 0) * 100
                        ];


                        // 🔐 Data สำหรับ hash เทียบว่าเปลี่ยนไหม
                        $hash_data[] = [
                            'id' => $key->id,
                            'status' => $key->status_val,
                            'updated_at' => $key->updated_at,
                            'image' => $key->image_last,
                            'last_check' => $key->last_check
                        ];
                    }



                    $hash = md5(json_encode($hash_data));
                    $response = [
                        "html" => $html,
                        'id' => $id,
                        'hash' => $hash
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function web_defacement_detail(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $site_code = $data['data']['site_code'];
                    $code = $data['data']['code'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $WebdefacmentSetting = WebdefacmentSetting::where("code", $code)->where('deleted_at', null)->where('active', 1)->with('get_site')->with('get_webdefacment_data_original_detail')->with('get_webdefacment_data_check_detail')->with('get_webdefacment_data_log_detail');

                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if (@$get_role_custom_first['superadmin'] == 1) {

                    } else if (@$get_role_custom_first['client'] == 1) {
                        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                            ->whereIn('id', $site_id_arr)//['49', '56']
                            ->get();
                    } else if (@$get_role_custom_first['site_support'] == 1) {
                        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                            ->whereIn('id', $site_id_arr)//['49', '56']
                            ->get();
                    } else if (@$get_role_custom_first['site_admin'] == 1) {
                        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                            ->whereIn('id', $site_id_arr)//['49', '56']
                            ->get();
                    } else if (@$get_role_custom_first['site_client'] == 1) {
                        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                            ->whereIn('id', $site_id_arr)//['49', '56']
                            ->get();
                    }

                    if (count($site_id_arr) > 0) {
                        $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id', $site_id_arr);
                    }
                    $WebdefacmentSetting = $WebdefacmentSetting->first();

                    if (!$WebdefacmentSetting) {
                        $response = ['error' => 'Web Defacement not found', 'status_code' => '404', 'debug_payload' => $data['data'], 'source' => 'web_defacement_detail'];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    $response = [
                        "code" => @$code,
                        "webdefacement" => $WebdefacmentSetting,
                        "webdefacment_data_original" => @$WebdefacmentSetting->get_webdefacment_data_original_detail[0],
                        "webdefacment_data_check" => @$WebdefacmentSetting->get_webdefacment_data_check_detail[0],
                        "webdefacment_data_log" => @$WebdefacmentSetting->get_webdefacment_data_log_detail,
                        "site_code" => @$site_code,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function web_defacement_update_original(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['webdefacment_id'];
                    $command = 'app:WebDefacementUpdateOriginal';
                    $params = [
                        'webdefacment_id' => $webdefacment_id,
                    ];

                    //  Artisan::call($command,$params);
                    //  $result = Artisan::output();
                    // $result = $this->WebDefacementUpdateOriginal_handle($webdefacment_id);
                    $WebdefacmentDataOriginal_data = WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
                    $WebdefacmentDataOriginal_data->last_update = date("Y-m-d H:i:s");
                    $WebdefacmentDataOriginal_data->updated_at = date("Y-m-d H:i:s");
                    $WebdefacmentDataOriginal_data->save();
                    $response = [
                        "data" => $WebdefacmentDataOriginal_data,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    //------------------------------

    public function WebDefacementUpdateOriginal_handle($webdefacment_id)
    {
        $result = array();

        $WebdefacmentSetting_data = WebdefacmentSetting::find($webdefacment_id);
        if ($WebdefacmentSetting_data) {
            $WebdefacmentDataOriginal_data = WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
            if (!$WebdefacmentDataOriginal_data) {
                $WebdefacmentDataOriginal_save = new WebdefacmentDataOriginal;
                $WebdefacmentDataOriginal_save->webdefacment_setting_id = $webdefacment_id;
                $WebdefacmentDataOriginal_save->save();
                $WebdefacmentDataOriginal_data = WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
            }
            $url = $WebdefacmentSetting_data->url;
            $Keyword_check = array();




            if ($this->is_url($url)) {
                $result["Result"] = 1;
                $result["message"] = "";
                $result['hash_code'] = "";
                $result["file_size"] = 0;
                $result["all_element"] = 0;
                $result['image_url'] = "";
                $result["image_path_original"] = "";
                $response = $this->getHtml($url);
                if ($response['content'] === FALSE) {
                    $webContent = "";
                    $result["Result"] = 0;
                    $result["message"] = "Html not found";
                } else {
                    $webContent = $response['content'];

                    if ($WebdefacmentSetting_data->hash == 1) {
                        $hashMD5 = hash($this->hashingAlgorithm, $webContent);
                        $result['hash_code'] = $hashMD5;
                    }
                    if ($WebdefacmentSetting_data->filesize == 1) {
                        $file_size = strlen($webContent);//filesize
                        $result["file_size"] = $file_size;

                    }
                    if ($WebdefacmentSetting_data->element == 1) {
                        $allElement = preg_match_all('/<([^\/!][a-z1-9]*)/i', $webContent, $matches);
                        $result['all_element'] = (int) $allElement;

                    }
                    if ($WebdefacmentSetting_data->image_check == 1) {

                        $url_id = $WebdefacmentDataOriginal_data->url_id;
                        $site_id = $WebdefacmentSetting_data->site_id;
                        $delay = $WebdefacmentSetting_data->delay_screen_shot_val;
                        if (!$url_id) {
                            $url_id = rand(10, 100);
                        }

                        $result["image_url"] = "/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/image_original.png";
                        $path_include = base_path() . '/public/screenshot/use/DownloadImage.php';
                        include_once($path_include);
                        $downloadImg = new \DownloadImage();
                        $Path_image = base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/image_original.png";
                        $downloadImg->download($url, $Path_image, $delay);
                        $result["image_path_original_full"] = $Path_image;
                        $result["image_path_original"] = "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/image_original.png";
                        $result["url_id"] = $url_id;



                        $compareMachine = new compareImages($result["image_path_original_full"]);
                        $image1Hash = $compareMachine->getHasString();
                        $result["imageHash"] = $image1Hash;


                        $WebdefacmentDataOriginal_data->url_id = $result["url_id"];
                        $WebdefacmentDataOriginal_data->imageHash = $result["imageHash"];

                    }


                    $result_checkDomainHeaders = $this->checkDomainHeaders($url, 1);
                    $result_URL_404 = $this->URL_404($url);
                    $result["DomainHeaders"] = $result_checkDomainHeaders;
                    $result["Is_URL_404"] = $result_URL_404;
                    // $blackListFound = $this->trackKeyWords($webContent,$WebdefacmentSetting_data->blacklist_keyword_content,$Keyword_check);//keyword

                    //$result['blacklist'] = $blackListFound;



                    $WebdefacmentDataOriginal_data->hash = $result['hash_code'];
                    $WebdefacmentDataOriginal_data->filesize = $result['file_size'];
                    $WebdefacmentDataOriginal_data->element = $result['all_element'];
                    $WebdefacmentDataOriginal_data->image = $result['image_url'];
                    $WebdefacmentDataOriginal_data->part_image = $result['image_path_original'];
                    $WebdefacmentDataOriginal_data->last_update = date("Y-m-d H:i:s");
                    $WebdefacmentDataOriginal_data->updated_at = date("Y-m-d H:i:s");
                    $WebdefacmentDataOriginal_data->save();

                    $parse = \parse_url($url);
                    $host = $parse['host']; // prints 'google.com'

                    $WebdefacmentSetting_data->DomainHeaders = json_encode($result_checkDomainHeaders);
                    $WebdefacmentSetting_data->user_agent = $result_checkDomainHeaders['Server'];
                    $WebdefacmentSetting_data->domain = $host;
                    $WebdefacmentSetting_data->save();

                }





                $result["Result"] = 1;
                $result["message"] = "";



            } else {
                $result["Result"] = 0;
                $result["message"] = "The url is not formatted.";
            }
        } else {
            $result["Result"] = 0;
            $result["message"] = "No data found.";
        }
        $result_json_e = json_encode($result);
        return $result_json_e;

    }

    private function compareImage2($dirPath, $imgSourcePath, $imgComparePath)
    {
        $compareImage = array();
        // $imgSourcePath = PATH_CAPTURE_SCREEN.'/'.$imgSourcePath;
        // $imgComparePath = PATH_CAPTURE_SCREEN.'/'.$imgComparePath;

        $imgSourcePath = $dirPath . $imgSourcePath;
        $imgComparePath = $dirPath . $imgComparePath;

        // $imgSourcePath = "D:\ทดสอบรูปภาพ\\2017-06-14-02-43-01408692.jpg";
        //$imgComparePath =  "D:\ทดสอบรูปภาพ\\2017-06-15-20-37-12458813.jpg";

        $image1 = $imgSourcePath;
        $compareMachine = new compareImages($image1);
        $image1Hash = $compareMachine->getHasString();
        $compareImage["image1Hash"] = $image1Hash;


        $image2 = $imgComparePath;
        $image2Hash = $compareMachine->hasStringImage($image2);
        $diff = $compareMachine->compareHash($image2Hash);
        $compareImage["image2Hash"] = $image2Hash;
        $compareImage["diff"] = $diff;
        return $compareImage;
    }
    function is_url($uri)
    {
        if (preg_match('/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}' . '((:[0-9]{1,5})?\\/.*)?$/i', $uri)) {
            return $uri;
        } else {
            return false;
        }
    }
    private function getHtml($url)
    {

        // you can add some code to extract/parse response number from first header. 
        // For example from "HTTP/1.1 200 OK" string.

        $content = $this->get_dataa($url);

        return array(
            'content' => $content
        );
    }
    function get_dataa($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    private function trackKeyWords($webContent, $blacklistKeywords, $Keyword_checks)
    {
        $keywordOK = array();
        $keywords = explode(',', $blacklistKeywords);
        foreach ($keywords as $keyword) {
            $trackFound = $this->CheckKeyword($webContent, $keyword);
            if (count($trackFound) > 0) {
                if (count($Keyword_checks) > 0) {
                    $filtereds = array();
                    $rows = $Keyword_checks;
                    foreach ($rows as $index => $columns) {
                        foreach ($columns as $key => $value) {
                            if ($key == 'key' && $value == $keyword) {
                                $filtereds[] = $columns;
                            }
                        }
                    }

                    foreach ($trackFound as $position) {
                        //ถ้ามีให้หาตำแหน่ง

                        if (!in_array($position, array_column($filtereds, 'position'))) {
                            //ไม่มีอยู่ใน ignore
                            array_push($keywordOK, array("key" => $keyword, "position" => $position));
                        }
                    }

                } else {
                    foreach ($trackFound as $position) {
                        array_push($keywordOK, array("key" => $keyword, "position" => $position));

                    }
                }
            }
        }

        return $keywordOK;
    }

    function CheckKeyword($html, $needle)
    {
        $lastPos = 0;
        $positions = array();

        while (($lastPos = strpos($html, $needle, $lastPos)) !== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + strlen($needle);
        }
        return $positions;
    }
    function checkDomainHeaders($url, $format = 0)
    {
        $url = parse_url($url);
        $end = "\r\n\r\n";
        $fp = fsockopen($url['host'], (empty($url['port']) ? 80 : $url['port']), $errno, $errstr, 30);
        if ($fp) {
            $out = "GET / HTTP/1.1\r\n";
            $out .= "Host: " . $url['host'] . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $var = '';
            fwrite($fp, $out);
            while (!feof($fp)) {
                $var .= fgets($fp, 1280);
                if (strpos($var, $end))
                    break;
            }
            fclose($fp);

            $var = preg_replace("/\r\n\r\n.*\$/", '', $var);
            $var = explode("\r\n", $var);
            if ($format) {
                foreach ($var as $i) {
                    if (preg_match('/^([a-zA-Z -]+): +(.*)$/', $i, $parts))
                        $v[$parts[1]] = $parts[2];
                }
                return $v;
            } else
                return $var;
        }

    }
    function URL_404($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        /* If the document has loaded successfully without any redirection or error */
        if ($httpCode >= 200 && $httpCode < 300) {
            return 0;
        } else {
            return 1;
        }
    }

    //------------------------------

    public function web_defacement_update_original_detail(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];

                    $webdefacement = WebdefacmentSetting::where('id', $id)->first();
                    $webdefacement_original = WebdefacmentDataOriginal::where('webdefacment_setting_id', $id)->orderBy('created_at', 'desc')->first();
                    $html_h = $webdefacement_original->hash;
                    $html_f = $webdefacement_original->filesize;
                    $html_e = $webdefacement_original->element;
                    $html_b = @$webdefacement->blacklist_keyword_content;
                    $html_l = $webdefacement_original->last_update;

                    $html_h2 = $webdefacement->baseline_merkle;

                    $response = [
                        "html_h" => @$html_h,
                        "html_f" => $html_f,
                        "html_e" => @$html_e,
                        "html_b" => @$html_b,
                        "html_l" => @$html_l,
                        "html_h2" => @$html_h2,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function web_defacement_deface_now(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['webdefacment_id'];
                    $command = 'app:WebDefacementProccessbyWebdefacment_id ' . $webdefacment_id;
                    $params = [
                        'webdefacment_id' => $webdefacment_id,
                    ];

                    Artisan::call($command);

                    $response = [
                        "data" => 'success',
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function web_defacement_deface_now_detail(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];

                    $webdefacement = WebdefacmentSetting::where('id', $id)->first();
                    $webdefacement_check = WebdefacmentDataCheck::where('webdefacment_setting_id', $id)->latest()->first();
                    $html_h = @$webdefacement_check->hash_new;
                    $html_f = @formatSizeUnits(@$webdefacement_check->filesize_new) . ' (Difference ' . @$webdefacement_check->filesize_percent . '%)';
                    $html_e = @$webdefacement_check->element_new;
                    $html_b = @$webdefacement->blacklist_keyword_current;
                    $html_l = @$webdefacement_check->last_update;

                    $html_h2 = @$webdefacement_check->merkle_new;

                    $response = [
                        "html_h" => @$html_h,
                        "html_f" => @$html_f,
                        "html_e" => @$html_e,
                        "html_b" => @$html_b,
                        "html_l" => @$html_l,
                        "html_h2" => @$html_h2,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function web_defacement_update_image(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $webdefacment_id = $data['data']['id'];
                    $webdefacement = WebdefacmentSetting::where('id', $webdefacment_id)->first();

                    if ($webdefacement->get_webdefacment_data_original_detail[0]->url_id) {
                        $url_id = $webdefacement->get_webdefacment_data_original_detail[0]->url_id;
                    } else {
                        $url_id = 0;
                    }

                    $site_id = $webdefacement->site_id;
                    $url_web = $webdefacement->url;
                    $port_web = $webdefacement->port;
                    $delay_screenshot_val = $webdefacement->delay_screen_shot_val;

                    // $command = 'app:WebDefacementsCreenshotCheck';
                    //   $result = exec('/usr/bin/php /var/www/html/threat-intelligent-center/threat-intelligent-center/artisan app:WebDefacementsCreenshotCheck '.$url_web.' '.$port_web.' '.$site_id.' '.$url_id.' '.$delay_screenshot_val);
                    // Artisan::call('app:WebDefacementsCreenshotCheck ' .$url_web, 
                    //   [
                    //     'url' => $url_web,
                    //      'port' => $port_web,
                    //    'site_id' => $site_id,
                    //     'url_id' => $url_id,
                    //        'delay' => $delay_screenshot_val
                    //    ]
                    //    );



                    $response = [
                        "Result" => 1,
                        "image_path_original" => url('/') . $webdefacement->image_original,
                        "image_path_original_full" => url('/') . $webdefacement->image_original,
                        "image_url" => url('/') . $webdefacement->image_original,
                        "message" => "",
                        "url_id" => $webdefacment_id
                    ];
                    // $result = Artisan::output();
                    //   $response = json_decode($result);

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function web_defacement_change_status(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {

                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $webdefacment_id = $data['data']['id'];
                    $data2 = WebdefacmentSetting::where('id', $webdefacment_id)->first();
                    $data2->webdeflacement_progress = 1;
                    $data2->status_val = 'Normal';
                    $data2->is_alert_sent = 0;
                    $data2->save();

                    $data_update = WebdefacmentDataCheck::where('webdefacment_setting_id', $webdefacment_id)->first();
                    if ($data_update) {
                        $data_update->hash_percent = 0;
                        $data_update->filesize_percent = 0;
                        $data_update->element_percent = 0;
                        $data_update->image_percent = 0;
                        $data_update->keyword_percent = 0;
                        $data_update->save();
                    }

                    $webdefacement = WebdefacmentSetting::where('id', $webdefacment_id)->first();

                    $html = '';
                    if ($webdefacement->status_val != 'Normal') {
                        $html = '<a href="#" id="accept_risk"
                        class="btn btn- ' . get_option('theme_color') . ' btn-sm btn-responsive">
                        Accept Risk
                        </a>';
                    }

                    $page = langapp('search');
                    $response = [
                        "html" => $html,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );
            return response()->json($response);
        }
    }

    public function web_defacement_check_status(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $url = trim($data['data']['url']);
                    $id = $data['data']['id'];

                    if (empty($url)) {
                        $response = ['status_code' => 0, 'status_text' => 'URL is empty'];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    if (!preg_match('/^https?:\/\//i', $url)) {
                        $url = 'http://' . $url;
                    }

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    $web_chk = WebdefacmentSetting::where('url', $url)->where('id', $id)->first();

                    if (!$web_chk) {
                        $response = ['status_code' => $http_code, 'status_text' => 'Setting not found', 'url' => $url, 'id' => $id];
                    } else {
                        if ($http_code == 200) {
                            $web_chk->webdeflacement_progress = 1;
                            $web_chk->web_status = 'up';
                            $web_chk->is_alert_sent = 0;
                            $web_chk->updated_at = now();
                        } else {
                            $web_chk->webdeflacement_progress = 3;
                            $web_chk->web_status = 'down';
                        }
                        $web_chk->save();

                        $response = ['url' => $url, 'id' => $id, 'status_code' => $http_code, 'web_status' => $web_chk->web_status];
                    }

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array('status_code' => 500, 'message' => $e->getMessage());
            return response()->json($response);
        }
    }

    public function web_defacement_alert_to_customer(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $setting_id = $data['data']['id'];
                    $setting = WebdefacmentSetting::find($setting_id);
                    if (!$setting) {
                        $response = ['success' => false, 'message' => 'Setting not found.'];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    $table = (new WebdefacmentSetting)->getTable();
                    $updated = DB::table($table)
                        ->where('id', $setting->id)
                        ->where(function ($q) {
                            $q->whereNull('alert_sent_customer_at')
                                ->orWhere('alert_sent_customer_at', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'));
                        })
                        ->update([
                            'alert_sent_customer_at' => now(),
                            'is_alert_sent_customer' => 1,
                            'updated_at' => now(),
                        ]);

                    if ($updated === 0) {
                        $retryAt = $setting->alert_sent_customer_at
                            ? Carbon::parse($setting->alert_sent_customer_at)->addHour()->format('d-m-Y H:i:s')
                            : null;
                        $response = [
                            'success' => false,
                            'message' => $retryAt ? "This defacement was alerted recently. Try again after" : "This defacement was alerted recently. Try again later.",
                            'retryAt' => $retryAt
                        ];
                    } else {
                        $emails = DB::table('site_config_email_alert_defacement_customer')
                            ->where('site_id', $setting->site_id)
                            ->pluck('email')->filter()->unique()->values()->all();

                        if (empty($emails)) {
                            $response = ['success' => false, 'message' => 'No recipient email configured.'];
                        } else {
                            Mail::to($emails)->send(new DefacementAlertMail($setting));
                            $setting->is_alert_sent_customer = true;
                            $setting->alert_sent_customer_at = now();
                            $setting->save();
                            $response = ['success' => true, 'message' => 'Alert sent To Customer successfully.'];
                        }
                    }

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array('status_code' => 500, 'message' => $e->getMessage());
            return response()->json($response);
        }
    }

    public function web_defacement_show_diff_hash(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $w_id = $data['data']['id'];
                    $chk_data = WebdefacmentDataCheck::where('webdefacment_setting_id', $w_id)->latest()->first();

                    if ($chk_data) {
                        $response = [
                            'success' => true,
                            'merkle_old' => $chk_data->merkle_old,
                            'merkle_new' => $chk_data->merkle_new,
                            'simhash_bits' => $chk_data->simhash_bits,
                            'section_diffs' => $chk_data->section_diffs,
                            'assets_add' => $chk_data->assets_add ? $chk_data->assets_add : [],
                            'assets_del' => $chk_data->assets_del ? $chk_data->assets_del : [],
                            'outbound_new' => $chk_data->outbound_new_not_whitelisted ? $chk_data->outbound_new_not_whitelisted : [],
                        ];
                    } else {
                        $response = ['success' => false, 'message' => 'No data found.'];
                    }

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
            $response = array('status_code' => 500, 'message' => $e->getMessage());
            return response()->json($response);
        }
    }

    public function web_defacement_export_report(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'web_defacement') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $webdefacementId = $data['data']['webdefacement_id'] ?? $data['data']['id'] ?? null;
                    $startDate = $data['data']['start_date'] ?? null;
                    $endDate = $data['data']['end_date'] ?? null;

                    if (empty($webdefacementId) || empty($startDate) || empty($endDate)) {
                        $response = ['error' => 'Missing required parameters', 'status_code' => '400', 'received_keys' => array_keys($data['data'])];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    $webdefacement = WebdefacmentSetting::find($webdefacementId);
                    if (!$webdefacement) {
                        $response = ['error' => 'Web Defacement not found', 'status_code' => '404', 'debug_payload' => $data['data']];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    $stats = WebdefacmentStatDaily::where('webdefacement_id', $webdefacementId)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date', 'asc')
                        ->get();

                    if ($stats->isEmpty()) {
                        $response = ['error' => 'No data available for the selected date range.', 'status_code' => '404'];
                        $data_transcation = json_encode($response);
                        $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    }

                    $filename = 'WebDefacement_Report_' . preg_replace('/[^a-zA-Z0-9]/', '_', $webdefacement->name) . '_' . $startDate . '_to_' . $endDate . '.csv';

                    // Build CSV content in memory
                    $handle = fopen('php://temp', 'r+');
                    fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                    $startDateFormatted = \Carbon\Carbon::parse($startDate)->format('d/m/Y');
                    $endDateFormatted = \Carbon\Carbon::parse($endDate)->format('d/m/Y');

                    fputcsv($handle, ['Name', $webdefacement->name]);
                    fputcsv($handle, ['URL', $webdefacement->url]);
                    fputcsv($handle, ['Domain', $webdefacement->domain]);
                    fputcsv($handle, ['Site', $webdefacement->get_site->name ?? 'N/A']);
                    fputcsv($handle, ['Report Period', $startDateFormatted . ' to ' . $endDateFormatted]);
                    fputcsv($handle, ['Export At', now()->format('d/m/Y H:i:s')]);
                    fputcsv($handle, ['']);
                    fputcsv($handle, ['No', 'Date', 'Web Status', 'Status', 'Last Online', 'Last Check', 'Score']);

                    $no = 1;
                    foreach ($stats as $stat) {
                        $dateFormatted = '';
                        $dateWithTime = '';
                        if (!empty($stat->date)) {
                            $dateFormatted = \Carbon\Carbon::parse($stat->date)->format('d/m/Y');
                            $dateWithTime = \Carbon\Carbon::parse($stat->date)->format('d/m/Y') . ' 23:59:59';
                        }

                        $webStatusDisplay = '';
                        $rawStatus = strtolower($webdefacement->web_status ?? '');
                        if ($rawStatus === 'up') {
                            $webStatusDisplay = 'Online';
                        } elseif ($rawStatus === 'down') {
                            $webStatusDisplay = 'Offline';
                        } else {
                            $webStatusDisplay = $webdefacement->web_status ?? '';
                        }

                        fputcsv($handle, [
                            $no++,
                            $dateFormatted,
                            $webStatusDisplay,
                            $stat->max_status ?? '',
                            $dateWithTime,
                            $dateWithTime,
                            isset($stat->avg_score) ? round($stat->avg_score) : ''
                        ]);
                    }

                    fputcsv($handle, ['']);
                    fputcsv($handle, ['Total Days', count($stats)]);

                    rewind($handle);
                    $csvContent = stream_get_contents($handle);
                    fclose($handle);

                    // Force CRLF line endings for strict Windows/Excel compatibility
                    $csvContent = preg_replace("/\r\n|\n\r|\n|\r/", "\r\n", $csvContent);

                    $response = [
                        'success' => true,
                        'filename' => $filename,
                        'csv' => base64_encode($csvContent),
                        'content_type' => 'text/csv',
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('API Export Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            $response = array('status_code' => 500, 'message' => $e->getMessage());
            return response()->json($response);
        }
    }

    private function dataFalse($bearerToken, $mode, $data)
    {
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if ($site['status_code'] !== '200') {
                return $this->AuthorizationRegister($header, $mode);
            }
            $value = $data;
            $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'], $site['data']['mac_address_key']);

            if ($data === false) {
                return $data;
            } else {
                $data_return = [
                    'site' => $site,
                    'data' => json_decode($data, true),
                ];
                return $data_return;
            }

        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e->getMessage(),
            );
            return response()->json($response);
        }
    }

    private function explode_val($val, $type = null, $url)
    {
        $result = '';
        if ($val) {
            $val_arr = explode(",", $val);
            if ($val_arr) {
                foreach ($val_arr as $tag) {
                    if ($type == 'tags') {
                        $result .= '<a href="' . $url . '/indicators/tags/' . $tag . '">' . $tag . '</a> ,';
                    } else if ($type == 'groups') {
                        $result .= '<a href="' . $url . '/indicators/groups/' . $tag . '">' . $tag . '</a> ,';
                    } else {
                        $result .= '<a href="#">' . $tag . '</a> ,';
                    }

                }
                $result = rtrim($result, ',');
            }
        } else {
            $result = '';
        }
        return $result;
    }
}
