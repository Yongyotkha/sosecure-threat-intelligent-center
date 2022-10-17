<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\SiteSettings\Entities\SiteSettings;
use App\Entities\TransactionBatchjob;
use Carbon\Carbon;

class AlertNotificationToAdminWithLine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'line:alert_serverdown_to_admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert if server(client) is down over 5 minutes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tz = new \DateTimeZone('Asia/Bangkok');
        $dateNow = Carbon::now();
        $dateNow->setTimezone($tz);
        $SiteSettings = SiteSettings::select('id', 'name')->where('active', '1')->whereNull('deleted_at')->get()->toArray();
        foreach ($SiteSettings as $key => $value) {
            $TransactionBatchjob = TransactionBatchjob::select('transcation_date_start')->where('site_id', $value['id'])->where('status', 1)->orderBy('transcation_date_start', 'desc')->first();
            if ($TransactionBatchjob) {
                $TransactionBatchjob = $TransactionBatchjob->toArray();
            } else {
                $TransactionBatchjob = ["transcation_date_start" => "0000-00-00 00:00:00"];
            }
            $SiteSettings[$key] = array_merge($SiteSettings[$key], $TransactionBatchjob);
        }
        
        // usort($SiteSettings, function ($a, $b) {
        //     $t1 = strtotime($a['transcation_date_start']);
        //     $t2 = strtotime($b['transcation_date_start']);
        //     return $t2 - $t1;
        // });
        // dd($SiteSettings);
        foreach ($SiteSettings as $key => $value) {
            $sitename = $value['name'];
            $status = 'Online';
            $tran = $value["transcation_date_start"];
            $dateDiffMin = $dateNow->diffInMinutes($tran);
            // dd($dateDiffMin);
            if ($dateDiffMin > 5) {
                $status = "Offine";
                $url        = 'https://notify-api.line.me/api/notify';
                $token      = 'pBiaQCPIVGOpTk8i8T23dNQzpr3xh4qLTwQzXXuXve1';
                $headers    = [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $token
                ];
                $fields     = "message=\nAlert Auto Notification\n---------------\n" .
                    "Site: " . $sitename . "\n" .
                    "Status: " . $status . "\n" .
                    "Last Online: " . $tran . "\n";

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                curl_close($ch);

                var_dump($result);
                $result = json_decode($result, TRUE);
            }
        }
    }
}
