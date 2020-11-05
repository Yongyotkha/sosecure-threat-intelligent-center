<?php

namespace Modules\Scans\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use phpseclib\Net\SSH1;
use phpseclib\Net\SSH2;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\TransactionTimeStampScans;
use Illuminate\Support\Facades\File;

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


    public function scan_domain($tab = 'overview')
    {
        $allowed      = ['overview', 'datatype', 'settings', 'logs'];
        $tab          = in_array($tab, $allowed) ? $tab : 'overview';
        $data['page'] = 'Domain Settings';
        $data['tab']  = $tab;
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
        $array = explode("\n", file_get_contents(public_path().'/files/scans/DNS_recon_filter_for_ip_v6.txt'));
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
        foreach($arrays_last_final as $data){
            dd($data[1]);
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
}
