@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">@langapp('rss_feed_settings') | www.xxx.xxx/xxx.xxx.rss</div>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                @icon('solid/download') CSV
            </a>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                <span>@icon('solid/trash-alt') @langapp('delete_all')</span>
            </button>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                <span>@icon('solid/trash-alt') @langapp('delete')</span>
            </button>

            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#create-news">
                @icon('solid/plus') @langapp('create') News
            </a>
        </header>
    </section>

    <section class="scrollable wrapper bg-grey">
        <section class="panel panel-default">
            <div class="container-fluid" style="padding: 2rem;">
                <div class="row m-b-md">
                    
                    <div class="col-lg-3">
                        <label for="">Keywords</label>
                        <select name="" id="keywords" class="select2-option form-control" multiple="multiple">
                            <option value="1">a</option>
                            <option value="2">b</option>
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label for="">Start Date</label>
                        <div class="input-group date">
                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                            <div class="input-group-addon">
                                @icon('solid/calendar-alt', 'text-muted')
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="">End Date</label>
                        <div class="input-group date">
                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="end_date"
                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                            <div class="input-group-addon">
                                @icon('solid/calendar-alt', 'text-muted')
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <label for="">Status</label>
                        <section id="select_news" class="select2-option form-control">
                            <option value="1" selected>All</option>
                        </section>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 text-right">
                        <button class="btn btn-info btn-responsive">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        <button class="btn btn-default btn-responsive" style="white-space: nowrap">
                            <i class="fas fa-broom"></i>
                            <span> Clear </span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <div class="tabbable">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="active"><a href="#tab_data_feed" data-toggle="tab">Data Feed (20)</a></li>
                <li><a href="#tab_data_error" data-toggle="tab">Data Error (42)</a></li>   
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_data_feed">
                    <section class="panel panel-default border-n">
                        <div class="table-responsive">
                            <table  class="table table-striped" id="table-dtfeed-template">
                                <thead>
                                    <tr>
                                        <th class="hide"></th>
                                        <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>Title</th>
                                        <th>Link</th>
                                        <th>Update</th>
                                        <th>Feed Date</th>
                                        <th>Status</th>
                                        <th>News</th>
                                        <th>Action</th>
                                        <th class="no-sort"></th>
                                    </tr>
                                </thead>
                                <tbody>
        
                                </tbody>
                            </table>   
                        </div>
                    </section>
                </div>
                <div class="tab-pane" id="tab_data_error">
                    <section class="panel panel-default">
                        <div class="table-responsive">
                            <table  class="table table-striped" id="table-dtfeed-error-template">
                                <thead>
                                    <tr>
                                        <th class="hide"></th>
                                        <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>Title</th>
                                        <th>Link</th>
                                        <th>Update</th>
                                        <th>Feed Date</th>
                                        <th>Status</th>
                                        <th>News</th>
                                        <th>Action</th>
                                        <th class="no-sort"></th>
                                    </tr>
                                </thead>
                                <tbody>
        
                                </tbody>
                            </table>   
                        </div>
                    </section>  
                </div>
            </div>
        </div>
    </section>
    
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    
     <!-- Modal RSS -->
    <div class="modal modal-slide size-50" id="create-news" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <span class="modal-title" id="exampleModalLabel">News</span>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             <form action="">
                 <div class="modal-body">
                     <div class="container-fluid">
                         <div class="form-group row">
                             <div class="col-md-4">
                                <h5>Data Source : cshub</h5>
                                <h5>Update : 2020-31-04 18:00:05</h5>
                             </div>
                             <div class="col-md-8">
                                 <h5>Name : Lorem ipsum dolor sit amet.</h5>
                                 <h5>URL : <a href="">www.example.com</a> 
                                    &nbsp;<button type="submit" class="btn btn-xs btn-info">Open</button>
                                    <button type="submit" class="btn btn-xs btn-info">Copy</button>
                                </h5>
                             </div>
                             <div class="col-xs-12">
                                 <hr>
                             </div>
                         </div>

                         <div class="row">
                            <div class="col-md-12">
                                <h5>Create News</h5>
                            </div>
                         </div>

                         <div class="line"></div>
                         <div class="row m-b-sm">
                             <div class="col-md-4"></div>
                             <div class="col-md-8 offset-md-4">
                                <button class="btn btn-default active">TH</button>
                                <button class="btn btn-default">EN</button>
                             </div>
                         </div>

                         <div class="form-group row">
                             <div class="col-md-4 text-right">
                                <h5>Categorys</h5>
                             </div>
                             <div class="col-md-8">
                                <select name="" id="category" class="select2-option form-control" multiple="multiple">
                                    <option value="1">a</option>
                                    <option value="2">b</option>
                                </select>
                             </div>
                         </div>
                         <div class="line"></div>

                         <div class="form-group row">
                             <div class="col-md-12">
                                <div class="tabbable">
                                    <ul class="nav nav-tabs nav-tabs-highlight">
                                        <li class="active"><a href="#tab_th" data-toggle="tab">TH</a></li>
                                        <li><a href="#tab_en" data-toggle="tab">EN</a></li>   
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_th">
                                            <section class="panel-body border-n">
                                                <form action="" class="bs-example form-horizontal ajaxifyForm validator">
                                                    <div class="form-group row">
                                                        <label for="" class="col-lg-12 control-label">Text <span class="text-danger">*</span></label>
                                                        <div class="col-lg-12">
                                                            <input type="text" class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <label for="" class="col-lg-12 control-label">Detail <span class="text-danger">*</span></label>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control markdownEditor" name="message" id="news-editor" data-id="1" required></textarea>
                                                        </div>
                                                    </div>
                                                </form>
                                            </section>
                                        </div>
                                        <div class="tab-pane" id="tab_en">
                                            <section class="panel panel-default">
                                               
                                            </section>  
                                        </div>
                                    </div>
                                </div>
                             </div>
                         </div>
                         <div class="line"></div>
                         <div class="form-group row">
                            <div class="col-md-4 text-right">
                               <h5>Reference</h5>
                            </div>
                            <div class="col-md-8">
                               <select name="" class="select2 select2-option form-control" multiple="multiple">
                                   <option value="1">a</option>
                                   <option value="2">b</option>
                               </select>
                            </div>
                        </div>
                         <div class="form-group row">
                            <div class="col-md-4 text-right">
                               <h5>Tags</h5>
                            </div>
                            <div class="col-md-8">
                               <select name="" class="select2 select2-option form-control" multiple="multiple">
                                   <option value="1">a</option>
                                   <option value="2">b</option>
                               </select>
                            </div>
                        </div>

                        <div class="line"></div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Send Email</h5>
                            </div>
                         </div>

                        <div class="form-group row">
                            <div class="col-md-4 text-right">Sites</div>
                            <div class="col-md-8">
                                <div class="checkbox" style="margin:0;">
                                    <label style="padding-left: 0;margin:0;">
                                        <input id="site" type="checkbox" name="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title=""></span>
                                    </label>
                                    <span class="view_site" style="cursor: pointer">View Site</span>
                                </div>
                            </div>
                        </div>
                        <div class="show_site form-group row">
                            <div class="col-md-4 text-right">Show Site</div>
                            <div class="col-md-8">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Name</th>
                                        </tr>
                                    </thead>
                                    <tr>
                                        <td>1</td>
                                        <td>Site 01</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4 text-right">Other</div>
                            <div class="col-md-8">
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <div class="line"></div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Public</h5>
                            </div>
                         </div>

                        <div class="form-group row">
                            <label class="col-lg-4 control-label text-right">Start</label>
                            <div class="col-lg-8">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                            <div class="input-group-addon">
                                                @icon('solid/calendar-alt', 'text-muted')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="checkbox">
                                            <label style="padding-left: 0;">
                                                <input id="set_exp" type="checkbox" name="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Set an expire date</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="show_end_exp_date" class="form-group row">
                            <label class="col-lg-4 control-label text-right">End</label>
                            <div class="col-lg-8">
                                <div class="input-group date">
                                    <input id="send_date" type="text" class="form-control datetimepicker-input"
                                    value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                    data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                    <div class="input-group-addon">
                                        @icon('solid/calendar-alt', 'text-muted')
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-4 control-label text-right">Status </label>
                            <div class="col-md-8">
                                <label class="switch">
                                    <input type="hidden" value="FALSE" name="">
                                    <input type="checkbox" name="" value="TRUE">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                     </div>
                 </div>

                 <div class="modal-footer">
                     <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
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
 </div>

</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.markdown')

<script>
$(function() {

    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

    $('#show_end_exp_date').hide();

    $('#set_exp').on('change',function(){
        if($(this).prop('checked')){
            $('#show_end_exp_date').show();
        }else{
            $('#show_end_exp_date').hide();
        }
    });
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('#category').select2({
            tags: true,
            tokenSeparators: [' ']
        });
        
        $('.select2').select2();

        $('.show_site').hide();
        $('.view_site').on('click',function(){
            $('.show_site').show();
        });
    });

    $('#table-dtfeed-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
    $('#table-dtfeed-error-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection