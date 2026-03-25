<table>
    <thead>
        <tr>
            <th>Site Name</th>
            {{-- <th>Detail</th> --}}
            <th>Rule</th>
            <th>Description</th>
            <th>Path</th>
            <th>Last Scan</th>
            <th>IP</th>
            <th>OS Type</th>
            <th>OS Description</th>
            <th>Channel</th>
            <th>Severity</th>
            <th>Datetime</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $data)
        <tr>
            <td>{{ @$data->site_name }}</td>
            <td>{{ @$data->agent_alerts_rule }}</td>
            <td>{{ @$data->agent_alerts_description }}</td>
            <td>{{ @$data->path }}</td>
            <td>{{ @$data->last_scan }}</td>
            <td>{{ @$data->site_agents_ip_private }}</td>
            <td>{{ @$data->os_type_name }}</td>
            <td>{{ @$data->site_agents_os_description }}</td>
            <td>{{ @$data->channel }}</td>
            <td>{{ @$data->severity_status }}</td>
            <td>{{ @$data->agent_alerts_created }}</td>
        </tr>
        @endforeach
    </tbody>
</table>