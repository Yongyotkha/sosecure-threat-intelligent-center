<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')  - {{ $domain->name }}</h4>
        </div>
        {!! Form::open(['route' => ['domainsettings.update', 'id' => $domain->id], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}

        <input type="hidden" name="id" value="{{  $domain->id  }}">

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" name="name" class="form-control" value="<?=@$domain->name?>">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-4 control-label">Domain <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" name="domain" class="form-control" value="<?=@$domain->domain?>">
                </div>
            </div>
            <div class="form-group row" style="display: none;">
                <label class="col-lg-4 control-label">Open Scan </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" name="open_scan" value="TRUE">
                        <span></span>
                    </label>
                </div>
            </div>

            <div class="form-group row" style="display: none;">
                <label class="col-lg-4 control-label"></label>
                <div class="col-lg-8">
                    <ul class="role-group">
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-1')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Content Analysis</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-1" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-2')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Crawling and Scanning</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-2" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-3')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">DNS</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-3" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-4')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Leaks, Dumps and Breaches</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-4" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-5')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Passive DNS</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-5" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-6')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Real World</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-6" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-7')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Social Media</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-7" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>


            <div class="form-group row" style="display: none;">
                <label class="col-lg-4 control-label">Scan Interval <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <select name="" id="scan_interval" class="select2-option form-control" multiple>
                        <option value="1">15</option>
                        <option value="2">30</option>
                        <option value="3">60</option>
                    </select>
                </div>
            </div>
 
            <div class="form-group row">
                <label class="col-lg-4 control-label">Default </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="checkbox" name="default" value="1" {{$domain->domain_default == 1 ? 'checked' : ''}}
                        {{$Domain_count == 1 ? 'disabled' : ''}}>
                        <span></span>
                    </label>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-4 control-label">Status </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="checkbox" name="status" value="1" {{$domain->status == 1 ? 'checked' : ''}}>
                        <span></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                {!! closeModalButton() !!}
                {!! renderAjaxButton() !!}
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
@endpush

@stack('pagestyle')
@stack('pagescript')