    <div class="modal-dialog modal-dialog-aside">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> New Site </h4>
            </div>
        
            {!! Form::open(['route' => 'sitesettings.save', 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'files' => true]) !!}
            <div class="modal-body">
                <div class="form-group row">
                    <label for="" class="col-md-3">Content</label>
                    <div class="col-md-9">
                        <textarea name="detail_th" class="form-control htmleditor" id="" cols="30" rows="10">
                        </textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="" class="col-md-3">Status</label>
                    <div class="col-md-9">
                        <select id="status_action" class="form-control select2">
                            <option value="1">Approved</option>
                            <option value="2">Cancle</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="" class="col-md-3">Send Mail</label>
                    <div class="col-md-9">
                        <label>
                            <input type="checkbox" name="sent_mail" class="" value="true">
                            <span class="label-text">Sent mail to customers</span>
                        </label>
                    </div>
                </div>

                <label></label>

            </div>

            {{-- @include('partial.privacy_consent') --}}
            
            {{-- <div class="modal-footer">
                {!! closeModalButton() !!}
                {!! renderAjaxButton() !!}
            </div> --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                <button type="button" onclick="change_status()" class="btn btn-info btn-rounded">
                    <i class="fas fa-paper-plane"></i>
                    Save
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>


</div>

@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@include('stacks.css.summernote')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@include('stacks.js.datepicker')
@include('scripts.summernote')


    <script>
        $(document).ready(function () {
            $('#categorys').select2({
                placeholder:'Categorys',
            });
        });

        $('form').each(function () {
            if ($(this).data('validator'))
                $(this).data('validator').settings.ignore = ".note-editor *";
        });

        $('#detail_th').summernote('destroy');
        



    </script>
@endpush

@stack('pagestyle')
@stack('pagescript')
