@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('compromised') > Assets Setting</div>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span data-rel="tooltip" title="@langapp('export') CSV">@icon('solid/download') CSV</span>
            </a>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right" value="bulk-delete">
                <span data-rel="tooltip" title="Are You Sure !">@icon('solid/trash-alt') @langapp('delete')</span>
            </button>

            <button type="submit" id="button" class="btn btn-sm btn-default pull-right" value="">
                <span data-rel="tooltip" title="Stop" data-placement="top">@icon('solid/stop')</span>
            </button>

            <button type="submit" id="button" class="btn btn-sm btn-default pull-right" value="">
                <span data-rel="tooltip" title="" data-original-title="Re-run">@icon('solid/redo')</span>
            </button>
            

            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#create_assets_compromised">
                @icon('solid/plus') @langapp('create')
            </a>

            <div class="pull-right" style="margin-top: 9px;">
                <select name="" id="" class="select2-option form-control select-site" style="min-width: 100px">
                    <option value="1">All Site</option>
                </select>
            </div>

        </header>

        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <div class="table-responsive">
                    <table  class="table table-striped" id="table-compromised-setting-template">
                        <thead>
                            <tr>
                                <th class="hide"></th>
                                <th class="no-sort">
                                    <label>
                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </th>
                                <th>@langapp('name')</th>
                                <th>@langapp('target')</th>
                                <th>@langapp('started')</th>
                                <th>@langapp('finished')</th>
                                <th>@langapp('status')</th>
                                <th>@langapp('element')</th>
                                <th>@langapp('corelations')</th>
                                <th>@langapp('update')</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </section>
        </section>

    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


    <!-- Modal create_assets_compromised -->
    <div class="modal fade" id="create_assets_compromised" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Asset</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" placeholder="name">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-md-3">Target Scan</label>
                        <div class="col-md-9">
                            <textarea name="" id="" cols="30" rows="8" class="form-control">
                                Scan target(s), which may be one or more domains, hostnames, IPv4 and v6 addresses, subnets (e.g. 1.2.3.0/24), ASNs,
                                phone numbers (must be prefixed with + and have no spaces dashes or brackets, e.g. +1555123123), e-mail addresses, usernames or human names.
                            </textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-md-3">Start Scan</label>
                        <div class="col-md-9">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="" value="TRUE">
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
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
    $('#table-compromised-setting-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection