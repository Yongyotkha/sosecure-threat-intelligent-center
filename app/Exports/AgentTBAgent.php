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

use App\FXSiteAgents;

class AgentTBAgent implements FromView, WithTitle
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
        $filter_agent_device = @$this->data['filter_agent_device'];
        $filter_agent_ip = @$this->data['filter_agent_ip'];
        $filter_agent_os_des = @$this->data['filter_agent_os_des'];
        $check_os_type = @$this->data['check_os_type'];
        $start_date = @$this->data['start_date'];
        $end_date = @$this->data['end_date'];

        $query = FXSiteAgents::
            join('site', 'site_agents.site_id', 'site.id')
            ->join('os_type', 'site_agents.os_type', 'os_type.id')
            ->select(
                'site.name as site_name',
                'site.logo as site_logo',
                'os_type.name as os_type_name',
                'site_agents.id as site_agents_id',
                'site_agents.device_name as site_agents_device_name',
                'site_agents.os_description as site_agents_os_description',
                'site_agents.system_info as site_agents_system_info',
                'site_agents.domain as site_agents_domain',
                'site_agents.ip_private as site_agents_ip_private',
                'site_agents.last_online as site_agents_last_online',
                'site_agents.status as site_agents_status',
                'site_agents.created_at as site_agents_created',
                'site_agents.batchjob_everydate',
                'site_agents.real_time_protection',
                'site_agents.usb_protection'
            )
            ->where('site_agents.deleted_at', null)
            ->where(function($query) use ($site_id){
                if($site_id != null)
                {
                    $query->where('site_agents.site_id', $site_id);
                }
            })
            ->where(function($query) use ($keyword_search){
                if($keyword_search != null) 
                {
                    $query->where('site.name', 'like', '%'.$request->keyword_search.'%')
                        ->orwhere('site_agents.device_name', 'like', '%'.$request->keyword_search.'%')
                        ->orwhere('os_type.name', 'like', '%'.$request->keyword_search.'%')
                        ->orwhere('site_agents.system_info', 'like', '%'.$request->keyword_search.'%')
                        ->orwhere('site_agents.domain', 'like', '%'.$request->keyword_search.'%')
                        ->orwhere('site_agents.ip_private', 'like', '%'.$request->keyword_search.'%');
                }
            })
            ->where(function($query) use ($start_date, $end_date){
                if($start_date != null && $end_date != null)
                {
                    $query->whereBetween('site_agents.last_online', [$start_date, $end_date]);
                }
            })
            ->where(function($query) use ($filter_agent_device){
                if($filter_agent_device)
                {
                    $query->where('site_agents.device_name', 'like', '%'.$filter_agent_device.'%');
                }
            })
            ->where(function($query) use ($filter_agent_ip){
                if($filter_agent_ip)
                {
                    $query->where('site_agents.ip_private', 'like', '%'.$filter_agent_ip.'%');
                }
            })
            ->where(function($query) use ($check_os_type){
                if($check_os_type)
                {
                    $query->where('site_agents.os_type', $check_os_type);
                }
            })
            ->where(function($query) use ($filter_agent_os_des){
                if($filter_agent_os_des)
                {
                    $query->where('site_agents.os_description', 'like', '%'.$filter_agent_os_des.'%');
                }
            })
            ->get();

        return view('agentmanagement::export.excel_tb_agent', [
            'query' => $query
        ]);
    }
}
