<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')  - {{ $rssfeedsettings->name }}</h4>
        </div>
        {!! Form::open(['route' => ['rssfeedsettings.update', 'id' => $rssfeedsettings->code], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => false]) !!}

        <input type="hidden" name="id" value="{{  $rssfeedsettings->code  }}">

        <div class="modal-body">

            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <input type="text" id="name_rss" name="name_rss" class="form-control" value="{{@$rssfeedsettings->name}}">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">URL
                    <span data-rel="tooltip" title="" data-original-title="URL"> <i class="far fa-question-circle"></i></span>
                    <span class="text-danger">*</span>
                </label>
                <div class="col-lg-9">
                    <input type="url" class="form-control" id="url_rss" name="url_rss" value="{{@$rssfeedsettings->url}}" >
                </div>
            </div>

            
            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-6">
                    <label class="switch">
                        <input type="checkbox" name="active" value="TRUE" {{$rssfeedsettings->status == 1 ? 'checked' : ''}}>
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
        </div>
        {!! Form::close() !!}
</div>

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@endpush

@stack('pagestyle')
@stack('pagescript')
