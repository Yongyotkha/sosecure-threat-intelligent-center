<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Add CPE </h4>
        </div>

        <div class="modal-body">

            <div class="form-group row">
                <label class="col-lg-3 control-label">OS Type <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <select name="os_type[]" id="os_type" class="select2-option form-control" style="width:100%;">
                        <option value="">Select OS Type</option>
                        @if (@$os)

                        @foreach ($os as $item)
                        <option value="{{@$item->id}}">{{@$item->name}}</option>
                        @endforeach

                        @endif
                    </select>

                    <span style="color:red;"><small id="check_os"></small></span>
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
                        <select name="cpe[]" id="cpe" class="select2-option form-control " style="min-width: 300px;">
                            <option value="">Select CPE</option>
                            {{-- @if (@$cpe)

                            @foreach ($cpe as $item)
                            <option value="{{@$item->id}},{{@$item->cpe}}">{{@$item->cpe}}</option>
                            @endforeach

                            @endif --}}

                        </select>

                        <span style="color:red;"><small id="check_cpe"></small></span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-lg-3 control-label">Remark</label>
                    <div class="col-lg-9">
                        <input type="text" name="add_remark[]" id="add_remark" class="form-control">
                        <span style="color:red;"><small id="check_remark"></small></span>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-lg-3 control-label d-md-none">&nbsp;</label>
                    <div class="col-lg-9">
                        <button class="btn btn-info" onclick="add_row()"><i class="fas fa-plus"></i> Add</button>
                    </div>
                </div>
            </div>

            <div id="chk-command" style="d-none">
                <div class="form-group row">
                    <label class="col-lg-3 control-label">IP <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <input type="text" name="name" id="ip" class="form-control">
                        <span style="color:red;"><small id="check_ip"></small></span>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Select Profile <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-8">
                                <select id="u_p" class="form-control check_test_select" required>
                                    <option value="">Choose an User</option>
                                    @if($Credentials)
                                    @foreach ($Credentials as $item)
                                    <option value="{{@$item->code}}">{{@$item->name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <span style="color:red;"><small id="check_user"></small></span>
                            </div>
                            <div class="col-lg-4 m-t-10">
                                <button class="btn btn-info" data-toggle="collapse" href="#demo"><i class="fas fa-plus"
                                        onclick="add_new()"></i>&nbsp; Add New</button>
                            </div>


                        </div>
                    </div>
                </div>

                <div id="demo" class="collapse box">

                    <fieldset class="collapsible">

                        <legend>Add Profile</legend>
                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">Name <span
                                    class="text-danger">*</span> </label>
                            <div class="col-lg-8">
                                <input type="text" id="name_new" class="form-control check_test">
                                <span style="color:red;"><small id="check_n"></small></span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">User <span
                                    class="text-danger">*</span> </label>
                            <div class="col-lg-8">
                                <input type="text" id="user_new" class="form-control check_test">
                                <span style="color:red;"><small id="check_u"></small></span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">Password <span
                                    class="text-danger">*</span> </label>
                            <div class="col-lg-8">
                                <input type="password" id="pass_new" class="form-control check_test">
                                <span style="color:red;"><small id="check_p"></small></span>
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">Site <span
                                    class="text-danger">*</span> </label>
                            <div class="col-lg-8">
                                <select id="site" class="form-control" required>
                                    <option value="">Select Site</option>
                                    @if($SiteSettings)
                                    @foreach ($SiteSettings as $item)
                                    <option value="{{@$item->id}}">{{@$item->name}}</option>
                        @endforeach
                        @endif
                        </select>
                        <span style="color:red;"><small id="check_site"></small></span>
                </div>
            </div> --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-rounded" data-toggle="collapse" data-target="#demo"
                    onclick="clear_data()">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                <button type="button" onclick="new_credentials()" class="btn btn-info btn-rounded">
                    <i class="fas fa-paper-plane"></i>
                    Save
                </button>
            </div>
            <hr>
            </fieldset>
        </div>
        <div class="form-group row">
            <label class="col-lg-3 control-label"></label>
            <div class="col-lg-9">
                <button class="btn btn-success" onclick="run_command()"><i class="fas fa-play"></i>&nbsp; Run</button>
                <div style="color:red;display: none;" id="check_run_result"><small >Error Command Please Try Later</small></div>
                <div style="color:red;"><small id="check_result"></small></div>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-lg-3 control-label">Result <span class="text-danger">*</span> </label>
            <div class="col-lg-9">
                <textarea name="" id="result" cols="30" rows="2" disabled class="form-control"></textarea>
                
            </div>
        </div>


        <div class="form-group row" id="cpe_mapping" style="display: none;">
            <label class="col-lg-3 control-label">Mapping <span class="text-danger" >*</span> </label>
            <div class="col-lg-3">
                <input type="hidden" style="display: none;" value="" name="cpe_mode" id="cpe_mode" class="form-control">
                <input type="text" name="os_information" placeholder="OS Information" id="os_information" class="form-control">
                <div style="color:rgb(133, 130, 130);"><small id="ex_os_information">Ex: debian_linux</small></div>
                <span style="color:red;"><small id="check_os_information"></small></span>
            </div>
            <div class="col-lg-6">
                <input type="text" name="cpe_information" placeholder="CPE" id="cpe_information" class="form-control">
                <div style="color:rgb(133, 130, 130);"><small id="ex_cpe_information">Ex: cpe:2.3:o:debian:debian_linux:3.0:*:*:*:*:*:*:*</small></div>
                <span style="color:red;"><small id="check_cpe_information"></small></span>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-lg-3 control-label">Remark</label>
            <div class="col-lg-9">
                <input type="text" name="name" id="remark_com" class="form-control">
                <span style="color:red;"><small id="check_remark_com"></small></span>
            </div>
        </div>


        <div class="form-group row">
            <label class="col-lg-3 control-label d-md-none">&nbsp;</label>
            <div class="col-lg-9">
                <button class="btn btn-info" onclick="add_row_command()"><i class="fas fa-plus"></i> Add</button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="table">
            <thead>
                <tr>
                    <th>CPE</th>
                    <th>Remark</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
            <tbody id="get_tr">
                {{-- <tr>
                            <td>Lorem ipsum dolor sit amet.</td>
                            <td>-</td>
                            <td>
                                <button class="btn btn-xs btn-danger">Delete</button>
                            </td>
                        </tr> --}}
            </tbody>
        </table>
    </div>
</div>




<div class="modal-footer">
    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i>
        Close</a>
    <button type="button" class="btn btn-info submit btn-rounded value_submit" onclick="save_value()"><i
            class="fas fa-paper-plane"></i> OK</button>
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

    $('#cpe').select2({
        tag: true,
        tokenSeparators: [' '],
        placeholder: 'select cpe',
        minimumInputLength: 1,
        ajax: {
            url: "{!! route('assets.select_cpe'); !!}",
            dataType: 'json',
            method: 'post',
            delay: 250,
            data: function (term) {
                var os_type = $("#os_type").val();
                return {
                    os_type: os_type,
                    term: term['term']
                };
            },
            processResults: function(data){
                return {
                    results: $.map(data, function(item){
                        return {
                            text: item.cpe,
                            id: item.cpe
                        }
                    })
                };
            },
            cache: true
        }
    });

    $('#os_type').change(function(){
        $('#cpe').val('');
        $('#cpe').text('');
    });

    $(function () {
        $('.check_test_select').select2();
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

    $(function() {

        $("#name_new").keypress(function() {

            $('#check_n').html('');

        });
        $("#user_new").keypress(function() {

            $('#check_u').html('');

        });
        $("#pass_new").keypress(function() {

            $('#check_p').html('');

        });
        $("#site").change(function() {

            $('#check_site').html('');

        });

        $("#ip").keypress(function() {

            $('#check_ip').html('');

            $("#u_p").change(function() {

                $('#check_user').html('');

            });

        });

        {{-- $("#os_type").change(function() {
            let os_id = this.value;
            if(os_id){
                $('#check_os').html('');
                $("#result").val('');
                $("#cpe_information").val('');
                $("#os_information").val('');
                $("#cpe_mode").val('');
                $("#cpe_mapping").hide();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{!! route('assets.selectCPE_by') !!}',
                    type: "get",
                    data: ({
                        os_id: os_id,
                    }),
                    datatype: "json",
                    beforeSend: function(){
                        loading('load');
                    },
                }).done(function(data){
                    $('#check_cpe').html('');
                    let text_select = '';
                    text_select += '<option selected value="">Select CPE</option>';
                    $.each(data, function(key, val){
                        text_select += '<option value="'+val.id+','+val.cpe+'">'+val.cpe+'</option>';
                    });
                    $('#cpe').html(text_select);
                    loading('stop_load');

                }).fail(function(jqXHR, ajaxOptions, thrownError){
                    loading('stop_load');
                    console.log("No response from server");
                });
            }else{
                let text_select = '';
                text_select += '<option selected value="">Select CPE</option>';
                $('#cpe').html(text_select);
            }
            
        }); --}}


        $("#cpe").change(function() {

            $('#check_cpe').html('');

        });


        $("#result").keypress(function() {

            $('#check_result').html('');

        });

    });
    function add_new() {

        $("#name_new").val('');
        $("#user_new").val('');
        $("#pass_new").val('');
        $('#site').val('').trigger('change');
        $('#check_p').html('');
        $('#check_u').html('');
        $('#check_n').html('');
        $('#check_site').html('');
    }

    function clear_data() {

        $("#name_new").val('');
        $("#user_new").val('');
        $("#pass_new").val('');
        $('#site').val('').trigger('change');
        $('#check_p').html('');
        $('#check_u').html('');
        $('#check_n').html('');
        $('#check_site').html('');
    }

    

    function new_credentials() {
        name_new = $('#name_new').val();
        user_new = $('#user_new').val();
        password_new = $('#pass_new').val();
        site = $('#site').val();

    
        if(name_new==''){
            $('#check_n').html('Please fill out.');

        }else if(user_new==''){
            $('#check_u').html('Please fill out.');

        }else if(password_new==''){
            $('#check_p').html('Please fill out.');

        }else if(site==''){
            $('#check_site').html('Please select site.');

        }else{
            $.ajax({
                type:"POST",
                url:"{{ route('assets.assets_add_user') }}",
                data:{
                    name:name_new,
                    password:password_new,
                    user:user_new,
                    site:@json($assets->site_id),
                    
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) {
                    loading('stop_load');
                    
                    if(response.message!=''){   
                        var data = {
                        id: response.id,
                        text: response.name,
                        };
                        var newOption = new Option(data.text, data.id, false, false);
                        $('#u_p').append(newOption).trigger('change');
                        $('#u_p').val(data.id).trigger('change');
                        $("div.box").collapse("hide");
                        
                        toastr.success(response.message, '@langapp('response_status')');
                    }else{

                    toastr.error('There is already this name in the system.', '@langapp('response_status')');

                    }


                    
                },
                error: function (error){
                    loading('stop_load');
                    var errors = error.response.data.errors;
                    var errorsHtml = '';
                    $.each(errors, function (key, value) {
                        errorsHtml += '<li>' + value[0] + '</li>';
                    });
                    toastr.error(errorsHtml, '@langapp('response_status') ');
                }

            });
        }
    }
    var convertedIntoArray = [];

    function add_row() {
        

        var os_type = $("#os_type").val();
        var cpe = $("#cpe").val();
        var res = cpe.split(",");
        var add_remark = $("#add_remark").val();

        if(os_type==''){
            $('#check_os').html('Please select os type.');
        {{-- }else if(res.length==0){ --}}
        }else if(cpe == null){
            $('#check_cpe').html('Please select cpe.');
        }else{
            {{-- <td>${res[1]}</td> --}}
            html=``;
            html+=`<tr>
            <td>${cpe}</td>
            <td>${add_remark}</td>
            <td style="display:none;">${os_type}</td>
            <td style="display:none;">${cpe}</td>
            <td><button class="btn btn-xs btn-danger" onclick="del_row(this)"><i class="fas fa-trash"></i></button></td>
            </tr>`;

            $('#get_tr').append(html);

            $('#cpe').val('').trigger('change');
            {{--$('#os_type').val('').trigger('change');--}}
            $("#add_remark").val('');
        }      
       
    };

    function add_row_command() {
        var os_type = $("#os_type").val();        
        var remark_com = $("#remark_com").val();
        var result = $("#result").val();
        var u_p = $("#u_p").val();  
        var os_information = $("#os_information").val();
        var cpe_information = $("#cpe_information").val();
        var cpe_mode = $("#cpe_mode").val();
        if(os_type==''){
            $('#check_os').html('Please select os type.');
        }else if(u_p==''){           
            $('#check_user').html('Please select user');
        }else if(result==''){
            $('#check_result').html('Please Run Command.');
        }else if(os_information==''){
            $('#check_os_information').html('Please fill out.');
        }else if(cpe_information==''){
            $('#check_cpe_information').html('Please fill out.');
        }else{
            html=``;
            html+=`<tr>
            <td>${cpe_information}</td>
            <td>${remark_com}</td>
            <td style="display:none;">${os_type}</td>
            <td><button class="btn btn-xs btn-danger" onclick="del_row(this)"><i class="fas fa-trash"></i></button></td>
            <td style="display:none;">${u_p}</td>
            <td style="display:none;">${os_information}</td>
            <td style="display:none;">${cpe_mode}</td>
            </tr>`;

            $('#get_tr').append(html);

            $('#u_p').val('').trigger('change');
            {{--$('#os_type').val('').trigger('change');--}}
            $("#remark_com").val('');
            $("#result").val('');
            $("#ip").val('');
            $("#cpe_information").val('');
            $("#os_information").val('');
            $("#cpe_mode").val('');
            $("#cpe_mapping").hide();
            $('#check_run_result').hide();
        }      
       
    };

    function run_command(){
        let ip = $("#ip").val();  
        let u_p = $("#u_p").val();  
        let os_id = $("#os_type").val();
        if(os_id==''){
            $('#check_os').html('Please select os type.');
        }else if(ip==''){
            $('#check_ip').html('Please fill out.');
        }else if(u_p==''){
            $('#check_user').html('Please select user.');
        }else{
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('assets.run_artisan_cpe') !!}',
                type: "post",
                data: ({
                    ip: ip,
                    u_p: u_p,
                    os_id: os_id,
                }),
                datatype: "json",
                beforeSend: function(){
                    $('#check_run_result').hide();
                    $("#cpe_mapping").hide();
                    loading('load');
                },
            }).done(function(data){
                let data_parse = JSON.parse(data);
                if(data_parse.Result==1){
                    $("#result").val(data_parse.result);
                    $("#cpe_mode").val(data_parse.mode);
                    if(data_parse.mode==1){
                        $("#cpe_information").val(data_parse.cpe);
                        $("#os_information").val(data_parse.os_name);
                    }else{
                        $('#cpe_mapping').show();
                    }
                }else{
                    $('#check_run_result').show();
                }
                loading('stop_load');
            }).fail(function(jqXHR, ajaxOptions, thrownError){
                $('#check_run_result').show();
                
                loading('stop_load');
                console.log("No response from server");
            });
        }



    }

    function del_row(ctl) {
        $(ctl).parents("tr").remove();
    }

    function save_value() {
        $("table#table tr").each(function() {
            var rowDataArray = [];
            var actualData = $(this).find('td');
            if (actualData.length > 0) {
                actualData.each(function() {
                    rowDataArray.push($(this).text());
                });
                convertedIntoArray.push(rowDataArray);
            }
        });

        var x = document.getElementById("table").rows.length;

        if(x==1){
            toastr.error('No Data', '@langapp('response_status') ');
        }else{
            $.ajax({
                type:"POST",
                url:"{{ route('assets.assets_add_data') }}",
                data:{
                    data:convertedIntoArray,
                    assets:@json($assets),
                    page:"{{$menu}}",
                    idip:"{{$idip}}",
                    iddomain:"{{$iddomain}}",
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) {
                    loading('stop_load');
                    $('.value_submit').attr('disabled',true);
                    toastr.success(response.message, '@langapp('response_status') ');
                    window.location.href = response.redirect;
                },
                error: function (error){
                    loading('stop_load');
                    var errors = error.response.data.errors;
                    var errorsHtml = '';
                    $.each(errors, function (key, value) {
                        errorsHtml += '<li>' + value[0] + '</li>';
                    });
                    toastr.error(errorsHtml, '@langapp('response_status') ');
                }

            });
        }
        
        
    }


</script>

@endpush

@stack('pagestyle')
@stack('pagescript')