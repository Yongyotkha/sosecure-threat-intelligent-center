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
                            @if (@$cpe)

                            @foreach ($cpe as $item)
                            <option value="{{@$item->id}},{{@$item->cpe}}">{{@$item->cpe}}</option>
                            @endforeach

                            @endif

                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-lg-3 control-label">Remark <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <input type="text" name="add_remark[]" id="add_remark" class="form-control">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-lg-12 text-center">
                        <button class="btn btn-info"  onclick="add_row()">Add</button>
                    </div>
                </div>
            </div>

            <div id="chk-command" style="d-none">
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Select Profile <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-8">
                                <select id="u_p" class="form-control check_test_select" required>
                                    <option value="">Choose an User</option>
                                    @if($Credentials)
                                    @foreach ($Credentials as $item)
                                    <option value="{{@$item->id}}">{{@$item->name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <button class="btn btn-info" data-toggle="collapse" href="#demo"><i class="fas fa-plus"
                                        onclick="add_new()"></i>Add New</button>
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
                            <button type="button" class="btn btn-danger btn-rounded" data-toggle="collapse"
                                data-target="#demo" onclick="clear_data()">
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
                        <button class="btn btn-success btn-block"><i class="fas fa-play"></i>&nbsp; Run</button>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 control-label">IP <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <input type="text" name="name" class="form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Remark <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <input type="text" name="name" class="form-control">
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
                        <button class="btn btn-info" onclick="add_row()">Add</button>
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
                    <tbody id ="get_tr">
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
            <button type="button" class="btn btn-info submit btn-rounded delete_webdefacement_submit" onclick="save_value()"><i
                    class="fas fa-paper-plane" ></i> OK</button>
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

        {{--$("#ip").keypress(function() {

            $('#check_i').html('');

            $("#u_p").change(function() {

                $('#check_u_p').html('');

            });

        });--}}

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
                    site:site,
                    
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) {
                    loading('stop_load');
                    
                    console.log(response.message);
                    
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

        html=``;
        html+=`<tr>
        <td>${res[1]}</td><td>${add_remark}</td>
        <td><button class="btn btn-xs btn-danger" onclick="del_row(this)">Delete</button></td>
        <td style="display:none;">${os_type}</td>
        <td style="display:none;">${res[0]}</td>
        </tr>`;

        $('#get_tr').append(html);


        $('#cpe').val('').trigger('change');
        $('#os_type').val('').trigger('change');
        $("#add_remark").val('');


        
       
    };

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

        console.log(convertedIntoArray);
    }


</script>

@endpush

@stack('pagestyle')
@stack('pagescript')