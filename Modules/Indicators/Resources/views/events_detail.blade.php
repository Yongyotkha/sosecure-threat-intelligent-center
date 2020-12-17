@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <a href="{{ route('indicators.events') }}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">Events > SSH-US...</div>

            <!--<button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
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
            </div>-->

        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <div class="panel-heading">
                    <a class="text-primary" href="{{ route('indicators.events') }}">Events</a> 
                    | 
                    <a href="{{ route('indicators.attributes') }}" class="text-muted">Attributes</a>
                </div>
                <div class="container-fluid" style="padding: 2rem">
                    <div class="row">
                        <div class="col-md-6">
                            <h1>SSH - US Honeypot loCs 2020-12-04</h1>
                            <p>Last Status : Modified | Public : <i class="fas fa-check"></i></p>
                            <p>Created : 2020-12-12 11:12 | Modified : 2020-12-11:12</p>
                            <p>Daily SSH brutefoce logs from a honeypot in the US on a/32</p>
                            <p>Tags : <a href="">honeypot</a>,<a href="">ssh</a>,<a href="">cowrie</a></p>
                            <p>Groups : <a href="">honeypot</a>,<a href="">ssh</a>,<a href="">cowrie</a></p>
                        </div>
                        <div class="col-md-6">
                            <h1 class="text-center">Type Attributes 5 (5210)</h1>
                            <div id="chart-show-bar"></div>
                        </div>
                    </div>
                </div>        
            </section>


            <div class="tabbable">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="active"><a href="#tab_attributes" data-toggle="tab">Attributes (442)</a></li>
                    <li id="tab-bookmark"><a href="#tab_related_event" data-toggle="tab">Related Event (905)</a></li>   
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_attributes">
                        <section class="panel panel-default">
                            <div id="main-list" class="row m-b-md">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table-attributes-template">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <label>
                                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </th>
                                                    <th>TYPE</th>
                                                    <th>Attribute Name</th>
                                                    <th>ROLE</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>   
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <label>
                                                            <input value="" type="checkbox" />
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </td>
                                                    <td>FileHash-SHA256</td>
                                                    <td><a href="">Lorem ipsum dolor sit amet.Lorem ipsum dolor sit amet.</a></td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        <a href="" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                                                    </td>
                                                </tr>    
                                            </tbody> 
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane" id="tab_related_event">
                        <section class="panel panel-default">
                            <div class="row m-b-md">
                                <div class="col-sm-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table-related-event">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <label>
                                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </th>
                                                    <th>TYPE</th>
                                                    <th>Attribute Name</th>
                                                    <th>ROLE</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>   
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <label>
                                                            <input value="" type="checkbox" />
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </td>
                                                    <td>FileHash-SHA256</td>
                                                    <td><a href="">Lorem ipsum dolor sit amet.Lorem ipsum dolor sit amet.</a></td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        <a href="" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                                                    </td>
                                                </tr>    
                                            </tbody> 
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </section>  
                    </div>
                </div>
            </div>

            {{-- <section class="panel panel-default">
                <div class="table-responsive">
                    <table class="table table-striped" id="table-events-template">
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
                            <tr>
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
                                    <a href="" target="_blank" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                                </td>
                            </tr>    
                        </tbody> 
                    </table>
                </div>
            </section> --}}

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
@include('stacks.js.chart')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.advanced_search')

<script>
    $('.select2-option').select2();

    $('#table-attributes-template').DataTable();
    $('#table-related-event').DataTable();


    const chart = new frappe.Chart("#chart-show-bar", { 
        title: "",
        data:{
            labels: ["IPv4", "URL" , "FileHash-SHA256",],
            datasets: [
                { values: [254, 2, 185] }
            ]
        },
        type: 'percentage',
        colors: ['#743ee2']
    });

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
</script>

@endpush
@endsection