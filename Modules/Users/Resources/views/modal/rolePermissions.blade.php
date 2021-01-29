<div class="modal-dialog modal-dialog-aside">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white"> @langapp('permissions') - {{ ucfirst($role->name) }}</h4>
            </div>
            <div class="modal-body">
    
                {!! Form::open(['route' => ['users.roles.changePerm', $role->id], 'class' => 'bs-example form-horizontal ajaxifyForm_custom']) !!}
    
    
                <input type="hidden" name="role_id" value="{{ $role->id }}">
    
                {{-- @foreach (\Spatie\Permission\Models\Permission::select('name', 'description')->orderBy('name', 'asc')->get() as $permission)

                    <div class="">
                        <label>
                            <input type="checkbox" name="perm[{{ $permission->name }}]" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                            <span class="label-text">{{ humanize($permission->name) }} - <span class="text-muted small">{{ $permission->description }}</span></span>
                        </label>
                    </div>
    
    
                <div class="line line-dashed line-lg pull-in"></div>
    
                @endforeach --}}

                <ul class="role-group">
                    @php  $i=1;  @endphp
                    @foreach($menus AS $menu)
                    <li>
                        <div class="role-main">
                            <span class="role-click" onclick="openrole(this,'role-{{$i}}')">@if(count($menu->get_menu_sub) > 0)@icon('solid/plus')@else <i class="fas fa-minus icon"></i>  @endif</span>
                            <span class="checkbox chk-inline">
                                <label>
                                    @if(in_array($menu->code,$site_menu_permission))
                                    @php $checked = 'checked'; @endphp
                                    @else
                                    @php $checked = ''; @endphp
                                    @endif
                                    <input type="checkbox" name="menu[]" {{$checked}} {{--checked=""--}} value="{{$menu->code}}">
                                    <span class="label-text" data-rel="tooltip" title="">{{$menu->name}}</span>
                                </label>
                            </span>
                        </div>
                        
                        @if(count($menu->get_menu_sub) > 0)
                            <ul id="role-{{$i}}" class="role-group-sub">
                            @foreach($menu->get_menu_sub as $menu_sub) 
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                @if(in_array($menu_sub->code,$site_menu_sub_permission))
                                                @php $checked = 'checked'; @endphp
                                                @else
                                                @php $checked = ''; @endphp
                                                @endif
                                                <input type="checkbox" name="menu_sub[]" {{$checked}} {{--checked=""--}} value="{{$menu_sub->code}}">
                                                <span class="label-text" data-rel="tooltip" title="">{{$menu_sub->name}}</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                            </ul>
                        @endif
                        

                    </li>
                    @php $i++; @endphp
                    @endforeach
                    
                </ul>
                
    
                <div class="modal-footer">
                    {!! closeModalButton() !!}
                    {!! renderAjaxButton() !!}
                </div>
    
                {!! Form::close() !!}
    
    
            </div>
    
        </div>
    </div>
    
    {{-- @include('partial.ajaxify') --}}


    <script>
       function openrole(onck,id){
            $('#'+id).slideToggle(150);
        }
        var form_save = '.formSaving';
        $('.formSavingAndRun').click(function() {
            form_save = '.formSavingAndRun';
        });
        $('.formPreview').click(function() {
            form_save = '.formPreview';
        });
        $('.formDraft').click(function() {
            form_save = '.formDraft';
        });
       
            $('.ajaxifyForm_custom').submit(function (event) {
                    $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                    event.preventDefault();
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
                                {{--window.location.href = response.data.redirect;--}}
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
       
    
        
    </script>