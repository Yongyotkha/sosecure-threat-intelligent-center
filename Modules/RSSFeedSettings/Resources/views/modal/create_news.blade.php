<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> News</h4>
        </div>
        {!! Form::open(['route' => ['rssfeedsettings.rss_data_store_news'], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'POST', 'files' => true]) !!}
                 <div class="modal-body">
                     <div class="container-fluid">
                         <div class="row">
                             <div class="col-md-4">
                                <h5>Date :</h5>
                                <h5>Update : {{ $rss -> transcation_datetime }}</h5>
                             </div>
                             <div class="col-md-8">
                                 <h5>Name : {{ $rss -> title }}</h5>
                                 <h5>URL : <a href="{{ $rss -> link }}" target="_blank">{{ $rss -> link }}</a> 
                                    &nbsp;<a href="{{ $rss -> link }}" target="_blank" class="btn btn-xs btn-info">Open</a>
                                    <button type="button" class="btn btn-xs btn-info" onclick="copy_link('{{ $rss -> link }}');">Copy</button>
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
                         <input type="hidden" name="rss_code" value="{{ $rss -> code }}">
                         <div class="form-group row">
                            <label for="" class="col-lg-12 control-label">Source <span class="text-danger">*</span></label>
                            <div class="col-lg-12">
                                <input type="text" class="form-control" name="source" value="{{ $rss -> link }}" required>
                            </div>
                        </div>
                         <div class="row">
                             <label for="" class="col-md-12 control-label">Category <span class="text-danger">*</span></label>
                             <div class="col-md-12">
                                <select name="category_news[]" id="category" class="select2-option form-control" multiple="multiple" required>
                                    @foreach ($category as $item)
                                        <option value="{{ $item -> id }}">{{ $item -> name }}</option>
                                    @endforeach
                                </select>
                             </div>
                         </div>
                         <div class="row">
                            <label for="" class="col-md-12 control-label">Tag <span class="text-danger">*</span></label>
                            <div class="col-md-12">
                               <select name="tags[]" id="tags" class="select2-option form-control" multiple="multiple" required>
                                    <option value="0">0</option>
                               </select>
                            </div>
                        </div>
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
                                                    <label for="" class="col-lg-12 control-label">Text (TH) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="title_th" required>
                                                    </div>
                                                </div>
            
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label">Detail (TH) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control markdownEditor" name="detail_th" data-id="1" required></textarea>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                        <div class="tab-pane" id="tab_en">
                                            <section class="panel-body border-n">
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label">Text (EN) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="title_en" id="title_en">
                                                    </div>
                                                </div>
            
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label">Detail (EN) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control markdownEditor" name="detail_en" id="detail_en" data-id="1"></textarea>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </div>
                             </div>
                         </div>

                         <div class="form-group row">
                             <div class="col-lg-6">
                                <label class="col-lg-4 control-label">Public Date </label>
                                <div class="col-lg-8">
                                    <div class="input-group date">
                                        <input id="send_date" type="text" class="form-control datetimepicker-input"
                                        value="{{  timePickerFormat($rss -> pubDate) }}" name="public_date"
                                        data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()" required>
                                        <div class="input-group-addon">
                                            @icon('solid/calendar-alt', 'text-muted')
                                        </div>
                                    </div>
                                </div>
                             </div>
                             <div class="col-lg-6">
                                <label class="col-lg-4 control-label">Status </label>
                                <div class="col-lg-8">
                                    <label class="switch">
                                        <input type="hidden" value="FALSE" name="">
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
                    <button type="submit" class="btn btn-success formPreview submit btn-rounded"><i class="fas fa-eye"></i> Preview</button>
                    {!! renderAjaxButton() !!}
                 </div>
                {!! Form::close() !!}
         </div>
     </div>
 </div>

 @push('pagestyle')
 @include('stacks.css.datepicker')
 @include('stacks.css.form')
 @endpush
 @push('pagescript')
 @include('stacks.js.form')
 @include('stacks.js.fullscreen')
 @include('partial.ajaxify')
 @include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.markdown')
<script>
    $(document).ready(function(){
        $("#tags").select2({
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
        });
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
