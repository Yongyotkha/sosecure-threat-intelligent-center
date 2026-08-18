<div class="modal-dialog modal-dialog-aside" style="max-width:720px;">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">View Log Data</h4>
        </div>
        <div class="modal-body">
            <p class="text-muted" style="margin-top:0;">
                <strong>{{ $agent_label }}</strong>
                @if(!empty($agent_ip))
                    <span> · IP {{ $agent_ip }}</span>
                @endif
            </p>

            <p><strong>Scan activity</strong> <small class="text-muted">(latest {{ count($scan_logs) }})</small></p>
            <div class="table-responsive" style="max-height:280px; overflow:auto;">
                <table class="table table-bordered table-striped table-condensed" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Mode</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scan_logs as $row)
                            <tr>
                                <td>{{ $row->mode ?: '-' }}</td>
                                <td>{{ $row->first_scan ?: '-' }}</td>
                                <td>{{ $row->last_scan ?: '-' }}</td>
                                <td style="word-break:break-word;">{{ $row->description ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No scan log data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="mt-3"><strong>Detection alerts</strong> <small class="text-muted">(latest {{ count($alert_logs) }})</small></p>
            <div class="table-responsive" style="max-height:280px; overflow:auto;">
                <table class="table table-bordered table-striped table-condensed" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th>Rule</th>
                            <th>Path</th>
                            <th>Last scan</th>
                            <th>Channel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alert_logs as $row)
                            <tr>
                                <td style="word-break:break-word;">{{ $row->rule ?: '-' }}</td>
                                <td style="word-break:break-all;">{{ $row->path ?: '-' }}</td>
                                <td>{{ $row->last_scan ?: ($row->created_at ?: '-') }}</td>
                                <td>{{ $row->channel ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No detection alerts</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
        </div>
    </div>
</div>
