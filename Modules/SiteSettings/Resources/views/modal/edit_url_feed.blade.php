    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal"
                    onclick="close_wdfm_website()">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();"
                        datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                    <span id="title_head"> Edit Website</span>
                </h4>
            </div>
            <form id="form_update_url_feed" enctype="multipat/form-data">
            {{-- {!! Form::open(['route' => ['urlfeed.update_url_feed'], 'class' => 'ajaxifyForm_custom', 'method' =>
            'POST']) !!} --}}
            <input type="hidden" name="mode" id="mode" value="create">
            <div class="modal-body">
                <div class="container-fluid">
                    {{-- <div id="site_id_show" class="form-group row">
                        <label class="col-lg-3 control-label"> Site <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <select name="site_id" id="edit_site_id" class="select2-option form-control">
                                <option value="all" {{ $query->site_id == 'all' || $query->site_id == '0' || $query->site_id == null ? 'selected' : '' }}>All Site</option>
                                @foreach ($SiteSettings as $data_SiteSetting)
                                <option value="{{$data_SiteSetting->id}}" {{ $query->site_id == $data_SiteSetting->id ? 'selected' : '' }}>{{$data_SiteSetting->name}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div> --}}

                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="name_web" id="edit_name_web" value="{{ $query->source }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Type <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <select name="type_web" id="edit_type_web" class="select2-option form-control">
                                <option value="Public" {{ $query->type == 'Public' ? 'selected' : '' }}>Public</option>
                                <option value="DarkWeb" {{ $query->type == 'DarkWeb' ? 'selected' : '' }}>DarkWeb</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row {{ $query->type == 'DarkWeb' ? '' : 'd-none' }}" id="div_header_edit">
                        <label class="col-lg-3 control-label">Header/Cookies <span class="text-danger"></span> </label>
                        <div class="col-lg-9">
                            <textarea class="form-control htmleditor" id="edit_header" name="header" data-id="1" style="display: none;">{!! $query->header !!}</textarea>
                            {{-- <textarea name="header" id="header" class="form-control note-codable" cols="30" rows="3"></textarea> --}}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">URL <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="url_web" id="edit_url_web" value="{{ $query->url }}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Port <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <div class="input-group">
                                <input type="text" class="form-control" name="port_web" id="edit_port_web" value="{{ $query->port }}"
                                    required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" onclick="edit_get_check_site()">Check</button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div id="edit_area_check_message_row" class="form-group row" style="display: none;">
                        <label class="col-lg-3 control-label"> </label>
                        <div class="col-lg-9">
                            <div id="edit_area_check_message"></div>
                        </div>
                    </div>

                    <input type="hidden" name="site" id="site">
                    <input type="hidden" name="hd_code" id="hd_code" value="{{ $query->code }}">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal"
                    onclick="close_wdfm_website()">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                {{-- disabled --}}
                <button id="edit_btn_save" type="button" onclick="update_url_feed();" class="btn btn-info btn-rounded formSaving" disabled>
                    <i class="fas fa-paper-plane"></i>
                    Save
                </button>
            </div>
            {{-- {!! Form::close() !!} --}}
            </form>
        </div>
    </div>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    @include('stacks.css.summernote')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.markdown')
@include('scripts.summernote')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.fullscreen')

<script>

    $('#edit_type_web').change(function(){

        let type_value = $('#edit_type_web :selected').val();

        if(type_value == 'Public')
        {
            $('#div_header_edit').addClass('d-none');
        }
        else
        {
            $('#div_header_edit').removeClass('d-none');
        }

    });

    function edit_get_check_site()
    {
        let url_web = $("#edit_url_web").val();
        let port_web = $("#edit_port_web").val();
        if(url_web && port_web) 
        {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('urlfeed.get_check_site') !!}',
                type: "post",
                data: ({
                    site_id:site_id,
                    url_web:url_web,
                    port_web:port_web
                }),
                beforeSend: function(){
                    f_loading(null, '#edit_url_web');
                    f_loading(null, '#edit_port_web');
                },
            }).done(function(data){
                f_loading_stop(null, '#edit_url_web');
                f_loading_stop(null, '#edit_port_web');
                var obj = JSON.parse(data);
                console.log(obj);
                var message = obj.message;

                if(obj.Result == 1) 
                {
                    console.log(55);
                    let DomainHeaders = JSON.stringify(obj.DomainHeaders);
                    let d_header = DomainHeaders;
                    let message_html = `
                        <div class="form-group row">
                            <div class="col-lg-12">
                                <div class="bg-success" style="display:inline-block;padding:5px;border-radius:5px;">
                                    <i class="fas fa-check-circle text-white fa-2x"></i> ${message}
                                </div>
                            </div>
                        </div>
                        <p>
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseExample" expanded="false" aria-expanded="false" aria-controls="collapseExample">
                                View header
                            </button>
                        </p>
                        <div class="collapse" id="collapseExample">
                            <div class="card card-body">
                                ${d_header}
                            </div>
                        </div>
                    `;

                    $("#edit_area_check_message_row").css("display","block");
                    $(".edit_area_image_screen").css("display","block");
                    $("#edit_area_check_message").html(message_html);

                    $("#edit_area_option").css("display","block");
                    $("#edit_btn_save").prop("disabled",false);
                    
                } 
                else 
                {
                    console.log(44);
                    let message_html = `
                        <div class="form-group row">
                            <div class="col-lg-12">
                                <div style="width: 100%; background: #ffebe6;">
                                    <i class="fas fa-times"></i> ${message}
                                </div>
                            </div>
                        </div>
                    `;

                    $("#edit_area_check_message_row").css("display","block");
                    $(".edit_area_image_screen").css("display","none");
                    $("#edit_area_check_message").html(message_html);
                    $("#edit_area_option").css("display","none");
                    $("#edit_btn_save").prop("disabled",true);
                }
                
            }).fail(function(jqXHR, ajaxOptions, thrownError){
                f_loading_stop(null, '#edit_url_web');
                f_loading_stop(null, '#edit_port_web');
                console.log("No response from server");
            });
        }
        else
        {
            toastr.error('Enter Url and Port.')
        }
    }
</script>