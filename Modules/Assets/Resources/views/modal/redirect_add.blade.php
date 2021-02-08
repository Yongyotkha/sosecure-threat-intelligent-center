<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Add Assets </h4>
        </div>
        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-3 control-label">Site <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <select id="site" class="select2-option form-control" style="width:100%;">
                        @if (@$SiteSettings)
                        <option value="">- Select Site -</option>
                            @foreach ($SiteSettings as $item)
                                <option value="{{ @$item->code }}">{{ @$item->name }}</option>
                            @endforeach

                        @else
                        <option value="Error Your Site Don't Have Scan Assets">Your Site Don't Have Scan Assets</option>
                        @endif

                    </select>
                    <span style="color:red;"><small id="check_os"></small></span>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i>
                    Close</a>
                <button type="button" class="btn btn-info submit btn-rounded value_submit" onclick="redirect_to_add_assets();">
                    <i class="fas fa-paper-plane"></i> Continue
                </button>
            </div>
        </div>
    </div>

</div>

@push('pagestyle')
    @include('stacks.css.form')
@endpush
@push('pagescript')
    @include('stacks.js.form')
    @include('stacks.js.fullscreen')
    @include('partial.ajaxify')

    <script>

    function redirect_to_add_assets() {
        let selectedValue = $('#site').children("option:selected").val();
        let url = "/scans/scans-domain/asset/"+selectedValue;
        let target = '';
        if(selectedValue===""){
            toastr.error("Please Select Site", '@langapp('response_status') ');
        }else{
            if(selectedValue == "Error Your Site Don't Have Scan Assets"){
                toastr.error("Your Site Don't Have Scan Assets", '@langapp('response_status') ');
            }else if(target == '_blank') { 
                window.open(url, target);
            } else {
                window.location = url;
            }
        }
    }

    </script>

@endpush

@stack('pagestyle')
@stack('pagescript')
