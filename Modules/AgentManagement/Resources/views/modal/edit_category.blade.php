<div class="modal-dialog modal-dialog-aside" role="document">
    <div class="modal-content">

        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">
                <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                    title="Fullscreen" data-placement="right"></i>
                Edit
            </h4>
        </div>
        <form id='form_edit_category' enctype="multipart/form-data">
            <div class="modal-body">

                <input type="hidden" name="hd_id" id="hd_id" value="{{$query->id}}">

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">
                        Name <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-lg-8 mb-1">
                                    <input type="text" name="edit_category_name" id="edit_category_name" class="form-control" value="{{$query->name}}">
                                    <span style="color:red;" id="error_edit_category_name" class="d-none"><small>Please enter your name category</small></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                <button type="button" value="Submit" required class="btn btn-info btn-rounded" id="btn_update_category">
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

$('#btn_update_category').click(function(e){
    e.preventDefault();

    let edit_category_name = $('#edit_category_name').val();

    if(edit_category_name)
    {
        var formData = new FormData(document.getElementById("form_edit_category"));

        $('#btn_update_category').html('Processing.. <i class="fas fa-spin fa-spinner"></i>');
        $('#btn_update_category').attr('disabled', true);

        $.ajax({
            url: "{{ route('agentmanagement.category_update') }}",
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

                    $('#btn_update_category').html('Success');

                    tbl_category_rule.ajax.reload();
                    tbl_all_rule.ajax.reload();

                    setTimeout(function(){
                        {{-- window.location.href = response.route; --}}
                    }, 3000);
                }
                else
                {
                    $('#btn_update_category').html('Try again');
                    $('#btn_update_category').attr('disabled', false);

                    toastr.error(response.message);
                }

            }
        });
    }
    else
    {
        input_check_err('#edit_category_name', '#error_edit_category_name')
    }

    
});

</script>

@endpush
@stack('pagestyle')
@stack('pagescript')

