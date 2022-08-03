<?php

namespace Modules\SiteSettings\Http\Controllers;

use App\TransactionTimeStampScans;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteSettings;
use Auth;
use Artisan;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\DataLeakSocial;

class UrlFeedController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $siteSettings;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, SiteSettings $siteSettings)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        // $get_data = $this->siteSettings->get_data($id);
        $get_role_custom_first = @get_role_custom();
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)->get();
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }

        
        $data = [];
        
        $data['SiteSettings'] = $SiteSettings;
        // $data['siteSettings'] = $SiteSettings;
        $data['page'] = 'URL Feed';
        // $data['menu'] = 'site';
        return view('sitesettings::url_feed')->with($data);
    }

    public function tbl_url_feed(Request $request)
    {
        $model = DataLeakSocial::
            where('deleted_at',null)
            // ->where('status',1)
            ->orderBy('created_at', 'DESC')
            ->get();

        return DataTables::of($model)
            ->editColumn('chk', function ($model) {
                return '<label><input type="checkbox" name="rss_id" class="rss_id" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            // ->addColumn('link', function ($model) {
            //     $html = '';
            //     $html .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' data-toggle='ajaxModal'>
            //         ".$model->link."
            //     </a>";
            //     return $html;
            // })
            // ->addColumn('transactionRssData_count', function ($model) {


            //     $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
            //     $client = new \MongoDB\Client($DB_MONGO_KEY);
            //     $db_name = 'social';
            //     $db = $client->$db_name;
            //     $collection = $db->DailyFeed;
            //     $where = array(
            //         'sourceid' => $model->id,
            //     );
        
            //     $cursor = $collection->find($where);   //This is the main line
            //     $document_all = $cursor->toArray();
            //     $cursor_count = count($document_all);
             



            //     $html = '';
            //     if($cursor_count > 0){
            //          $html .= '<a href="javascript:void(0);" onclick="view_social_count_data('.$model->id.');" >'.$cursor_count.'</a>';
            //     }else{
            //         $html .='0';

            //     }
            //     return $html;
            // })
            // ->addColumn('feed_last_mongodb', function ($model) {


            //     $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
            //     $client = new \MongoDB\Client($DB_MONGO_KEY);
            //     $db_name = 'social';
            //     $db = $client->$db_name;
            //     $collection = $db->feed_source;
            //     $where = array(
            //        'source_id' => $model->id,
                  
            //     );
        
            //     $cursor = $collection->find($where);   //This is the main line
            //     $document_all = $cursor->toArray();
            //     $cursor_count = count($document_all);
   
             



            //     $html = '';
            //     if($cursor_count > 0){
            //         foreach ($document_all as  $value) {
                      
                       

            //             try {
            //                 if (property_exists($value, 'last_feed_date')) {
            //                 $html .=  change_date_utc_to_thai($value->last_feed_date);
            //                 }
            //             } catch (Exception $e) {
                          
            //             }
            //         }

                   
            //     }
             
            //     return $html;
            // })
            ->addColumn('c_site', function ($model) {

                $site_name = '';

                if($model->site_id)
                {
                    if($model->site_id == 0 || $model->site_id == 'All')
                    {
                        $site_name = 'All Site';
                    }
                    else
                    {
                        $site_name = $model->get_site->name;
                    }
                }
                else
                {
                    $site_name = 'All Site';
                }

                return $site_name;
            })
            ->addColumn('c_type', function ($model) {

                $type = '';

                if($model->type == 1)
                {
                    $type = 'Custom';
                }
                else
                {
                    $type = 'Add On';
                }

                return $type;
            })
            ->addColumn('status', function ($model) {
                if($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="rss-active-'.$model->code.'" onchange="change_rss_active(\''.$model->code.'\')" '.$checked_val.' value="1">
                            <span></span>
                        </label>';
                return $html;
            })
            ->addColumn('action', function ($model) {
                $html = '';
                $html .= "
                    <a href='". route('urlfeed.edit_url_feed', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                        <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                    </a>
                    <a href='". route('urlfeed.modal_delete_url_feed', ['id' => $model->code]) ."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                        <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                    </a>
                </div>
                ";
                return $html;
            })
            ->rawColumns(['chk','link','status','action','transactionRssData_count'])
            ->make(true);
            // ->toJson();
    }

    public function insert_url_feed(request $request)
    {
        // dd($request->all());

        try 
        {
            $main_data = [];
            $main_data['code'] = Str::uuid()->toString();
            $main_data['source'] = @$request->name_web;
            $main_data['url'] = @$request->url_web;
            $main_data['tag'] = @$request->name_web;
            // $main_data['remark'] = @$request->site_id;
            $main_data['status'] = 1;
            $main_data['created_by'] = Auth::user()->id;
            $main_data['updated_by'] = Auth::user()->id;
            $main_data['feel_last'] = date('Y-m-d H:i:s');
            $main_data['site_id'] = @$request->site_id;
            $main_data['type'] = 2;
            $main_data['port'] = @$request->port_web;
            $main_data['transaction_status'] = 1;
    
            DataLeakSocial::create($main_data);
    
            $response = [
                'status' => 'success',
                'message' => 'Create URL | Success.',
                'redirect' => route('urlfeed.index')
            ];
        }
        catch (\Exception $e)
        {
            $response = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($response);
    }

    public function edit_url_feed(Request $request)
    {
        $get_role_custom_first = @get_role_custom();
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)->get();
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }

        $query = DataLeakSocial::where(['code' => $request->id])->first();
        
        $data = [];
        
        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = 'URL Feed';
        $data['query'] = $query;

        return view('sitesettings::modal.edit_url_feed')->with($data);
    }

    public function update_url_feed(request $request)
    {
        // dd($request->all());

        try 
        {
            $main_data = [];
            $main_data['source'] = @$request->name_web;
            $main_data['url'] = @$request->url_web;
            $main_data['tag'] = @$request->name_web;
            $main_data['updated_by'] = Auth::user()->id;
            $main_data['feel_last'] = date('Y-m-d H:i:s');
            $main_data['site_id'] = @$request->site_id;
            $main_data['type'] = 2;
            $main_data['port'] = @$request->port_web;
    
            DataLeakSocial::where('code', $request->hd_code)->update($main_data);
    
            $response = [
                'status' => 'success',
                'message' => 'Update URL | Success.',
                'redirect' => route('urlfeed.index')
            ];
        }
        catch (\Exception $e)
        {
            $response = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($response);
    }

    public function modal_delete_url_feed(request $request)
    {
        $query = DataLeakSocial::where(['code' => $request->id])->first();
        
        $data = [];
        $data['page'] = 'URL Feed';
        $data['query'] = $query;

        return view('sitesettings::modal.delete_url_feed')->with($data);
    }

    public function delete_url_feed(request $request)
    {
        // dd($request->all());

        try 
        {

            DataLeakSocial::where('code', $request->hd_code)->update(['deleted_at' => date('Y-m-d H:i:s')]);
    
            $response = [
                'status' => 'success',
                'message' => 'Delete URL | Success.',
                'redirect' => route('urlfeed.index')
            ];
        }
        catch (\Exception $e)
        {
            $response = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return response()->json($response);
    }

    public function get_check_site(Request $request)
    {
        // dd($request->all());

        $html = ''; 
        $site_id = $request->site_id;
        $url_web = $request->url_web;
        $port_web = $request->port_web;
        // dd($port_web);
        $command = 'app:WebDefacementDataCheck';

        $params = [
            'url' => $url_web,
            'port' => $port_web,
            'site_id' => $site_id,
        ];


            Artisan::call($command, $params);
            $result = Artisan::output();
            // dd($result);
        
       
    }

    public function change_status(Request $request)
    {
        $model = DataLeakSocial::where(['code' => $request->code])->update(['status' => $request->active]);

        $response = [
            'status' => 'success',
            'message' => 'Save Status | Success.'
        ];

        return response()->json($response);
    }
}
