@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">

            <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                <div class="header-flex-overflow m-t-7">
                    <div class="fwb-16">
                        <a href="javascript:history.back()" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                            @icon('solid/arrow-left')
                        </a>
                        <span>
                            Site Setting > {{ $site -> get_site -> name }} | Scan Domain : {{ $site -> get_domain -> domain }}
                        </span>
                    </div>

                    <div class="ml-2 text-right">
                            @if($tab == 'datatype' )
                            <div class="button-control pull-right">
                                
                                <div class="btn-group">
                                    <button data-target="#asset_to_use" data-toggle="modal" class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="asset-to-use" disabled="disabled"> Asset</button>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance" href="#advance-search"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></a>
                                </div>
                                <div class="btn-group d-none">
                                    <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">Group By
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-left">
                                        <li>
                                            <a href="#">
                                                Internet Name
                                            </a>
                                        </li>   
                                        {{-- <li>
                                            <a href="#">
                                                Affiliate - Internet Name
                                            </a>
                                        </li>   
                                        <li>
                                            <a href="#">
                                                Affiliate - Domain Name
                                            </a>
                                        </li>    --}}
                                        <li>
                                            <a href="#">
                                                Domain Name
                                            </a>
                                        </li>  
                                        <li>
                                            <a href="#">
                                                IP Address
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                IPv6 Address
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a href="#">
                                                Malicious Internet Name
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                Human Name
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                Internet Name
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                Email Address
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                Domain Name (Parent)
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                Phone Number
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @if($tab == 'asset' )
                        <div class="button-control pull-right">
                            <div class="btn-group">
                                <button data-target="#asset_to_use_manual" data-toggle="modal" class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="asset-to-use-manual">Add Assets To Use</button>
                            </div>
                            <div class="btn-group">
                                <a class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance" href="#advance-search"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" style="display:none;" disabled>
                                    <span data-rel="tooltip" title="Delete" data-placement="top">@icon('solid/trash-alt') @langapp('delete')</span>
                                </button>
                            </div>
        
                        </div>
                        @endif
                        &nbsp;
                    </div>
                </div>
            </header>

            <section class="scrollable wrapper" style="padding:0;">
                <div class="sub-tab text-uc small m-b-sm">

                    <ul class="nav pro-nav-tabs nav-tabs-dashed">
                        <li class="{{ ($tab == 'overview') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'overview', 'site_code' => $site -> code]) }}">
                                @icon('solid/database') @langapp('overview')
                            </a>
                        </li>

                        <li class="{{ ($tab == 'datatype') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'datatype', 'site_code' => $site -> code]) }}">
                                @icon('solid/folder-open') Data
                            </a>
                        </li>
                        <li class="{{ ($tab == 'asset') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'asset', 'site_code' => $site -> code]) }}">
                                @icon('solid/folder-open') Asset
                            </a>
                        </li>
                        {{-- <li class="{{ ($tab == 'settings') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'settings', 'site_code' => $site -> code]) }}">
                                @icon('solid/life-ring') @langapp('settings')
                            </a>
                        </li>
                        <li class="{{ ($tab == 'logs') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'logs', 'site_code' => $site -> code]) }}">
                            @icon('solid/clock') @langapp('logs')
                            </a>
                        </li> --}}
                    </ul>

                </div>

                @include('scans::tab.'.$tab)
                
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


        <!-- Modal Scans -->
        <div class="modal in fixed-left" id="asset_to_use" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Asset To Use
                    </h4>
                </div>
                <div class="modal-body">
                    <div id="show_asets" class="row"></div>
                </div>

                <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="save_assets()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

     <!-- Modal Scans -->
        <div class="modal in fixed-left" id="asset_to_use_manual" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Asset To Use
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-3" style="padding-left: 3rem">
                            <h3 class="text-dark">Assets</h3>
                        </div>
                        <div class="col-xs-9" style="padding-left: 6rem">
                            <h3 class="text-dark" >Referent</h3>
                        </div>
                    </div>
                    <div id="show_asets_manual"> </div>
                    <button type="button" class="btn btn-sm btn-info m-xs" style="margin-left: 1.8rem;" onclick="add_new_assets_manual()">
                        <span>@icon('solid/plus')  Add Assets
                    </button>
                </div>

                <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="save_assets_manual()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
@endpush

@push('pagescript')
@include('stacks.js.fullscreen')
<script>
    function view_cve_details(domain_id, site_id) {
        $('#modal_cve_detail').modal('show');
        $('#cve_detail_content').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        
        axios.post('{{ route('scans.get_cve_details') }}', {
            domain_id: domain_id,
            site_id: site_id
        })
        .then(function (response) {
            let html = '<div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th style="width: 150px; min-width: 150px; white-space: nowrap;">CVE ID</th><th style="width: 180px;">Severity</th><th>Description</th></tr></thead><tbody>';
            if(response.data.data.length > 0){
                response.data.data.forEach(item => {
                    let badgeColor = '#777';
                    let textColor = 'white';
                    
                    let severity = item.severity ? item.severity.toUpperCase() : 'UNKNOWN';
                    
                    if(severity == 'CRITICAL') { 
                        badgeColor = '#e64732'; 
                        textColor = 'white';
                    }
                    else if(severity == 'HIGH') { 
                        badgeColor = '#fcc838'; 
                        textColor = 'black'; 
                    }
                    else if(severity == 'MEDIUM') { 
                        badgeColor = '#f2ff15'; 
                        textColor = 'black'; 
                    }
                    else if(severity == 'LOW') { 
                        badgeColor = '#88ce4f'; 
                        textColor = 'white';
                    }
                    else if(severity == 'INFORMATION' || severity == 'INFO' || severity == 'NONE') { 
                        badgeColor = '#00dcff'; 
                        textColor = 'black';
                    }

                    let scoreHtml = item.cvss_score ? `<span style="padding: 1px 4px; font-weight: bold; margin-right: 5px;">${item.cvss_score}</span>` : '';

                    html += `<tr>
                        <td style="white-space: nowrap;"><a href="https://nvd.nist.gov/vuln/detail/${item.namecve}" target="_blank" class="text-info font-bold">${item.namecve}</a></td>
                        <td><span class="badge" style="background-color: ${badgeColor}; color: ${textColor}; padding: 4px 10px; font-size: 11px; display: inline-block; min-width: 100px; text-align: left; border-radius: 12px;">${scoreHtml}${severity}</span></td>
                        <td><small>${item.description}</small></td>
                    </tr>`;
                });
            } else {
                html += '<tr><td colspan="4" class="text-center">No CVE details found.</td></tr>';
            }
            html += '</tbody></table></div>';
            $('#cve_detail_content').html(html);
        })
        .catch(function (error) {
            console.log(error);
            $('#cve_detail_content').html('<div class="text-danger">Error loading data.</div>');
        });
    }
</script>

<!-- Modal CVE Detail -->
<div class="modal fade" id="modal_cve_detail" tabindex="-1" role="dialog" aria-labelledby="cveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="width: 90%; max-width: 1400px;" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="cveModalLabel"><i class="fas fa-bug"></i> CVE Details</h5>
            </div>
            <div class="modal-body" id="cve_detail_content">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection
