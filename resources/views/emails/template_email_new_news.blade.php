@php
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

@endphp
<!DOCTYPE html>
<html lang="th" style="font-size:0;line-height:0">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข่าวแจ้งเตือนภัยไซเบอร์</title>
  <style>
    body, table, td, p, a, span, div {
      font-family: 'TH SarabunPSK', Tahoma, Arial, sans-serif !important;
      padding:1px;
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

    }
    .footer {
      text-align: center;
      font-size: 14px;
      color: #000000;
      margin: 40px 20px;
    }
    @media only screen and (max-width: 600px) {
      .header {
        flex-direction: column;
        text-align: center;
      }
      .header a.button {
        margin-top: 10px;
      }
    }

    
  </style>
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
            <div class="detail"  style="  color: #000000 !important;">
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