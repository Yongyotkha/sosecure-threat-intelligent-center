<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

use Maatwebsite\Excel\Concerns\WithTitle;
use DB;

use App\YaraLog;

class AgentTBAlert implements FromView, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    use Exportable;

    // private $data;
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'SHEET1';
    }

    public function view(): View    
    {
        //
        // dd($this->data['customer_tel']);

        $site_id = @$this->data['site'];

        $keyword_search = @$this->data['keyword_search'];
        $filter_alert_rule = @$this->data['filter_alert_rule'];
        $filter_alert_des = @$this->data['filter_alert_des'];
        $check_alert = @$this->data['check_alert'];
        $check_alert_severity = @$this->data['check_alert_severity'];
        $check_alert_ignore = @$this->data['check_alert_ignore'];
        $start_date = @$this->data['start_date'];
        $end_date = @$this->data['end_date'];

        $query = DB::table('yara_log')
            ->join('site', 'yara_log.site_id', 'site.id')
            ->join('site_agents', 'yara_log.agent_id', 'site_agents.id')
            ->leftjoin('os_type', 'site_agents.os_type', 'os_type.id')
            ->leftjoin('rule_name', 'yara_log.rule', 'rule_name.rule_name')
            ->select(
                'site.name as site_name',
                'site.logo as site_logo',
                'site_agents.ip_private as site_agents_ip_private',
                'site_agents.os_description as site_agents_os_description',
                'yara_log.id as agent_alerts_id',
                'yara_log.rule as agent_alerts_rule',
                'yara_log.status as agent_alerts_status',
                'yara_log.updated_at as agent_alerts_created',
                'yara_log.device_name',
                'yara_log.first_scan',
                'yara_log.last_scan',
                'yara_log.channel',
                'yara_log.ignore_flag',
                'yara_log.path',
                'rule_name.description as agent_alerts_description',
                'rule_name.severity as severity_status',
                'os_type.name as os_type_name'
            )
            ->where('yara_log.status', 1)
            ->where(function($query) use ($site_id){
                if($site_id != null && $site_id != '')
                {
                    $query->where('yara_log.site_id', $site_id);
                }
            })
            ->where(function($query) use ($keyword_search){
                if($keyword_search != null) {
                    $query->where('site.name', 'like', '%'.$keyword_search.'%')
                        ->orwhere('yara_log.description', 'like', '%'.$keyword_search.'%');     
                }
            });

        $ids = @$this->data['ids'];
        if ($ids) {
            $ids_array = explode(',', $ids);
            $query->whereIn('yara_log.id', $ids_array);
        }

        if($start_date != null && $end_date != null)
        {
            $query->whereBetween('yara_log.last_scan', [$start_date, $end_date]);
        }

        if($filter_alert_rule != null)
        {
            $query->where('yara_log.rule', 'like', '%'.$filter_alert_rule.'%');
        }

        if($filter_alert_des != null)
        {
            $query->where('rule_name.description', 'like', '%'.$filter_alert_des.'%');
        }

        if($check_alert_severity != null)
        {
            $query->where('rule_name.severity', $check_alert_severity);
        }

        if(@$check_alert_ignore == 'all' || @$check_alert_ignore == '1')
        {
            if($check_alert_ignore == 'all')
            {
                $query->whereIn('yara_log.ignore_flag', ['Y','N']);
            }
            else if($check_alert_ignore == '1')
            {
                $query->where('yara_log.ignore_flag', 'N');
            }
        }
        else
        {
            $query->where('yara_log.ignore_flag', 'Y');
        }

        $query = $query->orderBy('yara_log.updated_at', 'desc')->get();

        return view('agentmanagement::export.excel_tb_alert', [
            'query' => $query
        ]);
    }
}
