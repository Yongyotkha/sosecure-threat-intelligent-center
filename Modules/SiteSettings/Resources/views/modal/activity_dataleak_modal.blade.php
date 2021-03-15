<style>
/* -------------------------------------
 * For horizontal version, set the
 * $vertical variable to false
 * ------------------------------------- */
/* -------------------------------------
 * General Style
 * ------------------------------------- */
@import url(https://fonts.googleapis.com/css?family=Noto+Sans);
body {
  /* max-width: 1200px;
  margin: 0 auto;
  padding: 0 5%;
  font-size: 100%;
  font-family: "Noto Sans", sans-serif;
  color: #000;
  background: #48b379; */
}

/* h2 {
  margin: 3em 0 0 0;
  font-size: 1.5em;
  letter-spacing: 2px;
  text-transform: uppercase;
} */

/* -------------------------------------
 * timeline_custom
 * ------------------------------------- */
#group_activity {
  list-style: none;
  margin: 50px 0 30px 120px;
  padding-left: 34px;
  border-left: 4px solid #d4d4d4;
}
#group_activity li {
  margin: 40px 0;
  position: relative;
}
#group_activity p {
  margin: 0 0 15px;
}

.date_custom {
  margin-top: -10px;
  top: 50%;
  left: -158px;
  font-size: 0.95em;
  line-height: 20px;
  position: absolute;
  width: 100px;
}

.circle_custom {
  margin-top: -10px;
  top: 50%;
  left: -44px;
  width: 15px;
  height: 15px;
  background: #48b379;
  border: 5px solid #2196f3;
  /* border-radius: 50%; */
  display: block;
  position: absolute;
}

.content_custom {
  /* max-height: 20px; */
  /* padding: 50px 20px 0; */
  border-color: transparent;
  border-width: 2px;
  border-style: solid;
  border-radius: 0.5em;
  position: relative;
}
.content_custom:before, .content_custom:after {
  content: "";
  width: 0;
  height: 0;
  border: solid transparent;
  position: absolute;
  pointer-events: none;
  right: 100%;
}
.content_custom:before {
  border-right-color: inherit;
  border-width: 20px;
  top: 50%;
  margin-top: -20px;
}
.content_custom:after {
  /* border-right-color: #48b379; */
  border-width: 17px;
  top: 50%;
  margin-top: -17px;
}
.content_custom p {
  max-height: 0;
  color: transparent;
  text-align: justify;
  word-break: break-word;
  hyphens: auto;
  overflow: hidden;
}

.label_custom {
  font-size: 1.3em;
  /* position: absolute; */
  z-index: 100;
  cursor: pointer;
  top: 20px;
  transition: transform 0.2s linear;
}

.radio {
  display: none;
}

.radio + .relative label {
  cursor: auto;
  /* transform: translateX(42px); */
}
.radio + .relative .circle_custom {
  background: #fff;
}
.radio ~ .content_custom {
  /* max-height: 180px; */
  border-color: #fff;
  margin-right: 20px;
  transform: translateX(20px);
  transition: max-height 0.4s linear, border-color 0.5s linear, transform 0.2s linear;
}
.radio ~ .content_custom p {
  max-height: 200px;
  color: #000;
  transition: color 0.3s linear 0.3s;
}

/* -------------------------------------
 * mobile phones (vertical version only)
 * ------------------------------------- */
@media screen and (max-width: 767px) {
  #group_activity {
    margin-left: 0;
    padding-left: 0;
    border-left: none;
  }
  #group_activity li {
    margin: 50px 0;
  }

  .label_custom {
    width: 85%;
    font-size: 1.1em;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    display: block;
    transform: translateX(18px);
  }

  .content_custom {
    padding-top: 45px;
    border-color: #000;
  }
  .content_custom:before, .content_custom:after {
    border: solid transparent;
    bottom: 100%;
  }
  .content_custom:before {
    border-bottom-color: inherit;
    border-width: 17px;
    top: -16px;
    left: 50px;
    margin-left: -17px;
  }
  .content_custom:after {
    border-bottom-color: #48b379;
    border-width: 20px;
    top: -20px;
    left: 50px;
    margin-left: -20px;
  }
  .content_custom p {
    font-size: 0.9em;
    line-height: 1.4;
  }

  .circle_custom, .date_custom {
    display: none;
  }
}

</style>


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
                <label class="col-lg-12 control-label label_custom" style="display: inline; width: 180px;"><strong>Activity History</strong></label>
                <a class="float-right btn btn-info" href="javascript:void(0)" id="button_add_activity" data-toggle="collapse" aria-expanded="false" onclick="add_activity();" style="margin-right: 5px;">Add Activity</a>
            </div>

            <div id="demo" class="collapse box">
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
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Status </label>
                    <div class="col-lg-9">
                        <select name="status_activity" id="status_activity" class="select2-option form-control" >
                            <option value="">Select</option>
                            <option value="in_progress">In Progress</option>
                            <option value="reported">Reported</option>
                            <option value="close">Close</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row float-right">
                    <div class="col-lg-12">
                        {{-- {!! closeModalButton() !!} --}}
                        <a href="#" id="btn_collape_close" class="btn btn-default btn-rounded"><i class="fas fa-times text-muted"></i> Close</a>
                        {!! renderAjaxButton() !!}
                    </div>
                    
                </div>
            </div>
            

            {{-- <h2>CSS3 group_activity</h2>
            <p>Please set the $vertical variable to false to see the horizontal version.</p> --}}
            <ul id='group_activity'>
                @if (!empty($ActivityHistory))
                        @foreach ($ActivityHistory as $value)
                            @php
                                $html_status_activity = '';
                                $activity_color = '';
                                $activity_name = '';
                                if($value->status_activity) {
                                    if($value->status_activity == 'in_progress') {
                                        $activity_color = '#FFC107';
                                        $activity_name = 'Progress';
                                    } else if ($value->status_activity == 'reported') {
                                        $activity_color = '#28A745';
                                        $activity_name = 'Reported';
                                    } else if ($value->status_activity == 'close') {
                                        $activity_color = '#DC3545';
                                        $activity_name = 'Close';
                                    }
                                    $html_status_activity = '<span class="badge" style="background-color: '.$activity_color.'; display: block;">'.$activity_name.'</span>';
                                }
                            @endphp
                            <li id="list_activity_{{$value->code}}" class='work'>
                                <input class='radio' id='work5' name='works' type='radio' checked>
                                <div class="relative">
                                <label for='work5' class="label_custom" style='font-weight: 900;'>{{$value->title}}</label>
                                <span class='date_custom' style="text-align:center;">{{$value->updated_at}}{!!$html_status_activity!!}</span>
                                
                                <span class='circle_custom'></span>
                                </div>
                                <div class='content_custom'>
                                <p>
                                    {!!$value->content!!}
                                </p>
                                </div>
                                <div><strong>Post By</strong> {{$value->users_name}}
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
                            <li class="work">
                            </li>
                        @endif
            
            </ul>
            
            {{-- <div class="form-group row">
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
            </div> --}}
                
           
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
                $("#status_activity").val('').trigger("change");
                $("div.box").collapse("hide");
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
        $("#status_activity").val('').trigger("change");
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
            $("div.box").collapse("show");
            $("#texttitle_new").val(data.ActivityHistory.title);
            $('#textcontent_new').summernote('code',data.ActivityHistory.content);
            $("#status_activity").val(data.ActivityHistory.status_activity).trigger("change");
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
                        console.log(response.success);
                        if(response.success == true) {
                            $(`#list_activity_${code_activity}`).remove();
                        }
                        event.preventDefault();
                        
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


    $("#button_add_activity").click(function() {
        $("div.box").collapse("toggle");
    });
    $("#btn_collape_close").click(function() {
        $("div.box").collapse("hide");
    });
    
</script>
@endpush

@stack('pagestyle')
@stack('pagescript')