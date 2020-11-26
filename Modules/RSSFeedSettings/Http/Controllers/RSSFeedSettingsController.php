<?php

namespace Modules\RSSFeedSettings\Http\Controllers;

use Modules\RSSFeedSettings\Http\Requests\CreateRssRequest;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\RSSFeedSettings\Entities\RSSData;
use Yajra\DataTables\DataTables;

class RSSFeedSettingsController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request , RSSData $RSSData)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
       $data['page'] = langapp('rss_feed_settings');
       return view('rssfeedsettings::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('rssfeedsettings::create');
    }

    public function rss_data()
    {
        $data['page'] = "rss data";
        return view('rssfeedsettings::rss_data')->with($data);
    }

    public function tableRssData(){
        $model = RSSData::all();
        return DataTables::of($model)
            ->editColumn('chk', function (RSSData $model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('status', function (RSSData $model) {
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
            ->addColumn('action', function (RSSData $model) {
                $html = '';
                $html .= "<a href='". route('rssfeedsettings.edit', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='". route('rssfeedsettings.delete', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
            })
            ->rawColumns(['chk','status','action'])
            ->toJson();
    }
    
    public function rss_setting()
    {
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_setting')->with($data);
    }

    public function rss_logs()
    {
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_logs')->with($data);
    }

    public function rss_news()
    {
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_news')->with($data);
    }

    public function rss_feed_all()
    {
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_feed_all')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        

        $RSSData = new RSSData;
        $RSSData->code = generator_uuid();
        $RSSData->name = $request->name_rss;
        $RSSData->url = $request->url_rss;
        $RSSData->status = $request->status_rss ? 1 : 0;
        $RSSData->created_by = @Auth::user()->id;
        $RSSData->save();


        return ajaxResponse(
            [
                'id'       => $RSSData->id,
                'message'  => langapp('saved_successfully'),
                'redirect' => route('rssfeedsettings.index'),
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
        return view('rssfeedsettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $get_data = RSSData::where("code",$id)->first();

        $data['rssfeedsettings'] = $get_data;
        // $data['page'] = $this->getPage();
        return view('rssfeedsettings::modal.update')->with($data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(CreateRssRequest $request, $id = null)
    {
       //  dd($request);
       //  exit();
        $RSSData = RSSData::where("code",$id)->first();
        // $CategorySettings->update($request->all());
        $RSSData->name = $request->name_rss;
        $RSSData->url = $request->url_rss;
        $RSSData->status = $request->active ? 1 : 0;
        $RSSData->save();

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id'       => $RSSData->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function change_status(Request $request)
    {
        // dd($request);
        // exit();
        $rss_code = $this->request->code;
        // $data['category_id'] = $this->request->category_id;
        // $CategorySettings = $this->categorySettings->findOrFail($data['category_id']);
        $rss = RSSData::where("code",$rss_code)->first();
        // $CategorySettings->update($request->all());
        // $CategorySettings->name = $request->name;
        $rss->status = $rss->status == 1 ? 0 : 1;
        $rss->save();

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id'       => $rss->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete(Request $id)
    {
        $data['rssfeedsettings'] = $id;
        return view('rssfeedsettings::modal.delete')->with($data);
    }

    public function delete_process($id = null)
    {
        $model = RSSData::where("code",$id);
        // dd($model);
        $model->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

}
