@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <div class="bc-head">Events</div>

            <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span>@langapp('Search_Advance')</span>
            </button>
            <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                    <option value="">All Site</option>
                    {{-- @if($SiteSettings)
                    @foreach($SiteSettings as $SiteSettings_val)
                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                    @endforeach
                    @endif --}}
                </select>
            </div>

        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <div class="panel-heading">
                    <a class="text-primary" href="{{ route('indicators.events') }}">Events</a>
                    |
                    <a href="{{ route('indicators.attributes') }}" class="text-muted">Attributes</a>
                </div>
                <div id="hide-advance-search" class="container-fluid" style="padding: 2rem">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group m-b-md">
                                <label for="" class="">Event Name</label>
                                <input type="text" class="form-control" name="event_name" id="event_name"
                                    placeholder="Search">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="">Group</label>
                                <select name="group[]" id="type" class="select2-option form-control"
                                    multiple="multiple">

                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="">Tag</label>
                                <select name="tag[]" id="tag" class="select2-option form-control" multiple="multiple">

                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="" class="">Date</label>
                            <div id="event_date" class="text-center"
                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <div class="col-md-12 text-right">
                            <button class="btn btn-info" id="search_data">
                                <i class="fas fa-search"></i>
                                <span> Search </span>
                            </button>
                            <button class="btn btn-default" id="clear_data">
                                <i class=" fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel panel-default">
                <div class="table-responsive">
                    <table class="table table-striped" id="table_events">
                        <thead>
                            <tr>
                                <th>
                                    <label>
                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </th>
                                <th>No</th>
                                <th>Event Name</th>
                                <th>Group</th>
                                <th>Tags</th>
                                <th>Attr</th>
                                <th>Published</th>
                                <th>Last Status</th>
                                <th>DateTime</th>
                                <th>View</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <tr>
                                <td>
                                    <label>
                                        <input value="" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </td>
                                <td>1</td>
                                <td>Suspicious proxy agent</td>
                                <td>
                                    <a href="">MIST FEED</a>
                                    <a href="">Phishing,UW</a>
                                </td>
                                <td>
                                    <a href="">Scan,Agent,</a>
                                    <a href="">Proxy,Spider</a>
                                </td>
                                <td>
                                    <a href="">5421</a>
                                </td>
                                <td>
                                    <i class="fas fa-check"></i>
                                </td>
                                <td>
                                    Modified
                                </td>
                                <td>
                                    2020-12-07 11:11
                                </td>
                                <td>
                                    152
                                </td>
                                <td>
                                    <a href="{{ route('indicators.events_detail') }}" class="btn btn-xs btn-info"><i
                                class="far fa-eye"></i> View</a>
                            </td>
                            </tr> --}}
                        </tbody>
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
@include('stacks.css.datepicker')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.advanced_search')
<script>
    $('.select2-option').select2();



  $(function() {
  
        var start = moment().startOf('hour');
        var end = moment().startOf('hour').add(32, 'hour');

        function cb(start, end) {
            $('#event_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            startDate = start;
            endDate = end;
        }

        $('#event_date').daterangepicker({
            timePicker: true,
            startDate: start,
            endDate: end,
            locale: {
                format: 'M/DD hh:mm A'
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

        cb(start, end);
    });

    $(function() {
        load_table(1);
    });


    function load_table(page=1){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('indicators.events_table')!!}',
            type: "post",
            data:({
                page : page
            }),
            beforeSend: function(){
                loading('load');
            },
        }).done(function(data){
	    loading('stop_load');
            
            {{--$('#count_news').text(data.count);--}}
            $("#table_events").html(data.html);
        }).fail(function(jqXHR, ajaxOptions, thrownError){
	    loading('stop_load');
            console.log("No response from server");
        });
    }



    
   
    

</script>

@endpush
@endsection