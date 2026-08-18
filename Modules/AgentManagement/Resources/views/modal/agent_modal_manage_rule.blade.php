<div class="modal-dialog modal-dialog-aside fullscreen size-half-50">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">
                <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" data-rel="tooltip"
                    title="Fullscreen" data-placement="right"></i>
                Manage Rules
                <small class="text-white" style="opacity:.85; margin-left:8px;">{{ $agent_label ?? '' }}</small>
            </h4>
        </div>

        <div class="modal-body">
            {{-- Search/filter outside <form> so Enter never POSTs updateManageRule --}}
            <div class="row" style="margin-bottom:10px;">
                <div class="col-sm-5">
                    <input type="text" id="manage_rule_search" class="form-control"
                        placeholder="Search name / file / category"
                        autocomplete="off"
                        oninput="window.manageRuleApplyFilters && window.manageRuleApplyFilters()"
                        onkeydown="if(event.key==='Enter'||event.keyCode===13){event.preventDefault();return false;}">
                </div>
                <div class="col-sm-3">
                    <select id="manage_rule_type_filter" class="form-control"
                        onchange="window.manageRuleApplyFilters && window.manageRuleApplyFilters()">
                        <option value="all">All (YARA + Ssdeep)</option>
                        <option value="yara">YARA only</option>
                        <option value="ssdeep">Ssdeep only</option>
                    </select>
                </div>
                <div class="col-sm-4 text-right">
                    <button type="button" class="btn btn-default btn-sm" id="btn_ignore_all"
                        onclick="window.manageRuleIgnoreAll && window.manageRuleIgnoreAll()">Ignore all</button>
                    <button type="button" class="btn btn-default btn-sm" id="btn_clear_ignore"
                        onclick="window.manageRuleClearIgnore && window.manageRuleClearIgnore()">Clear ignore</button>
                </div>
            </div>

            <form id="form_submit_manage_rule" action="{{ route('agentmanagement.updateManageRule') }}" method="POST"
                onsubmit="return window.manageRuleSaveForm ? window.manageRuleSaveForm(event) : false;">
                @csrf
                <input type="hidden" name="site_id" value="{{ $site_id }}">
                <input type="hidden" name="agent_id" value="{{ $agent_id }}">

                <div class="table-responsive">
                    <table id="tbl_manage_rule" class="table table-striped table-bordered" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>File / Version</th>
                                <th>Severity / Sigs</th>
                                <th>Ignore</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ruleNameSite as $item)
                                @php $rid = (int) $item->rule_id; @endphp
                                <tr data-type="yara"
                                    data-search="{{ strtolower(($item->category_name ?? '').' '.($item->rule_name ?? '').' '.($item->file_name ?? '').' '.($item->description ?? '').' yara') }}">
                                    <td><span class="label label-primary">YARA</span></td>
                                    <td>{{ $item->category_name }}</td>
                                    <td>{{ $item->rule_name }}</td>
                                    <td><small>{{ $item->file_name }}</small></td>
                                    <td>{{ $item->severity }}</td>
                                    <td>
                                        <input type="hidden" name="rule_ids[]" value="{{ $rid }}">
                                        <label style="font-weight:normal; margin:0;">
                                            <input class="ignore-chk" name="ignore[{{ $rid }}]" value="1" type="checkbox"
                                                data-kind="yara"
                                                {{ in_array($rid, $ignoredIds ?? [], true) ? 'checked' : '' }}>
                                            <span class="label-text">Off</span>
                                        </label>
                                    </td>
                                    <td>{{ $item->created_at }}</td>
                                </tr>
                            @endforeach

                            @if(!empty($ssdeep_ready))
                                @forelse(($ssdeep_site_packs ?? collect()) as $pack)
                                    @php
                                        $pid = (int) $pack->id;
                                        $label = method_exists($pack, 'displayLabel') ? $pack->displayLabel() : (($pack->version ?? '').' — '.($pack->file_name ?: ''));
                                    @endphp
                                    <tr data-type="ssdeep"
                                        data-search="{{ strtolower(($pack->category ?? '').' '.$label.' '.($pack->file_name ?? '').' '.($pack->version ?? '').' ssdeep') }}">
                                        <td><span class="label label-warning">Ssdeep</span></td>
                                        <td>{{ $pack->category ?: '-' }}</td>
                                        <td>{{ $label }}</td>
                                        <td><small>{{ $pack->file_name ?: ($pack->version ?: '-') }}</small></td>
                                        <td>{{ (int) $pack->signature_count }} sigs</td>
                                        <td>
                                            <input type="hidden" name="ssdeep_pack_ids[]" value="{{ $pid }}">
                                            <label style="font-weight:normal; margin:0;">
                                                <input class="ignore-chk" name="ssdeep_ignore[{{ $pid }}]" value="1" type="checkbox"
                                                    data-kind="ssdeep"
                                                    {{ in_array($pid, $ignoredSsdeepIds ?? [], true) ? 'checked' : '' }}>
                                                <span class="label-text">Off</span>
                                            </label>
                                        </td>
                                        <td>-</td>
                                    </tr>
                                @empty
                                    <tr data-type="ssdeep" data-search="ssdeep" class="manage-rule-empty-ssdeep">
                                        <td colspan="7" class="text-muted">No ssdeep packs assigned to this site.</td>
                                    </tr>
                                @endforelse
                            @else
                                <tr data-type="ssdeep" data-search="ssdeep" class="manage-rule-empty-ssdeep">
                                    <td colspan="7" class="text-muted">Ssdeep is not available on this Center.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer" style="padding-left:0; padding-right:0;">
                    {!! closeModalButton() !!}
                    <button type="button" class="btn btn-info formSaving btn-rounded"
                        onclick="window.manageRuleSaveForm && window.manageRuleSaveForm(event)">
                        <i class="fas fa-paper-plane"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
