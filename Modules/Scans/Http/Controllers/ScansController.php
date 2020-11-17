<?php

namespace Modules\Scans\Http\Controllers;

use App\TransactionScans;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use phpseclib\Net\SSH1;
use phpseclib\Net\SSH2;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use DataTables;
use Modules\SiteSettings\Entities\SiteSettings;
use App\DataScans;

class ScansController extends Controller
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
       $data['page'] = langapp('scans');
       return view('scans::index')->with($data);
    }


    public function scan_domain($tab = 'overview', $site_code)
    {
        $allowed      = ['overview', 'datatype', 'settings', 'logs'];
        $tab          = in_array($tab, $allowed) ? $tab : 'overview';
        $data['page'] = 'Domain Settings';
        $data['tab']  = $tab;
        $SiteSettings = TransactionTimeStampScans::where('code', $site_code)->first();
        $data['site']  = $SiteSettings;
        if($tab == 'overview'){
            $DataScans = DataScans::where('site_id', $SiteSettings -> site_id)->where('domain_id', $SiteSettings -> domain_id)->orderBy('total', 'desc')->get();
            $data['DataScans'] = $DataScans;
        }

        return view('scans::scans_domain')->with($data);
    }
    
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('scans::create');
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
        return view('scans::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('scans::edit');
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

    public function scan_command(Request $request){
        $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 2)->where('status', 1)->get();
        foreach($TransactionTimeStampScans as $TransactionTimeStampScan){
            $path = public_path().'/files/scans/'.$TransactionTimeStampScan->get_site->code.'/'.$TransactionTimeStampScan->get_domain->code;
            $array = explode("\n", file_get_contents($path.'/looking_for_subdomain.txt'));
            $arrays = [];
            $arrays_final = [];
            $arrays_last_final = [];
            foreach ($array as $item) {
                $arrays[] = explode("\t", $item);
            }
            foreach($arrays as $data){
                $arrays_final[] = $data;
            }
            foreach($arrays_final as $item){
                $arrays = [];
                foreach($item as $data){
                    if(!empty($data)){
                        $arrays[] = trim($data);
                    }
                }
                $arrays_last_final[] = $arrays;
            }
            $arrays_last_final = array_filter($arrays_last_final);
            array_pop($arrays_last_final);
            foreach($arrays_last_final as $item){
                $TransactionScans = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                ->where('domain_id', $TransactionTimeStampScan->domain_id)
                ->where('module', $item[0])
                ->where('data_type', $item[1])
                ->where('raw_data', $item[2])
                ->first();
                if(!empty($TransactionScans)){
                    $TransactionScans -> updated_at = Carbon::now();
                }else{
                    $CreateTransactionScans = new TransactionScans();
                    $CreateTransactionScans -> code = Str::uuid()->toString();
                    $CreateTransactionScans -> created_by = $TransactionTimeStampScan -> created_by;
                    $CreateTransactionScans -> site_id = $TransactionTimeStampScan->site_id;
                    $CreateTransactionScans -> domain_id = $TransactionTimeStampScan->domain_id;
                    $CreateTransactionScans -> module = $item[0];
                    $CreateTransactionScans -> data_type = $item[1];
                    $CreateTransactionScans -> raw_data = $item[2];
                    $CreateTransactionScans -> status = 1;
                    $CreateTransactionScans -> save();
                }
            }

            $TransactionTimeStampScan->progress = 3;
            $TransactionTimeStampScan->save();
        }
    }

    public function save_scan(){
        $array = explode("\n", file_get_contents(public_path().'/files/scans/looking_for_subdomain.txt'));
        $arrays = [];
        $arrays_final = [];
        $arrays_last_final = [];
        foreach ($array as $item) {
            $arrays[] = explode("\t", $item);
        }
        foreach($arrays as $data){
            $arrays_final[] = $data;
        }
        foreach($arrays_final as $item){
            $arrays = [];
            foreach($item as $data){
                if(!empty($data)){
                    $arrays[] = trim($data);
                }
            }
            $arrays_last_final[] = $arrays;
        }
        $arrays_last_final = array_filter($arrays_last_final);
        array_pop($arrays_last_final);
        dd($arrays_last_final);
    }

    public function tableData()
    {
        $model = TransactionTimeStampScans::query();
        return DataTables::eloquent($model)
            ->editColumn('chk', function (TransactionTimeStampScans $model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('name', function (TransactionTimeStampScans $model) {
                return '<label>'.$model -> get_site -> name.'</label>';
            })
            ->addColumn('domain', function (TransactionTimeStampScans $model) {
                return '<label>'.$model -> get_domain -> name.'</label>';
            })
            ->rawColumns(['chk','name','domain'])
            ->toJson();
    }
}
