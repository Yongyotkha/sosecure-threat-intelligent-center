<?php

namespace Modules\sitesettings\Http\Controllers;

use App\DeployCode;
use App\DeployHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Carbon\Carbon;
use App\ApiToken;

class SystemSettingsController extends Controller
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
    public function systemsetting($id)
    {

        $get_data = $this->siteSettings->get_data($id);
        // Get IoC Feed token
        $token = ApiToken::where('site_id', $get_data->id)
            ->where('type', 'ioc_feed')
            ->orderBy('id', 'desc')
            ->first();
            
        $data['token_obj'] = $token;
        $data['token'] = $token ? $token->token : '';

        // Get Service Receive API token
        $tokenServiceReceive = ApiToken::where('site_id', $get_data->id)
            ->where('type', 'service_receive_api')
            ->orderBy('id', 'desc')
            ->first();
        $data['token_service_receive'] = $tokenServiceReceive ? $tokenServiceReceive->token : '';

        $data['siteSettings'] = $get_data;
        $data['page'] = 'SystemSettings';
        $data['last_version'] = DeployCode::where('status', 1)->orderBy('version', 'desc')->where('deleted_at', null)->first()->version;
        $data['code_version'] = @DeployHistory::where('site_id', $get_data->id)->where('status', 1)->orderBy('version', 'desc')->where('deleted_at', null)->first()->version;
        // dd($data);
        return view('sitesettings::system_setting')->with($data);
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

    public function check_cookie_site(Request $request)
    {
        $firstCurrentVal = $request->firstCurrentVal;
        $currentVal = $request->currentVal; //secondVal
        $cookieVal = $request->cookieVal;

        if (!empty($cookieVal)) {
            if (!empty($currentVal)) {
                if (preg_match("/[a-z]/i", $cookieVal)) {
                    $SiteSettingsfor = SiteSettings::where('code', $cookieVal)->first();
                } else {
                    $SiteSettingsfor = SiteSettings::where('id', $cookieVal)->first();
                }
                if (preg_match("/[a-z]/i", $currentVal)) {
                    //use code
                    $data["siteValue"] = $SiteSettingsfor->code;
                } else {
                    //use id
                    $data["siteValue"] = $SiteSettingsfor->id;
                }
            } else {
                $data["siteValue"] = $firstCurrentVal;
            }
        } else {
            $data["siteValue"] = $firstCurrentVal;
        }

        if ($request->ajax()) {
            return response()->json($data);
        } else {
            return false;
        }
    }
}
