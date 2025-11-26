<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use App\Console\Commands\compareImages;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\TestHTMLWeb;
use Modules\Webdefacement\Entities\WebdefacmentDataCheck;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter; // ถ้าใช้ selector ของ DomCrawler ด้วย


class WebDefacementUpdateOriginal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:WebDefacementUpdateOriginal{webdefacment_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebDefacementUpdateOriginal {webdefacment_id}';
    private $hashingAlgorithm  = 'md5';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result = array();
        $webdefacment_id = $this->argument('webdefacment_id');
        $WebdefacmentSetting_data =    WebdefacmentSetting::find($webdefacment_id);
        if ($WebdefacmentSetting_data) {
            $WebdefacmentDataOriginal_data =    WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
            if (!$WebdefacmentDataOriginal_data) {
                $WebdefacmentDataOriginal_save = new WebdefacmentDataOriginal;
                $WebdefacmentDataOriginal_save->webdefacment_setting_id = $webdefacment_id;
                $WebdefacmentDataOriginal_save->save();
                $WebdefacmentDataOriginal_data =    WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
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
                $response   = $this->getHtml($url);
                if ($response['content'] === FALSE) {
                    $webContent = "";
                    $result["Result"] = 0;
                    $result["message"] = "Html not found";
                } else {
                    $webContent = $response['content'];

                    // if ($webdefacment_id == 167) {
                    //     $webContent = TestHTMLWeb::testHTML();
                    //     // Log::info($webContent);
                    // } else {
                    //     $webContent = $response['content'];
                    // }

                    try {
                        $selectors = json_decode($WebdefacmentSetting_data->hash_selectors ?: '[]', true) ?: ["header", "nav", "main", "#content", ".entry-content", "footer"];
                        $ignores   = json_decode($WebdefacmentSetting_data->hash_ignore_selectors ?: '[]', true) ?: [
                            ".time",
                            ".date",
                            ".timestamp",
                            ".counter",
                            ".views",
                            ".carousel",
                            ".slider",
                            ".ticker",
                            ".marquee",
                            ".ads",
                            ".advert",
                            ".banner",
                            "#cookie-consent",
                            ".toast",
                            ".modal",
                            ".live",
                            ".countdown"
                        ];
                        $whitelist = json_decode($WebdefacmentSetting_data->domain_whitelist ?: '[]', true) ?: [];
                        $allowPat  = json_decode($WebdefacmentSetting_data->asset_allow_patterns ?: '[]', true) ?: [
                            '\\.css$',
                            '\\.js$',
                            '\\.mjs$',
                            '\\.(png|jpe?g|gif|webp|svg)$',
                            '\\.(woff2?|ttf|otf|eot)$',
                            '^/assets/',
                            '^/static/',
                            '^/build/',
                            '^/dist/'
                        ];
                        $domNorm      = $this->normalizeHtml($webContent, $ignores);
                        $sectionsText = $this->sectionsText($domNorm, $selectors);
                        $sectionsHtml = $this->sectionsHtml($domNorm, $selectors);
                        $textAll      = implode("\n", array_values($sectionsText));

                        $c = new \Symfony\Component\DomCrawler\Crawler($domNorm);
                        $cnt = [
                            'footer'  => $c->filter('footer')->count(),
                            '#footer' => $c->filter('#footer')->count(),
                            '.footer' => $c->filter('.footer')->count(),
                            '#main'   => $c->filter('#main')->count(),
                            '.main'   => $c->filter('.main')->count(),
                            'body'    => $c->filter('body')->count(),
                            'content' => $c->filter('contents')->count(),
                            '.entry-content' => $c->filter('.entry-content')->count(),
                            'nav'     => $c->filter('nav')->count(),
                            'header'  => $c->filter('header')->count(),
                            'main'    => $c->filter('main')->count(),
                            '#content' => $c->filter('#content')->count(),
                            '.contents' => $c->filter('.contents')->count(),
                        ];
                        // \Log::debug('[SELS.footer] '.json_encode($cnt));


                        // 2.1 Fallback ถ้า selectors ไม่โดนเลย → เอา body ทั้งก้อนมาใช้
                        if (trim($textAll) === '') {
                            $crawler = new \Symfony\Component\DomCrawler\Crawler($domNorm);
                            if ($crawler->filter('body')->count()) {
                                $fallbackText   = trim(preg_replace('/\s+/', ' ', $crawler->filter('body')->text(' ')));
                                $sectionsText   = ['__fallback_body__' => $fallbackText];
                                $sectionsHtml   = $crawler->filter('body')->html();
                                $selectors      = ['body']; // จะถูกบันทึกลง snapshot/baseline
                            } else {
                                $fallbackText   = trim(preg_replace('/\s+/', ' ', strip_tags($domNorm)));
                                $sectionsText   = ['__fallback_all__' => $fallbackText];
                                $sectionsHtml   = $domNorm;
                                $selectors      = ['__all__'];
                            }
                            $textAll = implode("\n", array_values($sectionsText));
                            $result['_selectors_fallback_used'] = true;
                        }

                        // 3) digests (per-section + merkle + simhash) — เหมือนเดิม
                        $secDigNow = [];
                        foreach ($sectionsText as $sel => $txt) {
                            $secDigNow[$sel] = $this->sha256($txt);
                        }
                        // จัดเรียงตามชื่อ selector ให้คงที่ทุกครั้ง
                        $secDigSorted = $secDigNow;
                        ksort($secDigSorted, SORT_NATURAL);
                        $merkleNow = $this->merkleCanonical($secDigNow);
                        // $merkleNow = $this->merkleRoot(array_values($secDigSorted));

                        // (แนะนำ) เก็บไว้ debug ด้วย จะเห็นว่าใช้ leaves อะไรและลำดับไหน
                        $result['_merkle_leaves'] = $secDigSorted;


                        // $merkleNow = $this->merkleRoot(array_values($secDigNow));
                        // $simNowHex = $this->simhash64_hex($textAll);

                        // ===== ใช้ข้อความจากทั้งหน้าเว็บเพื่อทำ simhash =====
                        $crawler = new \Symfony\Component\DomCrawler\Crawler($domNorm);
                        if ($crawler->filter('body')->count()) {
                            // เอาเฉพาะข้อความใน body แล้ว normalize ช่องว่าง
                            $textFullPage = trim(preg_replace('/\s+/', ' ', $crawler->filter('body')->text(' ')));
                        } else {
                            // fallback ถ้าไม่มี <body>
                            $textFullPage = trim(preg_replace('/\s+/', ' ', strip_tags($domNorm)));
                        }

                        // simhash ของ "ทั้งหน้าเว็บ"
                        $simNowHex = $this->simhash64_hex($textFullPage);

                        Log::debug('defacement.simhash.fullpage', [
                            'text_length' => strlen($textFullPage),
                            'simNowHex'   => $simNowHex,
                        ]);



                        // 4) !! เปลี่ยนตรงนี้ !!  ดึง assets/outbound จาก “ทั้งหน้าเดิม” ไม่ใช่เฉพาะ sections
                        list($assetsAllFull, $outboundNow) = $this->assetsAndOutboundFromHtml($webContent, $url);
                        $assetsNow = [];
                        foreach ($assetsAllFull as $a) {
                            foreach ($allowPat as $pat) {
                                if (@preg_match('/' . $pat . '/', $a)) {
                                    $assetsNow[] = $a;
                                    break;
                                }
                            }
                        }
                        sort($assetsNow);

                        // 5) โหลด baseline (กัน null/invalid)
                        $baselineSec = json_decode($WebdefacmentSetting_data->baseline_section_hashes ?? '{}', true);
                        if (!is_array($baselineSec)) {
                            $baselineSec = [];
                        }
                        $baselineMerkle = $WebdefacmentSetting_data->baseline_merkle ?: '';
                        $baselineSimHex = $WebdefacmentSetting_data->baseline_text_fuzzy ?: '0000000000000000';
                        $assetsBase     = json_decode($WebdefacmentSetting_data->baseline_assets ?? '[]', true);
                        if (!is_array($assetsBase))  $assetsBase = [];
                        $outBase        = json_decode($WebdefacmentSetting_data->baseline_outbound ?? '[]', true);
                        if (!is_array($outBase))     $outBase = [];

                        $isFirstBaseline = true;

                        // 5.1 baseline รอบแรก
                        if ($isFirstBaseline) {
                            $setting = \Modules\WebDefacement\Entities\WebdefacmentSetting::find($webdefacment_id);
                            if ($setting) {
                                // --- canonical merkle (เรียงคีย์ในฟังก์ชัน merkleCanonical อยู่แล้ว) ---
                                $baselineMerkleCanonical = $this->merkleCanonical($secDigNow);

                                $setting->hash_scope              = 'sections';
                                $setting->hash_selectors          = json_encode($selectors, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                $setting->hash_ignore_selectors   = json_encode($ignores,   JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                $setting->baseline_section_hashes = json_encode($secDigNow, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                $setting->baseline_merkle         = $baselineMerkleCanonical;   // ← canonical
                                $setting->baseline_text_fuzzy     = $simNowHex;                 // ← HEX 16 ตัว
                                $setting->baseline_assets         = json_encode($assetsNow,   JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                $setting->baseline_h_assets       = $this->sha256(implode('', $assetsNow));
                                $setting->baseline_outbound       = json_encode($outboundNow, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                $setting->baseline_h_outbound     = $this->sha256(implode('', $outboundNow));
                                $setting->save();
                            }

                            // ใช้ค่าเดียวกับที่เพิ่งเซฟ (หลีกเลี่ยง $merkleNow ที่อาจไม่ canonical)
                            $baselineSec    = $secDigNow;
                            $baselineMerkle = isset($baselineMerkleCanonical) ? $baselineMerkleCanonical : $this->merkleCanonical($secDigNow);
                            $baselineSim    = $simNowHex;
                            $assetsBase     = $assetsNow;
                            $outBase        = $outboundNow;
                        }


                        // 6) diff per-section — adopt key ใหม่อัตโนมัติ
                        $sectionDiffs   = [];
                        $baselineSecMut = $baselineSec;
                        $adopted        = false;

                        foreach ($secDigNow as $sel => $h) {
                            if (!array_key_exists($sel, $baselineSecMut) || $baselineSecMut[$sel] === null || $baselineSecMut[$sel] === '') {
                                $baselineSecMut[$sel] = $h;
                                $sectionDiffs[] = ['section' => $sel, 'old' => $h, 'new' => $h, 'changed' => false, 'note' => 'adopted_missing_baseline'];
                                $adopted = true;
                            } else {
                                $old = $baselineSecMut[$sel];
                                $sectionDiffs[] = ['section' => $sel, 'old' => $old, 'new' => $h, 'changed' => ($old !== $h)];
                            }
                        }

                        $changedCount = 0;
                        foreach ($sectionDiffs as $d) {
                            if (!empty($d['changed'])) $changedCount++;
                        }

                        if ($changedCount === 0 && $merkleNow !== $baselineMerkle) {
                            // ไม่มี section ไหนเปลี่ยน แต่ root ต่าง → ซิงก์ baseline_merkle ให้ตรง canonical
                            $setting = \Modules\WebDefacement\Entities\WebdefacmentSetting::find($webdefacment_id);
                            if ($setting) {
                                $setting->baseline_merkle = $merkleNow;
                                $setting->save();
                            }
                            $baselineMerkle = $merkleNow;
                            $reason = 'baseline_merkle_recanonicalized'; // เพื่อระบุในรอบนี้ว่าเป็นการซิงก์ ไม่ใช่เหตุผิดปกติ
                        }


                        if ($adopted) {
                            $setting = \Modules\WebDefacement\Entities\WebdefacmentSetting::find($webdefacment_id);
                            if ($setting) {
                                $setting->baseline_section_hashes = json_encode($baselineSecMut, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                $setting->baseline_merkle         = $this->merkleCanonical($baselineSecMut); // ← อัปเดต merkle ของ baseline
                                $setting->save();
                            }
                            $baselineSec    = $baselineSecMut;
                            $baselineMerkle = $setting->baseline_merkle ?? $this->merkleCanonical($baselineSecMut); // ← sync ตัวแปรในหน่วยความจำด้วย
                        }



                        // 7) diff assets/outbound
                        if ($isFirstBaseline) {
                            $assets_add = [];
                            $assets_del = [];
                            $out_add = [];
                            $outbound_new_not_whitelisted = [];
                        } else {
                            $assets_add = array_values(array_diff($assetsNow,  $assetsBase));
                            $assets_del = array_values(array_diff($assetsBase, $assetsNow));
                            $out_add    = array_values(array_diff($outboundNow, $outBase));
                            $outbound_new_not_whitelisted = array_values(array_diff($out_add, $whitelist));
                        }

                        // 8) คะแนน/เหตุผล
                        if ($isFirstBaseline) {
                            $simBits = 0;
                            $signals_assets = 0.0;
                            $signals_outbound = 0.0;
                            $score = 0.0;
                            $reason = 'update original';
                        } else {
                        }

                        $result = [];
                        $result['_section_scope']          = 'sections';
                        $result['_section_selectors']      = $selectors;
                        $result['_merkle_old']             = $baselineMerkle;
                        $result['_merkle_new']             = $merkleNow;
                        $result['_simhash_bits']           = $simBits;
                        $result['_section_diffs']          = $sectionDiffs;
                        $result['_assets_add']             = $assets_add;
                        $result['_assets_del']             = $assets_del;
                        $result['_outbound_new_not_wl']    = $outbound_new_not_whitelisted;
                        $result['_score']                  = round($score, 3);
                        $result['_reason']                 = $reason;

                        $WebdefacmentDataCheck_save = new WebdefacmentDataCheck;
                        $WebdefacmentDataCheck_save->webdefacment_setting_id = $WebdefacmentSetting_data->id;

                        $WebdefacmentDataCheck_save->scope_snapshot       = $result['_section_scope']        ?? 'sections';
                        $WebdefacmentDataCheck_save->selectors_snapshot   = json_encode($result['_section_selectors'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                        $WebdefacmentDataCheck_save->merkle_old           = $result['_merkle_old'] ?? '';
                        $WebdefacmentDataCheck_save->merkle_new           = $result['_merkle_new'] ?? '';
                        $WebdefacmentDataCheck_save->simhash_bits         = (int)($result['_simhash_bits'] ?? 0);

                        $WebdefacmentDataCheck_save->section_diffs        = json_encode($result['_section_diffs'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                        $WebdefacmentDataCheck_save->assets_add           = json_encode($result['_assets_add'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                        $WebdefacmentDataCheck_save->assets_del           = json_encode($result['_assets_del'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                        $WebdefacmentDataCheck_save->outbound_new_not_whitelisted = json_encode($result['_outbound_new_not_wl'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

                        $WebdefacmentDataCheck_save->score  = (float)($result['_score'] ?? 0);
                        $WebdefacmentDataCheck_save->reason = $result['_reason'] ?? null;

                        if ($WebdefacmentDataCheck_save->save()) {
                            Log::info("Save Complete " . "Section Difference: " . json_encode($result['_section_diffs'] ?? [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
                        }
                    } catch (Exception $e) {
                        Log::info($e->getMessage());
                    }



                    if ($WebdefacmentSetting_data->hash == 1) {
                        $hashMD5 = hash($this->hashingAlgorithm, $webContent);
                        $result['hash_code'] = $hashMD5;
                    }
                    if ($WebdefacmentSetting_data->filesize == 1) {
                        $file_size = strlen($webContent); //filesize
                        $result["file_size"] = $file_size;
                    }
                    if ($WebdefacmentSetting_data->element == 1) {
                        $allElement = preg_match_all('/<([^\/!][a-z1-9]*)/i', $webContent, $matches);
                        $result['all_element'] = (int)$allElement;
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


                    $result_checkDomainHeaders =   $this->checkDomainHeaders($url, 1);
                    $result_URL_404 =   $this->URL_404($url);
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
                    if ($WebdefacmentDataOriginal_data->save()) {
                    }

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
        echo $result_json_e;
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
            $out  = "GET / HTTP/1.1\r\n";
            $out .= "Host: " . $url['host'] . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $var  = '';
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
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

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

    // ===== Helper: SHA-256 =====
    private function sha256($s)
    {
        return hash('sha256', (string)$s);
    }

    // ===== Helper: ดึงข้อความใน <body> แบบ normalize =====
    private function extractBodyText($html)
    {
        // พยายามใช้ DOMDocument เพื่อดึงเฉพาะ body
        $text = '';
        if (!is_string($html) || $html === '') return $text;
        // ป้องกัน warning encoding
        $internalErrors = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        // กำหนด meta charset กันภาษาตกหล่น
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($dom);
        $body = $xpath->query('//body')->item(0);
        if ($body) {
            $text = $this->nodeText($body);
        } else {
            // fallback: ตัด tag ออกทั้งหมด
            $text = strip_tags($html);
        }
        // normalize ช่องว่าง
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        return $text;
    }

    private function nodeText(\DOMNode $node)
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $node->nodeValue;
        }
        $out = '';
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $out .= $this->nodeText($child) . ' ';
            }
        }
        return $out;
    }

    // ===== Helper: Merkle canonical จาก map selector => hash =====
    private function merkleCanonical(array $sectionMap)
    {
        if (empty($sectionMap)) return $this->sha256(''); // กรณีว่าง
        // เรียงคีย์ก่อน
        ksort($sectionMap, SORT_NATURAL);
        // ใช้ค่าของแฮชเป็น leaves ตามลำดับคีย์ที่เรียงแล้ว
        $leaves = array_values($sectionMap);
        // ถ้า leaf ไม่ใช่ 64 hex ให้ sha256 เพิ่มเติมให้เป็นฐานเดียวกัน
        foreach ($leaves as &$v) {
            if (!is_string($v) || !preg_match('/^[0-9a-f]{64}$/i', $v)) {
                $v = $this->sha256($v);
            }
        }
        unset($v);

        // โยงขึ้นเป็น merkle: จับคู่ซ้าย-ขวา ถ้าคี่ก็ยกตัวสุดท้ายขึ้นไป
        while (count($leaves) > 1) {
            $next = array();
            for ($i = 0; $i < count($leaves); $i += 2) {
                if (isset($leaves[$i + 1])) {
                    $next[] = $this->sha256($leaves[$i] . $leaves[$i + 1]);
                } else {
                    // เดี่ยว ๆ ก็แฮชตัวเองซ้ำให้ขึ้นชั้น
                    $next[] = $this->sha256($leaves[$i]);
                }
            }
            $leaves = $next;
        }
        return $leaves[0];
    }

    // ===== Helper: ดึง assets และ outbound ทั้งหน้า =====
    private function assetsAndOutboundFromHtml($html, $baseUrl)
    {
        $assets = array();   // src/href ของ asset ชนิด img, script, link rel=stylesheet, video, audio
        $out    = array();   // ลิงก์ออกนอกโดเมน

        if (!is_string($html) || $html === '') return array($assets, $out);

        // เตรียม DOM
        $internalErrors = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        $xpath = new \DOMXPath($dom);

        // base host
        $pu = @parse_url($baseUrl);
        $baseHost = isset($pu['host']) ? strtolower($pu['host']) : '';

        // img[src]
        foreach ($xpath->query('//img[@src]') as $n) {
            /** @var \DOMElement $n */
            $u = trim($n->getAttribute('src'));
            if ($u !== '') $assets[] = $this->absUrl($u, $baseUrl);
        }
        // script[src]
        foreach ($xpath->query('//script[@src]') as $n) {
            $u = trim($n->getAttribute('src'));
            if ($u !== '') $assets[] = $this->absUrl($u, $baseUrl);
        }
        // link[rel=stylesheet][href]
        foreach ($xpath->query('//link[@rel][@href]') as $n) {
            $rel = strtolower(trim($n->getAttribute('rel')));
            if ($rel === 'stylesheet') {
                $u = trim($n->getAttribute('href'));
                if ($u !== '') $assets[] = $this->absUrl($u, $baseUrl);
            }
        }
        // video[src], source[src], audio[src]
        foreach ($xpath->query('//video[@src]|//audio[@src]|//source[@src]') as $n) {
            $u = trim($n->getAttribute('src'));
            if ($u !== '') $assets[] = $this->absUrl($u, $baseUrl);
        }

        // a[href] → outbound (โดเมนปลายทางต่างจาก baseHost)
        foreach ($xpath->query('//a[@href]') as $n) {
            $u = trim($n->getAttribute('href'));
            if ($u === '') continue;
            $abs = $this->absUrl($u, $baseUrl);
            $ph = @parse_url($abs);
            $host = isset($ph['host']) ? strtolower($ph['host']) : '';
            if ($host !== '' && $baseHost !== '' && $host !== $baseHost) {
                $out[] = $abs;
            }
        }

        // clean & uniq
        $assets = $this->uniqNormalizedUrls($assets);
        $out    = $this->uniqNormalizedUrls($out);

        return array($assets, $out);
    }

    private function absUrl($url, $base)
    {
        // ง่าย ๆ: ถ้าเป็น protocol-relative // ให้แปลงเป็น http(s) ของ base
        if (strpos($url, '//') === 0) {
            $bp = parse_url($base);
            $scheme = isset($bp['scheme']) ? $bp['scheme'] : 'http';
            return $scheme . ':' . $url;
        }
        // ถ้าเป็น absolute อยู่แล้ว
        if (preg_match('#^https?://#i', $url)) return $url;

        // รวมกับ base (แบบง่าย)
        $bp = parse_url($base);
        if (!$bp || !isset($bp['scheme']) || !isset($bp['host'])) return $url;

        $scheme = $bp['scheme'];
        $host   = $bp['host'];
        $port   = isset($bp['port']) ? ':' . $bp['port'] : '';
        $path   = isset($bp['path']) ? $bp['path'] : '/';

        // ถ้า url เริ่มด้วย / ให้ต่อจาก root
        if (strpos($url, '/') === 0) {
            return $scheme . '://' . $host . $port . $url;
        }

        // ตัดชื่อไฟล์ออก เหลือ directory
        if (substr($path, -1) !== '/') {
            $path = preg_replace('#/[^/]*$#', '/', $path);
        }
        return $scheme . '://' . $host . $port . $path . $url;
    }

    private function uniqNormalizedUrls(array $urls)
    {
        $out = array();
        $seen = array();
        foreach ($urls as $u) {
            // ตัด fragment/query เพื่อความเสถียรของ hash (ปรับตามนโยบายได้)
            $p = @parse_url($u);
            if (!$p || !isset($p['scheme']) || !isset($p['host'])) continue;
            $norm = strtolower($p['scheme'] . '://' . $p['host'] . (isset($p['path']) ? $p['path'] : '/'));
            if (!isset($seen[$norm])) {
                $seen[$norm] = true;
                $out[] = $norm;
            }
        }
        return $out;
    }
    private function normalizeHtml(string $html, array $ignoreSelectors = []): string
    {
        // --- Fast strip ก่อน ลดงาน DOM ---
        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is',   '', $html);
        $html = preg_replace('#<!--.*?-->#s',                   '', $html);

        // --- Encoding safety ---
        if (!preg_match('//u', $html)) {
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        }

        // --- โหลด DOM แบบ no-network / no-validate ---
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = false;

        $prevUseInternal = libxml_use_internal_errors(true);
        if (function_exists('libxml_disable_entity_loader')) {
            $prevDisable = libxml_disable_entity_loader(true); // กัน XXE/โหลดภายนอก (PHP 7.x)
        }

        $loaded = false;
        if (PHP_VERSION_ID >= 70300) {
            $loaded = @$doc->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET | LIBXML_COMPACT);
        } else {
            $loaded = @$doc->loadHTML($html); // PHP 7.0–7.2 ไม่มี options
        }

        if (function_exists('libxml_disable_entity_loader')) {
            libxml_disable_entity_loader($prevDisable ?? false);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseInternal);

        if (!$loaded) {
            // ถ้าโหลด DOM ไม่ขึ้น ให้คืนค่าที่ยุบช่องว่างแล้ว
            return trim(preg_replace('/\s+/', ' ', $html));
        }

        // --- ตัด noscript/template และ ignore selectors ด้วย Crawler ---
        $crawler = new Crawler($doc);

        foreach (['noscript', 'template'] as $tag) {
            foreach ($crawler->filter($tag) as $n) {
                if ($n->parentNode) {
                    $n->parentNode->removeChild($n);
                }
            }
        }

        foreach ($ignoreSelectors as $sel) {
            try {
                foreach ($crawler->filter($sel) as $n) {
                    if ($n->parentNode) {
                        $n->parentNode->removeChild($n);
                    }
                }
            } catch (\Throwable $e) {
                // selector ไม่ถูกต้อง → ข้าม
            }
        }

        // --- ลบ token/time/query สุ่ม ในทั้งเอกสาร ---
        $html2 = $doc->saveHTML() ?: '';
        $html2 = preg_replace([
            '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/',                 // ISO timestamp
            '/csrf[_-]?token\s*=\s*[\'"][A-Za-z0-9+\/=]{20,}[\'"]/',  // csrf token
            '/([?&])(cacheBust|cb|_|\d{1,2})=\d+/',                   // random qs
        ], '', $html2);

        // --- จัดเรียงแอตทริบิวต์ (จำกัดเพื่อความเร็ว/กันค้าง) ---
        // เฉพาะเมื่อขนาดไม่เกิน 2.5 MB และเฉพาะแท็กสำคัญ
        $MAX_BYTES_FOR_ATTR_SORT = 2500000;
        $SORT_TAGS = [
            'html',
            'head',
            'body',
            'meta',
            'link',
            'script',
            'img',
            'a',
            'div',
            'span',
            'header',
            'footer',
            'main',
            'section',
            'article',
            'nav',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6'
        ];

        if (strlen($html2) <= $MAX_BYTES_FOR_ATTR_SORT) {
            $doc2 = new \DOMDocument('1.0', 'UTF-8');
            $doc2->preserveWhiteSpace = false;
            $doc2->formatOutput = false;

            $prevUseInternal = libxml_use_internal_errors(true);
            if (PHP_VERSION_ID >= 70300) {
                @$doc2->loadHTML($html2, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET | LIBXML_COMPACT);
            } else {
                @$doc2->loadHTML($html2);
            }
            libxml_clear_errors();
            libxml_use_internal_errors($prevUseInternal);

            $xp2 = new \DOMXPath($doc2);
            $xpathExpr = '//' . implode(' | //', $SORT_TAGS);

            foreach ($xp2->query($xpathExpr) as $el) {
                /** @var \DOMElement $el */
                if (!$el->hasAttributes()) continue;

                $attrs = [];
                foreach (iterator_to_array($el->attributes) as $attr) {
                    $name = $attr->name;
                    // ตัด attrs ที่สุ่ม/เสียงดัง
                    if (preg_match('/^(data-|aria-)/', $name)) continue;
                    if ($name === 'nonce' || $name === 'integrity' || $name === 'crossorigin') continue;
                    if ($name === 'onclick' || strpos($name, 'on') === 0) continue; // inline js
                    $attrs[$name] = $attr->value ?? '';
                }

                // เคลียร์ และใส่คืนแบบเรียงชื่อคงที่
                while ($el->attributes->length) {
                    $el->removeAttribute($el->attributes->item(0)->name);
                }
                if ($attrs) {
                    ksort($attrs, SORT_NATURAL);
                    foreach ($attrs as $k => $v) {
                        $el->setAttribute($k, $v);
                    }
                }
            }

            $html2 = $doc2->saveHTML() ?: $html2;
        }

        // --- ยุบช่องว่างและคืนค่า ---
        $html2 = preg_replace('/\s+/', ' ', $html2);
        return trim($html2);
    }

    private function sectionsText(string $normalizedHtml, array $selectors): array
    {
        $crawler = new Crawler($normalizedHtml);
        $out = [];
        foreach ($selectors as $sel) {
            $node = $crawler->filter($sel);
            if ($node->count()) {
                $txt = $node->text();
                $txt = preg_replace('/\s+/', ' ', $txt);
                $out[$sel] = trim($txt);
            } else {
                $out[$sel] = '';
            }
        }
        return $out;
    }

    private function sectionsHtml(string $normalizedHtml, array $selectors): string
    {
        $crawler = new Crawler($normalizedHtml);
        $chunks = [];
        foreach ($selectors as $sel) {
            foreach ($crawler->filter($sel) as $n) {
                $doc = $n->ownerDocument;
                if ($doc) {
                    $chunks[] = $doc->saveHTML($n);
                }
            }
        }
        return implode("\n", $chunks);
    }


    private function hamming64_hex(string $hexA, string $hexB): int
    {
        $a = hex2bin(str_pad($hexA, 16, '0', STR_PAD_LEFT));
        $b = hex2bin(str_pad($hexB, 16, '0', STR_PAD_LEFT));
        if ($a === false || $b === false) return 64;

        $x = $a ^ $b; // XOR
        $bytes = unpack('C*', $x);
        $cnt = 0;
        foreach ($bytes as $byte) {
            // popcount 8-bit
            $v = $byte;
            $v = $v - (($v >> 1) & 0x55);
            $v = ($v & 0x33) + (($v >> 2) & 0x33);
            $cnt += ((($v + ($v >> 4)) & 0x0F) * 0x01);
        }
        return $cnt;
    }


    private function hash64(string $s): int
    {
        $bin = substr(hash('sha256', $s, true), 0, 8);
        $arr = unpack('J', $bin); // ต้องเป็น PHP 7.0+ (64-bit build แนะนำ)
        return $arr[1];
    }

    private function merkleRoot(array $hexDigests): string
    {
        if (!$hexDigests) {
            return $this->sha256('');
        }
        // แทนที่ arrow function ด้วย anonymous function
        $level = array_map(function ($h) {
            return hex2bin($h);
        }, $hexDigests);
        while (count($level) > 1) {
            $next = [];
            for ($i = 0; $i < count($level); $i += 2) {
                $l = $level[$i];
                $r = isset($level[$i + 1]) ? $level[$i + 1] : $l;
                $next[] = hash('sha256', $l . $r, true);
            }
            $level = $next;
        }
        return bin2hex($level[0]);
    }

    function isEmptyHash($v): bool
    {
        if (!is_string($v)) return true;
        $h = strtolower(trim($v));

        if ($h === '') return true;

        // SHA-256 ของสตริงว่าง
        if ($h === 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855') return true;

        // ศูนย์ล้วน (รองรับ 32/40/64/128 ตัวอักษร เผื่อ MD5/SHA1/SHA256/ฯลฯ)
        if (preg_match('/^0{16,128}$/', $h)) return true;

        // เครื่องหมายขีด/placeholder ที่บางระบบใช้
        if ($h === '-' || $h === 'null' || $h === 'undefined') return true;

        return false;
    }
    private function simhash64_hex(string $text): string
    {
        // ตัดคำแบบง่าย ๆ
        $tokens = preg_split('/\W+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        if (!$tokens) {
            // ค่าคงที่สำหรับข้อความว่าง: all zeros
            return '0000000000000000';
        }

        // เวกเตอร์น้ำหนัก 64 มิติ
        $vec = array_fill(0, 64, 0);

        foreach ($tokens as $tok) {
            // ใช้ sha256 แล้วเอา 8 ไบต์แรกเป็น fingerprint
            $hbin = substr(hash('sha256', $tok, true), 0, 8); // 8 bytes
            $bytes = array_values(unpack('C*', $hbin));       // [b0..b7]
            // ไล่บิตแบบ big-endian: b0(bit7..0), b1, ...
            for ($i = 0; $i < 64; $i++) {
                $byteIdx = intdiv($i, 8);
                $bitIdx  = 7 - ($i % 8);
                $bit = ($bytes[$byteIdx] >> $bitIdx) & 1;
                $vec[$i] += $bit ? 1 : -1;
            }
        }

        // สร้าง 8 ไบต์ผลลัพธ์จากสัญลักษณ์ของแต่ละมิติ
        $outBytes = array_fill(0, 8, 0);
        for ($i = 0; $i < 64; $i++) {
            $byteIdx = intdiv($i, 8);
            $bitIdx  = 7 - ($i % 8);
            if ($vec[$i] >= 0) {
                $outBytes[$byteIdx] |= (1 << $bitIdx);
            }
        }
        return bin2hex(call_user_func_array('pack', array_merge(['C*'], $outBytes)));
    }
}
