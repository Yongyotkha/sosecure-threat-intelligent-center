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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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
use ZipArchive;

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
        $site_settings_edit = DB::table('site')->where('active', 1)->select('id','code','name')->get();

        return view('rssfeedsettings::certificate.index', compact('site_settings','site_settings_edit'))->with($data);
    }

    public function store(Request $request)
    {
        try {
            
            // dd($request->all());
            if ($request->hasFile('cert_file_ssl')) {
                
                $file = $request->file('cert_file_ssl');
                $name = date('YmdHis') .'_'. $file->getClientOriginalName();
                $filePath = 'upload/certificate/';
                $filePathDB = 'upload/certificate/' . $name;

                // Move File -> Public
                $file ->move($filePath, $name);

                // Zip
                // $zip = new ZipArchive(); // Load zip library 
                // $zip_name = date('YmdHis') .'_ca'. '.zip';
                // if($zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
                // { 
                //     $zip->addFile($filePathDB, $name);
                //     $zip->close();
                // }
                // dd( $filePathDB, $zip_name );

            }

            // Store -> DB
            $site = implode(",", $request->cert_site);
            $store = DB::table('certificate')
            ->insert([
                'code' => generator_uuid(),
                'name' => $request->cert_name ? $request->cert_name : null,
                'ssl_certificate' => $filePathDB,
                'ssl_certificate_key' => null,
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

    public function update(Request $request)
    {
        try {
            
            // dd($request->all());
            $filePathDB = '';
            if ($request->hasFile('cert_file_ssl_edit')) {
                
                $file = $request->file('cert_file_ssl_edit');
                $name = date('YmdHis') .'_'. $file->getClientOriginalName();
                $filePath = 'upload/certificate/';
                $filePathDB = 'upload/certificate/' . $name;

                // Move File -> Public
                $file ->move($filePath, $name);

                // Zip
                // $zip = new ZipArchive(); // Load zip library 
                // $zip_name = date('YmdHis') .'_ca'. '.zip';
                // if($zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
                // { 
                //     $zip->addFile($filePathDB, $name);
                //     $zip->close();
                // }
                // dd( $filePathDB, $zip_name );

            }

            // Store -> DB
            $site = implode(",", $request->cert_site_edit);
            $store = DB::table('certificate')
            ->where('id', $request->cert_id_edit)
            ->update([
                'name' => $request->cert_name_edit ? $request->cert_name_edit : null,
                'ssl_certificate' => $filePathDB,
                'ssl_certificate_key' => null,
                'site_id' => $site,

                'status' => $request->cert_status_edit ? true : false,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => Auth::user()->id,
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

    public function view(Request $request)
    {
        try {

            // dd($request->all());
            $id = $request->id;
            // Select -> DB
            $site_all = DB::table('site')->where('active', 1)->select('id','name')->get();
            $query = DB::table('certificate')->where('id', $id)->first();
            $site = explode(",", $query->site_id);
            $mysite = [];

            foreach ($site as $site_list) {
                array_push($mysite, $site_list);
            }
            
            $data = [
                'status' => 200,
                'message' => "สำเร็จ",
                'data' => $query,
                'mysite' => $mysite,
                'site_all' => $site_all,
            ];

        } catch (\Exception $e) {
            $data = [
                'status' => 404,
                'message' => "เกิดข้อผิดพลาด",
            ];
        }

        return response()->json($data);
    }

    public function delete(Request $request)
    {
        try {

            // dd($request->all());
            $id = $request->id;
            // Delete -> DB
            $query = DB::table('certificate')
            ->where('id', $id)
            ->update([
                'status' => $request->cert_status_edit ? true : false,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => Auth::user()->id,
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);

            $data = [
                'status' => 200,
                'message' => "ลบรายการสำเร็จ",
            ];

        } catch (\Exception $e) {
            $data = [
                'status' => 404,
                'message' => "เกิดข้อผิดพลาด",
            ];
        }

        return response()->json($data);
    }

    public function change_status(Request $request)
    {
        try {

            // dd($request->all());
            $id = $request->id;
            // Change Status -> DB
            $query = DB::table('certificate')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => Auth::user()->id,
            ]);

            $data = [
                'status' => 200,
                'message' => "ปรับสถานะสำเร็จ",
            ];

        } catch (\Exception $e) {
            $data = [
                'status' => 404,
                'message' => "เกิดข้อผิดพลาด",
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

        $query = DB::table('certificate as cert')->where('cert.deleted_at', null)->get();

        return DataTables::of($query)
        ->addIndexColumn()
        ->editColumn('site_name', function ($query) {
            // Convert -> Array
            $site = explode(",", $query->site_id);

            $html = '';
            $html .= '<ul>';
            foreach ($site as $site_list) {
                if ($site_list == 'all') {
                    $html .= '<li><span>ทั้งหมด</span></li>';
                } else {
                    $getsite = DB::table('site')->where('id', $site_list)->where('active', '1')->select('id','name')->first();
                    $html .= '<li><span>'.$getsite->name.'</span></li>';
                }
            }
            $html .= '</ul>';

            return $html;
        })
        ->editColumn('download', function ($query) {
            if ($query->ssl_certificate) {
                $html = '
                    <a href="'.asset($query->ssl_certificate).'" class="btn btn-sm btn-info pull-right" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                ';
            } else {
                $html = '-';
            }

            return $html;
        })
        ->addColumn('status', function ($query) {
            if ($query->status == '1') {
                $checked_val = 'checked';
            } else {
                $checked_val = '';
            }
            $html = '';
            $html .= '
            <label class="switch">
                <input type="checkbox" id="'.$query->id.'" data-id="'.$query->id.'" data-status="'.$query->status.'" value="'.$query->status.'" '.$checked_val.' onchange="f_change_status_cert(this)">
                <span></span>
            </label>
            ';

            return $html;
        })
        ->editColumn('action', function ($query) {
            $html = '
                <a type="button" class="btn btn-sm btn-info pull-right" 
                data-toggle="modal" data-target="#cert_modal_update" data-id="'.$query->id.'" onclick="f_edit_cert(this)">
                    <i class="fas fa-edit"></i>
                </a>
                <a type="button" class="btn btn-sm btn-danger pull-right" data-id="'.$query->id.'" onclick="f_delete_cert(this)">
                    <i class="fas fa-trash-alt"></i>
                </a>
            ';

            return $html;
        })
        ->rawColumns(['status', 'action', 'site_name', 'download'])
        ->toJson();
    }

}
