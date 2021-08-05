@php
use Carbon\Carbon;
@endphp
@extends('layouts.app')
@section('content')

<style>
    .text_icon_social {
        font-size: 18px;
        font-weight: 500;
        color: black;
        display: block;
        margin-top: -10px;
        margin-left: 130px; 
    }
    .number-card_s {
        font-size: 30px;
        font-weight: 700;
    }
    .border_cicle {
        border: solid 4px #e4dcdc
    }
</style>
<section id="content" class="bg">
    <section class="hbox stretch">
        <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show" data-target="#setting-nav">@icon('solid/bars')</a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right btn-h-vis-menu">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">Data Leak</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_data_leak')
                    </section>
                </section>
            </section>
        </aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                    <div class="header-flex-overflow m-t-7">
                        <div class="fwb-16">
                            @if(TYPE_WEB == 'center')
                                @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                                    <button class="show-setting btn btn-icon btn-default btn-sm m-r-xs">@icon('solid/bars')</button>
                                @endif
                            @endif
                            <span>
                                Data Leak Data
                            </span>
                        </div>

                        <div class="ml-2 text-right">
                     
                            <div class="text-left max-w-select" style="display:inline-block;">
                                <select name="site" id="site" class="text-left select2-option form-control select-site">
                                    <option value="">All Site</option>
                                    @if($SiteSettings)
                                    @foreach($SiteSettings as $SiteSettings_val)
                                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
    

                            @if(!empty(get_role_custom()))
                            {{-- // var_dump(get_role_custom()['superadmin']);
                                // var_dump(get_role_custom()['site_admin']); --}}
                                @if(TYPE_WEB == 'center')
                                    @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                                        <a id="btn_dataleak_feed" href="{{site_url('/datafeedsocial')}}" class="btn btn-sm btn-info m-l-xs"><span> Dataleak Feed</span></a>
                                    @endif

                                    <a href="{{route('dataleak.create') }}" class="btn btn-sm btn-{{ get_option('theme_color') }}" data-toggle="ajaxModal">
                                        <span data-rel="tooltip" title="Add" data-placement="top">@icon('solid/plus')</span>
                                        <span class="hide-text">@langapp('add')</span>
                                    </a>
                                    
                                @endif
                            @endif

                            <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} ">
                                <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                            </a>

                            @if(!empty(get_role_custom()))

                                @if(TYPE_WEB == 'center')

                                    <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger"
                                        value="bulk-delete" disabled>
                                        <span data-rel="tooltip" title="Delete" data-placement="bottom">
                                            @icon('solid/trash-alt')<span class="hide-text">@langapp('delete')</span> 
                                        </span>
                                    </button>

                                @endif
                            @endif
                        </div>
                    </div>
                </header>



                <section class="scrollable wrapper">

                    <div class="container-fluid m-b-xs">
                        <div class="row">
                            <div class="col-xl-2 col-lg-2 col-md-12 nopadding m-b-12">
                                <div class="row">
                                    <div class="col-lg-12 col-md-4 col-sm-12 col-xs-12" style="height: 149.5px;">
                                        <a href="javascript:void(0)" onclick="dataType2('reported')">
                                           <div class="card-dash" style="height: 149.5px; border: 1px solid #e4e4e4;">
                                              <div class="left-card">
                                                 <div class="img-icon-card" style="width: 55px; height: 55px;"><img src="{{asset('images/icon/Reported.png')}}" alt="" onerror="setDefaultPic(this)"></div>
                                                 <h3 class="name-dash-text text-dark text-upper ">Reported</h3>
                                                 <span class="number-card green number_reported">0</span>
                                              </div>
                                           </div>
                                        </a>
                                     </div>
                                   <div class="col-lg-12 col-md-4 col-sm-12 col-xs-12" style="height: 149.5px;">
                                      <a href="javascript:void(0)" onclick="dataType2('in_progress')">
                                         <div class="card-dash" style="height: 149.5px; border: 1px solid #e4e4e4;">
                                            <div class="left-card">
                                               <div class="img-icon-card" style="width: 55px; height: 55px;"><img src="{{asset('images/icon/In progress.png')}}" alt="" onerror="setDefaultPic(this)"></div>
                                               <h3 class="name-dash-text text-dark text-upper ">In Progress</h3>
                                               <span class="number-card info number_in_progress">0</span>
                                            </div>
                                         </div>
                                      </a>
                                   </div>
                                   <div class="col-lg-12 col-md-4 col-sm-12 col-xs-12" style="height: 149px;">
                                      <a href="javascript:void(0)" onclick="dataType2('close')">
                                         <div class="card-dash" style="height: 150px; border: 1px solid #e4e4e4;">
                                            <div class="left-card">
                                               <div class="img-icon-card" style="width: 55px; height: 55px;"><img src="{{asset('images/icon/Close .png')}}" alt="" onerror="setDefaultPic(this)"></div>
                                               <h3 class="name-dash-text text-dark text-upper ">Close</h3>
                                               <span class="number-card warning number_close">0</span>
                                            </div>
                                         </div>
                                      </a>
                                   </div>
                                </div>
                             </div>
        
                             
                            <div class="col-xl-10 col-lg-10 col-md-12">
                                <div class="row">
                                    <div class="container-fluid" style="">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 col-xs-12 nopadding" style="border-right: 0px !important;border: 1px solid #e4e4e4;">
                                               <div class="col-md-12 nopadding" style="
                                                  /* grid-template-columns: 1fr 1fr; */
                                                  /* display: grid; */
                                                  ">
                                                  <div class="col-md-12 nopadding" style="
                                                     border-right: solid 0;
                                                     ">
                                                     <div class="card-dash-compro none-bg none-shadow col-md-6 col-xs-6" style="min-height: 149px; box-shadow: 0 0px 0px #f5f5f5;">
                                                        <a href="javascript:void(0)" onclick="dataType2('mobile')">
                                                           <div class="left-card">
                                                              <div id="left1" class="img-icon-card ice" style="height: 100px; width: 300px; text-align: left; margin-left: 90px;">
                                                                 <img src="{{asset('images/icon/MOBILE.png')}}" alt="" onerror="setDefaultPic(this)" class="border_cicle" style="width: 60px; height: 60px; margin-left: 50px; margin-top: 10px; border-radius: 50%; background: #d8d8d8;"><span class="number-card_s warning" style="font-size: 35px !important;padding: 20px; margin-left: 108px; color: #ffcc00;" id="icon_mobile">0</span>
                                                                 <p class="text_icon_social">Mobile</p>
                                                              </div>
                                                           </div>
                                                        </a>
                                                     </div>
                                                     <div class="card-dash-compro none-bg none-shadow col-md-6 col-xs-6" style="min-height: 149px; box-shadow: 0 0px 0px #f5f5f5;">
                                                        <a href="javascript:void(0)" onclick="dataType2('line')">
                                                           <div class="left-card">
                                                              <div id="right1" class="img-icon-card ice right" style="height: 100px; width: 300px; text-align: left; margin-left: 90px;">
                                                                 <img src="{{asset('images/icon/line_icon.png')}}" alt="" onerror="setDefaultPic(this)" class="border_cicle" style="width: 60px; height: 60px; margin-left: 50px; margin-top: 10px; border-radius: 50%; "><span class="number-card_s info" style="font-size: 35px !important;padding: 20px; margin-left: 108px; color: #00dcff;" id="icon_line">0</span>
                                                                 <p class="text_icon_social">Line</p>
                                                              </div>
                                                           </div>
                                                        </a>
                                                     </div>
                                                     <div class="card-dash-compro none-bg none-shadow col-md-6 col-xs-6" style="min-height: 149px; box-shadow: 0 0px 0px #f5f5f5;">
                                                        <a href="javascript:void(0)" onclick="dataType2('website')">
                                                           <div class="left-card">
                                                              <div id="left2" class="img-icon-card ice" style="height: 100px; width: 300px; text-align: left; margin-left: 90px;">
                                                                 <img src="{{asset('images/icon/WEBSITE.png')}}" alt="" onerror="setDefaultPic(this)" class="border_cicle" style="width: 60px; height: 60px; margin-left: 50px; margin-top: 10px; border-radius: 50%;"><span class="number-card_s " style="color: #8793db; font-size: 35px !important;padding: 20px; margin-left: 108px; color: #8793db;" id="icon_website">0</span>
                                                                 <p class="text_icon_social">Website</p>
                                                              </div>
                                                           </div>
                                                        </a>
                                                     </div>
                                                     <div class="card-dash-compro none-bg none-shadow col-md-6 col-xs-6" style="min-height: 149px; box-shadow: 0 0px 0px #f5f5f5;">
                                                        <a href="javascript:void(0)" onclick="dataType2('facebook')">
                                                           <div class="left-card">
                                                              <div id="right2" class="img-icon-card ice right" style="height: 100px; width: 300px; text-align: left; margin-left: 90px;">
                                                                 <img src="{{asset('images/icon/FACEBOOK.png')}}" alt="" onerror="setDefaultPic(this)" class="border_cicle" style="width: 60px; height: 60px; margin-left: 50px; margin-top: 10px; border-radius: 50%;"><span class="number-card_s info" style="font-size: 35px !important;padding: 20px; margin-left: 108px; color: #00dcff;" id="icon_facebook">0</span>
                                                                 <p class="text_icon_social">Fanpage</p>
                                                              </div>
                                                           </div>
                                                        </a>
                                                     </div>
                                                     <div class="card-dash-compro none-bg none-shadow col-md-6 col-xs-6" style="min-height: 149px; box-shadow: 0 0px 0px #f5f5f5;">
                                                        <a href="javascript:void(0)" onclick="dataType2('twitter')">
                                                           <div class="left-card">
                                                              <div id="left3" class="img-icon-card ice" style="height: 100px; width: 300px; text-align: left; margin-left: 90px;">
                                                                 <img src="{{asset('images/icon/TWIITER.png')}}" alt="" onerror="setDefaultPic(this)" class="border_cicle" style="width: 60px; height: 60px; margin-left: 50px; margin-top: 10px; border-radius: 50%;"><span class="number-card_s " style="color: #5bb984; font-size: 35px !important;padding: 20px; margin-left: 108px; color: #5bb984;" id="icon_twitter">0</span>
                                                                 <p class="text_icon_social">Twitter</p>
                                                              </div>
                                                           </div>
                                                        </a>
                                                     </div>
                                                     <div class="card-dash-compro none-bg none-shadow col-md-6 col-xs-6" style="min-height: 149px; box-shadow: 0 0px 0px #f5f5f5;">
                                                        <a href="javascript:void(0)" onclick="dataType2('other')">
                                                           <div class="left-card">
                                                              <div id="right3" class="img-icon-card ice right" style="height: 100px; width: 300px; text-align: left; margin-left: 90px;">
                                                                 <img src="{{asset('images/icon/OTHER2.png')}}" alt="" onerror="setDefaultPic(this)" class="border_cicle" style="width: 60px; height: 60px; margin-left: 50px; margin-top: 10px; border-radius: 50%;"><span class="number-card_s " style="color: #565656; font-size: 35px !important;padding: 20px; margin-left: 108px; color: #565656;" id="icon_other">0</span>
                                                                 <p class="text_icon_social">Other</p>
                                                              </div>
                                                           </div>
                                                        </a>
                                                     </div>
                                                  </div>
                                               </div>
                                            </div>
                                            <div class="col-md-3 col-sm-12 col-xs-12 nopadding">
                                               <div class="col-md-12 nopadding">
                                                  <div class="card-dash-compro none-bg none-shadow " style="border-top: solid 1px #e4e4e4;min-height: 51px;box-shadow: 0 0px 0px #f5f5f5;"></div>
                                               </div>
                                               <div class="col-md-12 nopadding">
                                                  <div class="card-dash-compro none-bg none-shadow " style="min-height: 149px; box-shadow: 0 0px 0px #f5f5f5;">
                                                     <a href="#" onclick="dataType('social')">
                                                        <div id="icon_publish" class="left-card" style="margin-left: 0px;">
                                                           <div class="img-icon-card ice" style="width: 150px;height: 150px;"><img src="{{asset('images/icebergline2.png')}}" alt="" onerror="setDefaultPic(this)"></div>
                                                           <h3 class="name-dash-text-compro text-dark text-upper ">Public</h3>
                                                           <span class="number-card warning" id="compromise-count">0</span>
                                                        </div>
                                                     </a>
                                                  </div>
                                               </div>
                                               <div class="col-md-12 nopadding">
                                                  <div id="area_pp" class="card-dash-compro none-bg none-shadow" style="min-height: 151px;box-shadow: 0 0px 0px #f5f5f5;"></div>
                                               </div>
                                            </div>
                                            <div class="col-md-3 col-sm-12 col-xs-12 nopadding" style=" border: 1px solid #e4e4e4;">
                                               <div class="col-md-12 nopadding">
                                                  <div class="card-dash-compro none-bg none-shadow " style="min-height: 51px; box-shadow: 0 1px 1px rgb(0 0 0 / 20%); "></div>
                                               </div>
                                               <div class="col-md-12 nopadding">
                                                  <div class="card-dash-compro none-bg none-shadow" style=" box-shadow: 0 1px 1px rgb(0 0 0 / 20%);">
                                                     <a href="#" onclick="dataType('darkweb_public')">
                                                        <div class="left-card">
                                                           <div class="img-icon-card ice" style="width: 150px;height: 150px; "><img src="{{asset('images/icebergline1.png')}}" alt="" onerror="setDefaultPic(this)"></div>
                                                           <h3 class="name-dash-text-compro text-dark text-upper">Dark Web</h3>
                                                           <span class="number-card info" id="darkweb-count">0</span>
                                                        </div>
                                                     </a>
                                                  </div>
                                               </div>
                                               <div class="col-md-12 nopadding">
                                                  <div class="card-dash-compro none-bg none-shadow " style="min-height: 149px; box-shadow: 0 1px 1px rgb(0 0 0 / 20%); "></div>
                                               </div>
                                            </div>
                                         </div>
                                    </div>
        
                                </div>
                            </div>
                        </div>
                    </div>

                    <section class="panel panel-default" id="hide-advance-search" style="display: none">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-md-12">
                                    <i class="fas fa-filter"></i> Filter
                                </div>
                        </header>
                        <div class="panel-body" style="padding: 0 !important">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row">
                                <div class="col-lg-6 mb-1">
                                    <h5 class="font-weight-bold">Keyword/Content</h5>
                                        <input type="text" id="keyword" class="form-control">
                                </div>
                                {{-- <div class="col-lg-4 mb-1">
                                    <h5 class="font-weight-bold">Source </h5>
                                    <select id="source" class="select2-option form-control">
                                        <option value="">All</option>
                                        @if ($source)
        
                                        @foreach ($source as $source)
                                        <option value="{{$source->id}}">{{$source->source}}
                                        </option>
                                        @endforeach
        
                                        @endif
                                    </select>
                                </div> --}}
                                <div class="col-lg-6 mb-1">
                                    <h5 class="font-weight-bold">Date</h5>
                                    <div id="social_datas_date" class="text-center form-control"
                                        style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4 mb-1">
                                    <h5 class="font-weight-bold">Type</h5>
                                    <div id="groupby-type" class="btn-group special">
                                        <button id="all" class="btn btn-grey check_type active" value="">
                                            <span> All</span>
                                        </button>
                                        <button class="btn btn-grey check_type btn-social-click" value="social">
                                            <span> Public </span>
                                        </button>
                                        <button class="btn btn-grey check_type" value="darkweb_public">
                                            <span> Darkweb </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-4 hide-social" style="display: none">
                                    <h5 class="font-weight-bold">Social</h5>
                                    <div id="groupby-social" class="btn-group special">
                                        <button id="all" class="btn btn-grey active check_social" value="Mobile">
                                            <span> Mobile App</span>
                                        </button>
                                        <button class="btn btn-grey check_social" value="Facebook">
                                            <span> Fanpage </span>
                                        </button>
                                        <button class="btn btn-grey check_social" value="Line">
                                            <span> Line </span>
                                        </button>
                                        <button class="btn btn-grey check_social" value="Twitter">
                                            <span> Twitter </span>
                                        </button>
                                        <button class="btn btn-grey check_social" value="Website">
                                            <span> Website </span>
                                        </button>
                                        <button class="btn btn-grey check_social" value="other">
                                            <span> Other </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 mb-1">
                                <h5 class="font-weight-bold">Serverity</h5>
                                <div id="btngroup_status" class="btn-group special ">
                                    
                                    <button class="btn btn-grey check_serverity active" value="" id="btn_search_all">
                                        <span> All </span>
                                    </button>
                                    <button class="btn check_serverity btn-grey" value="critical">
                                        <span> Critical </span>
                                    </button>
                                    <button class="btn check_serverity btn-grey" value="high">
                                        <span> High </span>
                                    </button>
                                    <button class="btn check_serverity btn-grey" value="medium">
                                        <span> Medium </span>
                                    </button>
                                    <button class="btn check_serverity btn-grey" value="low">
                                        <span> Low </span>
                                    </button>
                                    <button class="btn check_serverity btn-grey" value="information">
                                        <span> Information </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 mb-1">
                            <h5 class="font-weight-bold">Status Monitoring</h5>
                            <div id="btngroup_monitoring" class="btn-group special ">
                                
                                <button class="btn btn-grey check_monitoring active" value="" id="btn_monitoring">
                                    <span> All </span>
                                </button>
                                <button class="btn check_monitoring btn-grey" value="reported">
                                    <span> Reported </span>
                                </button>
                                <button class="btn check_monitoring btn-grey" value="in_progress">
                                    <span> Progress </span>
                                </button>
                                <button class="btn check_monitoring btn-grey" value="close">
                                    <span> Close </span>
                                </button>
                            </div>
                        </div>
                    </div>
                  
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13"
                                    onclick="search()">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="social_reset" class="btn btn-default btn-responsive btn-fz-13"
                                    style="white-space: nowrap">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                                <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13"
                                    style="white-space: nowrap">
                                    <i class="fas fa-times"></i>
                                    <span> Close </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>






                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12" style="    padding-top: 10px;">
                                    <i class="fas fa-table"></i> Table Data Leak Data
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="font-weight-bold">Status</h5>
                                    <div class="st-dt-leak">
                                        <span class="st-dt vrh" data-toggle="tooltip" data-placement="right" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip vrh'>Critical</div><div class='text-st-tooltip'>Critical	ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Critical</span>
                                        <span class="st-dt high" data-toggle="tooltip" data-placement="right" data-html="true"title="<div class='st-flex'><div class='box-st-tooltip high'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">High</span>
                                        <span class="st-dt md" data-toggle="tooltip" data-placement="right" data-html="true"  title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Medium</span>
                                        <span class="st-dt low" data-toggle="tooltip" data-placement="right" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของลูกค้าเช่น ข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">Low</span>
                                        <span class="st-dt vrl" data-toggle="tooltip" data-placement="right" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip vrl'>Informational</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลที่เป็นข้อมูลทั่วไปหรือเป็นข่าวที่ยังไม่ได้รับการยืนยันว่าเป็นข้อมูลรั่วไหลจริง</div></div>">Very Low</span>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table  class="table table-striped" style="width: 100%;" id="table_social_datas">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>Site</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Keyword</th>
                                            <th>Content</th>
                                            <th>Severity</th>
                                            <th>Status Monitoring</th>                                        
                                            <th>Data Feed</th>
                                            {{-- <th>View</th> --}}
                                            <th>Status</th>
                                            <th>@langapp('action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- <tr>
                                            <td>
                                                <label>
                                                    <input name="select_all" value="1" type="checkbox" class="select-chk"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </td>
                                            <td>
                                                Pantip
                                            </td>
                                            <td>
                                                Fibre
                                            </td>
                                            <td>
                                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Dignissimos,
                                            </td>
                                            <td class="no-wrap">
                                                2020-12-2020 12:12
                                            </td>
                                            <td>
                                                1
                                            </td>
                                            <td>
                                                <label class="switch">
                                                    <input type="hidden" value="FALSE" name="">
                                                    <input type="checkbox" name="status" checked value="TRUE">
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td class="no-wrap text-center">
                                                <button class="btn btn-danger btn-xs">
                                                    @icon('solid/trash-alt')
                                                </button>
                                            </td>
                                        </tr> --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
    </section>

    @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['site_admin'] == 1)
    @php $admin = 1; @endphp
    @else
    @php $admin = 0; @endphp
    @endif

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    {{-- <div class="modal in fixed-left" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <span class="modal-title" id="exampleModalLabel">Delete</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <p class="text-danger">@langapp('delete_warning')  </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="delete-all btn btn-danger btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Delete
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div> --}}

    <div class="modal" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
        style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning') </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                            class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" class="btn btn-info submit btn-rounded delete-all">
                        <i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal create_assets_vulnerability -->
    {{--<div class="modal in fixed-left" id="change_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Confirm Information
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select name="" id="" class="form-control">
                                <option value="1">Approved</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div> --}}

</section>


{{-- for (var i = 0; i < response.model.length; i++) {
    $('#fillter_click_keyword').html(`<a class="btn btn-selector" href="javascript:void(0)" onclick="click_keyword('${response.model[i]['keyword']}')">${response.model[i]['keyword']} (${response.model[i]['count_keyword']})</a>`);
} --}}


{{-- "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
    var date_day = '2021-01-01';
    var date = aData.get_data_leak_feed_one.feedtimepost;
    var date_sp = date.split(" ");
    if(date_sp.length > 0) {
        date_day = date_sp[0];
    }

    var date_now = '{{Carbon::now()}}';
    let date_now_sp = date_now.split(" ");
    if(date_now_sp.length > 0) {
        var date_now_day = date_now_sp[0];
    }

    console.log(date_day+' now:'+date_now_day);
    if ((date_day) == (date_now_day)) {
        $('td:eq(0)', nRow).addClass("custom_new");
        $('td:eq(1)', nRow).addClass("custom_new");
        $('td:eq(2)', nRow).addClass("custom_new");
        $('td:eq(3)', nRow).addClass("custom_new");
        $('td:eq(4)', nRow).addClass("custom_new");
        $('td:eq(5)', nRow).addClass("custom_new");
        $('td:eq(6)', nRow).addClass("custom_new");
        $('td:eq(7)', nRow).addClass("custom_new");
        $('td:eq(8)', nRow).addClass("custom_new");
        $('td:eq(9)', nRow).addClass("custom_new");
        $('td:eq(10)', nRow).addClass("custom_new");
    }
}, --}}

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.datepicker')
@include('stacks.css.form')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.activebutton')
@include('stacks.js.fullscreen')
@include('stacks.js.defaultpic')
<script>
var click_key = null;
var table;

{{--active_btn('#fillter_click_keyword .btn-selector');--}}
active_btn('#groupby-type .btn-grey');
active_btn('#groupby-social .btn-grey');
active_btn('#btngroup_status .btn-grey');
active_btn('#btngroup_monitoring .btn-grey');


    function myFunction(x) {
        let area_logo_os = document.getElementById("area_pp");
        let left1 = document.getElementById("left1");
        let left2 = document.getElementById("left2");
        let left3 = document.getElementById("left3");
        let right1 = document.getElementById("right1");
        let right2 = document.getElementById("right2");
        let right3 = document.getElementById("right3");
        if (x.matches) {
            area_logo_os.style.minHeight = "0px";
            left1.style.marginLeft = "60px";
            left2.style.marginLeft = "60px";
            left3.style.marginLeft = "60px";
            right1.style.marginLeft = "60px";
            right2.style.marginLeft = "60px";
            right3.style.marginLeft = "60px";
        } else {
            area_logo_os.style.minHeight = "151px";
            left1.style.marginLeft = "90px";
            left2.style.marginLeft = "90px";
            left3.style.marginLeft = "90px";
            right1.style.marginLeft = "90px";
            right2.style.marginLeft = "90px";
            right3.style.marginLeft = "90px";
        }
    }


    var x = window.matchMedia("(max-width: 990px)");
    myFunction(x);
    x.addListener(myFunction);




$('.btn').click(function(){
    if($('.btn-social-click').hasClass('active')){
        $('.hide-social').show();
    }else{
        $('.hide-social').hide();
    }
});

    var admin = '{{$admin}}';
        var visible_c = '';

        if(admin == 1) {
            visible_c = true;
        } else {
            visible_c = false;
        }

    var id_select_site = 'site';
    var search_val = 0;
    var keywords = null;
    var site = null;
    var type = null;
    var source = null;
    var startDate = null;
    var endDate = null;
    var isDateSearch = null;
    var social_id = [];
    var check_type = null;
    var check_serverity = null;
    var check_monitoring = null;
    var check_social = 'Mobile';
    

    $('#table_social_datas').on('click', '.select-chk', function () {
    if ($(this).is(':checked')) {

        $('#btn-change-status').prop("disabled", false);
    } else {
        
        if ($('.select-chk').filter(':checked').length < 1){

            $('#btn-change-status').attr('disabled',true);
        }
    }
    });

    $('#table_social_datas').on('click', '.social_id', function () {
        if ($(this).is(':checked')) {

            
            $('#btn-change-status').prop("disabled", false);
        } else {
            if ($('.social_id').filter(':checked').length < 1){
                
                $('#btn-change-status').attr('disabled',true);
            }
        }
    });

    $(function() {
        if(get_cookie_site()){
            cookie_change_site("{{route('systemsetting.check_cookie_site')}}",id_select_site);
            count_icon();
        }else{
            table_social_data();
            get_count();
            {{--count_keyword();--}}
            count_icon();
        }
    });

    $(".check_type").click(function() {
        check_type = $(this).val();
   
    });
    $(".check_social").click(function() {
        check_social = $(this).val();
   
    });

    $(".check_serverity").click(function() {
        check_serverity = $(this).val();
    });

    $(".check_monitoring").click(function() {
        check_monitoring = $(this).val();
    });

    $("#site").change(function() {
        set_cookie_site($(`#${id_select_site}`).val());
        site = this.value;   
        table_social_data();
        get_count();
        {{--count_keyword();--}}
        count_icon();
        });

    function search(){
        click_key = null;
        click_type2 = null;
        click_type = null;
        search_val = 1;
        keywords = $('#keyword').val();
        type = $('#type option:selected').val();
        source = $('#source option:selected').val();
        startDate =  $("#social_datas_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate =  $("#social_datas_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        $('.btn-selector').removeClass('active');
      
        table_social_data();
        {{--get_count();--}}
    }

    function table_social_data(){

      

        table = $('#table_social_datas').DataTable({
                pageLength: 50,
                processing: true,
                serverSide: true,
                destroy: true,
                "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                ajax: {
                    type: "POST",
                    url: '{!! route('socialdatas.socialdatas_all_site_tb') !!}',
                    data: function ( d ) {
                        d.keywords = keywords;
                        d.site = site;
                        d.type = type;
                        d.source = source;
                        d.search_val = search_val;
                        d.startDate = startDate;
                        d.endDate = endDate;
                        d.isDateSearch = isDateSearch;
                        d.check_type = check_type;
                        d.click_type = click_type;
                        d.click_key = click_key;
                        d.click_type2 = click_type2;
                        d.check_serverity = check_serverity;
                        d.check_monitoring = check_monitoring;
                        d.check_social = check_social;

                        return d;
                    },
                },  
                initComplete : function( settings, json){
                    $('[data-rel="tooltip"]').tooltip();
                    {{--console.log(json);--}}
                
                    
                },
                createdRow: function ( row, data, index ) {
                    $(row).attr('id', 'tr' + data.id);
                },
                "order": [ 8, 'desc' ],
                columnDefs: [
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false,
                        sortable: false,
                        width: '1px',
                        render: function (data, type, full, meta) {
                            return '<label><input type="checkbox" name="social_id" class="social_id"  value="' + full.id + '"><span class="label-text"></span></label>';
                        },
                    },
                    {
                        targets: 1,
                        width: '10px',
                        className:'nowrap',
                        render: function (data, type, full, meta) {

                            val = full.site_name;
                        
                            return val;

                        },
                    },
                    {
                        targets: 2,
                        width: '60px',
                        className:'nowrap',
                        render: function (data, type, full, meta) {
                
                            if(full.feel_type){
                                return get_word_leak_compromise(full.feel_type,'data_leak');
                            }
                            return '-';

                        },
                    
                    },
                    {
                        targets: 3,
                        width: '60px',
                        render: function (data, type, full, meta) {
                            
                            if(full.source_name){
                                return full.source_name;
                            }
                            return '-';

                        },
                    
                    },
                    {
                        targets: 4,
                        width: '10px',
                        render: function (data, type, full, meta) {
                
        
                            return full.keyword;
                            if(full.keyword){
                                return full.keyword;
                            }
                            return '-';

                        },

                     
                            
                    
                    },
                    {
                        targets: 5,
                        width: '10px',
                        render: function (data, type, full, meta) {

                            var new_html = '';
                            var date_day = '2021-01-01';
                            var date = full.feedtimepost;
                            var date_sp = date.split(" ");
                            if(date_sp.length > 0) {
                                date_day = date_sp[0];
                            }

                            var date_now = '{{Carbon::now()}}';
                            let date_now_sp = date_now.split(" ");
                            if(date_now_sp.length > 0) {
                                var date_now_day = date_now_sp[0];
                            }
                            if ((date_day) == (date_now_day)) {
                                {{--new_html += `<img src="{{asset('images/icon/new.png')}}" style="width:40px; border-radius: 10px;">`;--}}
                                new_html += `<span class="badge" style="background-color: #2196f3;">New</span>`;
                            }
                          
                            if(full.feedcontent){
                                var feedcontent =  stripHtml(full.feedcontent);
                                var res = full.keyword.split(",");
                                let content = '';
                                for(let i in res){
                                    const data2 = res[i];
                                    content += feedcontent.replaceAll(data2, '<span class="badge bg-warning">'+data2+'</span>');
                                }
                         
                                 return'<div class="dt-txt">'+new_html+full.feedcontent+'</div>';
                               
                            }else{
                                return '-';
                            }
                           
                        },
                    },
                    {
                        targets: 7,
                        width: '8%',
                        className: 'nowrap',
                        render: function (data, type, full, meta) {
                
                            if(full.status_monitoring=='in_progress'){
                                return '<span class="badge" style="background-color: #FFC107;">Progress</span>';
                            }else if(full.status_monitoring=='reported'){
                                return '<span class="badge" style="background-color: #28A745;">Reported</span>';
                            }else if(full.status_monitoring=='close'){
                                return '<span class="badge" style="background-color: #DC3545;">Close</span>';
                            }else{
                                return '-';
                            }

                        },
                    }, 
                    {
                        targets: 6,
                        width: '5%',
                        className: 'nowrap',
                        render: function (data, type, full, meta) {
                            if(full.serverity=='critical'){
                        return '<span class="badge" style="background-color: #b93624;">Critical</span>';
                            }else if(full.serverity=='high'){
                                return '<span class="badge" style="background-color: #fcc838;">High</span>';
                            }else if(full.serverity=='medium'){
                                return '<span class="badge" style="background-color: #f2ff15;color:#333;">Medium</span>';
                            }else if(full.serverity=='low'){
                                return '<span class="badge" style="background-color: #409967;">Low</span>';
                            }else if(full.serverity=='information'){
                                return '<span class="badge" style="background-color: #00dcff;">Information</span>';
                            }else{
                                return '-';
                            }

                        },
                    }, 
                    {
                        targets: 8,
                        width: '8%',
                        className: 'nowrap',
                        render: function (data, type, full, meta) {
                
                            if(full.feedtimepost){
                            return full.feedtimepost;
                            }else{
                                return '';
                            }

                        },
                    },                   
                    {
                        visible: visible_c,
                        targets: 9,
                        width: '3%',
                        render: function (data, type, full, meta) {

                            var checked_val = null;
                                if (full.status_data == 1) {
                                    checked_val = 'checked';
                                } else {
                                    checked_val = '';
                                }
                        
                            return  '<label class="switch"><input type="checkbox" id="social_active_' +full.id_data+  '" onchange="social_active( '+full.id_data+')" '+checked_val+' name="active" value="1"><span class="slider round"></span></label>';

                        }

                    },
                    {
                        targets: 10,
                        width: '7%',
                        className : 'nowrap',
                        render: function (data, type, full, meta) {
      
                            return `
                            <a href="${base_url}/socialdatas/activity_modal/${full.code_data}" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="far fa-comment-dots"></i></a>
                            <!--<a href="${base_url}/socialdatas/view_content/${full.code_data}" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="fas fa-eye"></i></a>-->
                            <a href="${base_url}/dataleak/edit_dataleak_modal/${full.code_data}" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                            </a>
                            <a href="${base_url}/socialdatas/delete_dataleakdata_modal/${full.code_data}" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>
                            `;
                      
                            
        
                            
                            
                            {{--href="${base_url}/rssfeedsettings/delete-rss_data/${full.code}"--}}
                        },
                    },

                ]
        
            });
    }

    function stripHtml(html){
        var temporalDivElement = document.createElement("div");
        temporalDivElement.innerHTML = html;
        return temporalDivElement.textContent || temporalDivElement.innerText || "";
    }

    function social_active(id) {    

        let checkState = $("#social_active_" + id).is(":checked") ? 1 : 0;
        axios.post('{{route('socialdatas.change_status_dataleakdata')}}', {
            status: checkState,
            code: id,
        }).then(function (response) {
            console.log(response.data.redirect);
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.response.data.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }

    $(function() {
    
        var start = moment().subtract(1, 'month').startOf('month');{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
        var end = moment();{{--moment().startOf('hour').add(32, 'hour')--}} {{--moment().subtract(0, 'year').endOf('year')--}}

        function cb(start, end) {
            $('#social_datas_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    
        }

        $('#social_datas_date').daterangepicker({
            timePicker: true,
            {{--timePicker24Hour: true,--}}
            startDate: start,
            endDate: end,
            locale: {
                format: 'M/DD hh:mm A'{{--format: 'M/DD HH:mm A'--}}
            },
            ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
        $('#social_datas_date').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = 1;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
        }
    });

    cb(start, end);

            $("#social_reset").click(function() {
                click_key = null;
                click_type = null;
                click_type2 = null;
                keywords = null;
                type = null;
                source = null;
                startDate =  null;
                endDate =  null;
                site = null;
                search_val = 0;
                isDateSearch = null;
                check_serverity = null;

                $("#keyword").val('');
                start = moment().subtract(1, 'month').startOf('month');
                end = moment();
                cb(start, end);
                $("#site").val('').trigger("change");
                $("#source").val('').trigger("change");
                $('.check_type').removeClass('active');
                $('.selector').removeClass('active');
                $('#all').addClass('active');
                $('.check_serverity').removeClass('active');
                $('#btn_search_all').addClass('active');
                $('.check_monitoring').removeClass('active');
                $('#btn_monitoring').addClass('active');
                check_monitoring = null;
                check_type = null;
                $('.hide-social').hide();
                table_social_data();
                get_count();
            

            });
    });

    $("#btn-change-status").click(function() {
        social_id = [];
        $('.social_id:checked').each(function () {
            social_id.push(this.value);
            
        });

        $('#delete_all').modal('show');
        $('.delete-all').click(function(){
            $.ajax({
                type:"POST",
                url:"{{ route('socialdatas.change_delete_dataleakdata') }}",
                data:{id: social_id},
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) {

                    toastr.success(response.message, '@langapp('response_status')');
                    window.location.href = response.redirect;
                },
                error: function (error){
                    loading('stop_load');
                    var errors = error.response.data.errors;
                    var errorsHtml = '';
                    $.each(errors, function (key, value) {
                        errorsHtml += '<li>' + value[0] + '</li>';
                    });
                    toastr.error(errorsHtml, '@langapp('response_status') ');
                }
            });
        });
    });



    function get_count() {

        if(search_val == true) {
            search_val = 1;
        } else {
            search_val = 0;
        }

        $.ajax({
            type:"POST",
            url:'{!! site_url('social/count_val') !!}',
            data: ({
                keywords : keywords,
                site_id : site,
                type : type,
                social : source,
                search_val : search_val,
                startDate : startDate,
                endDate : endDate,
                isDateSearch : isDateSearch,
                check_type : check_type,
                click_type : click_type,
                
            }),
            beforeSend: function(){
                
            },
            success:function(response) {
               
                if(response.darkweb){
                    $('#darkweb-count').text(response.darkweb);
                }else{
                    $('#darkweb-count').text(0);
                }
                if(response.social){
                    $('#compromise-count').text(response.social);
                }else{
                    $('#compromise-count').text(0);
                }
                
                
            },
            error: function (error){
                
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }

        });
}

var click_type = null;
var click_type2 = null;
    function dataType(data){
        
        click_type = data;
        click_type2 = '';
        search_val = 0;
        click_key = null;
        keywords = null;
        type = null;
        source = null;
        startDate =  null;
        endDate =  null;
        check_type = null;
        isDateSearch = null;
        
        $("#keyword").val('');
        $("#source").val('').trigger("change");
        $('.check_type').removeClass('active');
        $('.selector').removeClass('active');
        $('#all').addClass('active');
        $('.check_serverity').removeClass('active');
        $('#btn_search_all').addClass('active');
        check_serverity = null;
        $('.check_monitoring').removeClass('active');
        $('#btn_monitoring').addClass('active');
        check_monitoring = null;
        $('.hide-social').hide();
        table_social_data();
        {{--get_count();--}}
    }

    function dataType2(data){
        click_type = '';
        click_type2 = data;
        search_val = 0;
        click_key = null;
        keywords = null;
        type = null;
        source = null;
        startDate =  null;
        endDate =  null;
        check_type = null;
        isDateSearch = null;
        $("#keyword").val('');
        $("#source").val('').trigger("change");
        $('.check_type').removeClass('active');
        $('.selector').removeClass('active');
        $('#all').addClass('active');
        $('.check_serverity').removeClass('active');
        $('#btn_search_all').addClass('active');
        check_serverity = null;
        $('.check_monitoring').removeClass('active');
        $('#btn_monitoring').addClass('active');
        check_monitoring = null;
        $('.hide-social').hide();
        table_social_data();
        {{--get_count();--}}
    }


    function count_icon() {
        $.ajax({
            type:"POST",
            url:'{!! site_url('social/count_icon') !!}',
            data: ({
                site_id : site,
            }),
            beforeSend: function(){
                {{--loading('load');--}}
            },
            success:function(response) {
                {{--loading('stop_load');--}}
                $("#icon_mobile").html(response.icon_mobile);
                $("#icon_facebook").html(response.icon_facebook);
                $("#icon_line").html(response.icon_line);
                $("#icon_twitter").html(response.icon_twitter);
                $("#icon_website").html(response.icon_website);
                $("#icon_other").html(response.icon_other);
                $(".number_in_progress").html(response.number_in_progress);
                $(".number_reported").html(response.number_reported);
                $(".number_close").html(response.number_close);
            },
            error: function (error){
                {{--loading('stop_load');--}}
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }

        });
    }

   

</script>
@endpush
@endsection
