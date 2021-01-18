<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')  - {{ $user->name }}</h4>
        </div>
        {!! Form::open(['route' => ['user.update', 'id' => $user->code], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}

        <input type="hidden" name="id" value="{{  $user->id  }}">

        <div class="modal-body">


            <div class="row">
                <div class="col-md-12 mb-3">
                    {{-- <button type="submit" class="btn btn-info"> Support Password </button> --}}
                    <span class="" style="background-color: #3869d4; color: #fff; padding: 5px; border-radius: 5px;">Support Password</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th class="text-center">Password</th>
                            <th class="text-center">Date Expried</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <span>{{$user->email}}</span>
                            </td>
                            <td class="text-center">
                                <button type="button" id="btn_gen_pass" data-code="{{$user->code}}" class="btn btn-success btn-rounded"> Gen Copy Password </button>
                                <input type="hidden" id="p1">
                            </td>
                            <td class="text-center">
                                <span id="time_pass_expire"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


  
 
            <div class="modal-footer">
                {!! closeModalButton() !!}
                {{-- {!! renderAjaxButton() !!} --}}
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

    <script>
        $(document).ready(function() {
            $("#btn_gen_pass").click(function() {
                $(this).prop("disabled",true);
                let user_code = $(this).data("code");
                fn_gen_pass(user_code);
            });
        });

        function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).val()).select();
            setTimeout(function(){ document.execCommand("copy"); }, 1000);
            document.execCommand("copy");
            $temp.remove();
        }

        function fn_gen_pass(user_code) {
        
            var uuid = '{{ \Illuminate\Support\Str::uuid() }}';
        
            axios.post('{{route('user.process_gen_pass')}}', {
                test: '',
                user_code: user_code,
                pass: uuid,
            }).then(function (response) {
                $("#btn_gen_pass").prop("disabled",false);
                toastr.success(response.data.message, '@langapp('response_status')');
                console.log(response);
                $("#time_pass_expire").text(response.data.time_pass_expire);
                $("#p1").val(response.data.pass);
                setTimeout(function(){ copyToClipboard('#p1'); }, 3000);
                copyToClipboard('#p1');
            }).catch(function (error) {
                var errors = error.errors;
                var errorsHtml = "";
                $.each(errors, function (key, value) {
                    errorsHtml += "<li>" + value[0] + "</li>";
                });
                toastr.error(errorsHtml, '@langapp('response_status')');
            });
        }

    </script>
@endpush

@stack('pagestyle')
@stack('pagescript')