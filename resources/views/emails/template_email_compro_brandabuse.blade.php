<!DOCTYPE html>
<html lang="en" class="miro" style="background-color:#f3f4f8;font-size:0;line-height:0">
  <head xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en" style="font-family:Helvetica,Arial,sans-serif">
    <meta charset="UTF-8" style="font-family:Helvetica,Arial,sans-serif">
    <title style="font-family:Helvetica,Arial,sans-serif">Title</title>
    <link rel="stylesheet" href="../css/app.css" style="font-family:Helvetica,Arial,sans-serif">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" style="font-family:Helvetica,Arial,sans-serif">
    <meta name="viewport" content="width=device-width" style="font-family:Helvetica,Arial,sans-serif">
  </head>

  <body style="-moz-box-sizing:border-box;-ms-text-size-adjust:100%;-webkit-box-sizing:border-box;-webkit-text-size-adjust:100%;Margin:0;background:#f5f5f5;background-color:#f3f4f8;box-sizing:border-box;color:#0a0a0a;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;line-height:1.43;margin:0;min-width:600px;padding:0;text-align:left;width:100%!important">
    <table class="miro__container" align="center" width="600" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0;font-family:Helvetica,Arial,sans-serif;max-width:90%;min-width:90%;padding:0;text-align:left;vertical-align:top">
      <tr style="font-family:Helvetica,Arial,sans-serif;padding:0;text-align:left;vertical-align:top">
        <td class="miro__content-wrapper" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;hyphens:auto;line-height:1.43;margin:0;padding:0;padding-top:43px;text-align:left;vertical-align:top;word-wrap:break-word">
          <div class="miro__content" style="background-color:#fff;font-family:Helvetica,Arial,sans-serif">
            <div class="miro__header" style="background:#263042;font-family:Helvetica,Arial,sans-serif;height:100%;min-height:100px;padding:0 40px">
              <table class="miro__header-content" style="border-collapse:collapse;border-spacing:0;font-family:Helvetica,Arial,sans-serif;padding:0;text-align:left;vertical-align:top;width:100%">
                <tr style="font-family:Helvetica,Arial,sans-serif;padding:0;text-align:left;vertical-align:top">
                  <td class="miro__col-header-logo" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;hyphens:auto;line-height:1.43;margin:0;padding:0;padding-top:32px;text-align:left;vertical-align:top;width:50%;word-wrap:break-word">
                    <a href="{{url('/')}}" target="_blank" style="Margin:0;color:#2a79ff;font-family:Helvetica,Arial,sans-serif;font-weight:400;line-height:1.43;margin:0;padding:0;text-align:left;text-decoration:none">
						<img src="{{asset('images/logo_threat/logo.png')}}" style="-ms-interpolation-mode:bicubic;border:none;clear:both;display:block;font-family:Helvetica,Arial,sans-serif;height:45px;max-height:100%;max-width:100%;outline:0;text-decoration:none;width:auto">
                    </a>
                  </td>
                  <td class="miro__col-header-btn" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;hyphens:auto;line-height:1.43;margin:0;padding:0;padding-top:26px;text-align:right;vertical-align:top;width:50%;word-wrap:break-word">
                  <a href="{{url('/')}}" class="miro-btn" target="_blank" style="Margin:0;background-color:#fff;border:1px solid #050038;border-radius:4px;box-sizing:border-box;color:#050038!important;cursor:pointer;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:16px!important;font-stretch:normal;font-style:normal;font-weight:400;height:48px;letter-spacing:normal;line-height:48px!important;margin:0;padding:0;text-align:center;text-decoration:none;white-space:nowrap;width:170px">
						<span style="font-family:Helvetica,Arial,sans-serif">
							Go To Threat inSights
						</span>
                    </a>
                  </td>
                </tr>
              </table>
            </div>

            <div class="miro__content-body" style="font-family:Helvetica,Arial,sans-serif">
                {{-- Section TH --}}
                <div class="miro-title-block" style="background-position:center;background-repeat:no-repeat;background-size:100% auto;font-family:Helvetica,Arial,sans-serif;padding:0px 40px 0px">
                  <div style="margin-top: 20px">

                    <div style="text-align: left;">
                      <h1 style="font-weight:700;color:#000;font-size: 40px;">Add Complete</h1>
                    </div>
                    @foreach($compromised as $data)
                    <div style="overflow: auto;width:100%;">
                      <table style="border: 1px solid #dcdcdc;width:100%;background:#f1f1f1;overflow:auto">
                        @if ($data->feel_type=='social'||$data->feel_type=='darkweb_public')
                        <tr style="background: #3869d4;">
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">Site</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">Type</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">Keyword Ref</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">Source</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;width:300px;white-space:nowrap">Content</th>
                        </tr>
                        
                        <tr style="background: #fff">
                          <td style="color: #333;font-weight:bold;padding:10px;white-space:nowrap">
                            <div style="display: none;">{{$get_site = App\BrandAbuseSocialRef::where('brand_abuse_feed_id',$data->id)->select('site_id')->first()}}</div> 
                            <div style="display: none;">{{$site_name = Modules\SiteSettings\Entities\SiteSettings::where('id',$get_site->site_id)->select('name')->first()}}</div>
                            <span style="font-weight:500;color:#060606;font-weight:700;display:inline-block">Site : </span> <span> {{$site_name->name}} </span>
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;white-space:nowrap">
                            {{get_word_leak_compromise($data->feel_type,'brand_abuse')}}
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;">
                            {{@$data ->keyword}}
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;">
                            {{ @$data -> source_name }}
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;">
                            <div style="width: 300px">
                              {!! @$data -> feedcontent !!}
                            </div>
                          </td>
                        </tr>

                        @elseif ($data->feel_type=='darkweb'||$data->feel_type=='compromise'||$data->feel_type=='webserver'||$data->feel_type=='server')
                       
                        <tr style="background: #3869d4;">
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">Site</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">type</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">Keyword Ref</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;width:300px;white-space:nowrap">Content</th>
                          <th style="color: #fff;font-weight:bold;padding:10px;white-space:nowrap">Remark</th>
                        </tr>

                        <tr style="background: #fff">
                          <td style="color: #333;font-weight:bold;padding:10px;white-space:nowrap">
                            <div style="display: none;">{{$get_site = App\BrandAbuseSocialRef::where('brand_abuse_feed_id',$data->id)->select('site_id')->first()}}</div> 
                            <div style="display: none;">{{$site_name = Modules\SiteSettings\Entities\SiteSettings::where('id',$get_site->site_id)->select('name')->first()}}</div>
                            <span style="font-weight:500;color:#060606;font-weight:700;display:inline-block"></span> <span> {{$site_name->name}} </span>
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;white-space:nowrap">
                            {{get_word_leak_compromise(@$data->feel_type,'compromise')}}
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;">
                            {{@$data ->keyword}}
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;">
                            <div style="width: 300px">
                              {!! @$data -> feedcontent !!}
                            </div>
                          </td>
                          <td style="color: #333;font-weight:bold;padding:10px;">
                            {!! @$data -> source_name !!}
                          </td>
                        </tr>
                        @endif
                      </table>

                      <div style="color: #333;font-weight:bold;padding:10px;white-space:nowrap">
                        Date : {{ @$data -> created_at }}
                      </div>

                    </div>      
                    @endforeach
       

                  </div>
        

                </div>   
              {{-- Under Line --}}
              <div style="border: 1px solid #eee;margin-top:30px;"></div>

              <div class="miro__sep" style="background-color:#e1e0e7;font-family:Helvetica,Arial,sans-serif;height:1px"></div>
            </div>
          </div>
          <div class="miro__footer" style="font-family:Helvetica,Arial,sans-serif;padding-bottom:72px;padding-top:42px">
            <div class="miro__footer-title" style="color:#050038;font-family:Helvetica,Arial,sans-serif;font-size:16px;font-stretch:normal;font-style:normal;font-weight:400;letter-spacing:normal;line-height:1.38;margin-top:0!important;opacity:.7;text-align:center">You have received this notification because you have signed up
              <br style="font-family:Helvetica,Arial,sans-serif">for
              <a href="/" target="_blank" style="Margin:0;color:inherit;font-family:Helvetica,Arial,sans-serif;font-weight:400;line-height:1.43;margin:0;padding:0;text-align:left;text-decoration:none">SOSECURE</a>— endless online whiteboard for team collaboration.</div>
          </div>
        </td>
      </tr>
    </table>

    <div style="display:none;font:15px courier;font-family:Helvetica,Arial,sans-serif;line-height:0;white-space:nowrap"></div>
  </body>
</html>
