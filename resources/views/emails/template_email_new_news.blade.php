@php
use App\Helpers\HtmlCleaner;
$title_th = '';
$detail_th = '';
$title_en = '';
$detail_en = '';
$date_create = @$news['news']->public_date;
$link_th = '#';
$link_en = '#';
$news_view = @$news['news']->view;
if(@$news['news']->title_th) {
$title_th = $news['news']->title_th;
$detail_th = $news['news']->detail_th;
$link_th = route('news.public_detail_select', ['code' => @$news['news']->code , 'lang' => 'th']);
}
if(@$news['news']->title_en) {
$title_en = $news['news']->title_en;
$detail_en = $news['news']->detail_en;
$link_en = route('news.public_detail_select', ['code' => @$news['news']->code , 'lang' => 'en']);
}

$cate_html = '';
if(@$news['news']) {
foreach($news['news']->get_cate as $cate) {
$cate_html .= '<span style="background: #e6033c;color:#fff;padding:5px;margin-right:5px">'.@$cate->get_cate_name->name.'</span>';
}
}

$detail_th = HtmlCleaner::smartIndentParagraphs(
    HtmlCleaner::safeEmailHtml((string)$detail_th),
    2,           // threshold
    '0',         // indent (เราไม่ใช้ text-indent แล้ว)
    'always',    // mode
    0,           // fallbackNbspCount
    null,        // hardIndentWidth (ปิด)
    28,          // minChars
    '4em'        // spacerWidth (ย่อจริง)
);

$detail_en = HtmlCleaner::smartIndentParagraphs(
    HtmlCleaner::safeEmailHtml((string)$detail_en),
    2, '0', 'always', 0, null, 28, '4em'
);



@endphp
<!DOCTYPE html>
<html lang="th" style="font-size:0;line-height:0">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข่าวแจ้งเตือนภัยไซเบอร์</title>
  <style>
    /* Base */
    body,
    table,
    td,
    p,
    a,
    span,
    div {
      font-family: 'TH SarabunPSK', Tahoma, Arial, sans-serif !important;
      padding: 1px;
      color: #000000 !important;
    }

    .container {
      width: 100%;
      margin: 0 auto;
      background: #fff;
    }

    .header {
      padding: 5px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header img {
      height: 45px;
    }

    .header a.button {
      background: #fff;
      border: 1px solid #050038;
      border-radius: 4px;
      text-decoration: none;
      padding: 10px 20px;
      font-size: 16px;
    }

    .content {
      padding: 25px;
    }

    .content h2 {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .content p.meta {
      font-size: 14px;
      color: #000000;
      margin: 5px 0;
    }

    .content .detail {
      margin-top: 10px;
      text-indent: 10px;
      /* ถ้าอยากตัดการย่อหน้า ให้ลบหรือ override ด้านล่าง */
    }

    .footer {
      text-align: center;
      font-size: 14px;
      color: #000000;
      margin: 40px 20px;
    }

    /* --- Reset ระยะห่างพารากราฟใน detail --- */
    .content .detail p {
      margin: 0 !important;
      padding: 0 !important;
      text-indent: 0 !important;
      /* กันเคส indent ดันบรรทัดแรก */
      mso-line-height-rule: exactly;
    }

    /* (ตัวเลือก) ซ่อน <p> ว่างจริง ๆ */
    .content .detail p:empty {
      display: none !important;
    }

    /* --- จัดรูปใน detail ให้อยู่กึ่งกลางเสมอ (ไม่แก้ HTML) --- */
    .detail img,
    .detail p img,
    .detail a img,
    .detail span img,
    .detail div img {
      max-width: 100% !important;
      height: auto !important;
      display: block !important;
      margin-left: auto !important;
      /* จัดกลางด้วย margin auto */
      margin-right: auto !important;
      /* จัดกลางด้วย margin auto */
      float: none !important;
    }

    /* เคารพ align="center" / style="text-align:center" ที่มากับไฟล์ส่งออก */
    .detail p[align="center"],
    .detail div[align="center"],
    .detail span[align="center"],
    .detail p[style*="text-align:center"],
    .detail div[style*="text-align:center"],
    .detail span[style*="text-align:center"] {
      text-align: center !important;
    }

    /* เมื่อ parent center: ให้รูปเป็น inline เพื่อให้ Outlook/Gmail จัดกลางตามข้อความ */
    .detail p[align="center"] img,
    .detail div[align="center"] img,
    .detail span[align="center"] img,
    .detail p[style*="text-align:center"] img,
    .detail div[style*="text-align:center"] img,
    .detail span[style*="text-align:center"] img {
      display: inline !important;
      margin: 0 !important;
      font-size: 20px !important;
    }

    /* Responsive */
    @media only screen and (max-width: 600px) {
      .header {
        flex-direction: column;
        text-align: center;
      }

      .header a.button {
        margin-top: 10px;
      }

      .content .detail,
      .content .detail p {
        font-size: 22px !important;
        /* ใหญ่ขึ้นในมือถือ */
        line-height: 1.6 !important;
      }
    }
  </style>

  <!-- Fallback สำหรับ Outlook (Word engine) -->
  <!--[if mso]>
<style>
  .content .detail p {
    margin:0 !important;
    mso-margin-top-alt:0;
    mso-margin-bottom-alt:0;
    mso-line-height-rule:exactly;
    text-indent:0 !important;
  }
</style>
<![endif]-->

</head>

<body style="margin:0;padding:0;background:#f5f5f5;color:#000000;font-size:16px;line-height:1.6">
  <table class="container" cellpadding="0" cellspacing="0">
    <tr>
      <td>
        <!-- Header -->
        <div class="header" style="padding:5px 0px;background-color: #263042;">
          <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo_threat/logo.png') }}" alt="Logo">
          </a>

        </div>
        <br>
        <!-- Content -->
        <div class="content">

          @if($title_th)
          <div>
            <h2>{{$title_th}}</h2>
            <p class="meta">Date: {{$date_create}} | View: {{$news_view}} |
              <a href="{{$link_th}}" style="color:#2a79ff">Link</a>
              {!! $cate_html !!}
            </p>
            <div class="detail" style="  color: #000000; font-size: 20px;">
              {!! $detail_th !!}
            </div>
          </div>
          @endif

          <hr style="margin:10px 0; border: 0; height: 1px; background: #e1e0e7;">

          @if($title_en)
          <div>
            <h2>{{$title_en}}</h2>
            <p class="meta">Date: {{$date_create}} | View: {{$news_view}} |
              <a href="{{$link_en}}" style="">Link</a>
              {!! $cate_html !!}
            </p>
            <div class="detail" style="  color: #000000 !important;">
              {!! $detail_en !!}
            </div>
          </div>
          @endif
        </div>

        <!-- Footer -->
        <div class="footer">
          You have received this notification because you signed up for
          <a href="/" style="color:#888; text-decoration:underline">SOSECURE</a>.
        </div>
      </td>
    </tr>
  </table>
</body>

</html>