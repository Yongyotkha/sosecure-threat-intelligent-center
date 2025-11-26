@php
use Modules\WebDefacement\Entities\Site;
@endphp
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="light only">
    <title>แจ้งเตือน Web Down</title>
</head>

<body style="background:#f3f5f8;font-family:'Inter',system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial;color:#111827;margin:0;padding:0;">

    {{-- ส่วนหัว --}}
    <div class="miro__header" style="background:#263042;
    font-family:Helvetica,Arial,sans-serif;
    min-height:80px;
    padding:0 40px;
    display:flex;
    align-items:center;       /* จัดแนวกลางแนวดิ่ง */
    justify-content:space-between; /* ซ้าย-ขวาคนละฝั่ง */
">
        <!-- โลโก้ -->
        <a href="{{ url('/') }}" target="_blank" style="display:flex;align-items:center;text-decoration:none;">
            <img src="{{ asset('images/logo_threat/logo.png') }}"
                style="height:45px;display:block;">
        </a>

        <!-- ปุ่ม -->
        <a href="{{ url('/') }}" target="_blank"
            style="background-color:#fff;
              border:1px solid #050038;
              border-radius:4px;
              color:#050038;
              text-decoration:none;
              text-align:center;
              line-height:48px;
              height:48px;
              width:170px;
              font-size:16px;
              font-weight:400;">
            Go To Threat inSights
        </a>
    </div>


    {{-- ส่วนเนื้อหาแจ้งเตือน --}}
    <main style="background:#fff;max-width:1000px;margin:36px auto;border-radius:6px;overflow:hidden;">
        <div style="padding:56px 48px;text-align:center;min-height:420px;">
            <div style="max-width:260px;margin:0 auto 26px;">
                <img src="{{ asset('images/status/404_Not_Found_1.png') }}"
                    alt="offline"
                    style="width:100%;height:auto;display:block;margin:0 auto;">
            </div>

            <div style="font-size:25px;color:#1f2937;margin-top:6px;font-weight:600;">
                Site : {{ $setting->name ?? '-' }}
            </div>

            @php
            $statusLabel = 'Offline';
            if(isset($setting->web_status)){
            $s = strtolower(trim($setting->web_status));
            $statusLabel = in_array($s, ['down','offline','0','false','no']) ? 'Offline' : ucfirst($s);
            }
            @endphp

            <div style="margin-top:10px;font-size:20px;color:#e52424;font-weight:700;">
                Web Status : {{ $statusLabel }}
            </div>

            <div style="margin-top:24px;color:#6b7280;font-size:14px;line-height:1.8;">
                <div> <b>URL:</b> {{ $setting->url ?? '-' }}</div>
                <div> <b>User Agent:</b> {{ $setting->user_agent ?? '-' }}</div>
                <div> <b>Create Date:</b> {{ $setting->created_at ?? '-' }}</div>
                <div> <b>Last Online:</b> {{ $setting->last_online ?? '-' }}</div>
            </div>
        </div>
    </main>

    {{-- footer --}}
    <footer>
        <div style="font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#8a96a8;text-align:center">
            This is an automated message. Please do not reply.
        </div>
    </footer>
</body>

</html>