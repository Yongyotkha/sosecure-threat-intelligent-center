<?php

namespace Modules\CategorySettings\Http\Controllers;

use Auth;
use DataTables;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\CategorySettings\Http\Requests\CategorySettingsRequest;
use Modules\CategorySettings\Jobs\BulkDeleteCategorySettings;

// use Modules\Users\Transformers\UserResource;
use Modules\CategorySettings\Transformers\CategorysResource;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;


class CategorySettingsController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $categorySettings;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request , CategorySettings $categorySettings)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->categorySettings    = $categorySettings;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
    //    $data['page'] = langapp('category_settings');

       $data['filter'] = $this->request->filter;
       $data['page']   = $this->getPage();
       return view('categorysettings::index')->with($data);
    }

    public function test()
    {
       $data['page'] = langapp('category_settings');
       return $data['page'];
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('categorysettings::modal.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('categorysettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit(CategorySettings $id)
    {
        $data['categorySettings'] = $id;
        // dd($id);
        return view('categorysettings::modal.update')->with($data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
     public function update(CategorySettingsRequest $request, $id = null)
     {
        //  dd($request);
        //  exit();
         $CategorySettings = $this->categorySettings->findOrFail($id);
         // $CategorySettings->update($request->all());
         $CategorySettings->name = $request->name;
         $CategorySettings->active = $request->active ? 1 : 0;
         $CategorySettings->save();

         // if ($request->hasFile('logo')) {
         //     $this->uploadLogo($request, $client);
         // }
         return ajaxResponse(
             [
                 'id'       => $CategorySettings->id,
                 'message'  => langapp('changes_saved_successful'),
                 'redirect' => route('categorysettings.index'),
             ],
             true,
             Response::HTTP_OK
         );
     }


    public function delete(CategorySettings $id)
    {
        $data['categorySettings'] = $id;
        return view('categorysettings::modal.delete')->with($data);
    }

    public function delete_process($id = null)
    {
        $model = $this->categorySettings->find($id);
        // dd($model);
        $model->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('categorysettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
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
            BulkDeleteCategorySettings::dispatch($this->request->checked, Auth::id());
            $data['message']  = langapp('deleted_successfully');
            $data['redirect'] = url()->previous();
            return ajaxResponse($data);
        }
        return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
    }

    // public function change_status()
    // {
    //         $data['category_id'] = $this->request->category_id;

    //         // echo ($data['category_id']);
    //         // exit();

    //         // $get_active = $this->Site_model->get_by_id_site($site_id);
    //         // if($get_active) {
    //         //     $get_active = $get_active->site_active == 'Y' ? 'N' : 'Y';
    //         // } else {
    //         //     $get_active = 'N';
    //         // }

    //         // $site_set_data = array(
    //         //     "site_id" => $site_id,
    //         //     "site_active" => $get_active
    //         // );


    //         // $save_id = $this->Site_model->update_active__by_id_site($site_set_data);



    //     if ($this->request->has('category_id')) {

    //         $data['message']  = langapp('change_status_successfully');
    //         $data['redirect'] = url()->previous();
    //         return ajaxResponse($data);
    //     }
    //     return response()->json(['message' => 'No selected', 'errors' => ['missing' => ["Please select atleast 1 "]]], 500);
    // }

    public function change_status(CategorySettingsRequest $request)
    {
        // dd($request);
        // exit();
        $data['category_id'] = $this->request->category_id;
        $CategorySettings = $this->categorySettings->findOrFail($data['category_id']);
        // $CategorySettings->update($request->all());
        // $CategorySettings->name = $request->name;
        $CategorySettings->active = $CategorySettings->active == 1 ? 0 : 1;
        $CategorySettings->save();

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id'       => $CategorySettings->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('categorysettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

        /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tableData()
    {
        // $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);
        $model = $this->categorySettings->query();
        // ->with(['profile.business:id,name'])
        // ->orderByDesc('id')
        // ->paginate(50)
        // ->query();

        // $model = new CategorysResource(
        //     $this->categorySettings
        //         // ->with(['profile.business:id,name'])
        //         ->orderByDesc('id')
        //         ->paginate(50)
        // );

        return DataTables::eloquent($model)
            ->editColumn(
                'no',
                function ($categorySettings) {
                    return $categorySettings->id;
                }
            )
            ->editColumn(
                'chk',
                function ($categorySettings) {
                    return '<label><input type="checkbox" name="checked" value="' . $categorySettings->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'name',
                function ($categorySettings) {
                    return $categorySettings->name;
                }
            )
            ->editColumn(
                'status',
                function ($categorySettings) {
                    if($categorySettings->active == '1') {
                        $checked_val = 'checked';
                    } else {
                        $checked_val = '';
                    }
                    $html = '';
                    $html .= '<script>
                    function change_category_active (category_id) {
                        axios.post("'. route('categorysettings.change_status') .'", {
                            // params: {
                                category_id: category_id
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
                                <input type="checkbox" onclick="change_category_active(' . $categorySettings->id . ')" '.$checked_val.' name="active" value="1">
                                <span></span>
                              </label>';
                    return $html;
                }
            )
            ->editColumn(
                'action',
                function ($categorySettings) {
                    $html = '';
                    $html .= "<!--<a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                    @icon('solid/shield-alt')
                                </a>-->

                                <a href='". route('categorysettings.edit', ['id' => $categorySettings->id]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                </a>
                                <a href='". route('categorysettings.delete', ['id' => $categorySettings->id]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                </a>";
                    return $html;
                }
            )
            ->rawColumns(['no', 'chk', 'name', 'status', 'action'])
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
        return langapp('category_settings');
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
