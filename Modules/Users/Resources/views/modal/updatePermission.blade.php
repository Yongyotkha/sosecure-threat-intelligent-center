<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">@icon('solid/pencil-alt') @langapp('make_changes')  - {{ humanize($permission->name) }}</h4>
        </div>


        {!! Form::open(['route' => ['users.perm.update', 'id' => $permission->id], 'method' =>'PUT', 'class' => 'bs-example form-horizontal ajaxifyForm']) !!}
        <div class="modal-body">

        <input type="hidden" name="id" value="{{ $permission->id }}">

        <div class="form-group">
                <label class="col-lg-3 text-left control-label">@langapp('name')   <span
                class="text-danger">*</span></label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" value="{{ $permission->name }}" name="name">
                </div>
        </div>

        <div class="form-group">
                <label class="col-lg-3 text-left control-label">@langapp('description')   <span
                class="text-danger">*</span></label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" value="{{ $permission->description }}" name="description">
                </div>
        </div>

        </div>
        <div class="modal-footer">

            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
            
            </div>
    {!! Form::close() !!}

    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->

@include('partial.ajaxify')