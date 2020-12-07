<?php

namespace Modules\SiteSettings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;

class DataLeakController extends Controller
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
    
    public function keyword($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Keyword Setting';
       return view('sitesettings::keyword_setting')->with($data);
    }

    public function datafeed()
    {
       $data['page'] = 'Data Feed(Social)';
       return view('sitesettings::datafeed')->with($data);
    }

    public function socialdatas($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Social Datas';
       return view('sitesettings::social-datas')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sitesettings::create');
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
        return view('sitesettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('sitesettings::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
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
}
