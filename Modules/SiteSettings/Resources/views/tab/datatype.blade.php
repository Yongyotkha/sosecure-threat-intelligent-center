<header class="header b-b clearfix">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th class="text-center" width="10%">Elements</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left">Account On External Site</td>
                            <td class="text-center">47</td>
                        </tr>
                        <tr>
                            <td class="text-left">Affliate - IP Address</td>
                            <td class="text-center">81</td>
                        </tr>
                        <tr>
                            <td class="text-left">BGP AS Membership</td>
                            <td class="text-center">1</td>
                        </tr>
                        <tr>
                            <td class="text-left">Co-Hosted Site</td>
                            <td class="text-center">1</td>
                        </tr>
                        <tr>
                            <td class="text-left">Company Name</td>
                            <td class="text-center">1</td>
                        </tr>
                        <tr>
                            <td class="text-left">Country</td>
                            <td class="text-center">1</td>
                        </tr>
                        <tr>
                            <td class="text-left">DNS SRV Record</td>
                            <td class="text-center">1</td>
                        </tr>
                        <tr>
                            <td class="text-left">Domain Name</td>
                            <td class="text-center">1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <section class="">
                    <div class="row header-badge">
                        <div class="col-md-6 p-l-r-0"><span class="font-weight-bold"> <a href="">Data summary</a>  > <a href="">Data Type:Affilate - IP Address</a>  (81 Results)</span>
                        </div>
                        <div class="col-md-6 p-l-r-0 text-right">
                            <div class="pull-right p-r-sm">
                                <div class="form-group form-custom" style="margin-bottom:0;  width:200px;background: #263042 !important;">
                                    <div class="input-group" style="padding:.8rem;"><span
                                            class="input-group-btn icon-search"><i class="fas fa-search"></i></span><input
                                            type="text" class="form-control form-transparent" name="keyword"
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>  
                        </div>
                    </div>
                    <div class="show-datatype">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th  class="text-center"><h1>Data Element</h1></th>
                                        <th  class="text-center"><h1>Source Data Element</h1></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><h4>202.94.73.147</h4></td>
                                        <td><h4>wehelp.bacc.or.th</h4></td>
                                    </tr>
                                    <tr>
                                        <td><h4>202.94.73.147</h4></td>
                                        <td><h4>wehelp.bacc.or.th</h4></td>
                                    </tr>
                                    <tr>
                                        <td><h4>202.94.73.147</h4></td>
                                        <td><h4>wehelp.bacc.or.th</h4></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
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

<script>
    $(document).ready(function () {
        $('#indicator_type').select2({
            placeholder: 'Indicator Type',
        });
        $('#role').select2({
            placeholder: 'Role',
        });

        $('.nav-link').on('click', function () {
            $('.active-link').removeClass();
            $(this).addClass('active-link')
        });
    });
</script>
@endpush