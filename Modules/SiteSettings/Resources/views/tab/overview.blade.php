<header class="header b-b clearfix">
    <div class="panel-body text-center">
        <div class="row">
            <div class="col-md-4">
                <div class="card-overview">
                    <div class="card-text-ov">
                        <h3>Total Data Elements</h3>
                        <h1>3,216</h1>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <table class="table">
                    <tr>
                        <td>
                            <div class="progress--circle progress--25"></div>
                        </td>
                        <td><a href="">Target Website</a></td>
                        <td>1,793</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="progress--circle progress--100"></div>
                        </td>
                        <td><a href="">CommonCrawl</a></td>
                        <td>381</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="progress--circle progress--80"></div>
                        </td>
                        <td><a href="">Certificate Transparency</a></td>
                        <td>193</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="progress--circle progress--80"></div>
                        </td>
                        <td><a href="">DNS</a></td>
                        <td>116</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="progress--circle progress--50"></div>
                        </td>
                        <td><a href="">File Metadata Extactor</a></td>
                        <td>102</td>
                    </tr>
                </table>
                <div class="text-center">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        More
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <table class="table table-striped">
                    <tr>
                        <td>Name :</td>
                        <td>baac.or.th</td>
                    </tr>
                    <tr>
                        <td>Target(s) :</td>
                        <td>baac.or.th</td>
                    </tr>
                    <tr>
                        <td>Started :</td>
                        <td>2020-07-21 04:47:10</td>
                    </tr>
                    <tr>
                        <td>Completed :</td>
                        <td>2020-07-21 05:02:45</td>
                    </tr>
                    <tr>
                        <td>Status :</td>
                        <td>ABORTED</td>
                    </tr>
                </table>
                <div class="text-center">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        Logs
                    </button>
                    <button type="submit" class="btn btn-success btn-rounded">
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