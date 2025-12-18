<?php

namespace App\Console\Commands;

use Exception;
// use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use App\Console\Commands\compareImages;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;

use Modules\WebDefacement\Entities\WebdefacmentImageMark;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataLog;
use App\Entities\TransactionBatchjob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DefacementAlertMail;
use Symfony\Component\DomCrawler\Crawler;
use Modules\WebDefacement\Entities\TestHTMLWeb;
use PHPUnit\Framework\Test;
use App\Services\WebDefacementService;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Panther\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Panther\ChromeOptions;
use Nesk\Puphpeteer\Puppeteer;

class WebDefacementUpdateOriginal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:WebDefacementUpdateOriginal {webdefacment_id}';

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
        Log::info('[UpdateOriginal] start: ' . date("Y-m-d H:i:s"));
        ini_set('memory_limit', '2048M');

        $result = [
            "Result" => 0,
            "message" => "unknown",
        ];

        $webdefacment_id = $this->argument('webdefacment_id');
        if (!$webdefacment_id) {
            $result["message"] = "Missing argument: webdefacment_id";
            echo json_encode($result);
            return 0;
        }

        try {
            $WebdefacmentSetting = WebdefacmentSetting::find($webdefacment_id);
            if (!$WebdefacmentSetting) {
                $result["message"] = "No WebdefacmentSetting found.";
                echo json_encode($result);
                return 0;
            }

            // ensure Original row
            $Original = WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
            if (!$Original) {
                $Original = new WebdefacmentDataOriginal();
                $Original->webdefacment_setting_id = $webdefacment_id;
                $Original->save();
                $Original = WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
            }

            $url = $WebdefacmentSetting->url;
            if (!$this->is_url($url)) {
                $result["message"] = "The url is not formatted.";
                echo json_encode($result);
                return 0;
            }

            // --- ตรวจ web status + เตรียมค่า default config ให้คล้ายตัวอย่าง ---
            try {
                $status = $this->checkHttpStatus($url);
                $WebdefacmentSetting->web_status = ($status === 200) ? 'Up' : 'Down';
                Log::info("[UpdateOriginal] URL status {$status} : {$url}");
            } catch (\Throwable $e) {
                Log::warning("[UpdateOriginal] checkHttpStatus error: {$e->getMessage()}");
            }

            // default scope/selectors/ignores หากยังไม่เคยตั้ง
            $WebdefacmentSetting->hash_scope = $WebdefacmentSetting->hash_scope ?: 'sections';
            $defaultSelectors = [
                "head",
                "#header",
                ".header",
                "nav",
                "#nav",
                ".nav",
                "main",
                "#main",
                ".main",
                "#content",
                ".content",
                "content",
                ".entry-content",
                "footer",
                "#footer",
                ".footer"
            ];
            $WebdefacmentSetting->hash_selectors = $WebdefacmentSetting->hash_selectors ?: json_encode($defaultSelectors, JSON_UNESCAPED_UNICODE);

            $ignores = [
                "//*[contains(@class,'time')]",
                "//*[contains(@class,'date')]",
                "//*[contains(@class,'timestamp')]",
                "//*[contains(@class,'counter')]",
                "//*[contains(@class,'view')]",
                "//*[contains(@class,'carousel')]",
                "//*[contains(@class,'slider')]",
                "//*[contains(@class,'ticker')]",
                "//*[contains(@class,'marquee')]",
                "//*[contains(@class,'swiper-container')]",
                "//*[contains(@class,'ads')]",
                "//*[contains(@class,'advert')]",
                "//*[contains(@class,'banner')]",
                "//*[@id='cookie-consent']",
                "//*[contains(@class,'toast')]",
                "//*[contains(@class,'modal')]",
                "//*[contains(@class,'live')]",
                "//*[contains(@class,'countdown')]",
                "//*[contains(@class,'slick-track')]",
                "//*[contains(@class,'slick-slide')]",
                "//*[contains(@class,'swiper-wrapper')]",
                "//*[contains(@class,'swiper-slide')]",
                "//*[contains(@class,'fade')]",
                "//*[starts-with(@id,'__BVID__')]",
                "//*[contains(@class,'carousel-inner')]",
                "//*[contains(@class,'carousel-item')]",
                "//*[@id='fb-root']",
                "//*[contains(@class,'fb-customerchat')]",
                "//*[contains(@class,'popup')]",
                "//*[contains(@class,'banner')]",
                "//*[contains(@class,'modal')]"
            ];
            $WebdefacmentSetting->hash_ignore_selectors = $WebdefacmentSetting->hash_ignore_selectors ?: json_encode($ignores, JSON_UNESCAPED_SLASHES);

            $WebdefacmentSetting->domain_whitelist     = $WebdefacmentSetting->domain_whitelist     ?: '[]';
            $WebdefacmentSetting->asset_allow_patterns = $WebdefacmentSetting->asset_allow_patterns ?: '[
            "\\\\.css$","\\\\.js$","\\\\.mjs$","\\\\.json$",
            "\\\\.(png|jpe?g|gif|webp|svg)$",
            "\\\\.(woff2?|ttf|otf|eot)$",
            "^/assets/","^/static/","^/build/","^/dist/"
        ]';
            $WebdefacmentSetting->save();

            // --- ดึง HTML ---
            $resp = $this->getHtml($url);
            if ($resp['content'] === FALSE) {
                $webContent = "";
                $result["Result"]  = 0;
                $result["message"] = "Html not found";
                Log::warning("[UpdateOriginal] Html not found: {$url}");
                echo json_encode($result);
                return 0;
            }
            $webContent = $resp['content'];

            if ($webdefacment_id == 176 || $webdefacment_id == 173 || $webdefacment_id == 174 || $webdefacment_id == 204 || $webdefacment_id == 209) {
                $options = [];
                if ($webdefacment_id == 204) {
                    $options['userAgent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
                }
                $response = $this->getHtml3($url, 0, $options);
                $webContent = $response['content'];
                // Log::info($response['content']);
            }
            // file_put_contents(storage_path('app/dom_raw_original.html'), $webContent);

            

            // === [SECTION MONITOR] สร้าง baseline ครั้งแรก (canonical) ===
            // 1) selectors/ignores/allow/whitelist
            $selectors = json_decode($WebdefacmentSetting->hash_selectors ?: '[]', true) ?: ["header", "nav", "main", "#content", ".entry-content", "footer"];
            $ign      = json_decode($WebdefacmentSetting->hash_ignore_selectors ?: '[]', true) ?: [];
            $allowPat = json_decode($WebdefacmentSetting->asset_allow_patterns ?: '[]', true) ?: [];

            // 2) normalize + extract
            $domNorm      = $this->normalizeHtml($webContent, $ign);
            $sectionsText = $this->sectionsText($domNorm, $selectors);
            $textAll = trim(implode("\n", array_values($sectionsText)));
            if ($textAll === '') {
                // fallback: เอา body หรือ all text
                $crawler = new \Symfony\Component\DomCrawler\Crawler($domNorm);
                if ($crawler->filter('body')->count()) {
                    $fallbackText = trim(preg_replace('/\s+/', ' ', $crawler->filter('body')->text(' ')));
                    $sectionsText = ['__fallback_body__' => $fallbackText];
                    $selectors    = ['body'];
                } else {
                    $fallbackText = trim(preg_replace('/\s+/', ' ', strip_tags($domNorm)));
                    $sectionsText = ['__fallback_all__' => $fallbackText];
                    $selectors    = ['__all__'];
                }
            }

            // 3) digests
            $secDigNow = [];
            foreach ($sectionsText as $sel => $txt) {
                $secDigNow[$sel] = $this->sha256($txt);
            }
            ksort($secDigNow, SORT_NATURAL);
            $merkleNow = $this->merkleCanonical($secDigNow);

            // 4) simhash (ทั้งหน้า)
            $crawler = new \Symfony\Component\DomCrawler\Crawler($domNorm);
            if ($crawler->filter('body')->count()) {
                $textFull = trim(preg_replace('/\s+/', ' ', $crawler->filter('body')->text(' ')));
            } else {
                $textFull = trim(preg_replace('/\s+/', ' ', strip_tags($domNorm)));
            }
            $simNowHex = $this->simhash64_hex($textFull);

            // 5) assets/outbound จาก “ทั้งหน้า”
            [$assetsAll, $outboundNow] = $this->assetsAndOutboundFromHtml($domNorm, $url);
            $assetsNow = [];
            foreach ($assetsAll as $a) {
                foreach ($allowPat as $pat) {
                    if (@preg_match('/' . $pat . '/', $a)) {
                        $assetsNow[] = $a;
                        break;
                    }
                }
            }
            sort($assetsNow);

            // 6) หากยังไม่มี baseline → เซ็ต baseline (canonical)
           
                $WebdefacmentSetting->hash_scope              = 'sections';
                $WebdefacmentSetting->hash_selectors          = json_encode($selectors, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $WebdefacmentSetting->hash_ignore_selectors   = json_encode($ign,      JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $WebdefacmentSetting->baseline_section_hashes = json_encode($secDigNow, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $WebdefacmentSetting->baseline_merkle         = $merkleNow;     // canonical
                $WebdefacmentSetting->baseline_text_fuzzy     = $simNowHex;     // HEX 16 ตัว
                $WebdefacmentSetting->baseline_assets         = json_encode($assetsNow, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $WebdefacmentSetting->baseline_h_assets       = $this->sha256(implode('', $assetsNow));
                $WebdefacmentSetting->baseline_outbound       = json_encode($outboundNow, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                $WebdefacmentSetting->baseline_h_outbound     = $this->sha256(implode('', $outboundNow));
                $WebdefacmentSetting->save();
            

            // === เก็บค่าพื้นฐานตาม flag เดิม (hash/filesize/element) ===
            $hash_code = "";
            if ((int)$WebdefacmentSetting->hash === 1) {
                $hash_code = hash($this->hashingAlgorithm, $domNorm);
            }
            $file_size = ((int)$WebdefacmentSetting->filesize === 1) ? strlen($domNorm) : 0;
            $all_element = 0;
            if ((int)$WebdefacmentSetting->element === 1) {
                $all_element = (int)preg_match_all('/<([^\/!][a-z1-9]*)/i', $domNorm, $m);
            }

            // === ถ่ายภาพ/แฮชภาพ (เก็บ original) ===
            $image_url = "";
            $part_image = "";
            $imageHash = "";
            if ((int)$WebdefacmentSetting->image_check === 1) {
                $url_id = $Original->url_id ?: rand(10, 100);
                $site_id = $WebdefacmentSetting->site_id;
                $delay   = $WebdefacmentSetting->delay_screen_shot_val;

                $image_url  = "/images/webdefacment_mages/{$site_id}/{$url_id}/image_original.png";
                $path_full  = base_path() . "/public/images/webdefacment_mages/{$site_id}/{$url_id}/image_original.png";
                $part_image = "/public/images/webdefacment_mages/{$site_id}/{$url_id}/image_original.png";

                try {
                    $path_include = base_path() . '/public/screenshot/use/DownloadImage.php';
                    include_once($path_include);
                    $downloadImg = new \DownloadImage();
                    $downloadImg->download($url, $path_full, $delay);

                    $cmp = new compareImages($path_full);
                    $imageHash = $cmp->getHasString();
                    $Original->url_id   = $url_id;
                    $Original->imageHash = $imageHash;
                } catch (\Throwable $e) {
                    Log::warning("[UpdateOriginal] screenshot/hash error: {$e->getMessage()}");
                }
            }

            // === Domain headers & 404 ===
            try {
                $headers = $this->checkDomainHeaders($url, 1);
                $is404   = $this->URL_404($url);
                $parse   = parse_url($url);
                $host    = $parse['host'] ?? '';
                $WebdefacmentSetting->DomainHeaders = json_encode($headers);
                $WebdefacmentSetting->user_agent    = $headers['Server'] ?? ($headers['server'] ?? null);
                $WebdefacmentSetting->domain        = $host;
                $WebdefacmentSetting->save();
            } catch (\Throwable $e) {
                Log::warning("[UpdateOriginal] domain header check error: {$e->getMessage()}");
            }

            // === บันทึกค่า Original ===
            $Original->hash        = $hash_code;
            $Original->filesize    = $file_size;
            $Original->element     = $all_element;
            $Original->image       = $image_url;
            $Original->part_image  = $part_image;
            $Original->last_update = date("Y-m-d H:i:s");
            $Original->updated_at  = date("Y-m-d H:i:s");
            $Original->save();

            $result = [
                "Result" => 1,
                "message" => "",
                "hash_code" => $hash_code,
                "file_size" => $file_size,
                "all_element" => $all_element,
                "image_url" => $image_url,
                "part_image" => $part_image,
                "imageHash" => $imageHash,
                // "baseline_inited" => $isFirstBaseline ? 1 : 0,
            ];
        } catch (\Throwable $e) {
            Log::error("[UpdateOriginal] error: {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}");
            $result["Result"] = 0;
            $result["message"] = $e->getMessage();
        }

        echo json_encode($result);
        Log::info('[UpdateOriginal] done: ' . date("Y-m-d H:i:s"));
        return 0;
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

        // \Log::info('Content length: ' . strlen($content));
        // \Log::info('DIV count: ' . substr_count($content, '<div'));
        // \Log::info('Preview: ' . mb_substr($content, 0, 500));


        return array(
            'content' => $content
        );
    }
    // function get_dataa($url)
    // {
    //   $ch = curl_init();
    //   $timeout = 5;
    //   curl_setopt($ch, CURLOPT_URL, $url);
    //   curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0)");
    //   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //   curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    //   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    //   $data = curl_exec($ch);
    //   curl_close($ch);
    //   return $data;
    // }

    function get_dataa($url)
    {
        $ch = curl_init();
        $connectTimeout = 10; // เวลารอการเชื่อมต่อ
        $timeout = 60;        // เวลารอโหลดข้อมูลทั้งหมด

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        // ปิด verify ssl ถ้าเว็บใช้ https ที่ cert ไม่ถูกต้อง
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // ตั้งค่า User-Agent และ header ให้เหมือน browser จริง
        curl_setopt(
            $ch,
            CURLOPT_USERAGENT,
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125 Safari/537.36"
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: th-TH,th;q=0.9,en;q=0.8'
        ]);

        // รองรับ response ที่บีบอัด gzip/deflate
        curl_setopt($ch, CURLOPT_ENCODING, "");

        // เพิ่ม timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            \Log::error('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);
        return $data;
    }


    private function trackKeyWords($webContent, $blacklistKeywords, $Keyword_checks)
    {
        $keywordOK = array();
        if ($blacklistKeywords) {


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

    private function isPageHack($trackList, $pageTransaction, &$totalPoint)
    {
        $pageIsHack = false;

        foreach ($trackList as $key => $value) {
            $pageTransactionValue = $this->convertValueType($pageTransaction[$key]);

            if ($pageTransactionValue !== $value) {
                $pageIsHack = true;
                $totalPoint = $this->addPoint($totalPoint);
            }
        }
        return $pageIsHack;
    }

    private function convertValueType($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT)) {
            $value = (int)$value;
        }
        return $value;
    }


    // ===== Helpers for section hashing & diff (PHP 7 compatible) =====
    private function normalizeHtml(string $html, array $ignoreSelectors = []): string
  {
    libxml_use_internal_errors(true);

    // --- โหลด DOM ครั้งเดียว ---
    $doc = new \DOMDocument('1.0', 'UTF-8');
    @$doc->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET | LIBXML_COMPACT);
    libxml_clear_errors();
    libxml_use_internal_errors(false);

    $xpath = new \DOMXPath($doc);

    // --- ดึง <head> และ <body> แยกไว้ก่อน ---
    $headNode = $doc->getElementsByTagName('head')->item(0);
    $bodyNode = $doc->getElementsByTagName('body')->item(0);
    $headHtml = $headNode ? $doc->saveHTML($headNode) : '<head></head>';
    if (!$bodyNode) return $html; // กัน fail ถ้าไม่มี body

    // --- รวม ignore selectors + default dynamic selectors ---
    $defaultSelectors = [
      "//*[contains(@class,'time')]",
      "//*[contains(@class,'date')]",
      "//*[contains(@class,'timestamp')]",
      "//*[contains(@class,'counter')]",
      "//*[contains(@class,'view')]",
      "//*[contains(@class,'carousel')]",
      "//*[contains(@class,'slider')]",
      "//*[contains(@class,'ticker')]",
      "//*[contains(@class,'marquee')]",
      "//*[contains(@class,'swiper-container')]",
      "//*[contains(@class,'ads')]",
      "//*[contains(@class,'advert')]",
      "//*[contains(@class,'banner')]",
      "//*[contains(@class,'toast')]",
      "//*[contains(@class,'modal')]",
      "//*[contains(@class,'popup')]",
      "//*[contains(@class,'live')]",
      "//*[contains(@class,'countdown')]",
      "//*[contains(@class,'slick-track')]",
      "//*[contains(@class,'slick-slide')]",
      "//*[contains(@class,'swiper-wrapper')]",
      "//*[contains(@class,'swiper-slide')]",
      "//*[contains(@class,'fade')]",
      "//*[contains(@class,'floating-icon')]",
      "//*[contains(@class,'footer-bottom-bar')]",
      "//*[contains(@class,'cookie')]",
      "//*[contains(@class,'header-top-bar')]",
      "//*[@id='fb-root']",
      "//*[contains(@class,'fb-customerchat')]",
      "//*[starts-with(@id,'__BVID__')]",
    ];

    $selectors = array_values(array_unique(array_merge($ignoreSelectors, $defaultSelectors)));

    // --- ลบ node ที่ match (เฉพาะใน body) ---
    foreach ($selectors as $sel) {
      try {
        $nodeList = $xpath->query($sel);
        if (!$nodeList || $nodeList->length === 0) continue;
        $nodes = iterator_to_array($nodeList, false);

        foreach ($nodes as $n) {
          // skip ถ้า node อยู่ใน head
          $p = $n->parentNode;
          $insideHead = false;
          while ($p) {
            if (strtolower($p->nodeName) === 'head') {
              $insideHead = true;
              break;
            }
            $p = $p->parentNode;
          }
          if (!$insideHead && $n->parentNode) {
            $n->parentNode->removeChild($n);
          }
        }
      } catch (\Throwable $e) {
        // ข้าม selector ที่ query ไม่ได้
      }
    }

    // --- ล้าง attribute สุ่มใน body ---
    foreach ($doc->getElementsByTagName('*') as $el) {
      if (!$el->hasAttributes()) continue;
      $remove = [];
      foreach (iterator_to_array($el->attributes) as $attr) {
        $name = strtolower($attr->name);
        if (preg_match('/^(data-|aria-|nonce|integrity|crossorigin)/', $name)) $remove[] = $name;
        if (strpos($name, 'on') === 0) $remove[] = $name;
      }
      foreach ($remove as $r) {
        $el->removeAttribute($r);
      }
    }

    // --- save body ที่เหลือ ---
    $bodyHtml = $doc->saveHTML($bodyNode);

    // --- ประกอบกลับ (ใช้ head เดิมจาก DOM) ---
    $finalHtml = "<!DOCTYPE html>\n<html>\n{$headHtml}\n{$bodyHtml}\n</html>";

    // --- ยุบช่องว่าง + save debug ---
    $finalHtml = preg_replace('/\s+/', ' ', $finalHtml);
    // file_put_contents(storage_path('app/normalized.html'), $finalHtml);

    return trim($finalHtml);
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

    private function assetsAndOutboundFromHtml(string $html): array
    {
        $crawler = new Crawler($html);
        $assets = [];
        $hosts = [];

        foreach ($crawler->filter('link,script,img,iframe') as $n) {
            $u = $n->getAttribute('href');
            if (!$u) {
                $u = $n->getAttribute('src');
            }
            if ($u) {
                $assets[] = $u;
            }
            $p = parse_url($u ? $u : '');
            if (!empty($p['host'])) {
                $hosts[strtolower($p['host'])] = true;
            }
        }
        foreach ($crawler->filter('a') as $a) {
            $p = parse_url($a->getAttribute('href') ? $a->getAttribute('href') : '');
            if (!empty($p['host'])) {
                $hosts[strtolower($p['host'])] = true;
            }
        }

        sort($assets);
        $outbound = array_keys($hosts);
        sort($outbound);

        return [$assets, $outbound];
    }

    private function sha256(string $s): string
    {
        return hash('sha256', $s);
    }

    // ===== SimHash แบบเก็บเป็น HEX 16 ตัวอักษร (อิสระจาก 32/64-bit) =====
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
    private function merkleCanonical(array $secDigAssoc): string
    {
        if (empty($secDigAssoc)) {
            return hash('sha256', ''); // กรณีไม่มี section
        }
        // เรียงตามชื่อ selector ให้คงที่เสมอ
        ksort($secDigAssoc, SORT_NATURAL);

        // ต่อเป็น "selector:digest|selector:digest|..."
        $pieces = [];
        foreach ($secDigAssoc as $sel => $h) {
            $pieces[] = $sel . ':' . $h;
        }
        return hash('sha256', implode('|', $pieces));
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

    private function fetchHtmlShell(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
            CURLOPT_ENCODING       => '',
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return $body ?: '';
    }

    private function absolutize(string $path, string $base): string
    {
        if ($path === '') return $base;
        if (preg_match('#^https?://#i', $path)) return $path;
        if (strpos($path, '//') === 0) return (parse_url($base, PHP_URL_SCHEME) ?: 'https') . ':' . $path;
        $p = parse_url($base);
        $origin = ($p['scheme'] ?? 'https') . '://' . $p['host'] . (isset($p['port']) ? (':' . $p['port']) : '');
        if (strpos($path, '/') === 0) return $origin . $path;
        $dir = isset($p['path']) ? rtrim(dirname($p['path']), '/\\') : '';
        return $origin . ($dir ? $dir . '/' : '/') . $path;
    }

    private function extractScriptUrls(string $html, string $baseUrl): array
    {
        $c = new Crawler($html);
        $urls = [];
        $c->filter('script[src]')->each(function ($n) use (&$urls, $baseUrl) {
            $urls[] = $this->absolutize($n->attr('src'), $baseUrl);
        });
        // ให้ main.*.js มาก่อน
        usort($urls, function ($a, $b) {
            $score = function ($u) {
                $s = 0;
                if (preg_match('#/(?:static/)?js/[^/]*main[^/]*\.js#i', $u)) $s += 100;
                if (preg_match('#/(?:static/)?js/#i', $u)) $s += 50;
                if (preg_match('#\.js(\?|$)#i', $u)) $s += 10;
                return $s;
            };
            return $score($b) <=> $score($a);
        });
        // กรอง third-party ที่มักไม่มี API ของเว็บ
        $third = ['google-analytics.com', 'googletagmanager.com', 'googleapis.com', 'gstatic.com', 'unpkg.com', 'jsdelivr.net', 'cloudflare.com', 'cloudfront.net', 'fontawesome.com'];
        $urls = array_values(array_filter(array_unique($urls), function ($u) use ($third) {
            $h = parse_url($u, PHP_URL_HOST) ?: '';
            foreach ($third as $t) if (stripos($h, $t) !== false) return false;
            return true;
        }));
        return array_slice($urls, 0, 12);
    }

    private function fetchText(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
            CURLOPT_ENCODING       => '',
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
        return $body ?: '';
    }

    private function extractApiEndpointsFromJs(string $js, string $baseUrl): array
    {
        $found = [];
        preg_match_all('#https?://[^\s"\'`]+/(?:api|graphql)[^\s"\'`]*#i', $js, $mAbs);
        foreach ($mAbs[0] ?? [] as $u) $found[] = $u;

        preg_match_all('#(?<![a-z0-9_])/(?:api|graphql)[^\s"\'`]*#i', $js, $mRel);
        foreach ($mRel[0] ?? [] as $p) $found[] = $this->absolutize($p, $baseUrl);

        preg_match_all('#(?:fetch|axios\.(?:get|post|put|delete)|new\s+Request)\(\s*[\'"]([^\'"]+)[\'"]#i', $js, $mCalls);
        foreach ($mCalls[1] ?? [] as $p) {
            if (preg_match('#^(?:https?:)?//#', $p)) {
                $found[] = (strpos($p, '//') === 0) ? (parse_url($baseUrl, PHP_URL_SCHEME) . ':' . $p) : $p;
            } elseif (preg_match('#^(?:/)?(?:api|graphql)(?:/|$)#i', $p)) {
                $found[] = $this->absolutize($p, $baseUrl);
            }
        }
        // unique + ตัดพวกไฟล์ .js/.css
        $found = array_values(array_unique(array_filter($found, function ($u) {
            return !preg_match('#\.(?:css|js|map)(\?|$)#i', $u);
        })));
        return $found;
    }

    private function getHtml3($url, $retryCount = 0, $options = [])
  {
    $browser = null;
    $maxRetries = 3;

    try {
      ini_set('max_execution_time', 300);
      ini_set('default_socket_timeout', 300);
      set_time_limit(0);

      // ✅ บังคับให้ Chrome ใช้ HOME และ user data dir ที่ปลอดภัย
      $chromeHome = storage_path('app/chrome_home');
      if (!file_exists($chromeHome)) {
          mkdir($chromeHome, 0777, true);
      }
      putenv('HOME=' . $chromeHome);
      $_ENV['HOME'] = $chromeHome;

      putenv('NODE_PATH=' . base_path('puphpeteer_env/node_modules'));
      $_ENV['NODE_PATH'] = base_path('puphpeteer_env/node_modules');

      // 🟩 ปิด Chrome ที่ค้างไว้
      @exec("pkill -f 'chrome --headless' >/dev/null 2>&1");

      $puppeteer = new \Nesk\Puphpeteer\Puppeteer([
        'read_timeout' => 300,
        'idle_timeout' => 300,
      ]);

      $browser = $puppeteer->launch([
        'executablePath' => '/usr/bin/google-chrome',
        'headless' => true,
        'args' => [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-dev-shm-usage',
          '--disable-gpu',
          '--single-process',
          '--no-zygote',
          '--disable-background-timer-throttling',
          '--disable-renderer-backgrounding',
          '--disable-background-networking',
          '--disable-features=IsolateOrigins,site-per-process',
          '--window-size=1920,1080',
          "--user-data-dir={$chromeHome}/user_data",
        ],
      ]);

      $page = $browser->newPage();
      $page->setDefaultNavigationTimeout(90000); // เพิ่มเป็น 90 วินาที

      // 🟩 Set User-Agent to bypass basic blocking
      if (!empty($options['userAgent'])) {
          $page->setUserAgent($options['userAgent']);
      } else {
          $page->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
      }

      // 🟩 เปิด JavaScript
      $page->setJavaScriptEnabled(true);

      $page->goto($url, [
        'timeout' => 90000,
        'waitUntil' => ['load', 'domcontentloaded', 'networkidle0'], // รอ network ให้เงียบสนิท
      ]);

      // 🟩 รอให้หน้าเว็บโหลดเสร็จ
      sleep(2);

      // 🟩 SCROLL เพื่อ TRIGGER LAZY LOADING
      $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
            async () => {
                // Scroll ลงไปทีละน้อยเพื่อ trigger lazy loading
                const scrollStep = 300;
                const scrollDelay = 200;
                
                const totalHeight = Math.max(
                    document.body.scrollHeight,
                    document.documentElement.scrollHeight
                );
                
                for (let scrolled = 0; scrolled < totalHeight; scrolled += scrollStep) {
                    window.scrollTo(0, scrolled);
                    await new Promise(resolve => setTimeout(resolve, scrollDelay));
                }
                
                // Scroll กลับขึ้นบน
                window.scrollTo(0, 0);
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        "));

      // 🟩 รอ network requests ที่เกิดจาก scroll
      sleep(3); // รอให้ lazy loading โหลดเสร็จ

      // 🟩 WAIT UNTIL DOM STABLE (เพิ่มเวลารอ)
      $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
            () => {
                return new Promise(resolve => {
                    let last = document.body.innerHTML.length;
                    let stableCount = 0;
                    let attempts = 0;
                    const maxAttempts = 60;   // 30 seconds (60 × 500ms) - เพิ่มจาก 20

                    const check = () => {
                        attempts++;
                        const now = document.body.innerHTML.length;

                        if (now === last) {
                            stableCount++;
                            if (stableCount >= 4) return resolve(true); // stable 2s (เพิ่มจาก 3)
                        } else {
                            stableCount = 0;
                        }

                        last = now;

                        // Fallback → do not wait forever
                        if (attempts >= maxAttempts) {
                            console.log('DOM stability timeout, proceeding anyway');
                            return resolve(true);
                        }

                        setTimeout(check, 500);
                    };

                    check();
                });
            }
        "));

      // 🟩 รอเพิ่มอีกนิดเพื่อให้แน่ใจว่า dynamic content โหลดเสร็จ
      sleep(1);

      // 🟩 Pull final HTML snapshot
      $content = $page->content();

      // 🟩 ตรวจสอบว่า content ที่ได้มามีความสมบูรณ์หรือไม่
      if (strlen($content) < 500) {
        \Log::warning("getHtml3: Content too short ({$url}), length: " . strlen($content));
        throw new \Exception("Content too short, possible network error");
      }

      // 🟩 Log เพื่อ debug
      $elementCount = $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
            () => document.querySelectorAll('*').length
        "));
      \Log::info("getHtml3: Captured {$elementCount} elements from {$url}");

      return ['content' => $content, 'success' => true];
    } catch (\Throwable $e) {
      $errorMsg = $e->getMessage();

      // 🟩 ตรวจสอบว่าเป็น network error หรือไม่
      $isNetworkError = (
        stripos($errorMsg, 'ERR_SOCKET_NOT_CONNECTED') !== false ||
        stripos($errorMsg, 'ERR_CONNECTION') !== false ||
        stripos($errorMsg, 'ERR_NETWORK') !== false ||
        stripos($errorMsg, 'ERR_TIMED_OUT') !== false ||
        stripos($errorMsg, 'Navigation timeout') !== false ||
        stripos($errorMsg, 'Content too short') !== false
      );

      \Log::error("Puphpeteer Error (attempt " . ($retryCount + 1) . "/{$maxRetries}): {$errorMsg} at {$url}");

      // 🟩 ถ้าเป็น network error และยังลองไม่ถึง max retries ให้ลองใหม่
      if ($isNetworkError && $retryCount < $maxRetries) {
        \Log::info("Retrying {$url} (attempt " . ($retryCount + 2) . "/{$maxRetries})");

        // ปิด browser ก่อน retry
        if ($browser) {
          try {
            $browser->close();
          } catch (\Throwable $ex) {
            // ignore
          }
        }

        // รอสักครู่ก่อน retry
        sleep(2 + $retryCount); // รอนานขึ้นทุกครั้งที่ retry

        return $this->getHtml3($url, $retryCount + 1, $options);
      }

      // 🟩 ถ้า retry หมดแล้วหรือไม่ใช่ network error ให้ใช้ fallback
      // แต่ return พร้อม error flag เพื่อไม่ให้ diff
      $fallbackResult = $this->getHtmlFallback($url);
      $fallbackResult['success'] = false;
      $fallbackResult['error'] = $errorMsg;

      return $fallbackResult;
    } finally {
      if ($browser) {
        try {
          $browser->close();
        } catch (\Throwable $ex) {
          \Log::warning("Browser close failed: " . $ex->getMessage());
        }
      }
    }
  }





    // เก็บ function เก่าไว้เป็น fallback
    private function getHtmlFallback($url)
    {
        $content = $this->get_dataa3($url);
        return array(
            'content' => $content
        );
    }

    function get_dataa3($url)
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
    function checkHttpStatus($url)
    {
        // เติม http:// ถ้าไม่มี
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'http://' . $url;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,   // ใช้ HEAD เพื่อลดข้อมูลโหลด
            CURLOPT_FOLLOWLOCATION => true,   // ตาม redirect
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'WebDefacementBot/1.0'
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // ถ้าเว็บไม่รองรับ HEAD (405) → ลอง GET 1 ไบต์แทน
        if ($code === 405) {
            curl_setopt_array($ch, [
                CURLOPT_NOBODY => false,
                CURLOPT_RANGE  => '0-0'
            ]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }

        curl_close($ch);
        return $code;
    }
}
