<div class="modal-dialog modal-dialog-aside" role="document">
    <div class="modal-content">

        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">
                <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                    title="Fullscreen" data-placement="right"></i>
                Edit Rule
            </h4>
        </div>
        <form id='form_edit_rule' enctype="multipart/form-data">
            <div class="modal-body">

                <input type="hidden" name="hd_id" id="hd_id" value="{{$query->id}}">

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">
                        Rule Category <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-lg-12 mb-1">

                                    <input type="text" name="name" id="name" class="form-control" value="{{$query->name}}" readonly>
                                    {{-- <select name="name" id="edit_name" class="form-control check_test_select">
                                        <option value="">Choose an Category</option>
                                    </select>
                                    <span id="error_name" style="color:red;"></span> --}}
                                </div>
                                {{-- <div class="col-lg-4 mb-1">
                                    <button type="button" data-toggle="collapse" href="#demo" class="btn btn-{{ get_option('theme_color')  }} add-assets-new">
                                        <i class="fas fa-plus"></i>
                                        &nbsp; Add New
                                    </button>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-lg-12">

                        <div id="demo" class="collapse box">

                            <fieldset class="collapsible">
            
                                <legend>Add Category</legend>
                                <div class="form-group row">
                                    <label style="padding-top: 7px" class="col-lg-3 control-label">Name <span
                                            class="text-danger">*</span> </label>
                                    <div class="col-lg-8">
                                        <input type="text" name="name_new" id="name_new" class="form-control check_test">
                                        <span style="color:red;" id="check_name_new" class="d-none"><small>Please enter your name category</small></span>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="close_add_category" class="btn btn-danger btn-rounded" data-toggle="collapse"
                                        data-target="#demo">
                                        <i class="fas fa-times"></i>
                                        Close
                                    </button>
                                    <button type="button" onclick="add_new_category()" class="btn btn-info btn-rounded">
                                        <i class="fas fa-paper-plane"></i>
                                        Save
                                    </button>
                                </div>
                                <hr>
            
                            </fieldset>
            
                        </div>

                    </div> --}}
                    {{-- <div class="col-lg-3">
                    </div>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-12 mb-1">
                                <input type="file" name="file_rule_name" id="file_rule_name" class="form-control" accept="zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed">
                                <span id="error_file" style="color:red;"></span>
                            </div>
                            <div class="col-lg-12">
                                <span style="color:red;">รองรับเฉพาะไฟล์ .zip เท่านั้น</span>
                            </div>
                        </div>
                    </div> --}}
                </div>
                <div id="div_rule" class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">
                        Rule  <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-9">

                        <table id="rule_item" class="table mb-0">
                            <tbody>
                                @foreach($query_rule as $key => $rule)
                                <tr>
                                    <td style="width: 33.33%">
                                        <input type="text" id="detail_{{$key}}_file_name" name="detail[{{$key}}][file_name]" value="{{$rule->file_name}}" class="form-control" readonly>
                                        <input type="hidden" id="detail_{{$key}}_id" name="detail[{{$key}}][id]" value="{{$rule->id}}">
                                    </td>
                                    <td style="width: 33.33%">
                                        <input type="text" id="detail_{{$key}}_description" name="detail[{{$key}}][description]" class="form-control" value="{{$rule->description}}">
                                    </td>
                                    <td style="width: 33.33%">
                                        <select class="select-2--rule form-control" id="detail_{{$key}}_severity" name="detail[{{$key}}][severity]">
                                            <option value="Information" {{$rule->severity == 'Information' ? 'selected' : '' }}>Information</option>
                                            <option value="Low" {{$rule->severity == 'Low' ? 'selected' : '' }}>Low</option>
                                            <option value="Medium" {{$rule->severity == 'Medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="High" {{$rule->severity == 'High' ? 'selected' : '' }}>High</option>
                                            <option value="Critical" {{$rule->severity == 'Critical' ? 'selected' : '' }}>Critical</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <span id="error_detail" style="color:red;"></span>
                    </div>
                </div>

                {{-- <div class="form-group row" style="padding-top: 7px">
                    <label class="col-lg-3 control-label">Site </label>
                    <div class="col-lg-8">
                        <span class="checkbox">
                            <label>
                                <input type="checkbox" name="site[]" value="all">
                                <span class="label-text" data-rel="tooltip" title="" data-original-title="">
                                    All Site
                                </span>
                            </label>
                        </span>
                        @foreach ($query_site as $key => $name)
                        <span class="checkbox">
                            <label>
                                <input type="checkbox" name="site[]" value="{{@$key}}">
                                <span class="label-text" data-rel="tooltip" title="" data-original-title="">
                                    {{@$name}}
                                </span>
                            </label>
                        </span>
                        @endforeach
                    </div>
                </div> --}}

                <div class="form-group row" style="padding-top: 7px">
                    <label class="col-lg-3 control-label">Status </label>
                    <div class="col-lg-8">
                        <label class="switch">
                            <input type="checkbox" id="status" name="status" {{$query->status == 'Y' ? 'checked' : ''}} value="1">
                            <span></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                <button type="button" value="Submit" required class="btn btn-info btn-rounded" id="btn_update_rule">
                    <i class="fas fa-paper-plane"></i>
                    Save
                </button>
            </div>
        </form>
    </div>
</div>


@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
@include('stacks.js.markdown')
@include('stacks.js.hidesettings')
@include('stacks.js.defaultpic')

<script>

$('#btn_update_rule').click(function(e){
    e.preventDefault();

    var formData = new FormData(document.getElementById("form_edit_rule"));

    $('#btn_update_rule').html('Processing.. <i class="fas fa-spin fa-spinner"></i>');
    $('#btn_update_rule').attr('disabled', true);

    $.ajax({
        url: "{{ route('agentmanagement.agent_rule_update') }}",
        type: 'post',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        beforesend: function(){
            
        },
        success:function(response){

            if(response.status == 'success')
            {
                toastr.success(response.message);

                $('#btn_update_rule').html('Success');

                $('#ajaxModal').modal("hide");

                tbl_category_rule.ajax.reload();
                tbl_all_rule.ajax.reload();
                tbl_extention_rule.ajax.reload();

                setTimeout(function(){
                    {{-- window.location.href = response.route; --}}
                }, 3000);
            }
            else
            {
                $('#btn_update_rule').html('Try again');
                $('#btn_update_rule').attr('disabled', false);

                toastr.error(response.message);
            }

        }
    });
    
});

</script>

@endpush
@stack('pagestyle')
@stack('pagescript')

