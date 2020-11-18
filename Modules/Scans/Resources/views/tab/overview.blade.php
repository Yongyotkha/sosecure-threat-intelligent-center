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
                        <td>
                            <div class="progress--circle progress--{{ round($data -> total) }}"></div>
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
                    <button type="submit" class="btn btn-success btn-rounded  {{ round($data -> total > 0) ? '' : 'disabled' }}">
                        <i class="fas fa-play"></i>
                        Run Scan Now
                    </button>
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
