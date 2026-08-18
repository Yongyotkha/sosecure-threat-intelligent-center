@extends('layouts.app')

@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="m-t-10 m-b-10 clearfix">
                <div class="pull-left">
                    <strong>Agent Releases (OTA)</strong>
                    <div class="text-muted"><small>Upload agent builds, set target version, or roll back by selecting an older package.</small></div>
                </div>
                <div class="pull-right">
                    <a href="{{ route('agentmanagement.index') }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left"></i> Back to Agent Management
                    </a>
                </div>
            </div>
        </header>

        <section class="scrollable wrapper">
            <div class="alert alert-info" style="margin-bottom:15px;">
                <strong>Package types + OS</strong>
                <ul class="m-b-0" style="padding-left:18px; margin-top:6px;">
                    <li><strong>First-install</strong> — pick OS (Windows / Debian / Ubuntu / CentOS / Fedora). Shown as icons on Data Setting.</li>
                    <li><strong>Windows first-install</strong> — Inno <code>SOSECURE_Threat_inSight_Go_Setup_*.exe</code> (~90MB).</li>
                    <li><strong>Linux first-install</strong> — <code>.zip</code> / <code>.deb</code> / <code>.rpm</code> / <code>.whl</code> / <code>.tar.gz</code> for that distro.</li>
                    <li><strong>OTA agent binary</strong> — Windows only: <code>dist/insite-agent.exe</code> (~30MB). Updates agents already installed.</li>
                </ul>
                @if($globalTarget)
                    <p class="m-t-10 m-b-0">
                        Current <strong>global OTA</strong> target:
                        <span class="label label-primary">{{ $globalTarget->target_version }}</span>
                    </p>
                @else
                    <p class="m-t-10 m-b-0 text-muted">No global OTA target set yet.</p>
                @endif
            </div>

            <div class="panel panel-default" style="margin-bottom:15px;">
                <div class="panel-heading">Deploy checklist</div>
                <div class="panel-body" style="padding-top:10px; padding-bottom:10px;">
                    <ol class="m-b-0" style="padding-left:18px;">
                        <li>Ensure Center DB has OTA tables + <code>kind</code> + <code>os</code> columns (migrations under <code>2026_07_29_*</code> and <code>2026_08_07_*</code>).</li>
                        <li>Upload first-install packages per OS; upload Windows <strong>insite-agent.exe</strong> for OTA.</li>
                        <li>Set OTA target from the Windows agent-binary package; confirm Control Agent update schedule.</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="panel panel-default">
                        <div class="panel-heading">Upload package</div>
                        <div class="panel-body">
                            <form id="form-upload-release" method="POST" action="{{ route('agentmanagement.agent_release_upload') }}" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>Package type</label>
                                    <select name="kind" id="upload_kind" class="form-control" required>
                                        <option value="installer">First-install package</option>
                                        <option value="agent_binary">OTA agent binary (Windows only)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>OS</label>
                                    <select name="os" id="upload_os" class="form-control" required>
                                        <option value="windows">Windows</option>
                                        <option value="debian">Debian</option>
                                        <option value="ubuntu">Ubuntu</option>
                                        <option value="centos">CentOS</option>
                                        <option value="fedora">Fedora</option>
                                    </select>
                                    <small class="text-muted" id="upload_os_hint">Choose which platform this package is for.</small>
                                </div>
                                <div class="form-group">
                                    <label>Version</label>
                                    <input type="text" name="version" class="form-control" placeholder="e.g. 5.6.0" required>
                                </div>
                                <div class="form-group">
                                    <label>File</label>
                                    <input type="file" name="file_agent" id="upload_file" accept=".exe,.zip,.deb,.rpm,.whl,.tar.gz,.tgz,.bin" class="form-control" required>
                                    <small class="text-muted" id="upload_file_hint">Windows: .exe — Linux: .zip / .deb / .rpm / .whl / .tar.gz</small>
                                </div>
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="What changed in this build"></textarea>
                                </div>
                                <div class="form-group" id="ota-target-fields">
                                    <label>Assign as OTA target after upload</label>
                                    <select name="site_id" class="form-control">
                                        <option value="global">Global (all sites)</option>
                                        @foreach($site_settings as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="m-t-10" style="font-weight:normal;">
                                        <input type="checkbox" name="set_as_target" value="1">
                                        Set as OTA target version now
                                    </label>
                                </div>
                                <div class="m-t-15">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Upload release
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">Set / rollback OTA target</div>
                        <div class="panel-body">
                            <form id="form-set-target">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label>OTA package (agent binary only)</label>
                                    <select name="package_id" class="form-control" required>
                                        @foreach($packages as $pack)
                                            @php
                                                $pk = strtolower(trim((string) ($pack->kind ?? 'agent_binary')));
                                                $pos = strtolower(trim((string) ($pack->os ?? 'windows')));
                                                if ($pos === '') { $pos = 'windows'; }
                                            @endphp
                                            @if($pack->status === 'Y' && $pk !== 'installer' && $pos === 'windows')
                                                <option value="{{ $pack->id }}">{{ $pack->version }} — {{ $pack->file_name }} ({{ ucfirst($pos) }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Scope</label>
                                    <select name="site_id" class="form-control">
                                        <option value="global">Global (all sites)</option>
                                        @foreach($site_settings as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-undo"></i> Set as target (supports rollback)
                                </button>
                            </form>
                            @if($globalTarget)
                                <p class="m-t-10 text-muted">
                                    Current global target: <strong>{{ $globalTarget->target_version }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="panel panel-default">
                        <div class="panel-heading">Published packages</div>
                        <div class="panel-body" style="overflow:auto;">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Version</th>
                                        <th>Kind</th>
                                        <th>OS</th>
                                        <th>File</th>
                                        <th>SHA256</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($packages as $pack)
                                        @php
                                            $pk = strtolower(trim((string) ($pack->kind ?? 'agent_binary')));
                                            $kindLabel = $pk === 'installer' ? 'First-install' : 'OTA binary';
                                            $osKey = strtolower(trim((string) ($pack->os ?? 'windows')));
                                            if ($osKey === '') { $osKey = 'windows'; }
                                            $osLabel = ucfirst($osKey);
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $pack->version }}</strong></td>
                                            <td><span class="label {{ $pk === 'installer' ? 'label-warning' : 'label-primary' }}">{{ $kindLabel }}</span></td>
                                            <td><span class="label label-default">{{ $osLabel }}</span></td>
                                            <td>
                                                {{ $pack->file_name }}
                                                @if(!empty($pack->path))
                                                    <br><a href="{{ $pack->path }}" target="_blank" rel="noopener"><small>Download</small></a>
                                                @endif
                                            </td>
                                            <td><small>{{ $pack->sha256 }}</small></td>
                                            <td>{{ number_format((int)$pack->size_bytes) }}</td>
                                            <td>{{ $pack->status === 'Y' ? 'Active' : 'Inactive' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-xs btn-default btn-toggle-pack" data-id="{{ $pack->id }}">
                                                    Toggle
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8">No releases uploaded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">Recent update events</div>
                        <div class="panel-body" style="overflow:auto; max-height:360px;">
                            <table class="table table-condensed" id="tbl-release-events">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Agent</th>
                                        <th>Current</th>
                                        <th>Target</th>
                                        <th>Status</th>
                                        <th>Message</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</section>
@endsection

@push('pagescript')
<script>
(function () {
    function loadEvents() {
        $.get("{{ route('agentmanagement.agent_release_events') }}", function (res) {
            var rows = (res && res.data) ? res.data : [];
            var html = '';
            rows.forEach(function (e) {
                html += '<tr>' +
                    '<td>' + e.id + '</td>' +
                    '<td>' + (e.agent_id || '-') + '<br><small>' + (e.ip_private || '') + '</small></td>' +
                    '<td>' + (e.current_version || '-') + '</td>' +
                    '<td>' + (e.target_version || '-') + '</td>' +
                    '<td>' + (e.status || '-') + '</td>' +
                    '<td><small>' + (e.message || '') + '</small></td>' +
                    '<td><small>' + (e.created_at || '') + '</small></td>' +
                    '</tr>';
            });
            if (!html) html = '<tr><td colspan="7">No events yet.</td></tr>';
            $('#tbl-release-events tbody').html(html);
        });
    }

    function uploadErrorMessage(xhr) {
        if (!xhr) return 'Upload failed';
        if (xhr.status === 413) return 'File too large for the server (HTTP 413). Raise PHP/nginx upload limits.';
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) return xhr.responseJSON.message;
            if (xhr.responseJSON.errors) {
                var msgs = [];
                $.each(xhr.responseJSON.errors, function (k, v) {
                    msgs.push((v && v[0]) ? v[0] : k);
                });
                if (msgs.length) return msgs.join(' ');
            }
        }
        if (xhr.status === 419) return 'Session expired. Refresh the page and try again.';
        if (xhr.status === 500) return 'Server error while uploading. Check OTA tables / disk permissions.';
        return 'Upload failed (HTTP ' + (xhr.status || '?') + ')';
    }

    function syncUploadKindUI() {
        var kind = $('#upload_kind').val() || 'agent_binary';
        var $os = $('#upload_os');
        if (kind === 'installer') {
            $('#ota-target-fields').hide();
            $('#ota-target-fields input[name="set_as_target"]').prop('checked', false);
            $os.find('option').prop('disabled', false);
            $('#upload_os_hint').text('Choose which platform this first-install package is for.');
            $('#upload_file').attr('accept', '.exe,.zip,.deb,.rpm,.whl,.tar.gz,.tgz,.bin');
            $('#upload_file_hint').text('Windows: .exe (Setup) — Linux: .zip / .deb / .rpm / .whl / .tar.gz');
        } else {
            $('#ota-target-fields').show();
            $os.val('windows');
            $os.find('option').each(function () {
                $(this).prop('disabled', $(this).val() !== 'windows');
            });
            $('#upload_os_hint').text('OTA binary is Windows only (insite-agent.exe).');
            $('#upload_file').attr('accept', '.exe');
            $('#upload_file_hint').text('Upload dist/insite-agent.exe (~30MB).');
        }
    }
    $('#upload_kind').on('change', syncUploadKindUI);
    syncUploadKindUI();

    $('#form-upload-release').on('submit', function (ev) {
        ev.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var fd = new FormData(this);
        if ($form.find('#upload_kind').val() === 'installer' || !$form.find('input[name="set_as_target"]').is(':checked')) {
            fd.delete('set_as_target');
        } else {
            fd.set('set_as_target', '1');
        }
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        $.ajax({
            url: "{{ route('agentmanagement.agent_release_upload') }}",
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            timeout: 600000,
            success: function (res) {
                if (res.status === 'success') {
                    toastr.success(res.message || 'Uploaded');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(res.message || 'Upload failed');
                    $btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload release');
                }
            },
            error: function (xhr) {
                toastr.error(uploadErrorMessage(xhr));
                $btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload release');
            }
        });
    });

    $('#form-set-target').on('submit', function (ev) {
        ev.preventDefault();
        $.post("{{ route('agentmanagement.agent_release_set_target') }}", $(this).serialize())
            .done(function (res) {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(res.message || 'Failed');
                }
            })
            .fail(function () { toastr.error('Failed'); });
    });

    $(document).on('click', '.btn-toggle-pack', function () {
        var id = $(this).data('id');
        $.post("{{ route('agentmanagement.agent_release_toggle') }}", {
            _token: '{{ csrf_token() }}',
            package_id: id
        }).done(function (res) {
            if (res.status === 'success') {
                toastr.success(res.message);
                setTimeout(function () { location.reload(); }, 600);
            } else {
                toastr.error(res.message || 'Failed');
            }
        });
    });

    loadEvents();
    setInterval(loadEvents, 15000);
})();
</script>
@endpush
