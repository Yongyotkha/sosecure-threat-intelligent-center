@php
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\Site;
use Illuminate\Support\Str;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
@endphp
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>แจ้งเตือน Web Defacement</title>
  <style>
    body {
      font-family: Tahoma, sans-serif;
      background-color: #f7f7f7;
      color: #333;
      padding: 20px;
    }

    .container {
      max-width: 100%;
      margin: auto;
      background: white;
      border-radius: 8px;
      overflow: hidden;

    }

    .content-bottom {
      max-width: 100%;
      margin: auto;
      background: #050038;
      border-radius: 20px;
      overflow: hidden;
      color: white;
      /* margin: 20px; */
      /* margin-top: -13px; */
    }

    .header {
      background-color: #050038;
      color: white;
      padding: 16px;
      text-align: center;
    }

    .content {
      padding: 20px;
    }

    .footer {
      background-color: #eee;
      padding: 12px;
      text-align: center;
      font-size: 12px;
      color: #050038;
    }

    .btn {
      display: inline-block;
      background-color: #050038;
      color: white;
      padding: 10px 20px;
      border-radius: 99px;
      text-decoration: none;
      width: 150px;
      height: 40px;
    }

    .btn-serverity {
      display: inline-block;
      background-color: #ded6cdff;
      color: red;
      padding: 10px 20px;
      border-radius: 99px;
      text-decoration: none;
      width: 150px;
      height: 40px;
    }

    .section-title {
      font-weight: bold;
      margin-top: 16px;
    }

    .flex-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 24px;
      flex-wrap: wrap;
    }

    .table {
      width: 100%;
      max-width: 100%;
    }

    .table th,
    .table td {

      padding: 8px;
    }

    .table th {
      background-color: #f0f0f0;
      text-align: left;
    }

    .space {
      border-radius: 200px;
    }
  </style>
</head>

<body>
  <div class="row">
    <div class="col-12 justify-content" style="background-color: #050038;font-size: 20px; color: white; padding: 16px; ">
      <img src="{{ asset('images/logo_threat/logo.png') }}"
        alt="Logo"
        class="img-fluid"
        style="max-width: 150px; height: auto;">
    </div>
  </div>

  <div class="row">
    <div class="col-lg-12"
      style="background-color: #050038;
            font-size: 15px;
            color: white;
            padding: 4px;
            border-radius: 200px;
            margin: 4px 12px 12px 12px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;">
      <h3 style="margin: 0; font-weight: bold;">แจ้งเตือน Web Defacement</h3>
    </div>

  </div>
  <div class="row">
    <div class="col-lg-12"></div>
  </div>
  <div class="container" style="border-radius: 20px;margin-top: -5px;">
    <div class="content">
      <div class="flex-row">
        <!-- ตารางข้อมูล -->
        <div style="flex: 1;">
          <table class="table">
            <tr>
              <th>Name Page</th>
              <td>{{ $w->name ?? '-' }}</td>
            </tr>
            <tr>
              <th>Domain</th>
              <td>{{ $w->url ?? '-' }}</td>
            </tr>
            <tr>
              <th>User Agent</th>
              <td>{{ $w->user_agent ?? '-' }}</td>
            </tr>
            <tr>
              <th>Site</th>
              <td>{{ Site::getSite($w->site_id) ?? '-' }}</td>
            </tr>
            <tr>
              <th>Create Date</th>
              <td>{{ $w->created_at ?? '-' }}</td>
            </tr>
            <tr>
              <th>Last Online</th>
              <td>{{ $w->last_online ?? '-' }}</td>
            </tr>
          </table>
        </div>

        <!-- ปุ่มฝั่งขวา -->
        <div style="min-width: 240px; text-align: center;">
          <!-- ปุ่มบน -->
          <div class="btn" style="display: flex; justify-content: center; align-items: center; height: 38px;">
            <strong>Status</strong>
          </div>
          <!-- ปุ่มล่าง -->
          <div class="btn-serverity" style="display: flex; justify-content: center; align-items: center; height: 38px; margin-top: 4px;">
            <p style="margin: 0;"><strong>High</strong></p>
          </div>
        </div>

      </div>
    </div>
  </div>
  <br>
  <div class="content content-bottom">
    <h3 style="color: white; font-weight: bold;text-align: center;margin-top: -7px;">ค่าความเสี่ยง</h3>

    @php
    $data = null;
    $data_origin = null;
    if (!empty($w->id)) {
      $data = WebdefacmentDataCheck::getData($w->id);
      $data_origin = WebdefacmentDataOriginal::getDataOriginal($w->id);
    }
    @endphp

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
      <div style="width: 120px; color: white;">Hash</div>
      <div style="flex-grow: 1;">
        <div class="space" style="background-color: white; color: black; padding: 10px;">
          {{ $data->hash_new ?? '-' }}
        </div>
      </div>
    </div>

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
      <div style="width: 120px; color: white;">Filesize</div>
      <div style="flex-grow: 1;">
        <div class="space" style="background-color: white; color: black; padding: 10px;">
          @php
          $res_kb = 0;
          if (!empty($w->filesize_new)){
          $res_kb = ceil($w->filesize_new / 1024 * 100) / 100;
          }
          @endphp
          {{ $res_kb .' KB' ?? '-' }}
        </div>
      </div>
    </div>

    <div style="display: flex; align-items: center; margin-bottom: 15px;">
      <div style="width: 120px; color: white;">Element</div>
      <div style="flex-grow: 1;">
        <div class="space" style="background-color: white; color: black; padding: 10px;">
          {{ $data->element_new ?? '-' }}
        </div>
      </div>
    </div>

    <div style="display: flex; align-items: center;">
      <div style="width: 120px; color: white;">Last Update</div>
      <div style="flex-grow: 1;">
        <div class="space" style="background-color: white; color: black; padding: 10px;">
          {{ $w->updated_at ?? '-' }}
        </div>
      </div>
    </div>

    <br>
    <!-- <div class="row">
      <div class="col-lg-12">
        <div style="background-color: #050038;font-size: 15px; color: white; padding: 5px;border-radius: 200px;text-align: center; margin-left: 12px;margin-right: 12px;margin-top: 4px;margin-bottom: 12px;"></div>
      </div>
    </div> -->

  </div>

</body>

</html>