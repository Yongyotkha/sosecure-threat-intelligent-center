<header class="header b-b clearfix">
    <section class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    {!! Form::open(['class' => 'bs-example form-horizontal ajaxifyForm validator']) !!}     
    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-4 control-label">Open Scan </label>
                                <div class="col-lg-8">
                                    <label class="switch">
                                        <input type="hidden" value="FALSE" name="">
                                        <input type="checkbox" name="" value="TRUE">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group row">
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
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="col-lg-4 control-label">Domain <span class="text-danger">*</span> </label>
                                <div class="col-lg-8">
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-4 control-label">Scan Interval <span class="text-danger">*</span> </label>
                                <div class="col-lg-8">
                                    <select name="" id="scan_interval" class="select2-option form-control" multiple>
                                        <option value="1">Option</option>
                                        <option value="1">Option</option>
                                        <option value="1">Option</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-lg-4 control-label">Status </label>
                                <div class="col-lg-8">
                                    <label class="switch">
                                        <input type="hidden" value="FALSE" name="">
                                        <input type="checkbox" name="" value="TRUE">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        <div class="panel-footer"><button type="submit" class="btn btn-info formSaving submit btn-rounded"><i class="fas fa-paper-plane"></i> Save</button></div>
    </section>

    </header>
    
    @push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
    @endpush
    
    @push('pagescript')
    @include('stacks.js.datatables')
    @include('stacks.js.form')
    
    <script>
        $(document).ready(function () {
            $('#scan_interval').select2();
        });

        $('ul.role-group-sub').hide();
        function openrole(onck,id){
            $('#'+id).slideToggle(150);
        }
    </script>
    @endpush
