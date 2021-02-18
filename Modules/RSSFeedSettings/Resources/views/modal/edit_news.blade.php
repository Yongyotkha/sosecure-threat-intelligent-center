<div id="fullscreen-modal" class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">News</h4>
        </div>
            {!! Form::open(['route' => ['rssfeedsettings.rss_data_store_news_create'], 'class' => 'ajaxifyFormCreate', 'method' => 'POST', 'files' => true]) !!}
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                        <div class="col-md-12">
                            <h5>Create News</h5>
                        </div>
                        </div>
                        <div class="form-group row">
                        <label class="col-lg-12 control-label">Logo </label>
                        <div class="col-lg-12">
                            <div class="">
                                <input id="file-input" type="file" class="form-control" name="logo" value="">
                                <input type="hidden" name="action" value="{{@$action}}">
                                <input type="hidden" name="rss_code" value="{{ @$RSSNews -> code }}">
                            </div>
                        </div>
                    </div>
                        <div class="row">
                            <label for="" class="col-md-12 control-label" id="label_category">Category <span class="text-danger">*</span></label>
                            <div class="col-md-12">
                            <select name="category_news[]" id="category" class="select2-option form-control" multiple="multiple" required>
                                @foreach ($category as $item)
                                    <option value="{{ $item -> id }}"
                                        @if($RSSNews)
                                        @foreach(@$RSSNews->get_cate as $news_cate_val)
                                            @if($item->id == $news_cate_val->news_category_id)
                                                selected
                                            @else 
                                                
                                            @endif
                                        @endforeach
                                        @endif
                                    >{{ $item -> name }}</option>
                                @endforeach
                            </select>
                            </div>
                        </div>
                        <br>
                        <div class="form-group row">
                        <label for="" class="col-lg-12 control-label" id="label_source">Source {{--<span class="text-danger">*</span>--}}</label>
                        <div class="col-lg-12">
                            <select name="source" id="source_create" class="select2-option form-control">
                                <option value="">Select Source</option>
                                @if(@$get_source)
                                    
                                @foreach (@$get_source as $item)
                                    @php
                                        $source_checked = '';  
                                    @endphp
                                    @if(@$RSSNews) 
                                        @if($RSSNews->source == $item->name)
                                            @php 
                                                $source_checked = 'selected';  
                                            @endphp
                                        @endif
                                    @endif
                                    <option value="{{ $item -> name }}" value="{{@$item->name}}" {{$source_checked}}>{{ $item -> name }}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <br>
                        {{-- <div class="row">
                        <label for="" class="col-md-12 control-label" id="label_topic">Topic <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <select name="topic[]" id="topic" class="select2-option form-control" multiple="multiple"></select>
                        </div>
                    </div> --}}
                        <br>
                        {{-- 
                        <div class="row">
                            <div class="col-lg-12">
                            <div class="form-group">
                                <label>
                                    <input name="lang" value="th" type="checkbox" checked onclick="return false;"/>
                                    <span class="label-text">TH</span>
                                </label>
                                &nbsp;&nbsp;
                                <label>
                                    <input name="lang" value="en" type="checkbox"/>
                                    <span class="label-text">EN</span>
                                </label>
                            </div>
                            </div>
                        </div> --}}

                        <div class="row">
                            <div class="col-md-12">
                            <div class="tabbable">
                                <ul class="nav nav-tabs nav-tabs-highlight">
                                    <li class="active"><a href="#tab_th" data-toggle="tab">TH</a></li>
                                    <li><a href="#tab_en" data-toggle="tab">EN</a></li>   
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="tab_th">
                                        <section class="panel-body border-n">
                                            <div class="form-group row">
                                                <label for="" class="col-lg-12 control-label" id="label_title_th">Text (TH)</label>
                                                <div class="col-lg-12">
                                          
                                                    <input type="text" class="form-control" name="title_th" id="title_th" value="{{@$RSSNews->title_th}}">
                                                </div>
                                            </div>
        
                                            <div class="form-group row">
                                                <label for="" class="col-lg-12 control-label" id="label_detail_th">Detail (TH)</label>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control htmleditor" name="detail_th" id="detail_th" data-id="1">{!!@$RSSNews->detail_th!!}</textarea>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                    <div class="tab-pane" id="tab_en">
                                        <section class="panel-body border-n">
                                            <div class="form-group row">
                                                <label for="" class="col-lg-12 control-label">Text (EN)</label>
                                                <div class="col-lg-12">
                                                    <input type="text" class="form-control" name="title_en" id="title_en" value="{{@$RSSNews->title_en}}">
                                                </div>
                                            </div>
        
                                            <div class="form-group row">
                                                <label for="" class="col-lg-12 control-label">Detail (EN)</label>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control htmleditor" name="detail_en" id="detail_en" data-id="1">{!!@$RSSNews->detail_en!!}</textarea>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        {{--  --}}
                            {{-- <div class="row">
                                <label for="" class="col-md-12 control-label">Tag
                            </div>
                            <div class="row">
                            <div class="col-md-12">
                                    <select name="tags[]" id="tags" class="select2-option form-control" multiple="multiple"></select>
                                </div>
                            </div> --}}
                    <br>
                        <div class="form-group row">
                            <div class="col-lg-3 col-md-6">
                            <div class="row">
                                <label class="col-lg-4 control-label"> <span style="margin-top: 8px; display: inline-block;"> Public Date </span></label>
                                <div class="col-lg-8">
                                    <div class="input-group date">
                                        <input id="send_date" type="text" class="form-control datetimepicker-input"
                                        @php
                                            $date_public = '';
                                            if(@$RSSNews -> public_date) {
                                                $date_public = timePickerFormat($RSSNews -> public_date);
                                            } else {
                                                $date_public = timePickerFormat(date("Y-m-d"));
                                            }
                                        @endphp
                                        value="{{@$date_public}}" name="public_date"
                                        data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()">
                                        <div class="input-group-addon">
                                            @icon('solid/calendar-alt', 'text-muted')
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                            <div class="col-lg-9 col-md-6">
                            <div class="row">
                                <label class="col-lg-1 control-label" style="margin-top: 8px; display: inline-block;">Send Mail </label>
                                <div class="col-lg-1">
                                    <label class="switch" style="margin-top: 8px; display: inline-block;">
                                        <input type="checkbox" name="sent_mail" checked value="1">
                                        <span></span>
                                    </label>
                                </div>
                                <label class="col-lg-1 control-label" style="margin-top: 8px; display: inline-block;">Status </label>
                                <div class="col-lg-1">
                                    <label class="switch" style="margin-top: 8px; display: inline-block;">
                                        @php
                                        $checked_val = '';
                                            if(@$RSSNews -> status == 1) {
                                                $checked_val = 'checked';
                                            } else if(@$action == 'create') {
                                                $checked_val = 'checked';
                                            }
                                        @endphp
                                        <input type="checkbox" name="status" {{@$checked_val}} value="TRUE">
                                        <span></span>
                                    </label>
                                </div>
                             
                            </div>
                            </div>
                    </div>

                    </div>
                </div>

                 <div class="modal-footer">
                    {!! closeModalButton() !!}
                    <button type="submit" class="btn btn-warning formDraft btn-rounded"><i class="fas fa-save"></i> SaveDraft</button>
                    <button type="submit" class="btn btn-info formSaving submit btn-rounded"><i class="fas fa-paper-plane"></i> Save Public</button>
                    {{-- {!! renderAjaxButton() !!} --}}
                    {!! Form::close() !!}
                    {{-- <form action="{{ route('rssfeedsettings.rss_data_preview_news') }}" method="post" target="_blank">
                        <input type="text" name="title" id="title_preview">
                        <textarea name="detail" id="detail_preview"></textarea>
                        <button type="submit" class="btn btn-success formPreview btn-rounded"><i class="fas fa-eye"></i> Preview</button>
                    </form> --}}
                 </div>
         </div>
     </div>
 </div>

 @push('pagestyle')
 @include('stacks.css.form')
 @include('stacks.css.datepicker')
 @include('stacks.css.form')
 @include('stacks.css.summernote')
 @endpush
 @push('pagescript')
 @include('stacks.js.form')
@include('stacks.js.datepicker')
@include('scripts.summernote')
@include('stacks.js.markdown')
@include('stacks.js.hidesettings')
<script>

$('form').each(function () {
    if ($(this).data('validator'))
        $(this).data('validator').settings.ignore = ".note-editor *";
});

$('#source_create').select2({
            tags: true,
            tokenSeparators: [' ']
});


var form_save = '.formSaving';
    $('.formPreview').click(function() {
        form_save = '.formPreview';
    });
    $('.formDraft').click(function() {
        form_save = '.formDraft';
    });
    
    $('.ajaxifyFormCreate').submit(function (event) {
            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            $('.formSaving').attr('disabled',true);
            $('.formDraft').attr('disabled',true);
            
            event.preventDefault();
            var data = new FormData(this);
            if(form_save == '.formPreview'){
                data.append('formsubmit', 'formPreview');
            }else if(form_save == '.formDraft'){
                data.append('formsubmit', 'formDraft');
            }
            axios.post($(this).attr("action"), data).then(function (response) {
                toastr.success(response.data.message, '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                window.location.href = response.data.redirect;
            }).catch(function (error) {
                $('.formSaving').attr('disabled',false);
                $('.formDraft').attr('disabled',false);
                if(error.response.data.exception){
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
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
    
    $(document).ready(function(){
        {{--$('#source_create').select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/source',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params.term || '',
                        pageNum: params.page || 1,
                    }
                },
                processResults: function (data) {
                    return {
                    results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.name,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });--}}

        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });
    }); 
    function copy_link(value) {
        var tempInput = document.createElement("input");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        tempInput.value = value;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
    }
</script>
@endpush
 
 @stack('pagestyle')
 @stack('pagescript')
