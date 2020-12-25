<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')  - {{ $Site_keywords->name }}</h4>
        </div>
        {!! Form::open(['route' => ['keyword.update', 'id' => $Site_keywords->id], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}

        <input type="hidden" name="id" value="{{  $Site_keywords->id  }}">

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" name="name" class="form-control" value="<?=@$Site_keywords->name?>">
                </div>
            </div>

           
            <div class="form-group row">
                <label class="col-lg-4 control-label">Type <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <select name="type" id="type" class="select2-option form-control select-site" style="min-width: 300px;">
                        <option  value="social" {{ $Site_keywords->type=='social'  ? 'selected="selected"' : "" }}>Social</option>
                        <option  value="darkweb" {{$Site_keywords->type=='darkweb'  ? 'selected="selected"' : "" }}>Dark Web</option>
                    </select>
                </div>
            </div>
 
            <div class="form-group row">
                <label class="col-lg-4 control-label">Status </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="checkbox" name="status" checked value="1" {{$Site_keywords->status == 1 ? 'checked' : ''}}>
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
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@endpush

@stack('pagestyle')
@stack('pagescript')