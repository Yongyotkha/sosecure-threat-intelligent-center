<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Control Agent</h4>
        </div>
        {!! Form::open() !!}
        <div class="modal-body">
            {{-- <div class="row mt-2">
                <div class="col-lg-12">
                    <button class="btn btn-info">
                        คำสั่ง
                    </button>
                    <button class="btn btn-info">
                        คำสั่ง
                    </button>
                    <button class="btn btn-info">
                        คำสั่ง
                    </button>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-lg-12">
                    <label for="">
                    CMD :
                    </label>
                    <textarea class="form-control" name="" id="" cols="30" rows="10">

                    </textarea>
                    <div class="text-center  mt-2">
                        <button class="btn btn-info btn-block">Add</button>
                    </div>
                </div>
            </div>
            <br>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Agent</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>MTSC</td>
                        <td>192.10.1.1</td>
                        <td>Scan</td>
                        <td><span class="text-success">กำลังทำงาน</span></td>
                        <td>04-20-2022</td>
                    </tr>
                </tbody>
            </table> --}}

            <div class="row mt-2">
                <div class="col-lg-12">
                    <p>Batch Job Every Day</p>
                </div>
                <div class="col-lg-2">
                    <span>start:</span>
                </div>
                
                <div class="col-lg-10">
                    <select name="batch_start" id="batch_start" class="text-left select2-option form-control select-site" >
                        <?php for($hours=0; $hours<24; $hours++) // the interval for hours is '1'
                            for($mins=0; $mins<60; $mins+=30) // the interval for mins is '30'
                            echo '<option value="'.str_pad($hours,2,'0',STR_PAD_LEFT).':'
                            .str_pad($mins,2,'0',STR_PAD_LEFT).':00">'.str_pad($hours,2,'0',STR_PAD_LEFT).':'
                            .str_pad($mins,2,'0',STR_PAD_LEFT).'</option>';
                        ?>
                    </select>
                </div>

                <div class="col-lg-12 ">
                    <p>Real-time protection</p>
                    <label>
                        <input name="real_time" value="Y" id="real_time" type="checkbox" class="select-chk">
                        <span class="label-text">On</span>
                    </label>
                </div>

                <div class="col-lg-12">
                    <p>USB Protection</p>
                    <label>
                        <input name="usb" value="Y" id="usb" type="checkbox" class="select-chk">
                        <span class="label-text">On</span>
                    </label>
                   
                </div>

            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            <button type="submit" class="btn btn-info formSaving btn-rounded"><i class="fas fa-paper-plane"></i>Submit</button>
        </div>
        
        {!! Form::close() !!}
    </div>
</div>



<script>
    var form_save = '.formSaving';
    $('#form_delete_agent').submit(function (event) {
        event.preventDefault();

        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        $('.formSaving').attr('disabled',true);
        
        var data = new FormData(this);

        axios.post($(this).attr("action"), data)
            .then(function (response) {
                toastr.success('Deleted Successfully');
                $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                if(error.response.data.exception)
                {
                    $('.formSaving').attr('disabled',false);
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
                else
                {
                    $('.formSaving').attr('disabled',false);
                    var errors = error.response.data.errors;
                    var errorsHtml= '';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>'; 
                    });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
            }); 

    });
</script>

