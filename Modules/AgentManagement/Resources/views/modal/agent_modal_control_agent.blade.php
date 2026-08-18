<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Control Agent</h4>
        </div>
        {!! Form::open(['route' => ['agentmanagement.update_control_agent'], 'class' => 'ajaxifyForm_custom', 'id'=> 'form_update', 'method' => 'post']) !!}
        <div class="modal-body">
            <input type="hidden" name="hd_id" value="{{ $id }}">
            @if(!empty($config_updated_at_label))
                <p class="text-muted" style="margin:0 0 8px;">
                    <small>Last config update: {{ $config_updated_at_label }}</small>
                </p>
            @endif

            <div class="row mt-2">
                <div class="col-lg-12"><p><strong>Protection &amp; schedule</strong></p></div>

                <div class="col-lg-12">
                    <p>Batch scan time <small class="text-muted">(once daily)</small></p>
                </div>
                <div class="col-lg-2"><span>at:</span></div>
                <div class="col-lg-10">
                    <select name="batch_start" id="batch_start" class="form-control">
                        @foreach(($batch_time_options ?? []) as $hhmm => $label)
                            <option value="{{ $hhmm }}" {{ (string)$batchjob === (string)$hhmm ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-12">
                    <p>Threat intelligence sync (rules + ssdeep)</p>
                </div>
                <div class="col-lg-2"><span>every:</span></div>
                <div class="col-lg-10">
                    <select name="ti_sync_start" id="ti_sync_start" class="form-control">
                        @foreach(($interval_options ?? []) as $mins => $label)
                            <option value="{{ $mins }}" {{ (string)$ti_sync === (string)$mins ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-12">
                    <p>Agent update check</p>
                </div>
                <div class="col-lg-2"><span>every:</span></div>
                <div class="col-lg-10">
                    <select name="agent_update_schedule" id="agent_update_schedule" class="form-control">
                        @foreach(($agent_update_options ?? []) as $mins => $label)
                            <option value="{{ $mins }}" {{ (string)($agent_update_schedule ?? '1440') === (string)$mins ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-12">
                    <p>Real-time protection</p>
                    <label>
                        <input name="real_time" value="Y" type="checkbox" class="select-chk" {{ in_array((string)$real_time, ['Y','1'], true) ? 'checked' : ''}}>
                        <span class="label-text">On</span>
                    </label>
                </div>
                <div class="col-lg-12">
                    <p>USB Protection</p>
                    <label>
                        <input name="usb" value="Y" type="checkbox" class="select-chk" {{ in_array((string)$usb, ['Y','1'], true) ? 'checked' : ''}}>
                        <span class="label-text">On</span>
                    </label>
                </div>
                <div class="col-lg-12">
                    <p>Auto scan on login</p>
                    <label>
                        <input name="auto_scan_on_login" value="Y" type="checkbox" class="select-chk" {{ in_array((string)$auto_scan_on_login, ['Y','1'], true) ? 'checked' : ''}}>
                        <span class="label-text">On</span>
                    </label>
                </div>

                <div class="col-lg-12 mt-3"><hr><p><strong>Scan scope</strong></p></div>

                <div class="col-lg-12">
                    <p>Excluded paths <small class="text-muted">(empty by default — add if needed)</small></p>
                    <div id="exclusion-paths-list" class="path-list">
                        @foreach($exclusion_path_list as $path)
                            <div class="input-group path-row" style="margin-bottom:6px;">
                                <input type="text" name="exclusion_paths[]" class="form-control" value="{{ $path }}" placeholder="e.g. \windows">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-remove-path" title="Remove">&times;</button>
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-default" id="btn-add-exclusion">+ Add path</button>
                </div>

                <div class="col-lg-12 mt-3">
                    <p>
                        Scan extensions
                        <small class="text-muted" style="margin-left:8px;">
                            <a href="javascript:void(0)" id="ext-select-all">Select all</a> /
                            <a href="javascript:void(0)" id="ext-clear-all">Clear</a>
                        </small>
                    </p>
                    <div class="scan-ext-grid" style="max-height:220px; overflow:auto; border:1px solid #ddd; border-radius:4px; padding:10px;">
                        <div class="row">
                            @foreach($scan_extension_options as $ext)
                                <div class="col-xs-6 col-sm-4 col-md-3" style="margin-bottom:6px;">
                                    <label style="font-weight:normal; margin:0;">
                                        <input type="checkbox" class="scan-ext-chk" name="scan_extensions[]" value="{{ $ext }}"
                                            {{ in_array($ext, $scan_extensions_selected, true) ? 'checked' : '' }}>
                                        <span class="label-text">{{ $ext }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <p style="margin:10px 0 4px;"><small class="text-muted">Extra extensions (comma-separated, optional)</small></p>
                    <input type="text" name="scan_extensions_extra" class="form-control" value="{{ $scan_extensions_extra }}" placeholder=".foo,.bar">
                </div>

                {{-- Quick scan paths UI hidden; FeatureQuickScanCustomPaths off on agent --}}

                <div class="col-lg-12 mt-3"><hr><p><strong>Ssdeep</strong></p></div>

                {{-- Policy locked: engine/report/candidate On, quarantine Off (UI hidden) --}}
                <input type="hidden" name="ssdeep_enabled" value="Y">
                <input type="hidden" name="ssdeep_report_api" value="Y">
                <input type="hidden" name="quarantine_on_detect" value="N">
                <input type="hidden" name="send_ssdeep_candidate" value="Y">

                <div class="col-lg-12">
                    <p>Ssdeep match threshold (0–100)</p>
                    <input type="number" min="0" max="100" step="1" name="ssdeep_threshold" class="form-control" value="{{ (int)$ssdeep_threshold }}" style="max-width:120px;">
                </div>

            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            <button type="submit" class="btn btn-info formSaving btn-rounded"><i class="fas fa-paper-plane"></i>Submit</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<script>
    $(document).ready(function () {
        let time = "{{ $batchjob }}";
        let tiSync = "{{ $ti_sync ?? '60' }}";
        let agentUpd = "{{ $agent_update_schedule ?? '1440' }}";
        setTimeout(() => {
            $('#batch_start').val(time).trigger('change');
            $('#ti_sync_start').val(tiSync).trigger('change');
            $('#agent_update_schedule').val(agentUpd).trigger('change');
        }, 200);

        function addPathRow(listSelector, name, placeholder) {
            var html = '<div class="input-group path-row" style="margin-bottom:6px;">' +
                '<input type="text" name="' + name + '" class="form-control" value="" placeholder="' + placeholder + '">' +
                '<span class="input-group-btn">' +
                '<button type="button" class="btn btn-default btn-remove-path" title="Remove">&times;</button>' +
                '</span></div>';
            $(listSelector).append(html);
        }

        $('#btn-add-exclusion').on('click', function () {
            addPathRow('#exclusion-paths-list', 'exclusion_paths[]', 'e.g. \\windows');
        });
        $(document).on('click', '.btn-remove-path', function () {
            $(this).closest('.path-row').remove();
        });
        $('#ext-select-all').on('click', function () {
            $('.scan-ext-chk').prop('checked', true);
        });
        $('#ext-clear-all').on('click', function () {
            $('.scan-ext-chk').prop('checked', false);
        });
    });

    var form_save = '.formSaving';
    $('#form_update').submit(function (event) {
        event.preventDefault();
        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        $('.formSaving').attr('disabled',true);
        var data = new FormData(this);
        axios.post($(this).attr("action"), data)
            .then(function (response) {
                toastr.success('Update Successfully');
                $('#ajaxModal').modal("hide");
                $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                tbl_agent.ajax.reload();
            })
            .catch(function (error) {
                $('.formSaving').attr('disabled',false);
                if(error.response && error.response.data && error.response.data.exception) {
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                } else if (error.response && error.response.data && error.response.data.errors) {
                    var errorsHtml= '';
                    $.each(error.response.data.errors, function (key, value) {
                        errorsHtml += '<li>' + value[0] + '</li>';
                    });
                    toastr.error(errorsHtml , '@langapp('response_status') ');
                } else {
                    toastr.error('@langapp('request_failed')');
                }
                $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
            });
    });
</script>
