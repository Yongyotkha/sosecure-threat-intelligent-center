@php
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\Site;
use Illuminate\Support\Str;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentSetting;

$id = $id['id'];
$webDef_data = WebdefacmentSetting::where('id', $id)->first();
$webDef_data_chk = WebdefacmentDataCheck::where('webdefacment_setting_id', $id)->first();
@endphp

<!doctype html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    {{-- ต้องมี bootstrap css --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="card m-3">
    <div class="card-body text-center">
        <h1>Documents</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Section Selectors</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <button class="btn btn-primary" onclick="viewAsset('{{ $webDef_data->baseline_assets }}')">view</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <h1>Hash: {{ $webDef_data->hash }}</h1>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="assetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Asset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <table class="table table-bordered table-striped">
              <thead>
                  <tr>
                      <th>Section Selectors</th>
                  </tr>
              </thead>
              <tbody>
                  <tr>
                      <td id="assetContent">Testing</td>
                  </tr>
              </tbody>
          </table>
      </div>
    </div>
  </div>
</div>

{{-- bootstrap js + jquery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function viewAsset(asset) {
        document.getElementById('assetContent').innerText = asset;

        var modal = new bootstrap.Modal(document.getElementById('assetModal'));
        modal.show();
    }
</script>


@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.datatables')
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.activebutton')


@endpush

</body>
</html>
