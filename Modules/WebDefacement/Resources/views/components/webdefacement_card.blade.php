<div class="item-wdfm wdfm-inner-3">
    <div class="wdfm-card">
        <div class="wdfm-header">
            <div class="wdfm-img">
                <!-- <a href="{{ config('app.URL_CENTER_PUBLISH') . $key->image_last }}" data-lightbox="name-img-2">
                    <img src="{{ config('app.URL_CENTER_PUBLISH') . $key->image_last }}" onerror="setDefaultPic(this)"/>
                </a> -->
                <a href="{{ 'https://insights-staging.sosecure.co.th'. $key->image_last }}" data-lightbox="name-img-2">
                    <img src="{{ 'https://insights-staging.sosecure.co.th' . $key->image_last }}" onerror="setDefaultPic(this)"/>
                </a>
                
            </div>
        </div>
        <div class="wdfm-body">
            <div class="wdfm-btn">
                <a href="{{ route('webdefacement.detail', ['code' => $key->code]) }}" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View">
                    <i class="fas fa-eye"></i>
                </a>
            </div>
            <h4 class="wdfm-elip">{{ $key->name }}</h4>
            <p class="mdfm-text-muted">{{ $key->url }}</p>
        </div>
        <div class="wdfm-footer">
            <div class="wdfm-ft-left flex" style="width: 50%">
                <div><strong>Site </strong>: {{ $key->get_site->name ?? '-' }}</div>
                <div class="status-flex mr-2"><strong>Status</strong> : {!! get_webdefacment_status($key->status_val, 'color') !!}</div>
                <div class="text-sm-date">Last Online: {{ $key->last_online }}</div>
                <div class="text-sm-date">Last Check: {{ $key->last_check }}</div>
            </div>
            <div class="wdfm-ft-left flex" style="width: 50%">
                <div id="chart_wdfm_{{ $key->id }}" style="height: 180px"></div>
            </div>
        </div>

        <div class="wdfm-footer-action">
            <div style="display: flex;justify-content:center;">
                <a href="{{ route('webdefacement.detail', ['code' => $key->code]) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-eye"></i> View
                </a>

                @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                    <a href="#" onclick="btn_click_edit_webdefacement('{{ $key->code }}')" class="btn btn-info btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="#" onclick="btn_click_del_webdefacement({{ $key->id }})" class="btn btn-danger btn-sm btn_del_webdefacment">
                        <i class="fas fa-trash-alt"></i> Delete
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
