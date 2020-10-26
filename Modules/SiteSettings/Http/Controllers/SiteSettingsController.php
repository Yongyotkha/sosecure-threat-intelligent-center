<?php

namespace Modules\SiteSettings\Http\Controllers;

use Auth;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Jobs\BulkDeleteSiteSettings;

class SiteSettingsController extends Controller
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

    public function __construct(Request $request , SiteSettings $siteSettings)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
       $data['filter'] = $this->request->filter;
       $data['page']   = $this->getPage();
       return view('sitesettings::index')->with($data);
    }

    public function test_mongo()
    {
        $client = new \MongoDB\Client("mongodb://localhost:27017");//Client
        // $client = new \MongoDB\Driver\Manager("mongodb://localhost:27017");//Client
        $collection = $client->demo->threat_intelligent_center;
        $insertOneResult = $collection->insertOne([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);
        $data['filter'] = $this->request->filter;
        $data['page']   = $this->getPage();
       return view('sitesettings::index')->with($data);
    }

    public function test_mongo2()
    {
        $mongo_client = new MongoDBDriverManager();
        var_dump($mongo_client);
    //    return view('sitesettings::index')->with($data);
    }

    public function phpinfo()
    {
        phpinfo();
    }

    public function test()
    {
       $data['page'] = langapp('site_settings');
       return $data['page'];
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sitesettings::modal.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $SiteSettings = $this->siteSettings;
        $SiteSettings->code = generator_uuid();
        $SiteSettings->name = $request->name;
        $SiteSettings->descript = $request->descript;
        $SiteSettings->address = $request->address;
        $SiteSettings->remark = $request->remark;
        $SiteSettings->created_by = @Auth::user()->id;
        $SiteSettings->active = $request->active ? 1 : 0;
        $SiteSettings->save();

        foreach($request->category AS $category) {
            $SiteCategory = new SiteCategory;
            $SiteCategory->code = $SiteSettings->code;
            $SiteCategory->site_id = $SiteSettings->id;
            $SiteCategory->category_id = $category;
            $SiteCategory->save();
        }

        if ($request->hasFile('logo')) {
            $this->uploadLogo($request, $SiteSettings);
        }
        return ajaxResponse(
            [
                'id'       => $SiteSettings->id,
                'message'  => langapp('saved_successfully'),
                'redirect' =>route('sitesettings.edit', ['id' => $SiteSettings->code]),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('sitesettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $get_data = $this->siteSettings->get_data($id);
        $categories = CategorySettings::where([
            ['active',1],
            ['deleted_at','=',null]
        ])->get();
        $data['categories'] = $categories;
        $data['siteSettings'] = $get_data;
        $data['page'] = $this->getPage();
        return view('sitesettings::edit_site')->with($data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $SiteSettings = $this->SiteSettings->get_data($id);
        $SiteSettings->name = $request->name;
        $SiteSettings->descript = $request->descript;
        $SiteSettings->address = $request->address;
        $SiteSettings->remark = $request->remark;
        $SiteSettings->active = $request->active ? 1 : 0;
        $SiteSettings->save();
        SiteCategory::where('site_id', $SiteSettings -> id)->delete();
        foreach($request->category AS $category) {
            $SiteCategory = new SiteCategory;
            $SiteCategory->code = $SiteSettings->code;
            $SiteCategory->site_id = $SiteSettings->id;
            $SiteCategory->category_id = $category;
            $SiteCategory->save();
        }

        if ($request->hasFile('logo')) {
            $this->uploadLogo($request, $SiteSettings);
        }
        return ajaxResponse(
            [
                'id'       => $SiteSettings->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('sitesettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }


    public function delete(SiteSettings $id)
    {
        $data['siteSettings'] = $id;
        return view('sitesettings::modal.delete')->with($data);
    }

    public function holiday($status = null)
    {
        if ($status == 'enable') {
            Auth::user()->update(['on_holiday' => 1]);
            toastr()->warning(langapp('holiday_mode_enabled'), langapp('response_status'));
        } else {
            Auth::user()->update(['on_holiday' => 0]);
            toastr()->info(langapp('holiday_mode_disabled'), langapp('response_status'));
        }
        return redirect(url()->previous());
    }

    public function bulkDelete()
    {
        if ($this->request->has('checked')) {
            BulkDeleteSiteSettings::dispatch($this->request->checked, Auth::id());
            $data['message']  = langapp('deleted_successfully');
            $data['redirect'] = url()->previous();
            return ajaxResponse($data);
        }
        return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
    }

    public function change_status()
    {
        $data['site_id'] = $this->request->site_id;
        if ($this->request->has('site_id')) {
           
            $data['message']  = langapp('change_status_successfully');
            $data['redirect'] = url()->previous();
            return ajaxResponse($data);
        }
        return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
    }

        /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tableData()
    {
        $model = $this->siteSettings->query();
        return DataTables::eloquent($model)
            ->editColumn('no', function ($siteSettings) {
                    return $siteSettings->code;
            })
            ->editColumn('chk', function ($siteSettings) {
                    return '<label><input type="checkbox" name="checked" value="' . $siteSettings->code . '"><span class="label-text"></span></label>';
            })
            ->editColumn('logo', function ($siteSettings) {
                if($siteSettings->logo) {
                    $site_logo = asset('storage/logos/'.$siteSettings->logo);
                } else {
                    $site_logo = asset('storage/logos/default_logo.png');
                }
                $logo = '<div style="width: 100px; height: 50px;"><img src="'.$site_logo.'" style="object-fit: cover; width: 100%; height: 100%;"></div>';
                return $logo;
            })
            ->editColumn('name', function ($siteSettings) {
                return $siteSettings->name;
            })
            ->editColumn('categorys', function ($siteSettings) {
                $return = '';
                foreach($siteSettings -> get_categorys as $data){
                    $return .= $data -> category -> name. ', ';
                }
                return rtrim($return, ", ");
            })
            ->editColumn('status', function ($siteSettings) {
                if($siteSettings->active == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<script>
                function change_site_active (site_id) {
                    axios.post("'. route('sitesettings.api.change_status') .'", {
                        // params: {
                            site_id: site_id
                        // }
                        })
                    .then(function (response) {
                        toastr.warning(response.message, '.@langapp("response_status").');
                        window.location.href = response.redirect;
                    })
                    .catch(function (error) {
                        var errors = error.errors;
                        var errorsHtml = "";
                        $.each(errors, function (key, value) {
                            errorsHtml += "<li>" + value[0] + "</li>";
                        });
                        toastr.error(errorsHtml, '.@langapp("response_status"). ');
                    });
                    
                }
                </script>';

                // $html = '';
                $html .= '<label class="switch">
                            <input type="hidden" value="FALSE" name="">
                            <input type="checkbox" onclick="change_site_active(' . $siteSettings->id . ')" '.$checked_val.' name="active" value="1">
                            <span></span>
                            </label>';
                return $html;
            })
            ->editColumn('action', function ($siteSettings) {
                $html = '';
                $html .= "<a href='". route('sitesettings.edit', ['id' => $siteSettings->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                        </a> 
                        <a href='". route('sitesettings.delete', ['id' => $siteSettings->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                        </a>";
                return $html;
            })
            ->rawColumns(['no', 'chk', 'logo', 'name', 'categorys', 'status', 'action'])
            ->make(true);
    }

    // protected function applyFilter()
    // {
    //     if ($this->request->filled('filter')) {
    //         return $this->user->role($this->request->filter);
    //     }
    //     return $this->user->query();
    // }

    private function getPage()
    {
        return langapp('site_settings');
    }


    // public function gdprExport()
    // {
    //     GDPRExportData::dispatch(Auth::user());
    //     toastr()->info('We will send you an email when your data is available', langapp('response_status'));

    //     return redirect(url()->previous());
    // }
    // /**
    //  * Export users as CSV
    //  */
    // public function export()
    // {
    //     if (isAdmin()) {
    //         return (new UsersExport)->download('users_' . now()->toIso8601String() . '.csv');
    //     }
    //     abort(404);
    // }



}
