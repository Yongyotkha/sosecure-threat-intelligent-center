<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content" >
        <div class="modal-header bg-blue" id="gototop">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white" ><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Activity History</h4>
        </div>
    {!! Form::open(['route' => ['dataleak.activity_save'], 'class' => 'ajaxifyForm_custom_onlythis', 'files' => false]) !!}
        <div class="modal-body">
            <input type="hidden" name="id_DataLeakSocialRefs" class="form-control" value="{{$DataLeakSocialRefs->id}}">
            @if ($site)
            <input type="hidden" name="site_code" id="site_code" class="form-control" value="{{$site}}">   
            @endif
            <input type="hidden" name="check_active" id="check_active" class="form-control" value="1">
            <input type="hidden" name="code_edited_activity" id="code_edited_activity" class="form-control" value="">
            <div class="form-group row">
                <a class="float-right" href="javascript:void(0)" id='button_add_activity' onclick="add_activity();">Add Activity&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Title </label>
                <div class="col-lg-9">
                    <input type="text" name="title" id="texttitle_new" class="form-control" value="">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Content</label>
                <div class="col-lg-9" >
                    <textarea  class="form-control htmleditor" id="textcontent_new" name="content"  data-id="1"  >
                    </textarea>
                </div>
            </div>
            <div class="form-group row float-right">
                <div class="col-lg-12">
                    {!! closeModalButton() !!}
                    {!! renderAjaxButton() !!}
                </div>
                
            </div>
            

            <div class="form-group row">
                <label class="col-lg-12 control-label"><strong>Activity History</strong></label>
            </div>
            
            <div class="form-group row">
                <div class="card col-lg-12">
                    <ul class="list-group list-group-flush" id="group_activity">
                        @if (!empty($ActivityHistory))
                        @foreach ($ActivityHistory as $value)
                            <li class="list-group-item" id="list_activity_{{$value->code}}" style="border-color: black;">
                                <div>
                                    <strong>{{$value->title}}</strong>
                                </div>
                                <div>
                                    {!!$value->content!!}
                                </div>
                                <div>
                                    <strong>Post By</strong> {{$value->users_name}} <strong>Modified:</strong> {{$value->updated_at}}
                                    @if(TYPE_WEB == 'center')
                                        <span class="float-right">
                                            <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity('{{$value->id}}');">
                                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity('{{$value->id}}','{{$value->code}}');"><i class="fas fa-trash-alt"></i></a>
                                        </span>
                                    @else
                                        @if($value->user_id==Auth::user()->id)
                                            <span class="float-right">
                                                <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity('{{$value->id}}');">
                                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity('{{$value->id}}','{{$value->code}}');"><i class="fas fa-trash-alt"></i></a>
                                            </span>
                                        @endif
                                    @endif
                                    
                                </div>
                            </li>
                        @endforeach
                        @else
                            <li class="list-group-item">
                            </li>
                        @endif
                        

                    </ul>
                  </div>
            </div>
                {{-- <p>This is user {{ $user->id }}</p> --}}
           
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
        </div>
    {!! Form::close() !!}
    </div>
</div>

@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.summernote')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
@include('scripts.summernote')
@include('stacks.js.markdown')
<script>

    $('form').each(function () {
        if ($(this).data('validator'))
            $(this).data('validator').settings.ignore = ".note-editor *";
    });

    $(document).ready(function () {
        $('.select2-option').select2();
    });

    $('.ajaxifyForm_custom_onlythis').submit(function (event) {
        loading('load');
        event.preventDefault();
        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        $('.btn').attr('disabled',true);
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
                $('.btn').attr('disabled',false);
                $("#texttitle_new").val('');
                $('#textcontent_new').summernote('code','');
                $("#check_active").val("1");
                $("#code_edited_activity").val('');
                {{--$(".disable_atag").removeAttr("href");--}}
                {{--$(".disable_atag").removeAttr("onclick");--}}
                toastr.success(response.data.message, '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                reload_activity_history(response.data.socail_ref_id);
                {{--window.location.href = response.data.redirect;--}}
        })
        .catch(function (error) {
            if(error.response.data.exception){
                $('.btn').attr('disabled',false);
                toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
            }else{
                $('.btn').attr('disabled',false);
                var errors = error.response.data.errors;
                var errorsHtml= '';
                $.each( errors, function( key, value ) {
                    errorsHtml += '<li>' + value[0] + '</li>'; 
                });
                toastr.error( errorsHtml , '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
            }
            loading('stop_load');
        });
    });

    function add_activity(){
        $("#texttitle_new").val('');
        $('#textcontent_new').summernote('code','');
        $("#check_active").val("1");
        $("#code_edited_activity").val('');
    }

    function edit_activity(code_activity=0){    
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('dataleak.activity_get_edit_data') }}',
            type: "GET",
            data:{
                code_activity:code_activity,
            },
            beforeSend: function(){
                loading('load');
            },
        }).done(function(data){
            $("#texttitle_new").val(data.ActivityHistory.title);
            $('#textcontent_new').summernote('code',data.ActivityHistory.content);
            $("#check_active").val("2");
            $("#code_edited_activity").val(code_activity);
            loading('stop_load');
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            loading('stop_load');
            console.log("No response from server");
        });
    }

    function reload_activity_history(code){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('dataleak.activity_history_reload') }}',
            type: "GET",
            data:{
                code:code,
            },
            beforeSend: function(){
                loading('load');
            },
        }).done(function(data){
            loading('stop_load');
            $('#group_activity').html(data.html);
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            loading('stop_load');
            console.log("No response from server");
        });
    }

    function delete_activity(id_activity,code_activity){
        let site_code = $("#site_code").val();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to delete this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type:"DELETE",
                    url:"{{ route('dataleak.activity_delete') }}",
                    data:{
                        code_activity:id_activity,
                        site_code:site_code
                    },
                    beforeSend: function(){
                        $('.btn').attr('disabled',true);
                        loading('load');
                    },
                    success:function(response) {
                        event.preventDefault();
                        $(`#list_activity_${code_activity}`).remove();
                        {{--$(".disable_atag").removeAttr("href");
                        $(".disable_atag").removeAttr("onclick");--}}
                        toastr.success(response.message, '@langapp('response_status')');
                        $('.btn').attr('disabled',false);
                        loading('stop_load');
                        {{--window.location.href = response.redirect;--}}
                    },
                    error: function (error){
                        
                        $('.btn').attr('disabled',false);
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function (key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                        loading('stop_load');
                    }

                });
            }   
        })
    }
    
</script>
@endpush

@stack('pagestyle')
@stack('pagescript')