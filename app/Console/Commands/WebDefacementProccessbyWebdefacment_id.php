<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use App\Console\Commands\compareImages;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentImageMark;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataLog;
use Illuminate\Support\Facades\Artisan;

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use Modules\WebDefacement\Entities\TestHTMLWeb;
use Illuminate\Support\Facades\Mail;
use App\Mail\DefacementAlertMail;
use App\Entities\TransactionBatchjob;
use Illuminate\Support\Facades\DB;
use App\Services\WebDefacementService;


class WebDefacementProccessbyWebdefacment_id extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */

  protected $signature = 'app:WebDefacementProccessbyWebdefacment_id  {webdefacment_id}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'WebDefacementProccessbyWebdefacment_id';
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
    $webdefacment_id = $this->argument('webdefacment_id');

    $WebdefacmentSetting_datas = WebdefacmentSetting::where('id', $webdefacment_id)->whereNull('deleted_at')->get();

    if ($WebdefacmentSetting_datas->isEmpty()) {
        return;
    }
    
    foreach ($WebdefacmentSetting_datas as $key => $value) {
      $WebdefacmentSetting_update =   WebdefacmentSetting::find($value->id);
      $WebdefacmentSetting_update->webdeflacement_progress = 2;
      $WebdefacmentSetting_update->save();



      $webdefacment_id = $value->id;
      $WebdefacmentSetting_data =    $value;
      if ($WebdefacmentSetting_data) {
        $WebdefacmentDataOriginal_data =    WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
        if (!$WebdefacmentDataOriginal_data) {

          Artisan::call('app:WebDefacementUpdateOriginal', ['webdefacment_id' => $webdefacment_id]);
          Artisan::call('app:WebDefacementsCreenshotCheck', ['url' => $value->url, 'port' => $value->port, 'site_id' => $value->site_id, 'url_id' => 0, 'delay' => $value->delay_screen_shot_val]);
          $WebdefacmentDataOriginal_data =    WebdefacmentDataOriginal::where('webdefacment_setting_id', $webdefacment_id)->first();
        }
        if ($WebdefacmentDataOriginal_data) {


          $totalPoint = 0;
          $totalConfig = 0;
          $trackList['blacklist_parcent']  = 0;
          $trackList['file_size_parcent']  = 0;
          $trackList['hash_parcent']  = 0;
          $trackList['image_parcent']  = 0;
          $trackList['all_element_parcent'] = 0;
          $blackListFoundString = array();



          $url = $WebdefacmentSetting_data->url;
          $Keyword_check = array();
          $result = array();
          if ($this->is_url($url)) {
            $result["Result"] = 1;
            $result["messes "] = "";
            $result['hash_code'] = "";
            $result["file_size"] = 0;
            $result["all_element"] = 0;
            $result['image_url'] = "";
            $result["image_path_original"] = "";
            $result['blacklist'] = array();
            $message = ' <div class="main-card-log">';
            $result['image1Hash']  = "";
            $result['image2Hash']  = "";
            $result['image_diff']  = null;


            $result['all_element_parcent']  = 0;
            $result['file_size_parcent']  = 0;
            $result['hash_parcent']  = 0;
            $result['image_parcent']  = 0;
            $result['blacklist_parcent']  = 0;
            $result['_score_percent']  = 0;


            $image_path_2 = "";
            $response = $this->getHtml3($url);

            $htmlFetchFailed = !isset($response['content']) 
                            || $response['content'] === FALSE 
                            || $response['content'] === '' 
                            || (isset($response['success']) && $response['success'] === false);

            if ($htmlFetchFailed) {
              $webContent = "";
              $result["Result"] = 0;
              $result["messes "] = "Html not found" . (isset($response['error']) ? ": " . $response['error'] : "");
              Log::warning("[DefaceNow] HTML fetch failed for {$url}: " . ($response['error'] ?? 'unknown'));
            } else {
              $webContent = $response['content'];

              // ===== [SECTION MONITOR] baseline + diff (no-null, adopt keys) =====
              // ===== [SECTION MONITOR] (patched) =====
              try {
                // 1) config
                // 🟩 Synced with WebDefacementProccess (Cron)
                $selectors = json_decode($WebdefacmentSetting_data->hash_selectors ?: '[]', true) ?: [
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
                $ignores   = json_decode($WebdefacmentSetting_data->hash_ignore_selectors ?: '[]', true) ?: [
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
                  "//*[contains(@class,'v-application')]",
                  "//*[contains(@class,'v-main')]",
                  "//*[contains(@class,'v-navigation-drawer')]",
                  "//*[contains(@class,'v-skeleton-loader')]",
                  "//*[starts-with(@id,'__nuxt')]"
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
                $ignoreAsstPat = json_decode($WebdefacmentSetting_data->asset_ignore_patterns ?: '[]', true) ?: [
                  'news_main_pic',
                  'files-rice-',
                ];

                // 2) normalize + extract (เหมือนเดิม)
                $domNorm      = $this->normalizeHtml($webContent, $ignores);
                $sectionsText = $this->sectionsText($domNorm, $selectors);
                $sectionsHtml = $this->sectionsHtml($domNorm, $selectors);
                $textAll      = implode("\n", array_values($sectionsText));




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

                $crawler = new \Symfony\Component\DomCrawler\Crawler($domNorm);
                if ($crawler->filter('body')->count()) {
                  $textFullPage = trim(preg_replace('/\s+/', ' ', $crawler->filter('body')->text(' ')));
                } else {
                  $textFullPage = trim(preg_replace('/\s+/', ' ', strip_tags($domNorm)));
                }

                $simNowHex = $this->simhash64_hex($this->normalizeTextForSimhash($textFullPage));



                // 4) !! เปลี่ยนตรงนี้ !!  ดึง assets/outbound จาก “ทั้งหน้าเดิม” ไม่ใช่เฉพาะ sections
                list($assetsAllFull, $outboundNow) = $this->assetsAndOutboundFromHtml($domNorm, $url);
                $assetsNow = [];
                foreach ($assetsAllFull as $a) {
                  // Check ignore first
                  $isIgnored = false;
                  foreach ($ignoreAsstPat as $ipat) {
                    if (@preg_match('/' . $ipat . '/', $a)) {
                      $isIgnored = true;
                      break;
                    }
                  }
                  if ($isIgnored) continue;

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

                $isFirstBaseline = (empty($baselineMerkle) || empty($baselineSec));

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
                  $reason = 'baseline_init';
                } else {
                  // $simBits = $this->hamming64_hex($simNowHex, $baselineSimHex);
                  // $signals_assets   = min(1.0, (count($assets_add) + count($assets_del)) / 10.0);
                  // $signals_outbound = min(1.0, (count($out_add)) / 10.0);
                  // $merkleChanged = (int)($merkleNow !== $baselineMerkle);
                  // if ($adopted) {
                  //   $merkleChanged = 0;
                  // } // รอบ adopt ไม่เอาไปคิดสัญญาณ

                  // $score = 0.80 * $merkleChanged
                  //   + 0.15 * ($simBits / 64.0)
                  //   + 0.15 * min(1.0, (count($assets_add) + count($assets_del)) / 10.0)
                  //   + 0.15 * min(1.0, (count($out_add)) / 10.0);

                  // คำนวณคอมโพเนนต์ต่าง ๆ
                  $simBits = $this->hamming64_hex($simNowHex, $baselineSimHex);

                  // เดิมมีอยู่แล้ว
                  // $signals_assets   = min(1.0, (count($assets_add) + count($assets_del)) / 10.0); // 0..1
                  // $signals_outbound = min(1.0, (count($out_add)) / 10.0);                          // 0..1



                  // แทนที่บรรทัดเดิม:
                  // $signals_assets = min(1.0, (count($assets_add) + count($assets_del)) / 10.0);

                  // คิดจาก baseline ตรง ๆ (ดิบ) มีเท่าไหร่ก็นับเท่านั้น
                  $assetsBaseArr = is_array($assetsBase) ? $assetsBase : [];
                  $baselineAssetsTotal = count($assetsBaseArr);  // <-- ใช้ดิบ ไม่ unique/ไม่ filter

                  $changedAssets = (is_array($assets_add) ? count($assets_add) : 0)
                    + (is_array($assets_del) ? count($assets_del) : 0);

                  // ปัดเศษไว้เลย
                  $signals_assets   = ($baselineAssetsTotal > 0)
                    ? min(1.0, $changedAssets / $baselineAssetsTotal)
                    : (($changedAssets > 0) ? 1.0 : 0.0);

                  $component_assets = 0.20 * $signals_assets;

                  // ปัด 3 ตำแหน่ง
                  $signals_assets   = round($signals_assets, 3);
                  $component_assets = round($component_assets, 3);



                  // baseline จาก Settings
                  $outBase = json_decode($WebdefacmentSetting_data->baseline_outbound ?? '[]', true);
                  if (!is_array($outBase)) $outBase = [];
                  $baselineOutTotal = count($outBase);

                  // outbound ใหม่จาก Check
                  $outChk = WebdefacmentDataCheck::where('webdefacment_setting_id', $WebdefacmentSetting_data->id)
                    ->latest()
                    ->first();

                  $outNotWhitelisted = json_decode($outChk->outbound_new_not_whitelisted ?? '[]', true);
                  if (!is_array($outNotWhitelisted)) {
                    $outNotWhitelisted = [];
                  }
                  $changedOutbound = count($outNotWhitelisted);

                  // คำนวณสัญญาณ
                  $signals_outbound = 0.0;
                  if ($baselineOutTotal > 0) {
                    $signals_outbound = min(1.0, $changedOutbound / $baselineOutTotal);
                  } elseif ($changedOutbound > 0) {
                    $signals_outbound = 1.0;
                  }

                  // ปัดเศษ 3 ตำแหน่ง
                  $signals_outbound = round($signals_outbound, 3);
                  // $component_domain = round(0.20 * $signals_outbound, 3);



                  // ===== ใช้ $sectionDiffs โดยตรง =====
                  $sectionRatio     = 0.0;
                  $sectionsTotal    = 0;
                  $sectionsChanged  = 0;
                  $skippedEmptyPair = 0;



                  if (is_array($sectionDiffs) && !empty($sectionDiffs)) {
                    foreach ($sectionDiffs as $row) {
                      $old = $row['old'] ?? null;
                      $new = $row['new'] ?? null;

                      $hasOld = !$this->isEmptyHash($old);
                      $hasNew = !$this->isEmptyHash($new);

                      // ถ้าว่างทั้งคู่ => ไม่นับเข้าฐาน 100%
                      if (!$hasOld && !$hasNew) {
                        $skippedEmptyPair++;
                        continue;
                      }

                      // มีอย่างน้อยหนึ่งฝั่ง => นับเป็นฐาน 100%
                      $sectionsTotal++;

                      $flagChanged  = isset($row['changed']) ? (bool)$row['changed'] : null;
                      $xorEmpty     = ($hasOld xor $hasNew);                         // อีกฝั่งว่าง
                      $hashNotEqual = ($hasOld && $hasNew && trim($old) !== trim($new)); // มีทั้งคู่แต่ไม่เท่ากัน

                      if ($xorEmpty || $hashNotEqual || $flagChanged === true) {
                        $sectionsChanged++;
                      }
                    }

                    if ($sectionsTotal > 0) {
                      $sectionRatio = $sectionsChanged / $sectionsTotal; // 0..1
                    }
                  }

                  // รอบ adopted ไม่คิดสัญญาณจาก section_diff
                  if (!empty($adopted)) {
                    $sectionRatio = 0.0;
                  }

                  // คอมโพเนนต์ตามน้ำหนักใหม่
                  $component_section = 0.50 * $sectionRatio;                    // สูงสุด 0.50
                  $component_assets  = 0.20 * $signals_assets;                  // สูงสุด 0.20
                  $component_domain  = 0.20 * $signals_outbound;                // สูงสุด 0.20
                  $simBitsThreshold  = 30; // ≤30 bits = dynamic noise, ไม่นับเป็นคะแนน
                  $effectiveSimBits  = max(0, $simBits - $simBitsThreshold);
                  $component_bits    = 0.10 * min(1.0, $effectiveSimBits / (64.0 - $simBitsThreshold));  // สูงสุด 0.10

                  // รวมคะแนนสุดท้าย (0..1)
                  $score = $component_section + $component_assets + $component_domain + $component_bits;

                  // คํานวณเหตุผล (synced with Cron)
                  $reason = $adopted ? 'baseline_adopted_new_selectors'
                    : (!empty($outbound_new_not_whitelisted) ? 'new_outbound_domain'
                      : ((count($assets_add) + count($assets_del)) >= 2 ? 'assets_delta'
                        : ($score >= 0.75 ? 'score_threshold' : null)));
                }


                // 9) เก็บไว้ใน $result
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

                $result['_score_percent']          = $score * 100;

              } catch (\Throwable $e) {
                Log::error('[SECTION MONITOR] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
              }
              // ===== [/SECTION MONITOR] =====




              if ($WebdefacmentSetting_data->hash == 1) {




                $totalConfig += 1;
                $hashMD5 = hash($this->hashingAlgorithm, $webContent);
                $result['hash_code'] = $hashMD5;
                if ($WebdefacmentDataOriginal_data->hash == null) {
                  $WebdefacmentDataOriginal_data->hash = $hashMD5;
                  $WebdefacmentDataOriginal_data->save();
                }
                if ($result['hash_code'] != $WebdefacmentDataOriginal_data->hash) {
                  $result['hash_parcent']  = 100;
                } else {
                  $result['hash_parcent']  = 0;
                }

                $message = $message . '
                <div class="card-log">
                <div class="card-log-body">
                <p>Hash Difference ' . $result['hash_parcent'] . '%</p>
                </div>
                </div>';
              }
              if ($WebdefacmentSetting_data->filesize == 1) {
                $totalConfig += 1;
                $file_size = strlen($domNorm); //filesize - 🟩 Sync: ใช้ $domNorm เหมือน WebDefacementProccess
                $result["file_size"] = $file_size;

                if ($WebdefacmentDataOriginal_data->filesize == null || $WebdefacmentDataOriginal_data->filesize == 0) {
                  $WebdefacmentDataOriginal_data->filesize = $file_size;
                  $WebdefacmentDataOriginal_data->save();
                }

                // 🟩 Sync กับ WebDefacementProccess: ใช้สัดส่วนจริง
                $original = $WebdefacmentDataOriginal_data->filesize;
                $new      = $result['file_size'];
                $diff     = abs($original - $new);

                // ป้องกันหารศูนย์
                if ($original === 0) {
                  $result['file_size_parcent'] = $new > 0 ? 100 : 0;
                } else {
                  // คำนวณสัดส่วนต่างจากไฟล์เดิม
                  $percent = ($diff / $original) * 100;

                  // ถ้าอยากจำกัดสูงสุดไม่เกิน 100
                  if ($percent > 100) $percent = 100;

                  $result['file_size_parcent'] = round($percent, 2); // ปัดทศนิยม 2 ตำแหน่ง
                }

                $message = $message . '
                <div class="card-log">
                <div class="card-log-body">
                <p>File Size Difference ' . $result['file_size_parcent'] . '%</p>
                </div>
                </div>';
              }
              if ($WebdefacmentSetting_data->element == 1) {
                $totalConfig += 1;
                $allElement = preg_match_all('/<([^\/!][a-z1-9]*)/i', $domNorm, $matches); // 🟩 Sync: ใช้ $domNorm เหมือน WebDefacementProccess
                $result['all_element'] = (int)$allElement;

                if ($WebdefacmentDataOriginal_data->element == null || $WebdefacmentDataOriginal_data->element == 0) {
                  $WebdefacmentDataOriginal_data->element = $result['all_element'];
                  $WebdefacmentDataOriginal_data->save();
                }

                // 🟩 Sync กับ WebDefacementProccess: ใช้สัดส่วนจริง
                $original = $WebdefacmentDataOriginal_data->element;
                $new      = $result['all_element'];
                $diff     = abs($original - $new);

                if ($original === 0) {
                  $result['all_element_parcent'] = $new > 0 ? 100 : 0;
                } else {
                  $percent = ($diff / $original) * 100;  // เอาสัดส่วนการเปลี่ยนแปลงจริง
                  if ($percent > 100) $percent = 100;    // จำกัดสูงสุดที่ 100%
                  $result['all_element_parcent'] = round($percent, 2);
                }
                $message = $message . '
              <div class="card-log">
              <div class="card-log-body">
              <p>Element Difference ' . $result['all_element_parcent'] . '%</p>
              </div>
              </div>';
              }
              if ($WebdefacmentSetting_data->image_check == 2) {
                $totalConfig += 0.5;
                $url_id = $WebdefacmentDataOriginal_data->url_id;
                $site_id = $WebdefacmentSetting_data->site_id;
                $delay = $WebdefacmentSetting_data->delay_screen_shot_val;
                if (!$delay) {
                  $delay = 2000;
                }
                if (!$url_id) {
                  $url_id = rand(10, 100);
                }

                $image_name =  $site_id . '_' . $url_id . '_' . 'Defacement_Now';
                $result["image_url"] = "/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . ".png";
                $path_include = base_path() . '/public/screenshot/use/DownloadImage.php';
                include_once($path_include);
                $downloadImg = new \DownloadImage();
                $Path_image = base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . ".png";
                $downloadImg->download($url, $Path_image, $delay);
                $result["image_path_original_full"] = $Path_image;
                $result["image_path_original"] = "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . ".png";
                $result["url_id"] = $url_id;

                $WebdefacmentImageMark_check = WebdefacmentImageMark::where('webdefacment_data_original_id', $webdefacment_id)->get();
                if (count($WebdefacmentImageMark_check) > 0) {
                  $dir_folder_image_original = base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/image_original.png";
                  $image_original = imagecreatefrompng($dir_folder_image_original);
                  $black_original = ImageColorAllocate($image_original, 242, 242, 242);


                  $dir_folder_image_compare = base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . ".png";
                  $image_compare = imagecreatefrompng($dir_folder_image_compare);
                  $black_compare = ImageColorAllocate($image_compare, 242, 242, 242);

                  foreach ($WebdefacmentImageMark_check as $WebdefacmentImageMark_checkkey => $WebdefacmentImageMark_checkvalue) {
                    ImageFilledRectangle($image_original, $WebdefacmentImageMark_checkvalue->left, $WebdefacmentImageMark_checkvalue->top, $WebdefacmentImageMark_checkvalue->width, $WebdefacmentImageMark_checkvalue->hight, $black_original);

                    ImageFilledRectangle($image_compare, $WebdefacmentImageMark_checkvalue->left, $WebdefacmentImageMark_checkvalue->top, $WebdefacmentImageMark_checkvalue->width, $WebdefacmentImageMark_checkvalue->hight, $black_compare);
                  }

                  ImagePng($image_original, base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/image_original_custom.png");
                  ImagePng($image_compare, base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . "_custom.png");
                  // $compareImage = $this->compareImage2(base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/", 'image_original_custom.png', $image_name . "_custom.png");

                  $imageOriginalPath = $dirPath . $imgSourcePath;
                  $imageComparePath = $dirPath . $imgComparePath;

                  try {
                    if (file_exists($imageOriginalPath) && file_exists($imageComparePath)) {
                      $comparer = new compareImages($imageOriginalPath);
                      $imageDiffPercent = $comparer->compareBySlices($imageComparePath, 1000);

                      $result['image_parcent'] = $imageDiffPercent;
                      $result['image1Hash'] = $comparer->getHasString();
                      $result['image2Hash'] = $comparer->hasString($imageComparePath);
                      $result['image_diff'] = $imageDiffPercent;
                    } else {
                      Log::warning("ไฟล์ภาพไม่พบ: $imageOriginalPath หรือ $imageComparePath");
                      $result['image_parcent'] = 0;
                      $result['image1Hash'] = '';
                      $result['image2Hash'] = '';
                      $result['image_diff'] = 0;
                    }
                  } catch (\Throwable $e) {
                    Log::error("เกิดข้อผิดพลาดในการเปรียบเทียบภาพ: " . $e->getMessage());
                    $result['image_parcent'] = 0;
                    $result['image1Hash'] = '';
                    $result['image2Hash'] = '';
                    $result['image_diff'] = 0;
                  }

                  $result["image_url"] = "/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . "_custom.png";
                  $result["image_path_original"] = "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . "_custom.png";
                  $image_path_2  = "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/" . $image_name . ".png";
                } else {

                  $compareImage = $this->compareImage2(base_path() . "/public/images/webdefacment_mages/" . $site_id . "/" . $url_id . "/", 'image_original.png', $image_name . ".png");
                }


                if ($compareImage["diff"]  == 0) {
                  $result['image_parcent']  = 0;
                } else if ($compareImage["diff"] < 5) {
                  $pageColorDiff = true;
                  $totalPoint = $this->addPoint($totalPoint);
                  $result['image_parcent']  = 10;
                } else if ($compareImage["diff"] < 11) {
                  $pageColorDiff = true;
                  $totalPoint = $this->addPoint($totalPoint);
                  $result['image_parcent']  = 30;
                } else if ($compareImage["diff"] < 20) {
                  $pageColorDiff = true;
                  $totalPoint = $this->addPoint($totalPoint);
                  $result['image_parcent']  = 50;
                } else if ($compareImage["diff"] < 30) {
                  $pageColorDiff = true;
                  $totalPoint = $this->addPoint($totalPoint);
                  $result['image_parcent']  = 70;
                } else if ($compareImage["diff"] < 40) {
                  $pageColorDiff = true;
                  $totalPoint = $this->addPoint($totalPoint);
                  $result['image_parcent']  = 80;
                } else {
                  $pageColorDiff = true;
                  $totalPoint = $this->addPoint($totalPoint);
                  $result['image_parcent']  = 100;
                }
                $result['image1Hash']  = $compareImage["image1Hash"];
                $result['image2Hash']  = $compareImage["image2Hash"];
                $result['image_diff']  =  $compareImage["diff"];

                $message = $message . '
            <div class="card-log">
            <div class="card-log-body">
            <p>image Difference ' . $result['image_parcent'] . '%</p>
            </div>
            </div>';
              }


              $result_checkDomainHeaders =   $this->checkDomainHeaders($url, 1);
              $result_URL_404 =   $this->URL_404($url);
              $result["DomainHeaders"] = $result_checkDomainHeaders;
              $result["Is_URL_404"] = $result_URL_404;

              if ($WebdefacmentSetting_data->blacklist_keyword == 1) {
                $blackListFound = $this->trackKeyWords($webContent, $WebdefacmentSetting_data->blacklist_keyword_content, $Keyword_check);
                $result['blacklist'] = $blackListFound;
                if (count($blackListFound) > 0) {
                  $totalPoint = $this->addPoint($totalPoint);
                  $result['blacklist_parcent']  = 100;
                  // Format
                  foreach ($blackListFound as $value) {
                    array_push($blackListFoundString, $value["key"] . "(Position:[" . $value["position"] . "])");
                  }
                } else {
                  $result['blacklist_parcent']  = 0;
                }


                $message = $message . '
            <div class="card-log">
            <div class="card-log-body">
            <p>blacklist Difference ' . $result['blacklist_parcent'] . '%</p>
            </div>
            </div>';
              }



              // 🟩 Added: Log Assets Difference (Add/Del)
              $assets_diff_count = count($result['_assets_add'] ?? []) + count($result['_assets_del'] ?? []);
              if ($assets_diff_count > 0) {
                  $assetsMsg = "Changed: $assets_diff_count files";
                  $message = $message . '
                  <div class="card-log">
                  <div class="card-log-body">
                  <p>Assets Difference: ' . $assetsMsg . '</p>
                  </div>
                  </div>';
              }



              $pointAlert = $this->calculatePoint2($result, $totalConfig);

              $status = 'Normal';
              if ($pointAlert >= 50 && $pointAlert < 74) {
                $status = 'Medium';
              } else if ($pointAlert >= 75) {
                $status = 'High';
              }

              /* 🟩 REMOVED Force Medium Block by user request */
              $WebdefacmentDataCheck_save = new WebdefacmentDataCheck;
              $WebdefacmentDataCheck_save->webdefacment_setting_id = $webdefacment_id;
              $WebdefacmentDataCheck_save->hash_old =  $WebdefacmentDataOriginal_data->hash;
              $WebdefacmentDataCheck_save->hash_new =   $result['hash_code'];
              $WebdefacmentDataCheck_save->hash_percent =   $result['hash_parcent'];
              $WebdefacmentDataCheck_save->filesize_old =    $WebdefacmentDataOriginal_data->filesize;
              $WebdefacmentDataCheck_save->filesize_new =   $result['file_size'];
              $WebdefacmentDataCheck_save->filesize_percent =   $result['file_size_parcent'];
              $WebdefacmentDataCheck_save->element_old =    $WebdefacmentDataOriginal_data->element;
              $WebdefacmentDataCheck_save->element_new =   $result['all_element'];
              $WebdefacmentDataCheck_save->element_percent =   $result['all_element_parcent'];


              $WebdefacmentDataCheck_save->image_old =    $WebdefacmentDataOriginal_data->imageHash;
              $WebdefacmentDataCheck_save->image_new =   $result['image2Hash'];
              $WebdefacmentDataCheck_save->image_diff =   $result['image_diff'];
              $WebdefacmentDataCheck_save->image_percent =   $result['image_parcent'];

              $WebdefacmentDataCheck_save->image_url =   $result['image_url'];
              $WebdefacmentDataCheck_save->image_part =   $result['image_path_original'];
              $WebdefacmentDataCheck_save->keyword =   implode(",", $blackListFoundString);
              $WebdefacmentDataCheck_save->keyword_percent  = $result['blacklist_parcent'];
              $WebdefacmentDataCheck_save->last_update = date("Y-m-d H:i:s");
              $WebdefacmentDataCheck_save->percent_all = $pointAlert;
              $WebdefacmentDataCheck_save->status_code = $status;
              $WebdefacmentDataCheck_save->webdeflacement_progress = 1;

              if ($image_path_2) {
                $WebdefacmentDataCheck_save->image_part_2 = $image_path_2;
              }

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

              $WebdefacmentDataCheck_save->save();



              $WebdefacmentSetting_update =   WebdefacmentSetting::find($webdefacment_id);


              $color = "#88ce4f  !important";
              if ($status == "High") {
                $color = "#e64732 !important";
              }
              if ($status == "Medium") {
                $color = "#fcc838 !important";
              }

              $message = $message . '<div class="card-log"  style="background: ' . $color . ' "> <!-- ปล. ถ้าใส่สีให้ใช้แบบนี้นะครับ -->
         <div class="card-log-body">
         <p style="color: #fff">Total Difference ' . $pointAlert . '% (' . $status . ')</p>
         </div>
         </div>
         </div>';


              if ($status == 'Normal' || $status == 'Medium') {
                $WebdefacmentSetting_update->webdeflacement_progress = 1;

                if ($status == 'Medium' || (count($result['_assets_add'] ?? []) + count($result['_assets_del'] ?? []) > 0)) {
                  $WebdefacmentDataLog_save = new WebdefacmentDataLog;
                  $WebdefacmentDataLog_save->webdefacment_setting_id  = $webdefacment_id;
                  $WebdefacmentDataLog_save->webdefacment_data_check_id  = $WebdefacmentDataCheck_save->id;

                  $WebdefacmentDataLog_save->hash_percent  = $result['hash_parcent'];
                  $WebdefacmentDataLog_save->filesize_percent  = $result['file_size_parcent'];
                  $WebdefacmentDataLog_save->element_percent  = $result['hash_parcent'];
                  $WebdefacmentDataLog_save->image_percent  = $result['image_parcent'];
                  $WebdefacmentDataLog_save->all_percent  = $pointAlert;
                  $WebdefacmentDataLog_save->message = $message;
                  $WebdefacmentDataLog_save->created_date  = date("Y-m-d H:i:s");
                  $WebdefacmentDataLog_save->updated_date  = date("Y-m-d H:i:s");
                  $WebdefacmentDataLog_save->status_val  =  $status;
                  $WebdefacmentDataLog_save->save();
                }
              } else {
                $WebdefacmentSetting_update->webdeflacement_progress = 3;
                // $WebdefacmentSetting_update->last_check = date("Y-m-d H:i:s");
                $WebdefacmentDataLog_save = new WebdefacmentDataLog;
                $WebdefacmentDataLog_save->webdefacment_setting_id  = $webdefacment_id;
                $WebdefacmentDataLog_save->webdefacment_data_check_id  = $WebdefacmentDataCheck_save->id;

                $WebdefacmentDataLog_save->message = $message;
                $WebdefacmentDataLog_save->created_date  = date("Y-m-d H:i:s");
                $WebdefacmentDataLog_save->updated_date  = date("Y-m-d H:i:s");


                $WebdefacmentDataLog_save->hash_percent  = $result['hash_parcent'];
                $WebdefacmentDataLog_save->filesize_percent  = $result['file_size_parcent'];
                $WebdefacmentDataLog_save->element_percent  = $result['hash_parcent'];
                $WebdefacmentDataLog_save->image_percent  = $result['image_parcent'];
                $WebdefacmentDataLog_save->all_percent  = $pointAlert;


                $WebdefacmentDataLog_save->status_val  =  $status;
                $WebdefacmentDataLog_save->save();
              }

              $WebdefacmentSetting_update->image_last = $result['image_url'];
              $WebdefacmentSetting_update->last_check = date("Y-m-d H:i:s");
              $WebdefacmentSetting_update->last_online = date("Y-m-d H:i:s");
              $WebdefacmentSetting_update->status_val = $status;

              try {
                if ($status === 'High' && !$WebdefacmentSetting_update->is_alert_sent) {

                  // 1) ดึงอีเมลปลายทาง
                  $emails = DB::table('site_config_email_alert_defacement')
                    ->where('site_id', $WebdefacmentSetting_update->site_id)
                    ->pluck('email')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                  // กรองให้เหลืออีเมลที่ valid เท่านั้น
                  $emails = array_values(array_filter($emails, function ($e) {
                    return filter_var($e, FILTER_VALIDATE_EMAIL);
                  }));

                  if (!empty($emails)) {
                    try {
                      // 2) ดึง diff data สำหรับแนบในอีเมล
                      $svc  = app(WebDefacementService::class);
                      $diff = $svc->getDiffData($WebdefacmentSetting_update->id);

                      // 3) normalize เผื่อ service คืน string JSON มา
                      $toArr = function ($v) {
                        return is_string($v) ? (json_decode($v, true) ?: []) : (is_array($v) ? $v : []);
                      };
                      $diff['section_diffs'] = $toArr(isset($diff['section_diffs']) ? $diff['section_diffs'] : []);
                      $diff['assets_add']    = $toArr(isset($diff['assets_add']) ? $diff['assets_add'] : []);
                      $diff['assets_del']    = $toArr(isset($diff['assets_del']) ? $diff['assets_del'] : []);
                      $diff['outbound_new']  = $toArr(isset($diff['outbound_new']) ? $diff['outbound_new'] : []);

                      Mail::to($emails)->send(
                        new DefacementAlertMail($WebdefacmentSetting_update, $diff, 20, null)
                      );

                      // 5) อัปเดตสถานะหลังส่งสำเร็จ
                      $WebdefacmentSetting_update->is_alert_sent = true;
                      $WebdefacmentSetting_update->alert_sent_at = now();
                    } catch (\Throwable $e) {
                      Log::error("แจ้งเตือนล้มเหลว (command): " . $e->getMessage(), [
                        'setting_id' => $WebdefacmentSetting_update->id
                      ]);
                      // ไม่เซ็ต is_alert_sent เพื่อให้ retry รอบหน้า
                    }
                  } else {
                    Log::warning('ไม่พบอีเมลผู้รับ (command)', [
                      'site_id' => $WebdefacmentSetting_update->site_id
                    ]);
                  }
                }
              } catch (\Throwable $e) {
                Log::error("บล็อคแจ้งเตือนล้มเหลว (outer): " . $e->getMessage());
              }


              $WebdefacmentSetting_update->image_original = $result['image_url'];
              $WebdefacmentSetting_update->blacklist_keyword_current = $WebdefacmentDataCheck_save->keyword;
              $WebdefacmentSetting_update->save();

              //delete 4 last row
              $WebdefacmentDataLog_delete_list = array();
              $WebdefacmentDataLog_delete =  WebdefacmentDataLog::where('webdefacment_setting_id', $webdefacment_id)->orderBy('created_at', 'desc')->take(3)->get();
              foreach ($WebdefacmentDataLog_delete as $WebdefacmentDataLog_deletekey => $WebdefacmentDataLog_deletevalue) {
                array_push($WebdefacmentDataLog_delete_list, $WebdefacmentDataLog_deletevalue->id);
              }
              WebdefacmentDataLog::whereNotIn('id', $WebdefacmentDataLog_delete_list)->where('webdefacment_setting_id', $webdefacment_id)->delete();

              //delete 4 last row
              $WebdefacmentDataCheck_delete_list = array();
              $WebdefacmentDataCheck_delete =  WebdefacmentDataCheck::where('webdefacment_setting_id', $webdefacment_id)->orderBy('created_at', 'desc')->take(3)->get();
              foreach ($WebdefacmentDataCheck_delete as $WebdefacmentDataCheck_deletekey => $WebdefacmentDataCheck_deletevalue) {
                array_push($WebdefacmentDataCheck_delete_list, $WebdefacmentDataCheck_deletevalue->id);
              }
              WebdefacmentDataCheck::whereNotIn('id', $WebdefacmentDataCheck_delete_list)->where('webdefacment_setting_id', $webdefacment_id)->delete();




              // print_r($webdefacment_id);
            }
          } else {
            // $result["Result"] = 0;
            // $result["messes "] = "The url is not formatted.";
          }
        } else {
          // $result["Result"] = 0;
          // $result["messes "] = "No data found.";
        }
      }
      $TransactionBatchjob_Update = TransactionBatchjob::where('mode', 'WebDefacement_scan')->first();
      $TransactionBatchjob_Update->progress = 1;
      $TransactionBatchjob_Update->transcation_date_end = date("Y-m-d H:i:s");
      $TransactionBatchjob_Update->transcation_date  = date("Y-m-d H:i:s");
      $TransactionBatchjob_Update->save();
    }


    $result2 = array();
    $result2["Result"] = 1;
    $result2["messes "] = "";

    //print_r($result2);
  }


 private function calculatePoint2($trackList, $totalConfig)
  {
    // [ACTIVE] Use granular _score_percent (Deep Scan) instead of binary hash_parcent
    $totalPoint = $trackList['all_element_parcent'] + $trackList['file_size_parcent'] + $trackList['_score_percent'] + $trackList['image_parcent'] + $trackList['blacklist_parcent'];
    
    if ($totalConfig == 0) return 0;
    $result = $totalPoint / $totalConfig;
    return $result;
  }

  private function addPoint($totalPoint)
  {
    return $totalPoint += 5;
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

    // --- ยุบช่องว่าง ---
    $finalHtml = preg_replace('/\s+/', ' ', $finalHtml);

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

  // ===== Normalize text ก่อน SimHash: ลบ dynamic content (วันที่/เวลา/token/ตัวเลข) =====
  private function normalizeTextForSimhash(string $text): string
  {
    // 1) ลบ ISO date: 2026-02-16, 2026/02/16
    $text = preg_replace('/\b\d{4}[-\/]\d{1,2}[-\/]\d{1,2}\b/', '', $text);

    // 2) ลบ date แบบ dd/mm/yyyy, dd-mm-yyyy
    $text = preg_replace('/\b\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}\b/', '', $text);

    // 3) ลบเวลา: 13:41:46, 1:41 PM, 13:41
    $text = preg_replace('/\b\d{1,2}:\d{2}(:\d{2})?\s*(AM|PM|am|pm)?\b/', '', $text);

    // 4) ลบ hex tokens ยาว >= 16 ตัว (CSRF, nonce, session)
    $text = preg_replace('/\b[0-9a-fA-F]{16,}\b/', '', $text);

    // 5) ลบ UUID
    $text = preg_replace('/\b[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}\b/', '', $text);

    // 6) ลบตัวเลข standalone (view count, pagination, etc.)
    $text = preg_replace('/\b\d{1,10}\b/', '', $text);

    // 7) ยุบ whitespace
    $text = preg_replace('/\s+/', ' ', $text);

    return trim($text);
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

  // ===== getHtml3: Puppeteer-based HTML fetch (synced from WebDefacementProccess) =====
  private function getHtml3($url, $retryCount = 0, $options = [])
  {
    $browser = null;
    $maxRetries = 10;

    try {
      ini_set('max_execution_time', 300);
      ini_set('default_socket_timeout', 300);
      set_time_limit(0);

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
        ],
      ]);

      $page = $browser->newPage();
      $page->setDefaultNavigationTimeout(90000);

      // 🟩 Set User-Agent
      if (!empty($options['userAgent'])) {
          $page->setUserAgent($options['userAgent']);
      } else {
          $page->setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
      }

      $page->setJavaScriptEnabled(true);

      // ✅ waitUntil strategy
      $waitStrategies = [
          ['load', 'domcontentloaded', 'networkidle2'],
          ['load', 'domcontentloaded'],
          ['load'],
      ];
      $strategyIdx = min($retryCount, count($waitStrategies) - 1);
      $waitUntil = $waitStrategies[$strategyIdx];
      $timeout = $retryCount === 0 ? 90000 : 60000;
      
      \Log::info("[getHtml3] Attempt " . ($retryCount + 1) . " for {$url} (waitUntil: " . implode(',', $waitUntil) . ")");

      $response = $page->goto($url, [
        'timeout' => $timeout,
        'waitUntil' => $waitUntil,
      ]);

      $httpStatus = $response ? $response->status() : 0;
      sleep(1);

      // 🟩 SCROLL เพื่อ TRIGGER LAZY LOADING
      $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
            return (async () => {
                const scrollStep = 800; 
                const scrollDelay = 100; 
                
                const totalHeight = Math.max(
                    document.body.scrollHeight,
                    document.documentElement.scrollHeight
                );
                
                for (let scrolled = 0; scrolled < totalHeight; scrolled += scrollStep) {
                    window.scrollTo(0, scrolled);
                    await new Promise(resolve => setTimeout(resolve, scrollDelay));
                }
                
                window.scrollTo(0, 0);
                await new Promise(resolve => setTimeout(resolve, 200));
            })();
        "));

      sleep(2);

      // 🟩 WAIT UNTIL DOM STABLE
      $page->evaluate(\Nesk\Rialto\Data\JsFunction::createWithBody("
            () => {
                return new Promise(resolve => {
                    let last = document.body.innerHTML.length;
                    let stableCount = 0;
                    let attempts = 0;
                    const maxAttempts = 60;

                    const check = () => {
                        attempts++;
                        const now = document.body.innerHTML.length;

                        if (now === last) {
                            stableCount++;
                            if (stableCount >= 4) return resolve(true);
                        } else {
                            stableCount = 0;
                        }

                        last = now;

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

      sleep(1);

      $content = $page->content();

      if (strlen($content) < 500) {
        $title = $page->title();
        $msg = "Content too short. Len: " . strlen($content) . ", Status: {$httpStatus}, Title: {$title}";
        \Log::warning("getHtml3: {$msg} ({$url})");
        throw new \Exception($msg);
      }

      // 🟩 Screenshot Capture (Optional)
      $screenshotBase64 = null;
      $screenshotSaved = false;

      if (!empty($options['screenshot_path'])) {
          try {
              $dir = dirname($options['screenshot_path']);
              if (!is_dir($dir)) {
                  $mkdirResult = @mkdir($dir, 0775, true);
                  if ($mkdirResult) {
                      @chmod($dir, 0775);
                  } else {
                      \Log::warning("[getHtml3] Failed to create directory: {$dir}");
                  }
              }
              
              $page->screenshot([
                  'path' => $options['screenshot_path'],
                  'fullPage' => true
              ]);
              
              if (file_exists($options['screenshot_path'])) {
                  $screenshotSaved = true;
              } else {
                  \Log::warning("[getHtml3] Screenshot command ran but file not created at {$options['screenshot_path']}");
              }
          } catch (\Throwable $e) {
              \Log::warning("[getHtml3] Screenshot save failed ({$url}) - " . $e->getMessage());
          }
      } 
      elseif (!empty($options['screenshot'])) {
          try {
              $screenshotBase64 = $page->screenshot([
                  'encoding' => 'base64',
                  'fullPage' => true
              ]);
          } catch (\Throwable $e) {
              \Log::warning("getHtml3: Screenshot base64 failed ({$url}) - " . $e->getMessage());
          }
      }

      return [
          'content' => $content, 
          'success' => true, 
          'screenshot_base64' => $screenshotBase64,
          'screenshot_saved' => $screenshotSaved
      ];
    } catch (\Throwable $e) {
      $errorMsg = $e->getMessage();

      $isNetworkError = (
        stripos($errorMsg, 'ERR_SOCKET_NOT_CONNECTED') !== false ||
        stripos($errorMsg, 'ERR_CONNECTION') !== false ||
        stripos($errorMsg, 'ERR_NETWORK') !== false ||
        stripos($errorMsg, 'ERR_TIMED_OUT') !== false ||
        stripos($errorMsg, 'Navigation timeout') !== false ||
        stripos($errorMsg, 'Content too short') !== false
      );

      \Log::error("Puphpeteer Error (attempt " . ($retryCount + 1) . "/{$maxRetries}): {$errorMsg} at {$url}");

      if ($isNetworkError && $retryCount < $maxRetries) {
        \Log::info("Retrying {$url} (attempt " . ($retryCount + 2) . "/{$maxRetries})");

        if ($browser) {
          try {
            $browser->close();
          } catch (\Throwable $ex) {
            // ignore
          }
        }

        sleep(2 + $retryCount);

        return $this->getHtml3($url, $retryCount + 1, $options);
      }

      // 🟩 fallback disabled
      $fallbackResult = [];
      $fallbackResult['content'] = ''; 
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

  // Fallback function using curl
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
}
