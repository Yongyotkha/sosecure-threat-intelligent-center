@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('indicators')</div>
            {{-- <div class="btn-group pull-right">

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
                    <li><a href="#">@langapp('all') </a></li>
                </ul>

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
            </div> --}}

        </header>
        <section class="scrollable wrapper bg-white">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="">Indicator Type</label>
                        <select name="" id="role" class="select2-option form-control" multiple="multiple">
                            <option value="1">All</option>
                            <option value="2">CIDR</option>
                            <option value="3">CVE</option>
                            <option value="4">Domain</option>
                            <option value="5">Email</option>
                            <option value="6">FileHash-IMPHASH</option>
                            <option value="7">FileHash-MD5</option>
                            <option value="8">FileHash-PEHASH</option>
                            <option value="9">FileHash-SHA1</option>
                            <option value="10">FileHash-SHA256</option>
                            <option value="11">FilePath</option>
                            <option value="12">Hostname</option>
                            <option value="13">IPv4</option>
                            <option value="14">IPv6</option>
                            <option value="15">Mutex</option>
                            <option value="16">NIDS</option>
                            <option value="17">URI</option>
                            <option value="18">URL</option>
                            <option value="19">YARA</option>
                            <option value="20">Osquery</option>
                            <option value="21">Bitcoinaddress</option>
                            <option value="22">Ssl Certfinger Print</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="">Role</label>
                        <select name="" id="indicator_type" class="select2-option form-control" multiple="multiple">
                            <option value="1">Adware</option>
                            <option value="2">Backdoor</option>
                            <option value="3">Bruteforce</option>
                            <option value="4">Command & Control</option>
                            <option value="5">Delivery Email</option>
                            <option value="6">Document Exploit</option>
                            <option value="7">File Scanning</option>
                            <option value="8">Hacking Tools</option>
                            <option value="9">Hunting</option>
                            <option value="10">Macro Malware</option>
                            <option value="11">Malvertising</option>
                            <option value="12">Malware Hosting</option>
                            <option value="13">Memory Scanning</option>
                            <option value="14">PCAP Scanning</option>
                            <option value="15">Phishing</option>
                            <option value="16">RAT</option>
                            <option value="17">Ransomware</option>
                            <option value="18">Scanning Host</option>
                            <option value="19">Trojan</option>
                            <option value="20">Unknown</option>
                            <option value="21">Web Attack</option>
                            <option value="22">Worm</option>
                        </select>
                    </div>
                </div>
               
                <div class="col-md-3">
                    <div class="form-group">
                      <label for="" class="">Date</label>
                      <select name="" id="date" class="select2-option form-control">
                          <option value="Last 24 hours">Last hour</option>
                          <option value="Last 24 hours">Last 24 hours</option>
                          <option value="Last 24 hours">Last 7 days</option>
                          <option value="Last 24 hours">Last 30 days</option>
                          <option value="Last 24 hours" selected>All Time</option>
                      </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group m-b-md">
                        <label for="" class="">Keyword</label>
                        <input type="text" class="form-control" name="keyword" placeholder="Search">
                        {{-- <div class="input-group">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-info btn-icon">
                                    <i class="fas fa-search"></i>
                                </button>
                            </span>
                        </div> --}}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-info">
                        <i class="fas fa-search"></i>
                        <span> Search </span>
                    </button>
                    <button class="btn btn-defualt">
                        <i class="fas fa-broom"></i>
                        <span> Clear </span>
                    </button>
                </div>
            </div>
            <section class="">
                <div class="row header-badge">
                    <div class="col-md-6 p-l-r-0">
                        <span class="font-weight-bold">We've found 29 indicators</span>
                    </div>
                    <div class="col-md-6 p-l-r-0 text-right">
                        <div class="btn-group">
                            <button class="btn btn-dark btn-sm dropdown-toggle" data-toggle="dropdown">
                                @langapp('sort_by')
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="#">Recently Modified</a></li>
                                <li><a href="#">Least Recently Modified</a></li>
                                <li><a href="#">Recently Created</a></li>
                                <li><a href="#">Least Recently Created</a></li>
                                <li><a href="#">Name Ascending</a></li>
                                <li><a href="">Name Descending</a></li>
                                <li><a href="">Type Ascending</a></li>
                                <li><a href="">Tyoe Decending</a></li>
                                <li><a href="{{ route('clients.index') }}">@langapp('all') </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="show-indicators">
                    <ul class="list-indicators">
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                    </ul>
                </div>
            </section>
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
        $('#indicator_type').select2({
            placeholder:'Indicator Type',
        });
        $('#role').select2({
            placeholder:'Role',
        });
        $('#date').select2({
            placeholder:'Role',
        });
    });
</script>
@endpush
@endsection