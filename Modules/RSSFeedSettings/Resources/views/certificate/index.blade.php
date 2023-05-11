@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a href="{{route('news.index')}}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                        @icon('solid/arrow-left')
                    </a>
                    <div class="bc-head">Certificate</div>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#cert_modal_create">
                        @icon('solid/plus') @langapp('create')
                    </a>
                </header>

                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Certificate
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-rss-setting-template">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Name</th>
                                            <th>Rule Site</th>
                                            <th>Download</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <!-- Modal Create -->
    <div class="modal in fixed-left" id="cert_modal_create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                        title="Fullscreen" data-placement="right"></i>
                        Certificate Create
                    </h4>
                </div>
                {!! Form::open(['route' => ['certificate.save'], 'class' => 'ajaxifyForm_custom','files' => false]) !!}
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" id="cert_name" name="cert_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">SSL Certificate
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-12 mb-1">
                                    <input type="file" name="cert_file_ssl" id="cert_file_ssl" class="form-control" accept="">
                                    <span id="error_file" style="color:red;"></span>
                                </div>
                                <div class="col-lg-12">
                                    <span style="color:red;">รองรับเฉพาะไฟล์ ca.crt เท่านั้น</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row d-none">
                        <label class="col-lg-3 control-label">SSL Certificate Key
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-12 mb-1">
                                    <input type="file" name="cert_file_ssl_key" id="cert_file_ssl_key" class="form-control" accept="">
                                    <span id="error_file" style="color:red;"></span>
                                </div>
                                <div class="col-lg-12">
                                    <span style="color:red;">รองรับเฉพาะไฟล์ ca.crt เท่านั้น</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="padding-top: 7px">
                        <label class="col-lg-3 control-label">Site </label>
                        <div class="col-lg-8">
                            <span class="checkbox">
                                <label>
                                    <input type="checkbox" name="cert_site[]" value="all">
                                    <span class="label-text" data-rel="tooltip" title="" data-original-title=""> All Site </span></label></span>
                                </label>
                            </span>
                            @foreach ($site_settings as $site_settings)
                            <span class="checkbox">
                                <label>
                                    <input type="checkbox" name="cert_site[]" value="{{ $site_settings->id }}">
                                    <span class="label-text" data-rel="tooltip" title="" data-original-title=""> {{ $site_settings->name }} </span>
                                </label>
                            </span>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Status </label>
                        <div class="col-lg-6">
                            <label class="switch">
                                <input type="checkbox" name="cert_status" value="TRUE" checked>
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-info formSaving btn-rounded">
                        <i class="fas fa-paper-plane"></i> Save
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    {{-- <div class="modal" id="delete_rss_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true" style="left: unset">
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_rss_submit"
                        onclick="delete_rss_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div> --}}

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
    @include('stacks.js.hidesettings');
    @include('stacks.js.fullscreen');

    <script>
        $(document).ready(function () {
            $('#keywords').select2({
                tags: true,
                tokenSeparators: [' ']
            });

            $('#interval').select2({
                tags: true,
                tokenSeparators: [' ']
            });

            $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

            $('#show_end_exp_date').hide();

            $('#set_exp').on('change',function(){
                if($(this).prop('checked')){
                    $('#show_end_exp_date').show();
                }else{
                    $('#show_end_exp_date').hide();
                }
            });
        });

        $('#table-rss-setting-template').on('click', '.select-chk', function () {
            if ($(this).is(':checked')) {

                $('#btn_del_select').prop("disabled", false);
            } else {
                
                if ($('.select-chk').filter(':checked').length < 1){

                    $('#btn_del_select').attr('disabled',true);
                }
            }
        });

        $('#table-rss-setting-template').on('click', '.rss_id', function () {
            if ($(this).is(':checked')) {
                $('#btn_del_select').prop("disabled", false);
            } else {
                document.getElementById("select-all").checked = false;
                if ($('.rss_id').filter(':checked').length < 1){
                    
                    $('#btn_del_select').attr('disabled',true);
                }
            }
        }); 

        $(function () {
            
            var table = $('#table-rss-setting-template').DataTable({
                pageLength: 50,
                processing: true,
                serverSide: true,
                destroy: true,
                "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                ajax: {
                    url: '{!! route('certificate.tableCertificate') !!}',
                    data: function ( d ) {
                        return d;
                    },
                    type: "POST",
                },
                order: [[ 0, "desc" ]],
                columns: [
                    { data: 'DT_Row_Index', name: 'DT_Row_Index', className: 'no-wrap w-10' },
                    { data: 'name', name: 'name', className: 'no-wrap' },
                    { data: 'site_id', name: 'site_id', className: 'no-wrap' },
                    { data: 'download', name: 'download', className: 'no-wrap w-10' },
                    { data: 'status', name: 'status', className: 'no-wrap w-10' },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        sortable: false,
                        className: 'no-wrap w-10'
                    }
                ]
            });

        });

        var form_save = '.formSaving';
        $('.ajaxifyForm_custom').submit(function (event) {
            event.preventDefault();

                $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                $('.formSaving').attr('disabled',true);
                
                var data = new FormData(this);
                if(form_save == '.formSavingAndRun'){
                    data.append('formsubmit', 'formSavingAndRun');
                }else if(form_save == '.formPreview'){
                    data.append('formsubmit', 'formPreview');
                }else if(form_save == '.formDraft'){
                    data.append('formsubmit', 'formDraft');
                }
                axios.post($(this).attr("action"), data)
                    .then(function (response) {
                            toastr.success(response.data.message, '@langapp('response_status') ');
                            $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                            {{-- window.location.href = response.redirect; --}}
                })
                .catch(function (error) {
                    if(error.response.data.exception){
                        $('.formSaving').attr('disabled',false);
                        toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                        $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                    }else{
                        $('.formSaving').attr('disabled',false);
                        var errors = error.response.data.errors;
                        var errorsHtml= '';
                        $.each( errors, function( key, value ) {
                            errorsHtml += '<li>' + value[0] + '</li>'; 
                        });
                        toastr.error( errorsHtml , '@langapp('response_status') ');
                        $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                    }
                    
                });
        });

    </script>
@endpush
@endsection