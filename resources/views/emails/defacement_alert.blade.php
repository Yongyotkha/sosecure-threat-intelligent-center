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
    @media only screen and (max-width:700px) {
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
        font-size: 10px !important;
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
                  <td align="center" valign="middle" height="70" bgcolor="#376ad4"
                    style="font-family:Arial,Helvetica,sans-serif; color:#ffffff;
                      font-size:18px; font-weight:700; line-height:70px; mso-line-height-rule:exactly;
                      background:linear-gradient(90deg,#376ad4 0%,#00C3FF 100%);">
                    Notification Web Defacement
                  </td>

                </tr>
              </table>
            </td>
          </tr>



          <!-- Status pill -->
          <!-- Top info 2x3 grid (tables for email safety) -->
          <tr>
            <td style="padding:0 16px 3px; font-family:Arial,Helvetica,sans-serif;">


              <br>

              <!--[if mso]><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><![endif]-->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="desktop-only" style="border-collapse:collapse; table-layout:fixed;">
                <tr>
                  <!-- COL 1 -->
                  <td width="33.33%" valign="top" style="width:33.33%; padding:8px 12px;">
                    <br>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">

                      <tr>
                        <td style="padding:4px 24px 18px; vertical-align:middle;">

                          <div class="do-status" style="
                                  display:flex;
                                  align-items:center;
                                  justify-content:center;
                                  background:#e53935;
                                  color:#fff;
                                  font-family:Arial,Helvetica,sans-serif;
                                  font-weight:700;
                                  border-radius:20px;
                                  width:100%;
                                  min-height:115px;
                                  box-sizing:border-box;
                                  font-size:25px;
                              ">
                            High
                          </div>
                        </td>
                      </tr>




                    </table>
                  </td>

                  <!-- divider -->
                  <td width="1" style="width:1px; background:#e7e7ed;"></td>

                  <!-- COL 2 -->


                  <td width="33.33%" valign="top" valign="top" style="width:33.33%; padding:8px 12px;">
                    <br>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 100px">Domain:</td>
                        <td style="padding:0 0 12px 0; width:100%;" width="100%">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                            <tr>
                              <td align="left" style="padding:0; mso-line-height-rule:exactly;">
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:12px; color:#0b2a66; white-space:nowrap;">
                                  {{ $w->domain ?? '-' }}
                                </span>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td class="label-head"  style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 100px">Name Page:</td>
                        <td style="padding:0 0 12px 0; width:100%;" width="100%">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
                            <tr>
                              <td align="left" style="padding:0; mso-line-height-rule:exactly;">
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:12px; color:#0b2a66; white-space:nowrap;">
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
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:12px; color:#0b2a66; white-space:nowrap;">
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
                          <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:12px; color:#0b2a66;">{{ Site::getSite($w->site_id) ?? '-' }}</span>
                        </td>
                      </tr>
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 12px 0;width: 100px;">User Agent:</td>
                        <td style="padding:0 0 12px 0; text-align:left;width: 100%">
                          <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:12px; color:#0b2a66;">{{ $w->user_agent ?? '-' }}</span>
                        </td>
                      </tr>
                      <tr>
                        <td class="label-head" style="font-size:14px; color:#1b1f2a; white-space:nowrap; text-align:left; padding:0 12px 0 0;width: 100px;">Last Online:</td>
                        <td style="padding:0; width: 100%;" width="100%">
                          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="200" style="width:100%;">
                            <tr>
                              <td align="left" style="padding:0; mso-line-height-rule:exactly;">
                                <span style="display:block; background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:12px; color:#0b2a66; white-space:nowrap;">
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
              <!--[if mso]></tr></table><![endif]-->

              <table class="mobile-only" role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="vertical-align:middle;">
                    <div style="
                                  display:flex;
                                  align-items:center;
                                  justify-content:center;
                                  background:#e53935;
                                  color:#fff;
                                  font-family:Arial,Helvetica,sans-serif;
                                  font-size:20px;
                                  font-weight:700;
                                  border-radius:20px;
                                  width:100%;
                                  min-height:115px;
                                  box-sizing:border-box;
                              ">
                      High
                    </div>
                  </td>
                </tr>
              </table>



              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="mobile-only" style="border-collapse:collapse; table-layout:fixed;">
                <!-- 1) Name Page -->
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Domain:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->domain ?? '-' }}
                    </span>
                  </td>

                </tr>
                <!-- 2) Domain -->
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Name Page:</td>

                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->name ?? '-' }}
                    </span>
                  </td>

                </tr>
                <!-- 3) User Agent -->
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">User Agent:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->user_agent ?? '-' }}
                    </span>
                  </td>
                </tr>
                <!-- 4) Site -->
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Site:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ Site::getSite($w->site_id) ?? '-' }}
                    </span>
                  </td>
                </tr>
                <!-- 5) Create Date -->
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Create Date:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;">
                      {{ $w->created_at ?? '' }}
                    </span>
                  </td>
                </tr>
                <!-- 6) Last Online -->
                <tr class="m-w100">
                  <td class="m-label m-pad" style="font-size:14px; color:#1b1f2a; white-space:nowrap; padding:6px 0;">Last Online:</td>
                  <td class="m-value m-pad" style="padding:6px 0; display:flex; justify-content:flex-end;">
                    <span style="background:#e6f3ff; border-radius:999px; padding:8px 14px; font-weight:700; font-size:14px; color:#0b2a66;margin-right: 7;">
                      {{ $w->last_online ?? '' }}
                    </span>
                  </td>
                </tr>

              </table>

            </td>
          </tr>

          <!-- Section title -->

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

          <!-- Analytics -->
          <!-- {{ $w->filesizeper}} -->
          @php
          function circleBase64UltraHD(
          int $pct,
          string $label = '',
          int $size = 86,
          int $thickness = 8,
          int $retina = 4 // ใช้ 4x เพื่อความคม
          ): string {
          $pct = max(0, min(100, $pct));
          $retina = max(1, $retina);

          $S = $size * $retina;
          $T = max(2, $thickness * $retina);

          // Canvas โปร่งใส truecolor
          $im = imagecreatetruecolor($S, $S);
          imagesavealpha($im, true);
          $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
          imagefill($im, 0, 0, $trans);

          if (function_exists('imageantialias')) {
          imageantialias($im, true);
          }

          // สีพื้นหลังวง
          $gray = imagecolorallocate($im, 229, 231, 235);
          $fg = ($pct >= 80) ? imagecolorallocate($im, 220, 38, 38) // เขียว
          : (($pct >= 50) ? imagecolorallocate($im, 217, 119, 6) // ส้ม
          : imagecolorallocate($im, 22, 163, 74)); // แดง
          $white = imagecolorallocate($im, 255, 255, 255);
          $textC = imagecolorallocate($im, 11, 42, 102);

          // วาดวงเทา
          $outer = $S - 2 * $retina;
          $inner = $outer - 2 * $T;
          imagefilledellipse($im, $S / 2, $S / 2, $outer, $outer, $gray);

          // sector สี
          if ($pct > 0) {
          $start = -90;
          $end = -90 + (360 * $pct / 100.0);
          imagefilledarc($im, $S / 2, $S / 2, $outer, $outer, $start, $end, $fg, IMG_ARC_PIE);
          }

          // เจาะไส้ขาว
          imagefilledellipse($im, $S / 2, $S / 2, $inner, $inner, $white);



          // ตัวเลข %
          $percentText = $pct . '%';
          $fontPath = base_path('resources/fonts/Montserrat-Regular.ttf'); // แนะนำฟอนต์คมๆ
          if (is_file($fontPath) && function_exists('imagettftext')) {
          $fontSize = (int) round($S * 0.2); // ใหญ่ขึ้นสำหรับ Retina
          $bbox = imagettfbbox($fontSize, 0, $fontPath, $percentText);
          $textW = $bbox[2] - $bbox[0];
          $textH = $bbox[1] - $bbox[7];
          $x = (int) (($S - $textW) / 2);
          $y = (int) (($S + $textH) / 2);
          imagettftext($im, $fontSize, 0, $x, $y, $textC, $fontPath, $percentText);
          } else {
          $font = 12;
          $textW = imagefontwidth($font) * strlen($percentText);
          $textH = imagefontheight($font);
          $x = (int) (($S - $textW) / 2);
          $y = (int) (($S - $textH) / 2);
          imagestring($im, $font, $x, $y, $percentText, $textC);
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
          if ($chk->hash_percent != 0) {
          $data['all']['hash'] = [
          'name' => 'Hash',
          'value' => (float) $chk->hash_percent,
          ];
          }
          if ($chk->filesize_percent != 0) {
          $data['all']['filesize'] = [
          'name' => 'Filesize',
          'value' => (float) $chk->filesize_percent,
          ];
          }
          if ($chk->element_percent != 0) {
          $data['all']['element'] = [
          'name' => 'Element',
          'value' => (float) $chk->element_percent,
          ];
          }
          if ($chk->image_percent != 0) {
          $data['all']['image'] = [
          'name' => 'Image',
          'value' => (float) $chk->image_percent,
          ];
          }
          if ($chk->keyword_percent != 0) {
          $data['all']['blacklist'] = [
          'name' => 'Blacklist',
          'value' => (float) $chk->keyword_percent,
          ];
          }

          }
          } catch (\Throwable $th) {
          $chk = [];
          }



          @endphp

          <tr>
            <td style="padding:4px 6px 16px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="mobile-only circle-table">
                <tr>
                  @if(!empty($data['all']))
                  @foreach($data['all'] as $key => $item)
                  @php
                  if (is_array($item)) {
                  $value = (int)($item['value'] ?? 0);
                  $name = strx($item['name'] ?? $key);
                  } elseif (is_object($item)) {
                  $value = (int)($item->value ?? 0);
                  $name = strx($item->name ?? $key);
                  } else {
                  $value = (int)$item;
                  $name = strx($key);
                  }
                  $src = circleBase64UltraHD($value, $name, 86, 8, 4);
                  @endphp

                  <td class="circle-cell" align="center" style="padding:8px 0;">
                    <img
                      src="{{ $src }}"
                      width="120"
                      height="120"
                      alt="{{ $value ?? 0 }}%"
                      style="display:block;border:0;outline:none;text-decoration:none;">
                    <div style="font:13px Arial,Helvetica,sans-serif;color:#6b7a90;margin-top:8px;">
                      <p style="margin:0;"><b>{{ $name }}</b></p>
                    </div>
                  </td>
                  @endforeach
                  @endif
                </tr>
              </table>


              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="pc-only">
                <tr>

                  @if(!empty($data['all']))
                  @foreach($data['all'] as $key => $item)
                  @php
                  // ปลอดภัย: บังคับ value/name ให้เป็นชนิดที่ถูกต้อง
                  if (is_array($item)) {
                  $value = (int)($item['value'] ?? 0);
                  $name = strx($item['name'] ?? $key);
                  } elseif (is_object($item)) {
                  $value = (int)($item->value ?? 0);
                  $name = strx($item->name ?? $key);
                  } else {
                  $value = (int)$item;
                  $name = strx($key);
                  }
                  @endphp
                  @php
                  $src = circleBase64UltraHD($value, $name, 86, 8, 4);
                  @endphp

                  <td align="center" style="padding:8px 0;">
                    <img
                      src="{{ $src }}"
                      width="120"
                      height="120"
                      alt="{{ $value ?? 0 }}%"
                      style="display:block;border:0;outline:none;text-decoration:none;">
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




          <!-- Divider -->
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

          <!-- Compare tables -->


          @php
          $original_ = '';
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
            <td style="padding:0 16px 24px;" class="pc-only">

              <!-- <table role="presentation" width="100%" class="desktop-only" cellpadding="0" cellspacing="0"> -->
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

                      @if($chk->hash_percent)
                      <tr>
                        <td class="name-show">Hash</td>
                        <td>
                          <div style="margin-top:5px;"
                            class="input-feed">
                            {{ $original_['0']['hash'] ?? '' }}
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
                  <td width="50%" valign="top" style="padding:8px; ">
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

                      @if($chk->hash_percent)
                      <tr>
                        <td class="name-show">Hash</td>
                        <td>
                          <div style="margin-top:5px;"
                            class="new">
                            {{ $chk->hash_new ?? '' }}
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
            <td class="mobile-only">
              <table role="presentation" width="100%" class="mobile-only" cellpadding="0" cellspacing="0">
                <tr>
                  <!-- Original -->
                  <td width="50%" valign="top" style="padding:8px;">
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
                      <tr>
                        <td colspan="2" class="name-show">Hash</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            {{ $original_['0']['hash'] ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->filesize_percent)
                      <tr>
                        <td colspan="2" class="name-show">Filesize</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            {{ getKB($original_['0']['filesize']) ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->element_percent)
                      <tr>
                        <td colspan="2" class="name-show">Element</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            {{ $original_['0']['element']  ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->keyword_percent)
                      <tr>
                        <td colspan="2" class="name-show">Blacklist</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="input-feed" style="display:block;">
                            Found Blacklist Word !
                          </div>
                        </td>
                      </tr>
                      @endif

                      <tr>
                        <td colspan="2" class="name-show">Last Update</td>
                      </tr>
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
                  <!-- Current -->
                  <td width="50%" valign="top" style="padding:8px; ">
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
                      <tr>
                        <td colspan="2" class="name-show">Hash</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ $chk->hash_new ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->filesize_percent)
                      <tr>
                        <td colspan="2" class="name-show">Filesize</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ getKB($chk->filesize_new) ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      @if($chk->element_percent)
                      <tr>
                        <td colspan="2" class="name-show">Element</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ $chk->element_new  ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif
                      

                      @if($chk->keyword_percent)
                      <tr>
                        <td colspan="2" class="name-show">Blacklist</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="input-feed-align">
                          <div class="new" style="display:block;">
                            {{ $chk->keyword ?? '' }}
                          </div>
                        </td>
                      </tr>
                      @endif

                      <tr>
                        <td colspan="2" class="name-show">Last Update</td>
                      </tr>
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