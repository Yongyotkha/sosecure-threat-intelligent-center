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
    
    
                {{-- <div class="col-md-4">
                    <div class="form-group">
                        <label for="" class="">module</label>
                        <select name="" id="module" class="select2-option form-control" multiple="multiple">
                            <option value="1">All</option>
                        </select>
                    </div>
                </div> --}}
               
                <div class="col-md-4">
                    <div class="form-group">
                      <label for="" class="">Source</label>
                      <select name="" id="source" class="select2-option form-control">
                          <option value="all">All</option>
                      </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
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
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Data Type</th>
                            <th>Raw Data</th>
                            <th>Referent</th>
                            {{-- <th>Module</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($TransactionScans as $data)
                        <tr>
                            <td class="text-left">{{ $data -> data_type }}</td>
                            <td class="text-left">{{ $data -> raw_data }}</td>
                            <td class="text-left">{{ $data -> referent }}</td>
                        </tr>
                        @endforeach
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
        // $('#module').select2();
        $('#datatype').select2();
        $('#source').select2();
    });

    $('.hide-fillter').hide();
    $('#fillter-advance').click(function(){
        $('.hide-fillter').toggle();
    });
</script>
@endpush
