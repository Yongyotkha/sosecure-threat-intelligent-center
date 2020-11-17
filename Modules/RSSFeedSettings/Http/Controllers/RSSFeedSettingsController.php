<?php

namespace Modules\RSSFeedSettings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

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

    public function __construct(Request $request)
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
        //
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
        return view('rssfeedsettings::edit');
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
