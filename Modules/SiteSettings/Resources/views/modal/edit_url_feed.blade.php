    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal"
                    onclick="close_wdfm_website()">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();"
                        datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                    <span id="title_head"> Edit Website</span>
                </h4>
            </div>
            <form id="form_update_url_feed">
            {{-- {!! Form::open(['route' => ['urlfeed.update_url_feed'], 'class' => 'ajaxifyForm_custom', 'method' =>
            'POST']) !!} --}}
            <input type="hidden" name="mode" id="mode" value="create">
            <div class="modal-body">
                <div class="container-fluid">
                    <div id="site_id_show" class="form-group row">
                        <label class="col-lg-3 control-label"> Site <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <select name="site_id" id="site_id" class="select2-option form-control">
                                <option value="all" {{ $query->site_id == 'all' || $query->site_id == '0' || $query->site_id == null ? 'selected' : '' }}>All Site</option>
                                @foreach ($SiteSettings as $data_SiteSetting)
                                <option value="{{$data_SiteSetting->id}}" {{ $query->site_id == $data_SiteSetting->id ? 'selected' : '' }}>{{$data_SiteSetting->name}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="name_web" id="name_web" value="{{ $query->source }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">URL <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="url_web" id="url_web" value="{{ $query->url }}" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Port <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <div class="input-group">
                                <input type="text" class="form-control" name="port_web" id="port_web" value="{{ $query->port }}"
                                    required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" onclick="get_check_site()">Check</button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div id="area_check_message_row" class="form-group row" style="display: none;"><label
                            class="col-lg-3 control-label"> </label>
                        <div class="col-lg-9">
                            <div id="area_check_message"></div>
                        </div>
                    </div>

                    <input type="hidden" name="site" id="site">
                    <input type="hidden" name="hd_code" id="hd_code" value="{{ $query->code }}">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal"
                    onclick="close_wdfm_website()">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                {{-- disabled --}}
                <button id="btn_save" type="button" onclick="update_url_feed();" class="btn btn-info btn-rounded formSaving" disabled>
                    <i class="fas fa-paper-plane"></i>
                    Save
                </button>
            </div>
            {{-- {!! Form::close() !!} --}}
            </form>
        </div>
    </div>