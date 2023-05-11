<?php

namespace Modules\RSSFeedSettings\Http\Controllers;

use App\Entities\fx_transaction_client_news_categories;
use App\LogEmail;
use App\Mail\NewsMail;
use App\siteNewsRelated;
use App\Topic;
use App\transaction_client_rss;
use App\TransactionClientNews;
use App\FXCategories;
use Modules\RSSFeedSettings\Http\Requests\CreateRssRequest;
use Auth;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\RSSFeedSettings\Entities\NewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\RSSFeedSettings\Entities\NewsTag;
use Modules\RSSFeedSettings\Entities\NewsTopics;
use Modules\RSSFeedSettings\Entities\RSSData;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\Tags;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use Modules\SiteSettings\Entities\SiteSettings;
use Yajra\DataTables\DataTables;


class CertificateController extends Controller
{

    protected $item;
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }

    public function index()
    {
        // $role_custom = @check_role_custom();
        // if (!$role_custom['news']) {
        //     check_permission403();
        // }
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $data['page'] = langapp('certificate');
        $site_settings = DB::table('site')->where('active', 1)->select('id','code','name')->get();

        return view('rssfeedsettings::certificate.index', compact('site_settings'))->with($data);
    }

    public function store(Request $request)
    {
        try {
            // 
            // dd($request->all());

            // Store
            // if ($request->hasFile('file')) {

            //     $file = $request->file('file');
            //     $name = time() . $file->getClientOriginalName();
            //     $filePath = 'readvpn/testS3/' . $name;

            //     // Upload -> S3
            //     $path = Storage::disk('s3')->put($filePath, file_get_contents($file));
            //     $url = Storage::disk('s3')->url($filePath);

            //     // Move File -> Public
            //     $file->move($filePath, $filePath);

            // }
            $site = implode(",", $request->cert_site);
            // dd($site);
            $store = DB::table('certificate')
            ->insert([
                'code' => generator_uuid(),
                'name' => $request->cert_name ? $request->cert_name : null,
                'ssl_certificate' => '',
                'ssl_certificate_key' => '',
                'site_id' => $site,

                'status' => $request->cert_status ? true : false,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Auth::user()->id,
            ]);

            $data = [
                'status' => 200,
                'message' => "บันทึกสำเร็จ",
                'redirect' => route('certificate.index'),
            ];

        } catch (\Exception $e) {
            $data = [
                'status' => 404,
                'message' => "เกิดข้อผิดพลาด",
                'redirect' => route('certificate.index'),
            ];
        }

        return response()->json($data);
    }

    public function tableCertificate()
    {
        // $role_custom = @check_role_custom();
        // if (!$role_custom['news']) {
        //     check_permission403();
        // }
        // $model = RSSData::where('deleted_at', null)->get();
        $query = DB::table('certificate')->where('deleted_at', null)->get();
        return DataTables::of($query)
        ->addIndexColumn()
        ->editColumn('download', function ($query) {
            $html = "
                <a href='#' class='btn btn-sm btn-info pull-right' download>
                <i class='fas fa-download'></i> Download
                </a>
            ";

            return $html;
        })
        ->addColumn('status', function ($query) {
            if ($query->status == '1') {
                $checked_val = 'checked';
            } else {
                $checked_val = '';
            }
            $html = '';
            $html .= '<label class="switch">
                        <input type="checkbox" id="rss-active-' . $query->code . '" onchange="change_rss_active(\'' . $query->code . '\')" ' . $checked_val . ' value="1">
                        <span></span>
                    </label>';
            return $html;
        })
        ->editColumn('action', function ($query) {
            $html = "
                <a href='#' class='btn btn-sm btn-info pull-right'>
                    <i class='fas fa-edit'></i>
                </a>
                <a href='#' class='btn btn-sm btn-danger pull-right'>
                    <i class='fas fa-trash-alt'></i>
                </a>
            ";

            return $html;
        })
        // ->addColumn('action', function (RSSData $model) {
        //     $html = '';
        //     $html .= "<a href='" . route('rssfeedsettings.edit', ['id' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
        //     <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
        //     </a>
        //     <a href='" . route('rssfeedsettings.delete', ['id' => $model->code]) . "' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
        //     <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
        //     </a></div>";
        //     return $html;
        // })
        ->rawColumns(['status', 'action', 'download'])
        ->toJson();
    }

}
