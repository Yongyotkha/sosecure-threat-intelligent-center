<header class="header b-b clearfix">
    <div class="panel-body">
        <div class="hide-fillter" style="margin-bottom: 1rem">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group m-b-md">
                        <label for="" class="">Keyword</label>
                        <input type="text" class="form-control" name="keyword" placeholder="Search">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="">Datatype Type</label>
                        <select name="" id="datatype" class="select2-option form-control" multiple="multiple">
                            <option value="1">All</option>
                        </select>
                    </div>
                </div>
    
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group m-b-md">
                        <label for="" class="">Referent</label>
                        <input type="text" class="form-control" name="keyword" placeholder="">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group m-b-md">
                        <label for="" class="d-block">&nbsp;</label>
                        <button class="btn btn-info">
                            <i class="fas fa-search"></i>
                            <span> Search </span>
                        </button>
                        <button class="btn btn-default">
                            <i class="fas fa-broom"></i>
                            <span> Clear </span>
                        </button>
                    </div>

                </div>
            </div>
            <div class="row">
               
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Referent</th>
                            <th style="width: 20px" class="text-center">Status</th>
                            <th style="width: 20px" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                secureserver.net
                            </td>
                            <td>
                                <ul class="asset-list-tb">
                                    <li>Ip-166-62-28-135.ip.secureserver.net</li>
                                    <li>admin.sosecure.co.th</li>
                                </ul>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">Active</span>
                            </td>
                            <td class="no-wrap">
                                <button type="submit" class="btn btn-sm btn-info m-xs">
                                    <span>@icon('solid/edit')
                                </button>

                                <button type="submit" class="btn btn-sm btn-danger m-xs">
                                    <span>@icon('solid/trash-alt')
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                secureserver.net
                            </td>
                            <td>
                                <ul class="asset-list-tb">
                                    <li>Ip-166-62-28-135.ip.secureserver.net</li>
                                    <li>admin.sosecure.co.th</li>
                                </ul>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-danger">Inactive</span>
                            </td>
                            <td class="no-wrap">
                                <button type="submit" class="btn btn-sm btn-info m-xs">
                                    <span>@icon('solid/edit')
                                </button>

                                <button type="submit" class="btn btn-sm btn-danger m-xs">
                                    <span>@icon('solid/trash-alt')
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
        $('#datatype').select2();
        $('#source').select2();

        $('.select2').select2();

        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });
    });
</script>


@endpush

