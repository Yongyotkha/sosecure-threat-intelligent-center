<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@langapp('make_changes')  - {{ $categorySettings->name }}</h4>
        </div>
        {!! Form::open(['route' => ['categorysettings.api.update', 'id' => $categorySettings->id], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}

        <input type="hidden" name="id" value="{{  $categorySettings->id  }}">

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <div class="">
                        <input type="text" class="form-control" name="name" value="{{$categorySettings->name}}">
                        
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-6">
                    <label class="switch">
                        <input type="checkbox" name="active" value="1" {{$categorySettings->active == 1 ? 'checked' : ''}}>
                        <span></span>
                    </label>
                </div>
            </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('partial.ajaxify')
@endpush

@stack('pagestyle')
@stack('pagescript')