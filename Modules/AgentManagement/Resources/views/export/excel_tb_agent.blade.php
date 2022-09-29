<table>
    <thead>
        <tr>
            <th>Site Name</th>
            <th>Device Name</th>
            <th>OS Type</th>
            <th>OS Description</th>
            <th>System Info</th>
            <th>Domain</th>
            <th>IP</th>
            <th>Last Online</th>
            <th>Online Status</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $data)
        <tr>
            <td>{{ @$data->site_name }}</td>
            <td>{{ @$data->site_agents_device_name }}</td>
            <td>{{ @$data->os_type_name }}</td>
            <td>{{ @$data->site_agents_os_description }}</td>
            <td>{{ @$data->site_agents_system_info }}</td>
            <td>{{ @$data->site_agents_domain }}</td>
            <td>{{ @$data->site_agents_ip_private }}</td>
            <td>{{ @$data->site_agents_last_online }}</td>
            <td>
                @php
                    $html = '';
                    $date1 = @$data->site_agents_last_online;

                    $date1_d = date('d', strtotime($date1));
                    $date1_m = date('m', strtotime($date1));
                    $date1_y = date('Y', strtotime($date1));
                    $sumdate_1 = $date1_d.''.$date1_m.''.$date1_y;

                    $date2 = date('Y-m-d H:i:s');
                    $date2_d = date('d', strtotime($date2));
                    $date2_m = date('m', strtotime($date2));
                    $date2_y = date('Y', strtotime($date2));
                    $sumdate_2 = $date2_d.''.$date2_m.''.$date2_y;

                    $onlineTime = date('i', strtotime($date1));
                    $onlineTimeH = date('H', strtotime($date1));

                    $currentTime = date('i', strtotime($date2));
                    $currentTimeH = date('H', strtotime($date2));

                    if($sumdate_1 == $sumdate_2){
                        if($onlineTimeH == $currentTimeH){
                            if($onlineTime < $currentTime){
                                $sumTime = $currentTime - $onlineTime;
                                if($sumTime > 2){
                                    $html = 'Offline';
                                }else{
                                    $html = 'Online';
                                }
                            }else{
                                $sumTime = $onlineTime - $currentTime;
                                if($sumTime > 2){
                                    $html = 'Offline';
                                }else{
                                    $html = 'Online';
                                }
                            }
                        }else{
                            $html = 'Offline';
                        }
                    }else{
                        $html = 'Offline';
                    }
                @endphp

                {{ @$html }}
            </td>
            <td>{{ @$data->site_agents_status == 1 ? 'Active' : 'Inactive' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>