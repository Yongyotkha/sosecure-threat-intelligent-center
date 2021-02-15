<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Add Data Leak</h4>
        </div>

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-3 control-label">Site <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <select name="" id="" class="select2-option form-control" required>
                        <option value=""></option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Type <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <select name="" id="" class="select2-option form-control" required>
                        <option value="">Public</option>
                        <option value="">Dark Web</option>
                        <option value="">Web Server</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Source </label>
                <div class="col-lg-9">
                    <input type="text" class="form-control">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
        </div>
    </div>
</div>

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
<script>
    $(document).ready(function () {
        $('.select2-option').select2();
    });
</script>
@endpush