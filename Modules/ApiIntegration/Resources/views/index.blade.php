@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('settings') - @langapp('api_integration')</div>
            <a href="{{  route('users.export')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                @icon('solid/download') CSV
            </a>
            <a href="{{  route('apiintegration.create')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="ajaxModal">
                @icon('solid/plus') @langapp('create')
            </a>
            
        </header>
        <section class="scrollable wrapper">              
            <form method="POST" action="" accept-charset="UTF-8" class="bs-example form-horizontal ajaxifyForm">
                <div class="container-fluid">
                    <div class="bd-ol-ct m-t-xs">
                        <div class="row">
                            <div class="col-md-6 m-b-md">
                                <label for="">Credential</label>
                                <input type="text" class="form-control" readonly value="https://otx.alientvault.com">
                            </div>
                            <div class="col-md-6 m-b-md">
                                <label for="">Key API</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="bd-ol-ct m-t-xs">
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="row">
                                    <div class="col-xs-4 col-md-8 m-b-xs">
                                        <label for="">Function</label>
                                        <input type="text" class="form-control m-b-sm" value="/api/v1/indicator/export">
                                    </div>
                                    <div class="col-xs-4 col-md-2 m-b-xs text-center">
                                        <label for="">Method</label>
                                        <div class="d-flex-center"><span class="method-get">GET</span></div>
                                    </div>
                                    <div class="col-xs-4 col-md-2 m-b-xs">
                                        <label for="">Remark</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="row">
                                    <div class="col-xs-4 col-md-8 m-b-xs">
                                        <input type="text" class="form-control m-b-sm" value="/api/v1/indicator/submit_file/">
                                    </div>
                                    <div class="col-xs-4 col-md-2 m-b-xs text-center">
                                        <div class="d-flex-center"><span class="method-post">POST</span></div>
                                    </div>
                                    <div class="col-xs-4 col-md-2 m-b-xs">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12">
                                <div class="row">
                                    <div class="col-xs-4 col-md-8 m-b-xs">
                                        <input type="text" class="form-control m-b-sm" value="/api/v1/indicator/submited_files">
                                    </div>
                                    <div class="col-xs-4 col-md-2 m-b-xs text-center">
                                        <div class="d-flex-center"><span class="method-get">GET</span></div>
                                    </div>
                                    <div class="col-xs-4 col-md-2 m-b-xs">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="footer-action">
                        <button type="submit" class="btn btn-info formSaving submit btn-rounded">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <!-- Modal Gen Key -->
    <div class="modal fade" id="create_key" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Create Key</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Key <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                        <div class="input-group">
                            <input type="text" class="form-control" name="generate_key" value="" readonly>
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-info">Gen</button>  
                            </span>
                        </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Status </label>
                        <div class="col-lg-6">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="" value="TRUE">
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>
    
</section>





@push('pagestyle')
    @include('stacks.css.datatables')
@endpush

@push('pagescript')
@include('stacks.js.datatables')

<script>
$(function() {
    $('#table-vm-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection