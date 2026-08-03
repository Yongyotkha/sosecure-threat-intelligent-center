<div id="fullscreen-modal" class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">News</h4>
        </div>
        {!! Form::open(['route' => ['rssfeedsettings.rss_data_store_news'], 'class' => 'ajaxifyForm', 'method' => 'POST', 'files' => true]) !!}
                 <div class="modal-body">
                     <div class="container-fluid">
                         <div class="row">
                             <div class="col-md-4">
                                {{-- <h5>Date : </h5> --}}
                                <h5>Update : {{ $rss -> transcation_datetime }}</h5>
                             </div>
                             <div class="col-md-4">
                                 <h5 class="text-elip-line-1">Name : {{ $rss -> title }}</h5>
                             </div>
                             <div class="col-md-4" style="display: flex;align-items:center">
                                <h5 class="text-elip-line-1">URL : {{ $rss -> link }}  </h5>
                                <div style="display: flex;align-items:center">
                                    <a href="{{ $rss -> link }}" target="_blank" class="btn btn-xs btn-info"><i class="far fa-eye"></i> Open</a>
                                    <button type="button" class="btn btn-xs btn-info" onclick="copy_link('{{ $rss -> link }}');"><i class="fas fa-copy"></i> Copy</button>
                                </div>
                             </div>
                             <div class="col-xs-12">
                                 <hr>
                             </div>
                         </div>

                         <div class="row">
                            <div class="col-md-12">
                                <h5>Create News</h5>
                            </div>
                            <div class="col-xs-12">
                                <hr>
                            </div>
                         </div>
                         <input type="hidden" name="rss_code" value="{{ $rss -> code }}">
                         @if(!empty($ai_intel_code))
                         <input type="hidden" name="ai_intel_code" value="{{ $ai_intel_code }}">
                         @endif
                         <div class="row">
                             <label for="" class="col-md-12 control-label" id="label_category">Category <span class="text-danger">*</span></label>
                             <div class="col-md-12">
                                <select name="category_news[]" id="category" class="select2-option form-control" multiple="multiple" required>
                                    @foreach ($category as $item)
                                        
                                        <option value="{{ $item -> id }}"
                                            {{-- @if($RSSNews)
                                            @foreach(@$RSSNews->get_cate as $news_cate_val)
                                                @if($item->id == $news_cate_val->news_category_id)
                                                    selected
                                                @else 
                                                    
                                                @endif
                                            @endforeach
                                            @endif --}}
                                            >{{ $item -> name }}</option>
                                    @endforeach

                                </select>
                             </div>
                         </div>
                         <br>

                         {{-- <div class="row d-none">
                            <label for="" class="col-md-12 control-label" id="label_topic">Topic <span class="text-danger">*</span></label>
                            <div class="col-md-12">
                               <select name="topic[]" id="topic" class="select2-option form-control" multiple="multiple"></select>
                            </div>
                        </div> --}}
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
                                                        {{-- @php
                                                            $title_default = '';
                                                            if(@$RSSNews->title_th) {
                                                                $title_default = $RSSNews->title_th;
                                                            } else if ($rss->title) {
                                                                $title_default = $rss->title;
                                                            }
                                                        @endphp --}}
                                                        <input type="text" class="form-control" name="title_th" id="title_th" value="{{ @$rss -> title }}">
                                                    </div>
                                                </div>
            
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label" id="label_detail_th">Detail (TH)</label>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control htmleditor" name="detail_th" id="detail_th" data-id="1" required></textarea>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                        <div class="tab-pane" id="tab_en">
                                            <section class="panel-body border-n">
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label">Text (EN)</span></label>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="title_en" id="title_en" >
                                                    </div>
                                                </div>
            
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label">Detail (EN)</label>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control htmleditor" name="detail_en" id="detail_en" data-id="1"></textarea>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </div>
                             </div>
                         </div>

                        <div class="row d-none">
                            <label for="" class="col-md-12 control-label">Tag
                        </div>
                        {{-- <div class="row d-none">
                            <div class="col-md-12">
                                <select name="tags[]" id="tags" class="select2-option form-control" multiple="multiple"></select>
                            </div>
                        </div> --}}

                        <br>
                         <div class="form-group row">
                             <div class="col-lg-4">
                                <label class="control-label">Public Date </label>
                                <div class="input-group date">
                                    <input id="public_date" type="text" class="form-control datetimepicker-input"
                                    {{-- @php
                                        $date_public = '';
                                        if(@$RSSNews -> public_date) {
                                            $date_public = timePickerFormat($RSSNews -> public_date);
                                        } else if ($rss -> pubDate){
                                            $date_public = timePickerFormat($rss -> pubDate);
                                        }
                                    @endphp --}}
                                    name="public_date"
                                    data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()">
                                    <div class="input-group-addon">
                                        @icon('solid/calendar-alt', 'text-muted')
                                    </div>
                                </div>
                             </div>
                             <div class="col-lg-1">
                                <label class="control-label">Send Mail </label>
                                <div >
                                    <label class="switch">
                                        <input type="checkbox" name="sent_mail" checked value="1">
                                        <span></span>
                                    </label>
                                </div>
                             </div>
                             <div class="col-lg-1">
                                <label class="control-label">Status </label>
                                <div>
                                    <label class="switch">
                                        {{-- @php
                                        $checked_val = '';
                                            if(@$RSSNews -> status == 1) {
                                                $checked_val = 'checked';
                                            } else if(@$action == 'create') {
                                                $checked_val = 'checked';
                                            }
                                        @endphp --}}
                                        <input type="checkbox" name="status" checked value="TRUE">
                                        <span></span>
                                    </label>
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
 @include('stacks.css.datepicker')
 @include('stacks.css.form')
 @include('stacks.css.summernote')
 @endpush
 @push('pagescript')
 @include('stacks.js.form')
 @include('stacks.js.form')
@include('stacks.js.datepicker')
@include('scripts.summernote')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/cadcenter-th/fonts/thsarabunnew.css">
    <style>
    @font-face {
        font-family: 'TH SarabunPSK';
        src: url('https://cdn.jsdelivr.net/gh/cadcenter-th/fonts/thsarabunnew.eot');
        src: url('https://cdn.jsdelivr.net/gh/cadcenter-th/fonts/thsarabunnew.eot?#iefix') format('embedded-opentype'),
             url('https://cdn.jsdelivr.net/gh/cadcenter-th/fonts/thsarabunnew.woff2') format('woff2'),
             url('https://cdn.jsdelivr.net/gh/cadcenter-th/fonts/thsarabunnew.woff') format('woff'),
             url('https://cdn.jsdelivr.net/gh/cadcenter-th/fonts/thsarabunnew.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    .note-editable {
  font-family: 'TH SarabunPSK', sans-serif !important;
}
</style>
<script>

$('form').each(function () {
    if ($(this).data('validator'))
        $(this).data('validator').settings.ignore = ".note-editor *";
});

$('#detail_th').summernote('destroy');
{{--var markupStr = '{{@$RSSNews->detail_th}}';--}}
{{--$('#detail_th').summernote();--}}
{{--$('#detail_th').summernote('code', markupStr);--}}
$('#detail_th').summernote('destroy');
        $('.htmleditor').summernote({
            height: 300,
            fontSizes: ['14' ,'16' ,'18' ,'20' ,'22' ,'24' ,'26' ,'32'], // custom font size
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['fontsize', 'fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']],
            ],
            fontNames: ['TH SarabunPSK'],
            fontNamesIgnoreCheck: ['TH SarabunPSK']
        });
        @if(!empty($detail_default))
        $('#detail_th').summernote('code', {!! json_encode($detail_default) !!});
        @endif

     var form_save = '.formSaving';
    $('.formPreview').click(function() {
        form_save = '.formPreview';
    });
    $('.formDraft').click(function() {
        form_save = '.formDraft';
    });
    $('.ajaxifyForm').submit(function (event) {
        if(form_save == '.formSaving'){
            let category = $('#category option:selected').val();
            let topic = $('#topic option:selected').val();
            let title_th = $('#title_th').val();
            let detail_th = $('#detail_th').val();
            var detail_th_code = $('#detail_th').summernote('code');
            console.log(detail_th_code);
            if(category == undefined){
                $('#label_category').css('color', '#a94442');
                $('#category').css('border-color', '#a94442');
            }else{
                $('#label_category').css('color', '#656d78');
                $('#category').css('border-color', '#656d78');
            }
            {{--if(topic == undefined){
                $('#label_topic').css('color', '#a94442');
                $('#topic').css('border-color', '#a94442');
            }else{
                $('#label_topic').css('color', '#656d78');
                $('#topic').css('border-color', '#656d78');
            }--}}
            if(title_th == ''){
                $('#label_title_th').css('color', '#a94442');
                $('#title_th').css('border-color', '#a94442');
            }else{
                $('#label_title_th').css('color', '#656d78');
                $('#title_th').css('border-color', '#656d78');
            }
            if(detail_th == ''){
                $('#label_detail_th').css('color', '#a94442');
                $('#detail_th').css('border-color', '#a94442');
            }else{
                $('#label_detail_th').css('color', '#656d78');
                $('#detail_th').css('border-color', '#656d78');
            }

            if(category == undefined || title_th == '' || detail_th == ''){
                return false;
            }
        }

        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        $('.formSaving').attr('disabled',true);
        event.preventDefault();
        var detail_th_code2 = $('#detail_th').summernote('code');
            console.log(detail_th_code2);

        var data = new FormData(this);
        if(form_save == '.formPreview'){
            data.append('formsubmit', 'formPreview');
            data.append('detail_th_code2', detail_th_code2);
        }else if(form_save == '.formDraft'){
            data.append('formsubmit', 'formDraft');
            data.append('detail_th_code2', detail_th_code2);
            
        }
        axios.post($(this).attr("action"), data)
        .then(function (response) {
            if (response.data.warning) {
                // กรณีข่าวซ้ำ แสดง toastr.warning แทน success
                toastr.warning(response.data.message, 'คำเตือน');
            } else {
                toastr.success(response.data.message, '@langapp('response_status') ');
                
                 $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                 window.location.href = response.data.redirect;
            }

        })
          .catch(function (error) {
            $('.formSaving').attr('disabled',false);
            console.log(error);
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
        {{--$("#topic").select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/topic',
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
                                id: item.id,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });--}}
        {{--$("#tags").select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/tags',
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
                                id: item.id,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });--}}
        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days'),defaultDate: moment()});
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
