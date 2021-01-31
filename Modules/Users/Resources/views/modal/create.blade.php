<div class="modal-dialog modal-dialog-aside">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">@icon('solid/plus') @langapp('create')  </h4>
            </div>
    
    
            <div class="modal-body">
    
                <div class="panel-body">
    
                {!! Form::open(['route' => 'users.api.save', 'class' => 'bs-example form-horizontal ajaxifyForm_custom']) !!}
                   
    
                    <input class="display-none" type="hidden" name="username"/>
                    <input class="display-none" type="hidden" name="password"/>
    
    
                    {{-- <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label>@langapp('username')  @required</label>
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="col-md-6">
                                <label>@langapp('password')  </label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            
                        </div>
                    </div> --}}
    
    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Name @required</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                    </div>
    
    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label>@langapp('email') @required</label>
                                <input type="email" name="email" class="form-control" placeholder="you@domain.com" required>
                            </div>
                            {{-- <div class="col-md-6">
                                <label>@langapp('company')</label>
    
                                <select class="select2-option width100" name="company">
                                        <option value="-">None</option>
                                        @foreach (Modules\Clients\Entities\Client::select('id', 'name')->get() as $company)
                                            <option value="{{ $company->id }}">{{  $company->name  }}</option>
                                        @endforeach
    
    
                                </select>
    
                            </div> --}}
                        </div>
                    </div>
    
    
                    {{-- <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label>@langapp('address')  </label>
                                <input type="text" name="address" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>@langapp('country')  </label>
                                <select class="select2-option form-control" name="country">
                                    @foreach (DB::table('countries')->select('name')->get() as $country)
                                        <option value="{{  $country->name  }}" {{ $country->name == get_option('company_country') ? 'selected' :'' }}>{{  $country->name  }}</option>
                                    @endforeach
    
                                </select>
                            </div>
                        </div>
                    </div> --}}
    
                    {{-- <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label>@langapp('city')</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>@langapp('state')</label>
                                <input type="text" name="state" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label>@langapp('zipcode')</label>
                                <input type="text" name="zip_code" class="form-control">
                            </div>
                        </div>
                    </div> --}}
    
    
                    {{-- <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label>@langapp('phone')</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>@langapp('mobile')</label>
                                <input type="text" name="mobile" class="form-control">
                            </div>
                        </div>
                    </div> --}}
    
    
                    {{-- <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label>@langapp('website')  </label>
                                <input type="text" name="website" class="form-control" placeholder="https://workice.com">
                            </div>
                            <div class="col-md-6">
                                <label>Twitter</label>
                                <input type="text" name="twitter" class="form-control">
                            </div>
                        </div>
                    </div> --}}
    
    
                    {{-- <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label>@langapp('hourly_rate')  </label>
    
                                <input type="text" class="form-control" name="hourly_rate" placeholder="22">
                            </div>
    
                            <div class="col-md-6">
                                <label class="display-block">@langapp('department')  </label>
    
    
                                <select name="department[]" class="select2-option form-control" multiple="multiple">
                                        @foreach (App\Entities\Department::all() as $d)
                                            <option value="{{  $d->deptid  }}">
                                                {{  $d->deptname  }} 
                                            </option>
                                        @endforeach
                                </select>
    
    
                            </div>
                        </div>
                    </div> --}}
    
    
                    {{-- <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>@langapp('locale')  {{ get_option('locale') }}</label>
                                    <select class="select2-option form-control" name="locale">
                                        @foreach (locales() as $loc)
                                            <option value="{{ $loc['code']  }}" {{ get_option('locale') == $loc['code'] ? 'selected' : ''  }}>
                                                {{  ucfirst($loc['language'])  }} - {{ $loc['code'] }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="col-md-6">
                                    <label>Skype</label>
                                    <input type="text" placeholder="john.doe" name="skype" class="form-control">

                                </div>
                            </div>
                        </div> --}}

                <div class="form-group">
                    <div class="row">

                        <div class="col-md-12">

                            <label class="display-block">@langapp('roles')</label>
                            <select name="role_id" class="select2-option form-control" onchange="check_role(value)" ><!--multiple="multiple"-->
                                @foreach (Role::whereNotIn('id', [3])->get() as $role)
                                    <option value="{{ $role->id }}" {{  $role->name == get_option('default_role') ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                </div>

                <div class="form-group" id="area_select_site">
                    <div class="row">

                        <div class="col-md-12">

                            <label class="display-block">Site</label>
                            {{-- <select name="site[]" class="select2-option form-control" multiple="multiple"><!--multiple="multiple"-->
                                @foreach (Modules\SiteSettings\Entities\SiteSettings::select()->get() as $role)
                                    <option value="{{ $role->name }}" {{  $role->name == get_option('default_role') ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select> --}}


                            <select name="site[]" id="select-site" class="select2-option form-control select-site" multiple="multiple">
                                {{-- <option value="">Select Site</option> --}}
                                @if($SiteSettings)
                                @foreach($SiteSettings as $SiteSettings_val)
                                <option value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                                @endforeach
                                @endif
                            </select>

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="display-block">Status </label>
                            <div class="col-lg-12">
                                <label class="switch">
                                    <input type="checkbox" name="active" value="TRUE" checked>
                                    <span></span>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

               

                    @include('partial.privacy_consent')
    
                    <div class="modal-footer">
                        
                        {!! closeModalButton() !!}
                        {!! renderAjaxButton() !!}
    
    
                    </div>
    
                    {!! Form::close() !!}
    
    
                </div>

            </div>
    
    
        </div>

    </div>
    
    
    @push('pagestyle')
    @include('stacks.css.form')
    @endpush
    @push('pagescript')
    @include('stacks.js.form')
    {{-- @include('partial/ajaxify') --}}

    <script>

        $(document).ready(function () {
            $('#role').select2();
        });


   
        var form_save = '.formSaving';
        $('.ajaxifyForm_custom').submit(function (event) {
            event.preventDefault();
    
                $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                
                var data = new FormData(this);
                if(form_save == '.formSavingAndRun'){
                    data.append('formsubmit', 'formSavingAndRun');
                }else if(form_save == '.formPreview'){
                    data.append('formsubmit', 'formPreview');
                }else if(form_save == '.formDraft'){
                    data.append('formsubmit', 'formDraft');
                }
                axios.post($(this).attr("action"), data)
                    .then(function (response) {
                            toastr.success(response.data.message, '@langapp('response_status') ');
                            $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                            window.location.href = response.data.redirect;
                })
                .catch(function (error) {
                    if(error.response.data.exception){
                        toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                        $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                    }else{
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


        function check_role(val) {
            console.log(val);
            if(val == 1) {
                $("#select-site").prop("disabled",true);
                $("#area_select_site").css("display","none");
            } else {
                $("#select-site").prop("disabled",false);
                $("#area_select_site").css("display","block");
            }
        }



    </script>


    
    @endpush
    
    @stack('pagestyle')
    @stack('pagescript')