@extends('layouts.app')
@section('content')
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
<style>
    /* กันไม่ให้คอนเทนต์ท้าย ๆ โดนแถบปุ่มบัง */
    .scrollable.wrapper {
        padding-bottom: 0px;
    }

    .section-bottom-bar {
        position: sticky;
        bottom: 0;
        left: 0;
        width: 100%;
        display: flex;
        z-index: 1050;
    }

    /* .section-bottom-bar .flex-btn {
        flex: 1;
        border-radius: 99px;
        padding: 14px;
        font-size: 16px;
    } */


    .section-bottom-bar {
        position: sticky;
        bottom: 0;
        left: 0;
        width: 100%;
        display: flex;
        z-index: 1050;
    }

    .section-bottom-bar .flex-btn {
        flex: 1 1 0;
        box-sizing: border-box;
        padding: 14px 16px;
        font-size: 16px;
        border-radius: 99px;
        transition:
            flex 200ms ease,
            padding 200ms ease,
            font-size 200ms ease,
            transform 200ms ease,
            background-color 150ms ease,
            color 150ms ease;
        transform-origin: center bottom;
    }

    .section-bottom-bar:hover .flex-btn {
        flex: 0.8;
        transform: scale(0.96);
        opacity: 0.5;
        height: 15%;
        
    }

    .section-bottom-bar:hover .flex-btn:hover {
        flex: 1.6;
        padding: 22px 16px;
        font-size: 18px;
        opacity: 1;
        font-weight: bold;
        animation-duration: 300ms;
        /* transition-delay: 200ms; */
    }

    .section-bottom-bar .btn-info:hover {
        color: #fff !important;
        background-color: #023f81 !important;
        border-color: #fff;
    }

    .section-bottom-bar .btn-danger:hover {
        color: #fff !important;
        background-color: #ce0000ff !important;
        border-color: #fff !important;
    }


    /* (ทางเลือก) ลดอนิเมชันบนจอเล็ก */
    @media (max-width: 480px) {
        .section-bottom-bar:hover .flex-btn {
            flex: 0.9;
            transform: none;
        }
        .section-bottom-bar:hover .flex-btn:hover {
            flex: 1.2;
            padding: 18px 14px;
            font-size: 17px;
        }
    }
    .text-message{
        font-size: 14px;
        color: #ff0000ff;
        font-weight: bold;
    }
    .text-message-warning{
        font-size: 14px;
        color: #fada5e !important;
        font-weight: bold;
    }
    .text-description{
        text-align: justify;
        font-size: 13px;
        color: #683d3dff;
        font-weight: bold;
    }

    .section-bottom-bar:hover .flex-btn:hover{
          box-shadow:
    0 6px 12px rgba(0,0,0,.08),
    inset 0 0 0 2px currentColor;
    animation: outlinePulse 1s ease-in-out infinite;
    }
    @keyframes outlinePulse{
    0%,100%{ box-shadow: 0 6px 12px rgba(0,0,0,.08), inset 0 0 0 2px currentColor; }
    50%    { box-shadow: 0 10px 20px rgba(0,0,0,.14), inset 0 0 0 4px rgba(255,255,255,.6); }
    }




</style>
<section id="content" class="bg">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <section class="vbox">
        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head" style="margin-top: 10px;">
                <a href="{{@$site_code?route('webdefacement_website.index',['id'=>@$site_code]):route('webdefacement.index')}}"
                    class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                    @icon('solid/arrow-left')
                </a>
                @langapp('webdefacement') > {{@$webdefacement->name}}
            </div>

            <div class="d-none">
                <span>Status &nbsp;</span>
                <span id="status_val_webdefacement">
                    {!!@get_webdefacment_status(@$webdefacement->status_val,'color')!!}
                </span>
                &nbsp;
            </div>
            

            &nbsp;

        </header>

        <section class="scrollable wrapper">

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-globe-europe"></i> Analytics
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="toggle_ana" style="margin-left:5px;" class="btn btn-xs text-dark" onclick="collpase_chart('#wdfm-analytics','#toggle_ana')">
                                <i class="fas fa-minus-square"></i>Collapse
                            </button>
                        </div>
                    </div>
                </header>
                <div class="panel-body" id="wdfm-analytics">
                    <h3 class="text-center">Last Update : {{@$webdefacement->last_check}}</h3>

                    <!-- Circle -->

                    @php
          $chk = '';
          $data['all'] = [];
          try {
          $chk = $webdefacment_data_check;
          if(!empty($chk)){
          if ($chk->hash_percent > -1) {
          $data['all']['hash'] = [
          'name' => 'Hash',
          'value' => (float) $chk->hash_percent,
          ];
          }
          if ($chk->filesize_percent > -1) {
          $data['all']['filesize'] = [
          'name' => 'Filesize',
          'value' => (float) $chk->filesize_percent,
          ];
          }
          if ($chk->element_percent > -1) {
          $data['all']['element'] = [
          'name' => 'Element',
          'value' => (float) $chk->element_percent,
          ];
          }
          if ($chk->image_percent > -1) {
          $data['all']['image'] = [
          'name' => 'Image',
          'value' => (float) $chk->image_percent,
          ];
          }
          if ($chk->keyword_percent > -1) {
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

                    <div class="wrapper-circle">
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

                        if ($value >= 80) {
                        $color = 'danger';
                        } elseif ($value >= 50) {
                        $color = 'warning';
                        } else {
                        $color = 'green';
                        }
                        @endphp                                                                   
                        <div>
                            <div class="c100 p{{$value}} {{$color}}">
                                <span>{{$value}}%</span>
                                <div class="slice">
                                    <div class="bar"></div>
                                    <div class="fill"></div>
                                </div>
                            </div>
                            <h3 class="text-center text-dark font-weight-bold">{{$name}}</h3>
                        </div>
                    @endforeach
                    @endif
                        
                    </div>
                </div>
            </section>

            <section class="panel panel-default d-none">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-globe-europe"></i> Log
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="togglelog" style="margin-left:5px;" class="btn btn-xs text-dark" onclick="collpase_chart('#wdfm-log','#togglelog')">
                                <i class="fas fa-minus-square"></i>Collapse
                            </button>
                        </div>
                    </div>
                </header>
                <div class="panel-body" id="wdfm-log">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" style="margin-bottom:0 !important;">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Description</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Datetime</th>

                            </tr>

                            @if ($webdefacment_data_log)
                            @foreach ($webdefacment_data_log as $webdefacment_data_log)
                            <tr>
                                <td class="text-center">{!! @$loop->iteration !!}</td>
                                <td class="nowrap">
                                    @if ($webdefacment_data_log->message)
                                    {!!@$webdefacment_data_log->message!!}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($webdefacment_data_log->status_val)
                                    {!!@get_webdefacment_status(@$webdefacment_data_log->status_val,'color')!!}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="no-wrap">{{@$webdefacment_data_log->updated_at}}</td>
                            </tr>
                            @endforeach
                            @endif

                            <!-- ตัวอย่าง Log เอาที่อยู่ใน td >  <div class="main-card-log"> 
                            <tr>
                                <td class="text-center">1</td>
                                <td> 
                                    <div class="main-card-log">
                                        <div class="card-log">
                                            <div class="card-log-body">
                                                <p>Hash Difference 30%</p>
                                            </div>
                                        </div>
                                        <div class="card-log">
                                            <div class="card-log-body">
                                                <p>File Size Difference 30%</p>
                                            </div>
                                           
                                        </div>
                                        <div class="card-log">
                                            <div class="card-log-body">
                                                <p>Hash Difference 30%</p>
                                            </div>
                                        </div>
                                        <div class="card-log">
                                            <div class="card-log-body">
                                                <p>Element Difference 30%</p>
                                            </div>
                                        </div>
                                        
                                        <div class="card-log"  style="background: #fcc838 ">
                                            <div class="card-log-body">
                                                <p>Total Difference 30% (Medium)</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                   Medium
                                </td>
                                <td class="no-wrap">2021-01-17 09:47:38</td>
                            </tr>
                            -->


                        </table>
                    </div>
                </div>
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-globe-europe"></i> Web Defacement
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="togglecollapsechart" style="margin-left:5px;" class="btn btn-xs text-dark" onclick="collpase_chart('#details_webdefacement','#togglecollapsechart')">
                                <i class="fas fa-minus-square"></i>Collapse
                            </button>
                        </div>
                    </div>
                </header>
                <div class="panel-body" id="details_webdefacement">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" style="margin-bottom:0 !important;">
                            <tr>
                                <th width="250px">Name Page</th>
                                <td>{{@$webdefacement->name}}</td>
                            </tr>
                            <tr>
                                <th>URL</th>
                                <td>
                                    {{@$webdefacement->url}}
                                    <a href="{{@$webdefacement->url}}" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-link"></i> Link</a>
                                </td>

                            </tr>
                            <tr>
                                <th>Domain</th>
                                <td>{{@$webdefacement->domain}}</td>
                            </tr>
                            <tr>
                                <th>User Agent</th>
                                <td>
                                    {{@$webdefacement->user_agent}}

                                </td>
                            </tr>
                            <tr>
                                <th>Site</th>
                                <td>{{@$webdefacement->get_site->name}}</td>
                            </tr>
                            <tr>
                                <th>Create Date</th>
                                <td>{{@$webdefacement->created_at}}</td>
                            </tr>
                            <tr>
                                <th>Last Online</th>
                                <td>{{@$webdefacement->last_online}}</td>
                            </tr>
                            <tr>
                                <th>Last Check</th>
                                <td>{{@$webdefacement->last_check}}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id='defacement_status'>{!!@get_webdefacment_status(@$webdefacement->status_val,'color')!!}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="table-responsive" id='updateO'>
                        <table class="table table-striped table-bordered table-hover" style="margin-bottom:0 !important;">
                            <thead>
                                <tr>
                                    <th width="250px"></th>
                                    <th>Original</th>
                                    <th>Current</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(@$webdefacement->hash == 1)
                                <tr>
                                    <th>Hash</th>
                                    <td id='Hash'>{{@$webdefacment_data_original->hash}}</td>
                                    <td id='hash_new'><span>{{@$webdefacment_data_check->hash_new}}</span> <span class="pull-right text-info">(Difference {{@$webdefacment_data_check->hash_percent}}%)</span></td>
                                </tr>
                                @endif
                                @if(@$webdefacement->filesize == 1)
                                <tr>
                                    <th>File Size</th>
                                    <td id='FileSize'>{{@formatSizeUnits($webdefacment_data_original->filesize)}}</td>
                                    <td id='filesize_new'><span>{{@formatSizeUnits($webdefacment_data_check->filesize_new)}}</span> <span class="pull-right text-info">(Difference {{@$webdefacment_data_check->filesize_percent}}%)</span></td>
                                </tr>
                                @endif
                                @if(@$webdefacement->element == 1)
                                <tr>
                                    <th>Element</th>
                                    <td id='Element'>{{@$webdefacment_data_original->element}}</td>
                                    <td id='element_new'><span>{{@$webdefacment_data_check->element_new}}</span> <span class="pull-right text-info">(Difference {{@$webdefacment_data_check->element_percent}}%)</span></td>
                                </tr>
                                @endif
                                @if(@$webdefacement->blacklist_keyword_content == 1)
                                <tr>
                                    <th>Blacklist Keyword</th>
                                    <td id='BlacklistKeyword'>{{@$webdefacement->blacklist_keyword_content}}</td>
                                    <td id='blacklist_keyword_current'>{{@$webdefacement->blacklist_keyword_current}}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Last Update</th>
                                    <td id='LastUpdate'>{{@$webdefacment_data_original->last_update}}</td>
                                    <td id='last_update'>{{@$webdefacment_data_check->last_update}}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>&nbsp;</th>
                                    <td>
                                        <button class="btn btn-info" onclick="update_original()">Update Original</button>
                                    </td>
                                    <td>
                                        <button class="btn btn-info" onclick="deface_now()">Defacement Now</button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            @if(@$webdefacement->image_check == 1)
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-globe-europe"></i> Defacement Screen
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="togglecollapse" style="margin-left:5px;" class="btn btn-xs text-dark" onclick="collpase_chart('#Defacement','#togglecollapse')">
                                <i class="fas fa-minus-square"></i>Collapse
                            </button>
                        </div>
                    </div>
                </header>
                <div class="panel-body" id="Defacement">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="wdfm-container">
                                <div class="item-wdfm wdfm-inner half-two">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img" id='updateImage_original'>
                                                {{-- <a href="{{config('app.URL_CENTER_PUBLISH').@$webdefacement->image_original}}" data-lightbox="name-img-2">
                                                <img src="{{config('app.URL_CENTER_PUBLISH').@$webdefacement->image_original}}" onerror="setDefaultPic(this)" />
                                                </a> --}}
                                                <a href="https://insights.sosecure.co.th{{@$webdefacement->image_original}}" data-lightbox="name-img-2">
                                                    <img src="https://insights.sosecure.co.th{{@$webdefacement->image_original}}" onerror="setDefaultPic(this)" />
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            {{-- <div class="wdfm-btn">
                                                <a class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div> --}}
                                            <h4>{{@$webdefacement->name}}</h4>
                                            <p class="mdfm-text-muted">{{@$webdefacement->url}}</p>
                                        </div>
                                        <div class="wdfm-footer">
                                            <div class="wdfm-ft-left flex rw">
                                                <div><strong>Original</strong></div>
                                                <div><strong>Last Update</strong> : {{@$webdefacment_data_original->last_update}}</div>
                                                <div><button class="btn btn-info" onclick="update_image()">Update</button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-wdfm wdfm-inner half-two">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                {{-- <a href="{{config('app.URL_CENTER_PUBLISH').@$webdefacement->image_last}}" data-lightbox="name-img-2">
                                                <img src="{{config('app.URL_CENTER_PUBLISH').@$webdefacement->image_last}}" onerror="setDefaultPic(this)" />
                                                </a> --}}
                                                <a href="https://insights.sosecure.co.th{{@$webdefacement->image_last}}" data-lightbox="name-img-2">
                                                    <img src="https://insights.sosecure.co.th{{@$webdefacement->image_last}}" onerror="setDefaultPic(this)" />
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            {{-- <div class="wdfm-btn">
                                                <a href="{{route('webdefacement.detail',['code'=>$code])}}" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                            <i class="fas fa-eye"></i>
                                            </a>
                                        </div> --}}
                                        <h4>{{@$webdefacement->name}}</h4>
                                        <p class="mdfm-text-muted">{{@$webdefacement->url}}</p>
                                    </div>
                                    <div class="wdfm-footer">
                                        <div class="wdfm-ft-left flex rw">
                                            <div><strong>Current</strong></div>
                                            <div><strong>Last Update </strong>: {{@$webdefacment_data_check->last_update}}</div>
                                            <div class="status-flex"><strong>Status </strong>: &nbsp;{{@$webdefacment_data_check->status_code}}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </section>
            @endif


            <!-- <div id="load_status"></div> -->
            @if (@$webdefacement->status_val != 'Normal') 
                <div id="float-btns" class="section-bottom-bar">
                    <button class="btn btn-danger flex-btn" onclick="alert_to_customer(@json($webdefacement->id))">Alert To Customer</button>
                    <button class="btn btn-info flex-btn" id="btn-accept_risk" onclick="accept_risk()">Accept Risk </button>
                </div>
            @endif
            


            <!-- Old Code -->
             <!-- <div class="pull-right" style="margin-top: 10px" id='load_status'> -->
                <!-- <span id="check_status_val_webdefacement">
                    @if (@$webdefacement->status_val != 'Normal')
                    <a href="#" id="accept_risk" onclick="accept_risk()"
                        class="btn btn-{{ get_option('theme_color') }}  btn-responsive">
                        Accept Risk
                    </a>
                </span>
                @endif -->
            <!-- </div> -->

            <!-- End Old Code -->

        </section>
    </section>

</section>




@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.lightbox')
@include('stacks.css.c3')
@include('stacks.css.datepicker')
@include('stacks.css.form')
@include('stacks.css.circle_chart')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.lightbox')
@include('stacks.js.c3')

<script>
    function collpase_chart(id,text){
        $(id).slideToggle();
        if($(text).text() == 'Expanded'){
            $(text).html('<i class="fas fa-minus-square"></i>Collapse');
        }else{
            $(text).html('<i class="fas fa-plus-square"></i>Expanded');
        }
    }

    $(document).ready(function(){
        $('.wdfm-card').hover(function(){
            $(this).find('.wdfm-header').toggleClass('wdfm-header-upper');
        }); 
    });

    chart_circle('#chart_01');

    function chart_circle(id){
        const myc3 = c3.generate({
            bindto: id,
            data: {
                columns: [
                    ['data', 91.4]
                ],
                type: 'gauge',
            },
            gauge: {
               label: {
                   format: function(value, ratio) {
                       return value;
                   },
                   show: false 
               },
           min: 0, 
           max: 360,
           units: ' %',
           width: 39
            },
            color: {
                pattern: ['#FF0000', '#F97600', '#F6C600', '#60B044'], 
                threshold: {
                   unit: 'value',
                   max: 200,
                    values: [30, 60, 90, 100]
                }
            },
            size: {
                height: 180
            }
        });
    }


    function accept_risk() { 
     
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (!result.isConfirmed) return;

                const openLoader = () => Swal.fire({
                    title: 'Status Changing...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    heightAuto: false,
                    didOpen: () => Swal.showLoading()
                });
                const stopLoader = () => {
                    if (Swal.isLoading()) Swal.hideLoading();
                    if (Swal.isVisible()) Swal.close();
                };

                 openLoader();
                $.ajax({
                    type:"POST",
                    url:"{{ route('webdefacement.change_status') }}",
                    data:{id: {!!json_encode(@$webdefacement->id)!!}},
                    beforeSend: function(){
                        $('#load_status').loading('start');
                    },
                    success:function(response) {
                 
                        

                        $('#status_val_webdefacement').html('{!!@get_webdefacment_status('Normal','color')!!}');
                        $('#defacement_status').html('{!!@get_webdefacment_status('Normal','color')!!}');
                        $('#check_status_val_webdefacement').html(response.html);
               
                        $('#load_status').loading('stop');
                        toastr.success(response.message, '@langapp('response_status')');
                        stopLoader();
                        {{--window.location.href = response.redirect;--}}
                    },
                    error: function (error){
                        $('#load_status').loading('stop');
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function (key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        stopLoader();
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                    }
      
                });
                     
        })
    };

    function deface_now(){
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type:"POST",
                    url:"{{ route('webdefacement.deface_now') }}",
                    data:{id: {!!json_encode(@$webdefacement->id)!!}},
                    beforeSend: function(){
                        $('#updateO').loading('start');
                        
                    },
                    success:function(response) {
                        $.ajax({
                            type:"POST",
                            url:"{{ route('webdefacement.deface_now_detail') }}",
                            data:{id: {!!json_encode(@$webdefacement->id)!!}},
                            beforeSend: function(){
                                
                            },
                            success:function(response) {
                        
                                $('#hash_new').html(response.html_h);
                                $('#filesize_new').html(response.html_f);
                                $('#element_new').html(response.html_e);
                                $('#last_update').html(response.html_l);
                                $('#blacklist_keyword_current').html(response.html_b);
                                $('#updateO').loading('stop');
                               
                                if(response.success){
                                    toastr.success('Update Success', '@langapp('response_status')');
                                } else {
                                    toastr.error(data.message, '@langapp('response_status')');
                                }
                            },
                            error: function (error){
                                $('#updateO').loading('stop');
                                var errors = error.response.data.errors;
                                var errorsHtml = '';
                                $.each(errors, function (key, value) {
                                    errorsHtml += '<li>' + value[0] + '</li>';
                                });
                                toastr.error(errorsHtml, '@langapp('response_status') ');
                            }
            
                        });
                    },
                    error: function (error){
                        $('#updateO').loading('stop');
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function (key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                    }
    
                });

            }   
        })
    }

    function update_original() { 
     
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type:"POST",
                    url:"{{ route('webdefacement.update_original') }}",
                    data:{id: {!!json_encode(@$webdefacement->id)!!}},
                    beforeSend: function(){
                        $('#updateO').loading('start');
                        
                    },
                    success:function(response) {
                        $.ajax({
                            type:"POST",
                            url:"{{ route('webdefacement.update_original_detail') }}",
                            data:{id: {!!json_encode(@$webdefacement->id)!!}},
                            beforeSend: function(){
                                
                            },
                            success:function(response) {
                        
                                $('#Hash').html(response.html_h);
                                $('#FileSize').html(response.html_f);
                                $('#Element').html(response.html_e);
                                $('#BlacklistKeyword').html(response.html_b);
                                $('#LastUpdate').html(response.html_l);
                                $('#updateO').loading('stop');
                                {{--window.location.href = response.redirect;--}}
                            },
                            error: function (error){
                                $('#updateO').loading('stop');
                                var errors = error.response.data.errors;
                                var errorsHtml = '';
                                $.each(errors, function (key, value) {
                                    errorsHtml += '<li>' + value[0] + '</li>';
                                });
                                toastr.error(errorsHtml, '@langapp('response_status') ');
                            }
            
                        });


                    
              
                        let data = JSON.parse(response);
        
                        if(data.Result==1){
                            toastr.success('Update Success', '@langapp('response_status')');
                        } else {
                            toastr.error(data.message, '@langapp('response_status')');
                        }
                        
                        {{--window.location.href = response.redirect;--}}
                    },
                    error: function (error){
                        $('#updateO').loading('stop');
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function (key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                    }
    
                });

            }   
        })
    };



        function update_image() { 
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type:"POST",
                    url:"{{ route('webdefacement.update_image') }}",
                    data:{id: {!!json_encode(@$webdefacement->id)!!}},
                    beforeSend: function(){
                        $('#Defacement').loading('start');
                    },
                    success:function(response) {

                        let data = JSON.parse(response);

                        $('#updateImage_original').html(`<a href="${base_url}/${data.image_url}" data-lightbox="name-img-2"><img src="${base_url}/${data.image_url}" onerror="setDefaultPic(this)"/>a>`);
                
                            $('#Defacement').loading('stop');

                        
                    
                
                        if(data.Result==1){
                            toastr.success('Update Success', '@langapp('response_status')');
                        } else {
                            toastr.error(data.message, '@langapp('response_status')');
                        }
                        {{--window.location.href = response.redirect;--}}
                    },
                    error: function (error){
                        ('#updateImage_original').loading('stop');
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function (key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                    }
    
                });

            }   
        })
    };


        $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });

        function alert_to_customer(id) {
            let domain = '{{@$webdefacement->domain}}';
            let namepage = '{{@$webdefacement->name}}';
            let site = '{{@$webdefacement->get_site->name}}';
        Swal.fire({
            title: 'Are you sure To send this Alert?',
            html: `
                    <table class="table table-bordered table-striped" style="border: none">
                    <tr>
                        <td colspan="2">
                            <p class="text-message-warning">Infomation</p> 
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="text-description">Domain: </p>
                        </td>
                        <td>
                            <p style="text-align: left">${domain}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="text-description">Namepage:</p>
                        </td>
                        <td>
                            <p style="text-align: left"> ${namepage}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p class="text-description">Site:</p>
                        </td>
                        <td>
                            <p style="text-align: left"> ${site}</p>
                        </td>
                    </tr>
                    </table>
                     <p class="text-message-warning">Alert will be sent to the customer.</p>
                    `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes',
            width: '500px',
            
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Alert is Sending...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                heightAuto: false,
                didOpen: () => {
                Swal.showLoading();
                }
            });

            const $btn = $('#btn-alert-customer');
            $btn.prop('disabled', true).addClass('disabled');

            $.ajax({
            type: 'POST',
            url: "{{ route('webdefacement.alert_to_customer') }}",
            data: { id: id },          
            dataType: 'json',
            beforeSend: function() {

            },
            success: function(res) {
                if (res && res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Alert sent',
                    text: res.message || 'Customer has been notified.',
                    heightAuto: false
                });
                } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: (res && res.message) ? res.message : 'Cannot alert customer.',
                    heightAuto: false
                });
                }
            },
            error: function(xhr) {
                const msg =
                (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) ||
                xhr.statusText || 'Unexpected error';
                const errs = (xhr.responseJSON && xhr.responseJSON.errors)
                ? Object.values(xhr.responseJSON.errors).flat().join('\n')
                : '';
                const time = xhr.responseJSON && xhr.responseJSON.retryAt ? xhr.responseJSON.retryAt : '';
                Swal.fire({
                icon: 'error',
                title: 'Already sent to customer',
                html: `
                        <p class="text-message">${msg}</p>
                        ${errs ? `<p>${errs}</p>` : ''}
                        ${time ? `<p style="font-size: 14px;font-weight: bold;font-color: #ff0000df">${time}</p>` : ''}
                    `,
                heightAuto: false,
                width: 600,

                color: '#e2e8f0',              
                iconColor: '#ef4444',          
                backdrop: 'rgba(0,0,0,.4)',    

                cancelButtonText: 'Close',
                });
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('disabled');
                Swal.hideLoading();

            }
            });
        });
        }





</script>

<script>    

    (function() {
        const bar = document.getElementById('float-btns');
        const section = document.getElementById('content');
        const sentinel = document.getElementById('end-sentinel');
        if (!bar || !section || !sentinel) return;

        function layoutBar() {

            const srect = section.getBoundingClientRect();
            bar.style.left = srect.left + 'px';
            bar.style.width = srect.width + 'px';

            const r = sentinel.getBoundingClientRect();
            const overlap = Math.max(0, window.innerHeight - r.top);
            bar.style.transform = `translateY(-${overlap}px)`;
        }

        window.addEventListener('scroll', layoutBar, {
            passive: true
        });
        window.addEventListener('resize', layoutBar);

        if ('ResizeObserver' in window) {
            const ro = new ResizeObserver(layoutBar);
            ro.observe(section);
        }

        layoutBar();
    })();
</script>
@endpush
@endsection