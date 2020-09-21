<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">New Site </h4>
        </div>
        {!! Form::open(['route' => 'sitesettings.api.save', 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'files' => true]) !!}

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <div class="">
                        <input type="text" class="form-control" name="name" value="">
                        
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Descript  </label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="text" class="form-control" name="descript" value=""> --}}
                        <textarea id="descript" name="descript" rows="4" style="width: 100%;"></textarea>
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
                        <textarea id="address" name="address" rows="4" style="width: 100%;"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Remark </label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="textarea" class="form-control" name="remark" value=""> --}}
                        <textarea id="remark" name="remark" rows="4" style="width: 100%;"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-6">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" checked name="active" value="1">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- @include('partial.privacy_consent') --}}
        
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