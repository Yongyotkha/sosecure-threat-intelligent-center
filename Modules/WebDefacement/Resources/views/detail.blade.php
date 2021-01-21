@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
        <div class="bc-head" style="margin-top: 10px;max-width: 265px !important;">
            <a href="{{route('webdefacement.index')}}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                @icon('solid/arrow-left')
            </a>
            @langapp('webdefacement') > {{@$webdefacement->name}}
        </div>

        <div class="pull-right" style="margin-top: 10px" id='load_status'>
            <span>Status &nbsp;</span>
            <span id="status_val_webdefacement">
            {!!@get_webdefacment_status(@$webdefacement->status_val,'color')!!}
            </span>
            &nbsp;
            <span id="check_status_val_webdefacement">
            @if (@$webdefacement->status_val != 'Normal')   
                <a href="#" id="accept_risk" onclick="accept_risk()"
                    class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive">
                    Accept Risk
                </a>
            </span>    
            @endif
        </div>

        &nbsp;

    </header>

        <section class="scrollable wrapper">
            <section class="panel panel-default">
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
                                <td >{{@$webdefacement->domain}}</td>
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
                                    <td id='hash_new'>{{@$webdefacment_data_check->hash_new}} (Difference {{@$webdefacment_data_check->hash_percent}}%)</td>
                                </tr>
                                @endif
                                @if(@$webdefacement->filesize == 1)
                                <tr>
                                    <th>File Size</th>
                                    <td id='FileSize'>{{@formatSizeUnits($webdefacment_data_original->filesize)}}</td>
                                    <td id='filesize_new'>{{@formatSizeUnits($webdefacment_data_check->filesize_new)}} (Difference {{@$webdefacment_data_check->filesize_percent}}%)</td>
                                </tr>
                                @endif
                                @if(@$webdefacement->element == 1)
                                <tr>
                                    <th>Element</th>
                                    <td id='Element'>{{@$webdefacment_data_original->element}}</td>
                                    <td id='element_new'>{{@$webdefacment_data_check->element_new}} (Difference {{@$webdefacment_data_check->element_percent}}%)</td>
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
                                            <div class="wdfm-img" id='updateImage_original'>
                                                <a href="{{@$webdefacement->image_original}}" data-lightbox="name-img-2">
                                                    <img src="{{@$webdefacement->image_original}}" onerror="setDefaultPic(this)"/>
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
                                                <div><button class="btn btn-info" onclick="update_image()">Update</button></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-wdfm wdfm-inner half-two">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="{{@$webdefacement->image_last}}" data-lightbox="name-img-2">
                                                    <img src="{{@$webdefacement->image_last}}" onerror="setDefaultPic(this)"/>
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
            @endif




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
            if (result.isConfirmed) {
                $.ajax({
                    type:"POST",
                    url:"{{ route('webdefacement.change_status') }}",
                    data:{id: {!!json_encode($webdefacement->id)!!}},
                    beforeSend: function(){
                        $('#load_status').loading('start');
                    },
                    success:function(response) {
                 
                        

                        $('#status_val_webdefacement').html('{!!@get_webdefacment_status('Normal','color')!!}');
                        $('#defacement_status').html('{!!@get_webdefacment_status('Normal','color')!!}');
                        $('#check_status_val_webdefacement').html(response.html);
               
                        $('#load_status').loading('stop');
                        toastr.success(response.message, '@langapp('response_status')');
                        {{--window.location.href = response.redirect;--}}
                    },
                    error: function (error){
                        $('#load_status').loading('stop');
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
                    data:{id: {!!json_encode($webdefacement->id)!!}},
                    beforeSend: function(){
                        $('#updateO').loading('start');
                        
                    },
                    success:function(response) {
                        $.ajax({
                            type:"POST",
                            url:"{{ route('webdefacement.deface_now_detail') }}",
                            data:{id: {!!json_encode($webdefacement->id)!!}},
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
                    data:{id: {!!json_encode($webdefacement->id)!!}},
                    beforeSend: function(){
                        $('#updateO').loading('start');
                        
                    },
                    success:function(response) {
                        $.ajax({
                            type:"POST",
                            url:"{{ route('webdefacement.update_original_detail') }}",
                            data:{id: {!!json_encode($webdefacement->id)!!}},
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
                 data:{id: {!!json_encode($webdefacement->id)!!}},
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
</script>
@endpush
@endsection
