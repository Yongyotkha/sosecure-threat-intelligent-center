@php
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\Site;
use Illuminate\Support\Str;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
@endphp
@php
function strx($v): string {
if (is_array($v)) {
return (string)($v['name'] ?? json_encode($v, JSON_UNESCAPED_UNICODE));
}
if (is_object($v)) {
return method_exists($v, '__toString')
? (string)$v
: (string)json_encode($v, JSON_UNESCAPED_UNICODE);
}
return (string)$v;
}

$siteName = $w->name ?? 'Unknown Site';

$secRaw = $diff['section_diffs'] ?? [];
$assetsAddRaw = $diff['assets_add'] ?? [];
$assetsDelRaw = $diff['assets_del'] ?? [];
$outNewRaw = $diff['outbound_new'] ?? [];
$score = $diff['score'] ?? 0;

$section = is_string($secRaw) ? (json_decode($secRaw, true) ?: []) : (is_array($secRaw) ? $secRaw : []);
$assetsAdd = is_string($assetsAddRaw) ? (json_decode($assetsAddRaw, true) ?: []) : (is_array($assetsAddRaw) ? $assetsAddRaw : []);
$assetsDel = is_string($assetsDelRaw) ? (json_decode($assetsDelRaw, true) ?: []) : (is_array($assetsDelRaw) ? $assetsDelRaw : []);
$outNew = is_string($outNewRaw) ? (json_decode($outNewRaw, true) ?: []) : (is_array($outNewRaw) ? $outNewRaw : []);

// บางระบบมี key แตกต่าง/สะกดไม่ตรง แก้ mapping เบื้องต้น
// เช่น { part: 'head', old_hash: '...', new_hash: '...', changed: '1' }
$section = array_map(function($row){
// map key ชื่อแปลกๆ ให้เป็น section/old/new ให้หมด
if (!isset($row['section']) && isset($row['part'])) $row['section'] = $row['part'];
if (!isset($row['old']) && isset($row['old_hash'])) $row['old'] = $row['old_hash'];
if (!isset($row['new']) && isset($row['new_hash'])) $row['new'] = $row['new_hash'];
return $row;
}, $section);

// เปลี่ยน changed ให้เป็น boolean จริง (รองรับ 'true','1',1,'yes','changed')
$isChanged = function($v){
return $v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'TRUE' || $v === 'yes' || $v === 'changed';
};

$changed = array_values(array_filter($section, function($row) use ($isChanged){
return $isChanged($row['changed'] ?? null);
}));

// จำกัดจำนวนแถว
$limit = $limit ?? 20;
$secShow = array_slice($changed, 0, $limit);
$addShow = array_slice($assetsAdd, 0, $limit);
$delShow = array_slice($assetsDel, 0, $limit);
$outShow = array_slice($outNew, 0, $limit);

// Merkle
$mo = (string) ($diff['merkle_old'] ?? '');
$mn = (string) ($diff['merkle_new'] ?? '');
@endphp

<!doctype html>
<html lang="th">

<head>
  <meta charset="utf-8">
  <meta name="x-apple-disable-message-reformatting">
  <meta name="color-scheme" content="light">
  <meta name="supported-color-schemes" content="light">
  <title>Notification Web Defacement</title>

  <style>
    /* เริ่มต้น: โชว์ desktop-only, ซ่อน mobile-only */
    .desktop-only {
      display: block;
    }

    .mobile-only {
      display: none;
    }

    /* มือถือ: ซ่อน desktop-only, โชว์ mobile-only */
    @media only screen and (max-width:875px) {
      .desktop-only {
        display: none !important;
      }

      .pc-only {
        display: none !important;
      }

      .mobile-only {
        display: table !important;
        width: 100% !important;
      }

      .mobile-only .circle-cell {
        width: 100% !important;
        display: inline-block !important;
        vertical-align: top !important;
        box-sizing: border-box !important;
      }

      .mobile-only .input-feed {
        background: #d5ecff;
        border-radius: 99px;
        padding: 8px 10px;
        font-size: 14px;
        color: #2a4a8bff;
        height: 15px;
        width: 90%;
      }

      .mobile-only .new {
        background: #d5ecff;
        border-radius: 99px;
        padding: 8px 10px;
        font-size: 14px;
        color: #ff0000ff;
        height: 15px;
        width: 90%;
      }

      .mobile-only .name-show {
        font-size: 14px;
        color: #2758c3ff;
        width: 25%;
        font-weight: 600;
        text-align: left;
      }

      .m-w100 {
        width: 100% !important;
        display: table-row !important;
      }

      .m-pad {
        padding: 10px 0 !important;
      }

      .m-label {
        width: 35% !important;
        /* กำหนดสัดส่วน label */
        text-align: left !important;
        padding-right: 8px !important;
        white-space: nowrap;
      }

      .m-value {
        width: 97% !important;
        /* ที่เหลือเป็นของ value */
        text-align: left !important;

      }

      .m-value span {
        display: block !important;
        width: 100% !important;
        /* pill ยาวเต็ม cell */
        text-align: left;
      }

      .value-badge {
        display: inline-block;
        min-width: 150px;
        /* หรือ width:150px; ถ้าต้องการเท่ากันเป๊ะ */
        text-align: center;
        background: #e6f3ff;
        border-radius: 999px;
        padding: 8px 14px;
        font-weight: 700;
        font-size: 14px;
        color: #0b2a66;
      }


    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      /* ซ้าย-ขวา 2 คอลัมน์ */
      column-gap: 32px;
      row-gap: 14px;
      align-items: start;
    }

    .field {
      display: grid;
      grid-template-columns: auto 1fr;
      /* label | value */
      align-items: center;
      column-gap: 10px;
    }

    .label {
      white-space: nowrap;
      font-weight: 600;
    }

    .value {
      min-width: 0;
      /* สำคัญ! ให้กล่องหดได้ ไม่ดัน layout */
    }

    .pill {
      display: flex;
      align-items: center;
      padding: 10px 14px;
      border-radius: 999px;
      background: #e7f2ff;
      /* สีเดียวกับช่องอื่น */
      font-weight: 700;
      line-height: 1.2;
      min-height: 38px;
      /* ความสูงขั้นต่ำให้ดูนิ่ง */
      width: 100%;
      box-sizing: border-box;
      /* ให้จัดการข้อความสวยเวลาแคบลง */
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      /* ปกติไม่ตัดบรรทัด */
      font-size: clamp(14px, 1.6vw, 18px);
      /* ฟอนต์ยืดหดตามจอ */
    }

    @media (min-width:700px) and (max-width:900px) {
      span {
        font-size: 15px !important;
      }

      .do-status {
        font-size: 25px !important;
      }

      .pc-only .input-feed {
        font-size: 12px !important;
        align-items: center !important;
      }

      .label-head {
        font-size: 12px !important;
      }
    }

    @media (max-width:680px) {
      .m-w100 .m-label {
        padding-bottom: 4px !important;
      }

      .m-w100 .m-value {
        display: block !important;
        /* ไม่บีบเป็นแถวเดียว */
        text-align: left !important;
      }

      .m-w100 .m-value span {
        display: block !important;
        width: 95% !important;
        white-space: normal !important;
        /* อนุญาตตัดบรรทัด */
        word-break: break-word !important;
        /* กันค่าที่ยาว (วันที่/URL) */
      }
    }

    .pc-only .input-feed {
      background: #d5ecff;
      border-radius: 99px;
      padding: 8px 10px;
      font-size: 15px;
      color: #2a4a8bff;
      height: 15px;
      display: flex;
      align-items: center !important;
    }

    .name-show {
      padding: 5px 10px;
      font-size: 14px;
      color: #2758c3ff;
      width: 25%;
      font-weight: 600;
    }

    .new {
      background: #d5ecff;
      border-radius: 99px;
      padding: 8px 10px;
      font-size: 15px;
      color: #ff0000ff;
      height: 15px;
      display: flex;
    }

    .new-status {
      background: #d5ecff;
      border-radius: 99px;
      padding: 8px 10px;
      font-size: 15px;
      color: #ff0000ff;
      height: 15px;
      text-align: center;
    }

    .input-feed-align {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .pc-only tr:last-child td {
      padding-bottom: 15px;
      /* ปรับตามต้องการ */
    }

    .td-mobile tr:last-child td {
      padding-bottom: 15px;
      /* ปรับตามต้องการ */
    }
  </style>

</head>



<body style="margin:0; padding:0; background:#f2f5f9;">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f2f5f9;">
    <tr>
      <td align="center" style="padding:24px 12px;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; background:#ffffff; border-radius:10px; overflow:hidden;">

          <!-- Header bar -->
          <tr>
            <td style="padding:0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#050038;">
                <tr>
                  <td align="left" style="padding:16px 20px;">
                    <img src="{{ asset('images/logo_threat/logo.png') ?? 'https://via.placeholder.com/120x28?text=SOSECURE' }}" alt="SOSECURE" width="150" height="40" style="display:block; border:0; outline:none;">
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Title -->
          <tr>
            <td style="padding:0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center" valign="middle" height="70"
                      style="font-family:Arial,Helvetica,sans-serif; color:#050038; font-size:18px; font-weight:700; line-height:40px; mso-line-height-rule:exactly;">
                    Notification Web Defacement
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          

          <!-- Top info 2x3 grid (desktop) -->
          <tr>
            <td style="padding:0 16px 3px; font-family:Arial,Helvetica,sans-serif;">
              <br>

              <!-- Desktop 3 columns -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="desktop-only" style="border-collapse:collapse; table-layout:fixed;">
                <tr>
                  <!-- COL 1 -->
                  <td width="33.33%" valign="top" style="width:33.33%; padding:8px 12px;">
                    <br>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="padding:4px 24px 18px; vertical-align:middle;">
                          <div class="do-status" style="
                            display:flex; align-items:center; justify-content:center;
                            background:#e53935; color:#fff; font-family:Arial,Helvetica,sans-serif;
                            font-weight:700; border-radius:20px; width:100%; min-height:115px;
                            box-sizing:border-box; font-size:25px;">
                            High
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>

                  <!-- divider -->
                  <td width="1" style="width:1px; background:#e7e7ed;"></td>
                  

                  <!-- COL 2 -->
                  <td width="33.33%" valign="top" style="width:33.33%; padding:8px 12px;">
                    <br>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 120px">Domain:</td>
                        <td style="padding:0 0 12px 0; width:100%;" width="100%">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                            <tr>
                              <td align="left" style="padding:0; mso-line-height-rule:exactly;">
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66; white-space:nowrap;">
                                  {{ $w->domain ?? '-' }}
                                </span>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 100px">Name Page:</td>
                        <td style="padding:0 0 12px 0; width:100%;" width="100%">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                            <tr>
                              <td align="left" style="padding:0; mso-line-height-rule:exactly;">
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66; white-space:nowrap;">
                                  {{ $w->name ?? '-' }}
                                </span>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 100px;" width="160px">Create Date:</td>
                        <td style="padding:0 0 12px 0; width: 100%;" width="100%">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                            <tr>
                              <td align="left" style="padding:0; mso-line-height-rule:exactly;">
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66; white-space:nowrap;">
                                  {{ $w->created_at ?? '-' }}
                                </span>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>

                  <!-- divider -->
                  <td width="1" style="width:1px; background:#e7e7ed;"></td>

                  <!-- COL 3 -->
                  <td width="33.33%" valign="top" style="width:33.33%; padding:8px 12px;">
                    <br>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 100px">Site:</td>
                        <td style="padding:0 0 12px 0; text-align:left;width: 100%">
                          <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">{{ Site::getSite($w->site_id) ?? '-' }}</span>
                        </td>
                      </tr>
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 100px;">User Agent:</td>
                        <td style="padding:0 0 12px 0; text-align:left;width: 100%">
                          <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">{{ $w->user_agent ?? '-' }}</span>
                        </td>
                      </tr>
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 0 0;width: 100px;">Last Online:</td>
                        <td style="padding:0; width: 100%;" width="100%">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="200" style="width:100%;">
                            <tr>
                              <td align="left" style="padding:0; mso-line-height-rule:exactly;">
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66; white-space:nowrap;">
                                  {{ $w->last_online ?? '-' }}
                                </span>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Mobile: status -->
              <table class="mobile-only" role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="vertical-align:middle;">
                    <div style="
                      display:flex; align-items:center; justify-content:center; background:#e53935;
                      color:#fff; font-family:Arial,Helvetica,sans-serif; font-size:20px; font-weight:700;
                      border-radius:20px; width:100%; min-height:115px; box-sizing:border-box;">
                      High
                    </div>
                  </td>
                </tr>
              </table>

              <!-- Mobile: fields -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="mobile-only" style="border-collapse:collapse; table-layout:fixed;">
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Domain:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->domain ?? '-' }}
                    </span>
                  </td>
                </tr>
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Name Page:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->name ?? '-' }}
                    </span>
                  </td>
                </tr>
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">User Agent:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->user_agent ?? '-' }}
                    </span>
                  </td>
                </tr>
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Site:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ Site::getSite($w->site_id) ?? '-' }}
                    </span>
                  </td>
                </tr>
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Create Date:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->created_at ?? '' }}
                    </span>
                  </td>
                </tr>
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Last Online:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66; margin-right: 7;">
                      {{ $w->last_online ?? '' }}
                    </span>
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Section title: Analytics -->
          <tr>
            <td>
              <br>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center" valign="middle" height="40"
                      style="font-family:Arial,Helvetica,sans-serif;background-color: #efefef;font-size:14px;font-weight:700;color:#0b2a66;">
                    Analytics
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Analytics: PHP + circles -->
          <!-- {{ $w->filesizeper}} -->
          @php
          function circleBase64UltraHD(
            int $pct,
            string $label = '',
            int $size = 86,
            int $thickness = 8,
            int $retina = 2
          ): string {
            $pct = max(0, min(100, $pct));
            $retina = max(1, $retina);

            $S = $size * $retina;
            $T = max(2, $thickness * $retina);

            $im = imagecreatetruecolor($S, $S);
            imagesavealpha($im, true);
            $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $trans);

            if (function_exists('imageantialias')) {
              imageantialias($im, true);
            }

            $gray = imagecolorallocate($im, 229, 231, 235);
            $fg = ($pct >= 80) ? imagecolorallocate($im, 220, 38, 38)
                 : (($pct >= 50) ? imagecolorallocate($im, 217, 119, 6)
                 : imagecolorallocate($im, 22, 163, 74));
            $white = imagecolorallocate($im, 255, 255, 255);
            $textC = imagecolorallocate($im, 11, 42, 102);

            $outer = $S - 2 * $retina;
            $inner = $outer - 2 * $T;
            imagefilledellipse($im, $S / 2, $S / 2, $outer, $outer, $gray);

            if ($pct > 0) {
              $start = -90;
              $end = -90 + (360 * $pct / 100.0);
              imagefilledarc($im, $S / 2, $S / 2, $outer, $outer, $start, $end, $fg, IMG_ARC_PIE);
            }

            imagefilledellipse($im, $S / 2, $S / 2, $inner, $inner, $white);

            $percentText = $pct . '%';
            $fontPath = base_path('resources/fonts/Montserrat-Regular.ttf');
            
            // ตรวจสอบว่ามี FreeType และไฟล์ Font หรือไม่
            $hasTTF = is_file($fontPath) && function_exists('imagettftext');

            if ($hasTTF) {
              $fontSize = (int) round($S * 0.2); // ปรับลดเป็น 0.2 (ขนาดตั้งต้นแบบคมชัด)
              $bbox = imagettfbbox($fontSize, 0, $fontPath, $percentText);
              $textW = $bbox[2] - $bbox[0];
              $textH = $bbox[1] - $bbox[7];
              $x = (int) (($S - $textW) / 2);
              $y = (int) (($S + $textH) / 2);
              imagettftext($im, $fontSize, 0, $x, $y, $textC, $fontPath, $percentText);
            } else {
              // --- FALLBACK CASE (เครื่อง UAT มักจะติดตรงนี้) ---
              // ขยายขนาดขึ้นเล็กน้อย (80x80) เพื่อให้ภาพไม่แตก (Quality ดีขึ้น) 
              // แต่ยังรักษาความใหญ่ของ Font มาตรฐานไว้
              imagedestroy($im);
              
              $smallS = 90; 
              $smallT = 8;
              $im = imagecreatetruecolor($smallS, $smallS);
              imagesavealpha($im, true);
              $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
              imagefill($im, 0, 0, $trans);
              
              $gray = imagecolorallocate($im, 229, 231, 235);
              $fg = ($pct >= 80) ? imagecolorallocate($im, 220, 38, 38) : (($pct >= 50) ? imagecolorallocate($im, 217, 119, 6) : imagecolorallocate($im, 22, 163, 74));
              $white = imagecolorallocate($im, 255, 255, 255);
              $textC = imagecolorallocate($im, 5, 20, 50);

              imagefilledellipse($im, $smallS/2, $smallS/2, $smallS-2, $smallS-2, $gray);
              if ($pct > 0) {
                imagefilledarc($im, $smallS/2, $smallS/2, $smallS-2, $smallS-2, -90, -90+(360*$pct/100), $fg, IMG_ARC_PIE);
              }
              imagefilledellipse($im, $smallS/2, $smallS/2, $smallS-(2*$smallT), $smallS-(2*$smallT), $white);

              $font = 5; 
              $textW = imagefontwidth($font) * strlen($percentText);
              $textH = imagefontheight($font);
              imagestring($im, $font, ($smallS-$textW)/2, ($smallS-$textH)/2, $percentText, $textC);
            }

            ob_start();
            imagepng($im);
            $raw = ob_get_clean();
            imagedestroy($im);

            return 'data:image/png;base64,' . base64_encode($raw);
          }
          @endphp

          @php
          $chk = '';
          $data['all'] = [];
          try {
            $chk = WebdefacmentDataCheck::getData($w->id);
            if(!empty($chk)){
              if ($chk->score * 100 != 0) {
                $data['all']['hash'] = ['name' => 'Hash','value' => (float) $chk->score*100];
              }
              if ($chk->filesize_percent != 0) {
                $data['all']['filesize'] = ['name' => 'Filesize','value' => (float) $chk->filesize_percent];
              }
              if ($chk->element_percent != 0) {
                $data['all']['element'] = ['name' => 'Element','value' => (float) $chk->element_percent];
              }
              if ($chk->image_percent != 0) {
                $data['all']['image'] = ['name' => 'Image','value' => (float) $chk->image_percent];
              }
              if ($chk->keyword_percent != 0) {
                $data['all']['blacklist'] = ['name' => 'Blacklist','value' => (float) $chk->keyword_percent];
              }
            }
          } catch (\Throwable $th) {
            $chk = [];
          }
          @endphp

          <!-- Analytics circles (mobile & desktop) -->
          <tr>
            <td style="padding:4px 6px 16px;">
              <!-- mobile-only row -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="mobile-only circle-table">
                <tr>
                  @if(!empty($data['all']))
                    @foreach($data['all'] as $key => $item)
                      @php
                        if (is_array($item)) {
                          $value = (int)($item['value'] ?? 0);
                          $name  = strx($item['name'] ?? $key);
                        } elseif (is_object($item)) {
                          $value = (int)($item->value ?? 0);
                          $name  = strx($item->name ?? $key);
                        } else {
                          $value = (int)$item;
                          $name  = strx($key);
                        }
                        $src = circleBase64UltraHD($value, $name, 86, 8, 4);
                      @endphp
                      <td class="circle-cell" align="center" style="padding:8px 0;">
                        <img src="{{ $src }}" width="120" height="120" alt="{{ $value ?? 0 }}%" style="display:block;border:0;outline:none;text-decoration:none;">
                        <div style="font:13px Arial,Helvetica,sans-serif;color:#6b7a90;margin-top:8px;">
                          <p style="margin:0;"><b>{{ $name }}</b></p>
                        </div>
                      </td>
                    @endforeach
                  @endif
                </tr>
              </table>

              <!-- desktop row -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="pc-only">
                <tr>
                  @if(!empty($data['all']))
                    @foreach($data['all'] as $key => $item)
                      @php
                        if (is_array($item)) {
                          $value = (int)($item['value'] ?? 0);
                          $name  = strx($item['name'] ?? $key);
                        } elseif (is_object($item)) {
                          $value = (int)($item->value ?? 0);
                          $name  = strx($item->name ?? $key);
                        } else {
                          $value = (int)$item;
                          $name  = strx($key);
                        }
                        $src = circleBase64UltraHD($value, $name, 86, 8, 4);
                      @endphp
                      <td align="center" style="padding:8px 0;">
                        <img src="{{ $src }}" width="120" height="120" alt="{{ $value ?? 0 }}%" style="display:block;border:0;outline:none;text-decoration:none;">
                        <div style="font:14px Arial,Helvetica,sans-serif;color:#6b7a90;margin-top:8px;">
                          <p style="margin:0;"><b>{{ $name  }}</b></p>
                        </div>
                      </td>
                    @endforeach
                  @endif
                </tr>
              </table>
            </td>
          </tr>

          <!-- Divider: Values Comparison -->
          <tr>
            <td style="padding:0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center" valign="middle" height="40"
                      style="font-family:Arial,Helvetica,sans-serif;background-color: #efefef;font-size:14px;font-weight:700;color:#0b2a66;">
                    Values Comparison
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Compare tables (PC) -->
          @php
          $original_ = '';
          $original_hash = '';
          try{
            $original_ = WebdefacmentDataOriginal::GetDataOriginal($w->id);
          }catch(\Throwable $th){
            $original_ = [];
          }
          @endphp

          @php
          function getKB($filesize){
            $res_kb = 0;
            if (!empty($filesize)){
              $res_kb = ceil($filesize / 1024 * 100) / 100;
            }
            return $res_kb .' KB';
          }
          @endphp

          <tr>
            <td style="padding:0 16px 5px;" class="pc-only">
              <table class="pc-only" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed;margin-bottom: 20px;">
                <tr>
                  <!-- Original -->
                  <td width="50%" valign="top" style="padding:8px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                           style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                      <tr>
                        <td colspan="2"
                            style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                   padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                   font-size:14px">
                          Original
                        </td>
                      </tr>

                      @if($chk->score)
                      <tr>
                        <td class="name-show">Hash</td>
                        <td>
                          <div style="margin-top:5px; max-width:250px; word-wrap:break-word; overflow-wrap:break-word; line-height:1.4;" class="input-feed">
                          <span title="{{ $diff['merkle_old'] ?? '' }}">
                            {{ Str::limit($diff['merkle_old'] ?? '', 20, '...') }}
                          </span>
                        </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->filesize_percent)
                      <tr>
                        <td class="name-show">Filesize</td>
                        <td>
                          <div class="input-feed">
                            {{ getKB($original_['0']['filesize']) ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->element_percent)
                      <tr>
                        <td class="name-show">Element</td>
                        <td>
                          <div class="input-feed">
                            {{ $original_['0']['element']  ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->keyword_percent)
                      <tr>
                        <td class="name-show">Blacklist</td>
                        <td>
                          <div class="input-feed">
                            Found Blacklist Word !
                          </div>
                        </td>
                      </tr>
                      @endif

                      <tr style="padding-bottom: 15px;">
                        <td class="name-show">Last Update</td>
                        <td>
                          <div class="input-feed">
                            {{ $w->updated_at ?? '' }}
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>

                  <!-- Current -->
                  <td width="50%" valign="top" style="padding:8px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                           style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                      <tr>
                        <td colspan="2"
                            style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                   padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                   font-size:14px">
                          Current
                        </td>
                      </tr>

                      @if($chk->score)
                      <tr>
                        <td class="name-show">Hash</td>
                        <td>
                          <div style="margin-top:5px; max-width:250px; word-wrap:break-word; overflow-wrap:break-word; line-height:1.4; color:red;" class="input-feed">
                          <span title="{{ $diff['merkle_new'] ?? '' }}">
                            {{ Str::limit($diff['merkle_new'] ?? '', 20, '...') }}
                          </span>
                        </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->filesize_percent)
                      <tr>
                        <td class="name-show">Filesize</td>
                        <td>
                          <div class="new">
                            {{ getKB($chk->filesize_new) ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->element_percent)
                      <tr>
                        <td class="name-show">Element</td>
                        <td>
                          <div class="new">
                            {{ $chk->element_new  ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->keyword_percent)
                      <tr>
                        <td class="name-show">Blacklist</td>
                        <td>
                          <div class="new">
                            {{ $chk->keyword ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      <tr>
                        <td class="name-show">Last Update</td>
                        <td>
                          <div class="new">
                            {{ $w->updated_at ?? '' }}
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Compare tables (Mobile stacked) -->
          <tr class="mobile-only">
            <td style="padding:0 16px 10px;">
              <table role="presentation" width="100%" class="mobile-only" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="100%" valign="top" style="padding:8px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="td-mobile"
                           style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                      <tr>
                        <td colspan="2"
                            style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                   padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                   font-size:14px">
                          Original
                        </td>
                      </tr>

                      @if($chk->hash_percent)
                      <tr><td colspan="2" class="name-show">Hash</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            {{ $original_['0']['hash'] ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->filesize_percent)
                      <tr><td colspan="2" class="name-show">Filesize</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            {{ getKB($original_['0']['filesize']) ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->element_percent)
                      <tr><td colspan="2" class="name-show">Element</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            {{ $original_['0']['element']  ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->keyword_percent)
                      <tr><td colspan="2" class="name-show">Blacklist</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            Found Blacklist Word !
                          </div>
                        </td>
                      </tr>
                      @endif

                      <tr><td colspan="2" class="name-show">Last Update</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            {{ $w->updated_at ?? '' }}
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td width="100%" valign="top" style="padding:8px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="td-mobile"
                           style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                      <tr>
                        <td colspan="2"
                            style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                   padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                   font-size:14px">
                          Current
                        </td>
                      </tr>

                      @if($chk->hash_percent)
                      <tr><td colspan="2" class="name-show">Hash</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ $chk->hash_new ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->filesize_percent)
                      <tr><td colspan="2" class="name-show">Filesize</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ getKB($chk->filesize_new) ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->element_percent)
                      <tr><td colspan="2" class="name-show">Element</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ $chk->element_new  ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->keyword_percent)
                      <tr><td colspan="2" class="name-show">Blacklist</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ $chk->keyword ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      <tr><td colspan="2" class="name-show">Last Update</td></tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ $w->updated_at ?? '' }}
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

              </table>
            </td>
          </tr>

          <!-- Content Detection (mobile+pc common) -->
          <tr class="mobile-only">
            <td style="padding:0 16px 10px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed; margin:7px 0;">
                <tr>
                  <td align="center" valign="middle" height="40"
                      style="font-family:Arial,Helvetica,sans-serif;background-color:#efefef;font-size:14px;font-weight:700;color:#0b2a66;">
                    Content Detection
                  </td>
                </tr>

                <tr>
                  <td style="padding:0 10px 5px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed;">
                      <tr>
                        <td>
                          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                              style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif; margin-top:7px;">

                          <tr>
                            <td colspan="4" align="center"
                                style="padding:14px 8px; font-weight:bold; font-size:14px; background-color:#3869d4; color:#fff; border-radius:99px; line-height:7px;">
                              Sections (
                                  {{ is_array($section)
                                      ? collect($section)->where('changed', true)->count()
                                      : 'NA'
                                  }}
                                )
                            </td>
                          </tr>

                          <tr style="color:#2758c3;">
                            <th style="padding:10px; text-align:center;">Section</th>
                            <th style="padding:10px; text-align:center;">Status</th>
                          </tr>

                          @foreach($section as $sec)
                            @if($sec['changed'] == true)
                              <tr>
                                <td style="padding:10px; text-align:center;">{{ $sec['section'] ?? '' }}</td>
                                </td>
                                <td style="padding:5px; text-align:center; color:red;">
                                  {{ $sec['changed'] ? 'Changed' : '' }}
                                </td>
                              </tr>
                            @endif
                          @endforeach
                        </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- Assets blocks (stacked for mobile, grid for pc) -->
                <tr>
                  <td>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed; margin-bottom: 20px;">
                      @php
                      $blocks = [];
                      if(count($assetsAdd) > 0){ $blocks[] = 'add'; }
                      if(count($assetsDel) > 0){ $blocks[] = 'del'; }
                      if(count($outNew) > 0){ $blocks[] = 'out'; }
                      if(isset($assetsOther) && count($assetsOther) > 0){ $blocks[] = 'other'; }
                      @endphp

                      @foreach($blocks as $block)
                        <tr>
                          <td valign="top" style="padding: 8px;">
                            @if($block === 'add')
                              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                     style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif; margin-bottom:5px;">
                                <tr>
                                  <td colspan="2"
                                      style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center;
                                             padding:8px; border-radius:99px;
                                             font-size:14px">
                                    Assets Add ({{ count($assetsAdd) }})
                                  </td>
                                </tr>
                                @foreach ($assetsAdd as $asset)
                                  <tr>
                                    <td style="padding:5px 8px; text-align:center; vertical-align:middle;">{{ $loop->iteration }}</td>
                                    <td style="padding:5px 8px;">{{ $asset ?? '' }}</td>
                                  </tr>
                                @endforeach
                              </table>
                            @endif

                            @if($block === 'del')
                              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                  style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif; margin-bottom:5px;">

                              <tr>
                                <td colspan="2"
                                    style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center;
                                          padding:8px; border-radius:99px;
                                          font-size:14px">
                                  Assets Deleted ({{ count($assetsDel) }})
                                </td>
                              </tr>

                              @foreach ($assetsDel as $asset)
                                <tr>
                                  <td style="padding:5px 8px; text-align:center; vertical-align:middle;">{{ $loop->iteration }}</td>

                                  <td style="padding:5px 8px;">
                                    <div style="max-width:300px; word-wrap:break-word; overflow-wrap:break-word; line-height:1.4;">
                                      <span title="{{ $asset ?? '' }}">
                                        {{ Str::limit($asset ?? '', 30, '...') }}
                                      </span>
                                    </div>
                                  </td>
                                </tr>
                              @endforeach
                            </table>
                            @endif

                            @if($block === 'out')
                              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                     style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif; margin-bottom:15px;">
                                <tr>
                                  <td colspan="2"
                                      style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center;
                                             padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;
                                             font-size:14px">
                                    Domaina ({{ count($outNew) }})
                                  </td>
                                </tr>
                                @foreach ($outNew as $outNews)
                                  <tr>
                                    <td style="padding:5px 8px; text-align:center; vertical-align:middle;">{{ $loop->iteration }}</td>
                                    <td style="padding:5px 8px;">{{ $outNews ?? '' }}</td>
                                  </tr>
                                @endforeach
                              </table>
                            @endif

                            @if($block === 'other')
                              <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                     style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif; margin-bottom:15px;">
                                <tr>
                                  <td colspan="2"
                                      style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center;
                                             padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;
                                             font-size:14px">
                                    Other ({{ count($assetsOther) }})
                                  </td>
                                </tr>
                                @foreach ($assetsOther as $item)
                                  <tr>
                                    <td style="padding:5px 8px; text-align:center; vertical-align:middle;">{{ $loop->iteration }}</td>
                                    <td style="padding:5px 8px;">{{ $item ?? '' }}</td>
                                  </tr>
                                @endforeach
                              </table>
                            @endif
                          </td>
                        </tr>
                      @endforeach

                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Content Detection (PC duplicate block for layout parity) -->
          <tr>
            <td style="padding:0 16px 10px;" class="pc-only">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed; margin:7px 0;">
                <tr>
                  <td align="center" valign="middle" height="40"
                      style="font-family:Arial,Helvetica,sans-serif;background-color:#efefef;font-size:14px;font-weight:700;color:#0b2a66;">
                    Content Detection
                  </td>
                </tr>

                <tr>
                  <td style="padding:0 10px 5px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed;">
                      <tr>
                        <td>
                          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                              style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif; margin-top:7px;">

                          <tr>
                            <td colspan="4" align="center"
                                style="padding:14px 8px; font-weight:bold; font-size:14px; background-color:#3869d4; color:#fff; border-radius:99px; line-height:7px;">
                              Sections (
                                  {{ is_array($section)
                                      ? collect($section)->where('changed', true)->count()
                                      : 'NA'
                                  }}
                                )
                            </td>
                          </tr>

                          <tr style="color:#2758c3;">
                            <th style="padding:10px; text-align:center;">Section</th>
                            <th style="padding:10px; text-align:center;">Original</th>
                            <th style="padding:10px; text-align:center;">Current</th>
                            <th style="padding:10px; text-align:center;">Status</th>
                          </tr>

                          @foreach($section as $sec)
                            @if($sec['changed'] == true)
                              <tr>
                                <td style="padding:10px; text-align:center;">{{ $sec['section'] ?? '' }}</td>

                                <td style="padding:5px; text-align:center;">
                                  {{ Str::limit($sec['old'] ?? '', 15, '...') }}
                                </td>
                                <td style="padding:5px; text-align:center;">
                                  {{ Str::limit($sec['new'] ?? '', 15, '...') }}
                                </td>
                                <td style="padding:5px; text-align:center; color:red;">
                                  {{ $sec['changed'] ? 'Changed' : '' }}
                                </td>
                              </tr>
                            @endif
                          @endforeach
                        </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <!-- PC: 2–4 columns assets (depending on blocks) -->
                <tr>
                  <td>
                    <table class="pc-only" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; table-layout:fixed;margin-bottom: 20px;">
                      <tr>
                        @php
                        $blocks = [];
                        if(count($assetsAdd) > 0){ $blocks[] = 'add'; }
                        if(count($assetsDel) > 0){ $blocks[] = 'del'; }
                        if(count($outNew) > 0){ $blocks[] = 'out'; }
                        if(isset($assetsOther) && count($assetsOther) > 0){ $blocks[] = 'other'; }
                        $colWidth = count($blocks) > 0 ? 100 / count($blocks) : 100;
                        @endphp

                        @foreach($blocks as $block)
                          @if($block === 'add')
                          <td width="{{ $colWidth }}%" valign="top" style="padding: 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                              <tr>
                                <td colspan="2"
                                    style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                           padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                           font-size:14px">
                                  Assets Add ( {{ count($assetsAdd) }} )
                                </td>
                              </tr>
                              @foreach ($assetsAdd as $asset)
                                <tr>
                                  <td style="padding:5px 8px; text-align:center; vertical-align:middle;">
                                    {{ $loop->iteration }}
                                  </td>
                                  <td style="padding:5px 8px;">
                                    <div class="new">{{ $asset ?? '' }}</div>
                                  </td>
                                </tr>
                              @endforeach
                            </table>
                          </td>
                          @endif

                          @if($block === 'del')
                          <td width="{{ $colWidth }}%" valign="top" style="padding: 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                              <tr>
                                <td colspan="2"
                                    style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                           padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                           font-size:14px">
                                  Assets Deleted ( {{ count($assetsDel) }} )
                                </td>
                              </tr>
                              @foreach ($assetsDel as $asset)
                                <tr>
                                  <td style="padding:5px 8px; text-align:center; vertical-align:middle;">
                                    {{ $loop->iteration }}
                                  </td>
                                  <td style="padding:5px 8px;">
                                    <div class="new">{{ $asset ?? '' }}</div>
                                  </td>
                                </tr>
                              @endforeach
                            </table>
                          </td>
                          @endif

                          @if($block === 'out')
                          <td width="{{ $colWidth }}%" valign="top" style="padding: 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                              <tr>
                                <td colspan="2"
                                    style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                           padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                           font-size:14px">
                                  Domaina ( {{ count($outNew) }} )
                                </td>
                              </tr>
                              @foreach ($outNew as $outNews)
                                <tr>
                                  <td style="padding:5px 8px; text-align:center; vertical-align:middle;">
                                    {{ $loop->iteration }}
                                  </td>
                                  <td style="padding:5px 8px;">
                                    <div class="new">{{ $outNews ?? '' }}</div>
                                  </td>
                                </tr>
                              @endforeach
                            </table>
                          </td>
                          @endif

                          @if($block === 'other')
                          <td width="{{ $colWidth }}%" valign="top" style="padding: 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="border-radius:18px; background:#f1f1f1; font-family:Arial,Helvetica,sans-serif;">
                              <tr>
                                <td colspan="2"
                                    style="background:#3869d4; color:#ffffff; font-weight:bold; text-align:center; 
                                           padding:8px; border-top-left-radius:10px; border-top-right-radius:10px;border-radius:99px;
                                           font-size:14px">
                                  Other ( {{ count($assetsOther) }} )
                                </td>
                              </tr>
                              @foreach ($assetsOther as $item)
                                <tr>
                                  <td style="padding:5px 8px; text-align:center; vertical-align:middle;">
                                    {{ $loop->iteration }}
                                  </td>
                                  <td style="padding:5px 8px;">
                                    <div class="new">{{ $item ?? '' }}</div>
                                  </td>
                                </tr>
                              @endforeach
                            </table>
                          </td>
                          @endif
                        @endforeach
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:12px 24px 20px; font-family:Arial,Helvetica,sans-serif; font-size:11px; color:#8a96a8;">
              This is an automated message. Please do not reply.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>


</html>