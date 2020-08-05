@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <div class="bc-head">@langapp('compromised')</div>
            <div class="btn-group pull-right">
                <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">
                    @langapp('filter')
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#">
                            @langapp('Last Hour')
                        </a>
                  </li>
                    <li><
                        a href="#">@langapp('all') </>
                    </li>
                    <a href="{{  route('clients.create') }}"
                    class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" data-toggle="ajaxModal"
                    title="@langapp('create') " data-placement="bottom">
                    @icon('solid/plus') @langapp('create')
                </a>
                
                <a href="{{  route('clients.import')  }}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" title="@langapp('import_clients') " data-placement="bottom" data-toggle="ajaxModal">
                    @icon('solid/cloud-upload-alt') @langapp('import')
                </a>
                <a href="{{  route('clients.export')  }}"
                    class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" title="CSV" data-placement="bottom">
                    @icon('solid/cloud-download-alt') CSV
                </a>
                </ul>
            </div>
            
        </header>
        <section class="scrollable wrapper bg-white">
            <form method="POST" action="" accept-charset="UTF-8" class="bs-example form-horizontal ajaxifyForm">
                <section class="panel panel-default">
                    <header class="panel-heading"> @langapp('settings') </header>
                    <div class="panel-body">
                        <div class="form-group"><label class="col-lg-3 control-label">Log Server <span class="text-danger">*</span></label>
                            <div class="col-lg-6">
                                <select id="log_server" name="log_server" class="form-control">
                                    <option value="TCP" selected="">TCP</option>
                                    <option value="UDP">UDP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <header class="panel-heading"> @langapp('log_formatting') </header>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-lg-3 control-label">@langapp('volnerability') <span class="text-danger">*</span></label>
                            <div class="col-lg-6">
                                <input type="text" name="" class="form-control" value="" required="">
                            </div>
                        </div>
                        <div class="form-group"><label class="col-lg-3 control-label">@langapp('compromised') </label>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" value="" name="">
                            </div>
                        </div>
                        <div class="form-group"><label class="col-lg-3 control-label">@langapp('data_leak') </label>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" value="" name="">
                            </div>
                        </div>
                    </div>

                    <header class="panel-heading"> @langapp('email_setting') </header>
                    <div class="panel-body">
                        <div class="form-group"><label class="col-lg-3 control-label">@langapp('email') </label>
                            <div class="col-lg-6">
                                <input type="email" class="form-control" value="" name="">
                            </div>
                        </div>
                        <div class="form-group">  
                            <label class="col-sm-3 control-label">Options</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="checkbox">
                                            <label>
                                                <input type="hidden" value="FALSE" name="">
                                                <input type="checkbox" name="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="" data-original-title="">IOC</span>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="hidden" value="FALSE" name="">
                                                <input type="checkbox" name="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="" data-original-title="">News</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="checkbox">
                                            <label>
                                                <input type="hidden" value="FALSE" name="">
                                                <input type="checkbox" name="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="" data-original-title="">ETC</span>
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="hidden" value="FALSE" name="">
                                                <input type="checkbox" name="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="" data-original-title="">Data Leak</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-footer">
                        <button type="submit" class="btn btn-info formSaving submit btn-rounded">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </section>
            </form>
        </section>
    </section>
    
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')

<script>
    $(document).ready(function () {
        $('#log_server').select2();
    });
</script>
@endpush
@endsection