<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Add CPE </h4>
        </div>
    
        <div class="modal-body">

            <div class="form-group row">
                <label class="col-lg-3 control-label">Type <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <select name="" id="" class="select2-option form-control" style="width:100%;">
                        <option value="window">Window</option>
                        <option  value="linux">Linux</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 control-label">Select <span class="text-danger">*</span> </label>
                <div class="col-lg-3">
                    <label> 
                        <input type="radio" name="cpe_radio" value="add" checked> 
                        <span class="label-text">Add</span>
                    </label>
                </div>
                <div class="col-lg-3">
                    <label> 
                        <input type="radio" name="cpe_radio" value="command"> 
                        <span class="label-text">Command</span>
                    </label>
                </div>
            </div>

            <div id="chk-add" style="d-none">
                <div class="form-group row">
                    <label class="col-lg-3 control-label">CPE <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <select name="" id="" class="select2-option form-control " style="min-width: 300px;">
                            <option value="window">cpe:2.3:a:microsoft</option>
                        </select>
                    </div>
                </div>
    
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Remark <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <input type="text" name="name" class="form-control">
                    </div>
                </div>
    
                <div class="form-group row">
                    <div class="col-lg-12 text-center">
                        <button class="btn btn-info">Add</button>
                    </div>
                </div>
            </div>

            <div id="chk-command" style="d-none">
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Select Profile <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-8">
                                <select name="" id="" class="select2-option form-control">
                                    <option value="select-profile">Select Profile</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <button class="btn btn-info"><i class="fas fa-plus"></i>Add New</button>
                            </div>
                            <div class="col-lg-12 m-t-10">
                                <button class="btn btn-success btn-block"><i class="fas fa-play"></i>Run</button>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Result <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <textarea name="" id="" cols="30" rows="2" class="form-control"></textarea>
                    </div>
                </div>
    
                <div class="form-group row">
                    <div class="col-lg-12 text-center">
                        <button class="btn btn-info">Add</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <td>CPE</td>
                            <td>Remark</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Lorem ipsum dolor sit amet.</td>
                            <td>-</td>
                            <td>
                                <button class="btn btn-xs btn-danger">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>



        
        <div class="modal-footer">
            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
        </div>
        {!! Form::close() !!}
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
    $(function () {
        $('#chk-add').hide();
        $('#chk-command').hide();
        
        if($('input[name=cpe_radio]:checked').val() == "add"){
            $('#chk-add').show();
            $('#chk-command').hide();
        }

        $("input[name=cpe_radio]:radio").click(function(){
            if($('input[name=cpe_radio]:checked').val() == "add"){
                $('#chk-add').show();
                $('#chk-command').hide();
            }
            else if($('input[name=cpe_radio]:checked').val() == "command"){
                $('#chk-command').show();
                $('#chk-add').hide();
            }
        });
 
    });
</script>
@endpush

@stack('pagestyle')
@stack('pagescript')
