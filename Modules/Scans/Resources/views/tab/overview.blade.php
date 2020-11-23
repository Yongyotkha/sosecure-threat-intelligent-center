<header class="header b-b clearfix">
    <div class="panel-body text-center">
        <div class="row">
            <div class="col-md-4">
                <div class="card-overview">
                    <div class="card-text-ov">
                        <h3>Total Data Elements</h3>
                        <h1>{{ $site -> elements }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <table class="table">
                    @foreach($DataScans as $data)
                    <tr>
                        <td class="d-flex align-items-center justify-content-center">
                            <div class="progress-circle {{ round($data -> total > 50) ? 'over50' : '' }}  p{{ round($data -> total) }}">
                                <div class="left-half-clipper">
                                   <div class="first50-bar"></div>
                                   <div class="value-bar"></div>
                                </div>
                             </div>
                        </td>
                        <td><a href="">{{ $data -> data_type }}</a></td>
                        <td>{{ $data -> total }}</td>
                    </tr>
                    @endforeach
                </table>
                <div class="text-center">
                    <a href="{{ route('scans.index', ['tab' => 'datatype', 'site_code' => $site -> code]) }}" class="btn btn-default btn-rounded">
                        More
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <table class="table table-striped">
                    <tr>
                        <td>Name :</td>
                        <td>{{ $site -> get_domain -> domain }}</td>
                    </tr>
                    <tr>
                        <td>Target(s) :</td>
                        <td>{{ $site -> get_domain -> domain }}</td>
                    </tr>
                    <tr>
                        <td>Started :</td>
                        <td>{{ $site -> created_at }}</td>
                    </tr>
                    <tr>
                        <td>Completed :</td>
                        <td>{{ $site -> updated_at }}</td>
                    </tr>
                    <tr>
                        <td>Status :</td>
                        <td>
                            {{ get_name_scan_status($site -> progress) }}
                        </td>
                    </tr>
                </table>
                <div class="text-center">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        Logs
                    </button>
                    @if(@$site -> progress !== 3)
                        <button type="button" class="btn btn-success btn-rounded" disabled>
                            <i class="fas fa-play"></i>
                            Run Scan Now
                        </button>
                    @else
                        <a href='{{ route("get.scans.redo_process", ["id" => $site->code]) }}' class='btn btn-success btn-rounded' data-toggle='ajaxModal'>
                            <i class="fas fa-play"></i>
                            Run Scan Now
                        </a>
                    @endif
                   
                </div>
            </div>
        </div>
    </div>
</header>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')

@endpush
