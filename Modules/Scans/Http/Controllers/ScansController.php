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
        // $process = new SSH2('10.104.0.7');
        // $process->login('root', '$0$ecure-!@#$%^&*()');
        // $stream_out = $process->exec('ssh -t root@10.104.0.12  "/usr/spiderfoot/sf.py  -m sfp_dnsbrute,sfp_dnsresolve,sfp_whois -s baac.or.th -q -F AFFILIATE_DOMAIN_NAME,AFFILIATE_INTERNET_NAME,AFFILIATE_COMPANY_NAME,AFFILIATE_DOMAIN_WHOIS,INTERNET_NAME"'); 
        
        // echo $request->header('User-Agent');
        // $mac = new Process('ssh -t root@10.104.0.12  "/usr/spiderfoot/sf.py  -m sfp_dnsbrute,sfp_dnsresolve,sfp_whois -s baac.or.th -q -F AFFILIATE_DOMAIN_NAME,AFFILIATE_INTERNET_NAME,AFFILIATE_COMPANY_NAME,AFFILIATE_DOMAIN_WHOIS,INTERNET_NAME"');
        // $mac->setTimeout(3600);
        // $mac->run();

        // echo '<pre>' .$mac->getOutput() . '</pre>';
        // echo $mac->getOutput();
        $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 0)->where('status', 1)->get();
        foreach($TransactionTimeStampScans as $TransactionTimeStampScan){
            $TransactionTimeStampScan->progress = 1;
            $TransactionTimeStampScan->save();

            $domain = $TransactionTimeStampScan->get_domain->domain;
            $current_1 = '';
            $current_2 = '';
            $cmd_1 = 'ssh -t root@10.104.0.12  /usr/spiderfoot/sf.py -m sfp_dnsbrute,sfp_dnsresolve,sfp_whois,sfp_crobat_api -s '.$domain.' -q -FAFFILIATE_DOMAIN_NAME,AFFILIATE_INTERNET_NAME,INTERNET_NAME';
            $cmd_2 = 'ssh -t root@10.104.0.12  "/usr/spiderfoot/sf.py -m sfp_dnsbrute,sfp_dnsresolve,sfp_whois,sfp_crobat_api,sfp_crt -s '.$domain.' -q -r -F IP_ADDRESS"';
            $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
            );
            flush();
            $process_1 = proc_open($cmd_1, $descriptorspec, $pipes, realpath('./'), array());
            if (is_resource($process_1)) {
                while ($s = fgets($pipes[1])) {
                    $current_1 .= $s;
                    echo $s;
                    flush();
                }
            }

            $path = public_path().'/files/scans/'.$TransactionTimeStampScan->get_site->code;
            File::makeDirectory($path, $mode = 0777, true, true);

            $file = $path.'/file_1.txt';
            file_put_contents($file, $current_1);

            $process_2 = proc_open($cmd_2, $descriptorspec, $pipes, realpath('./'), array());
            if (is_resource($process_2)) {
                while ($s = fgets($pipes[1])) {
                    $current_2 .= $s;
                    echo $s;
                    flush();
                }
            }

            $file = $path.'/file_2.txt';
            file_put_contents($file, $current_2);

            $TransactionTimeStampScan->progress = 2;
            $TransactionTimeStampScan->save();
        }
    }
}
