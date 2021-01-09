@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}
        <header class="header bg-white b-b b-light head-d-flex-nowrap"
        style="white-space: nowrap;overflow-x: auto;">
        <div class="bc-head m-none">
            <a href="{{route('webdefacement.index')}}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                @icon('solid/arrow-left')
            </a>
            @langapp('webdefacement') > {{@$webdefacement->name}}
        </div>

        &nbsp;

    </header>

        <section class="scrollable wrapper">
            
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-globe-europe"></i> Web Defacement
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="togglecollapsechart" style="margin-left:5px;" class="btn text-dark" onclick="collpase_chart('#details_webdefacement','#togglecollapsechart')">
                                <i class="fas fa-minus-square"></i>Collapse
                            </button>
                        </div>
                    </div>
                </header>
                <div class="panel-body" id="details_webdefacement">
                    <table class="table table-striped table-bordered table-hover" style="margin-bottom:0 !important;">
                        <tr>
                            <th width="250px">Name Page</th>
                            <td>{{@$webdefacement->name}}</td>
                        </tr>
                        <tr>
                            <th>URL</th>
                            <td>
                                {{@$webdefacement->url}}
                                <a href="{{@$webdefacement->url}}" class="btn btn-info btn-xs"><i class="fas fa-link"></i> Link</a>

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
                            <td> {!!@get_webdefacment_status(@$webdefacement->status_val,'color')!!}</td>
                        </tr>
                    </table>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th width="250px"></th>
                                <th>Original</th>
                                <th>Current</th>
                            </tr>
                        </thead>
                        <tbody>                  
                            <tr>
                                <th>Hash</th>
                                <td>{{@$webdefacment_data_original->hash}}</td>
                                <td>{{@$webdefacment_data_check->hash_new}}</td>
                            </tr>
                            <tr>
                                <th>File Size</th>
                                <td>{{@$webdefacment_data_original->filesize}} KB</td>
                                <td>{{@$webdefacment_data_check->filesize_new}}</td>
                            </tr>
                            <tr>
                                <th>Element</th>
                                <td>{{@$webdefacment_data_original->element}} tag</td>
                                <td>{{@$webdefacment_data_check->element_new}}</td>
                            </tr>
                            <tr>
                                <th>&nbsp;</th>
                                <td>{{@$webdefacment_data_original->last_update}}</td>
                                <td>{{@$webdefacment_data_check->last_update}}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>&nbsp;</th>
                                <td colspan="2">
                                    <button class="btn btn-info">Update Original</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-globe-europe"></i> Defacement Screen
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="togglecollapse" style="margin-left:5px;" class="btn text-dark" onclick="collpase_chart('#Defacement','#togglecollapse')">
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
                                            <div class="wdfm-img">
                                                <a href="{{asset(@$webdefacment_data_original->image)}}" data-lightbox="name-img-2">
                                                    <div class="wdfm-logo" style="background-image:url({{asset(@$webdefacment_data_original->image)}})"></div>
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
                                                <div>Original</div>
                                                <div>Last Update : {{@$webdefacment_data_original->last_update}}</div>
                                                <div><button class="btn btn-info">Update</button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-wdfm wdfm-inner half-two">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="{{asset(@$webdefacment_data_original->image_new)}}" data-lightbox="name-img-2">
                                                    <div class="wdfm-logo" style="background-image:url({{asset(@$webdefacment_data_original->image_new)}})"></div>
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
                                                <div>Current</div>
                                                <div>Last Update : {{@$webdefacment_data_check->last_update}}</div>
                                                <div class="status-flex">Status : &nbsp;{{@$webdefacment_data_check->status_code}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-globe-europe"></i> Log
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="togglelog" style="margin-left:5px;" class="btn text-dark" onclick="collpase_chart('#wdfm-log','#togglelog')">
                                <i class="fas fa-minus-square"></i>Collapse
                            </button>
                        </div>
                    </div>
                </header>
                <div class="panel-body" id="wdfm-log">
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
                                    <td >{{@$webdefacment_data_log->message}}</td>
                                    <td class="text-center">{!!@get_webdefacment_status(@$webdefacment_data_log->status_val,'color')!!}</td>
                                    <td class="no-wrap">{{@$webdefacment_data_log->updated_date}}</td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                </div>
            </section>


          </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
    @include('stacks.css.lightbox')

    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.lightbox')

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
</script>
@endpush
@endsection