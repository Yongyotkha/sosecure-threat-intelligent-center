<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@langapp('make_changes')  - {{ $siteSettings->name }}</h4>
        </div>
        {!! Form::open(['route' => ['sitesettings.api.update', 'id' => $siteSettings->id], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}

        <input type="hidden" name="id" value="{{  $siteSettings->id  }}">

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <div class="">
                        <input type="text" class="form-control" name="name" value="{{$siteSettings->name}}">
                        
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Descript  </label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="text" class="form-control" name="descript" value=""> --}}
                        <textarea id="descript" name="descript" rows="4" style="width: 100%;">{{$siteSettings->descript}}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Logo </label>
                <div class="col-lg-9">
                    <div class="">
                        <input type="file" class="form-control" name="logo" value="">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Address </label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="text" class="form-control" name="address" value=""> --}}
                        <textarea id="address" name="address" rows="4" style="width: 100%;">{{$siteSettings->address}}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Remark </label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="textarea" class="form-control" name="remark" value=""> --}}
                        <textarea id="remark" name="remark" rows="4" style="width: 100%;">{{$siteSettings->remark}}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-6">
                    <label class="switch">
                        <input type="checkbox" name="active" value="1" {{$siteSettings->active == 1 ? 'checked' : ''}}>
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