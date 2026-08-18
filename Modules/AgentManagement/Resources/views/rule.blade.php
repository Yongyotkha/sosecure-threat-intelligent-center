@extends('layouts.app')

@section('content')

<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow" style="height: 47px;">
                <div class="fwb-16">
                    <span style="margin-top: 2px">
                        Agent Rule
                    </span>
                </div>

                <div class="ml-2 text-right">
                
                    <button id="add_rule" data-toggle="modal" data-target="#add_rule_modal"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                        <span data-rel="tooltip" title="Add Rule" data-placement="top">@icon('solid/plus')<span class="hide-text">Add Rule</span></span>
                    </button>

                    <button id="add_category" data-toggle="modal" data-target="#add_category_modal"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                        <span data-rel="tooltip" title="Add Category" data-placement="top">@icon('solid/plus')<span class="hide-text">Add Category</span></span>
                    </button>

                    {{-- Extension catalog superseded by Control Agent → Scan extensions --}}
                    <button id="btn_add_extension" data-toggle="modal" data-target="#add_extension_modal"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }} hide" style="display:none;">
                        <span data-rel="tooltip" title="Add Extension" data-placement="top">@icon('solid/plus')<span class="hide-text">Add Extension</span></span>
                    </button>

                    <button id="add_ssdeep_pack" data-toggle="modal" data-target="#add_ssdeep_pack_modal"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                        <span data-rel="tooltip" title="Add Master Ssdeep" data-placement="top">@icon('solid/plus')<span class="hide-text">Add Master Ssdeep</span></span>
                    </button>

                </div>     
            </div>
        </header>

        <section class="scrollable wrapper">
            
            <section class="m-b-10">
                <div class="row">
                    <div class="col-md-12 col-lg-12">
                        <div class="loadrule backdrop-loader">
                            <div class="loader4 centerloader"></div>
                            <div class="loadding-text">Loading ...</div>
                        </div>
                        <div class="box-chart-color bg-white">
                            <div class="d-flex align-items-center header-chart-p">
                                <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
                                <h1 class="text-blue bold-500">Top 10 Rule Category</h1>
                            </div>
                            <div class="divider-dark"></div>
                            <div id="chart-top-rule-cate" class="h-chart"></div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md-12">
                    <div class="tabbable">
                        <ul class="nav nav-tabs nav-tabs-highlight">
                            <li class="active"><a href="#tab_rule_site" data-toggle="tab" id="tab_rule_site_click">Rule Site</a></li>
                            <li><a href="#tab_agent" data-toggle="tab" id="tab_agent_click">Master Rule</a></li>
                            <li><a href="#tab_ssdeep_site" data-toggle="tab" id="tab_ssdeep_site_click">Ssdeep Site</a></li>
                            <li><a href="#tab_ssdeep_pack" data-toggle="tab" id="tab_ssdeep_pack_click">Master Ssdeep</a></li>
                            <li><a href="#tab_ssdeep_candidate" data-toggle="tab" id="tab_ssdeep_candidate_click">Ssdeep Candidates</a></li>
                            <li><a href="#tab_category" data-toggle="tab" id="tab_category_click">Category</a></li>
                            <li class="hide" style="display:none;"><a href="#tab_extention" data-toggle="tab" id="tab_extention_click">Extension</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_rule_site">
                                <section class="panel panel-default">
                                    <header class="panel-heading font-bold panel-header-blue">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <i class="fas fa-table"></i> Rule Site
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="form-group m-b-md">
                                                    <label for="" class="">Site</label>
                                                    <select name="search_site" id="search_site" class="tselect2-option form-control select-search_site" >
                                                        <option value="" disabled selected>Select Site</option>
                                                            @if ($site_settings)
                                                            @foreach ($site_settings as $site_settings)
                                                                <option value="{{$site_settings->id}}">{{$site_settings->name}}</option>
                                                            @endforeach
                                                            @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group m-b-md">
                                                    <label for="" class="">Keyword</label>
                                                    <input type="text" class="form-control" name="keyword_search" id="keyword_search" placeholder="Search">
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <button type="button" id="btn_search_rule" class="btn btn-info btn-responsive btn-fz-13" style="margin-top: 22px;">
                                                    <i class="fas fa-search"></i>
                                                    @langapp('apply')
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row row_extension_set d-none">
                                            <div class="col-lg-4">
                                                <div class="form-group m-b-md">
                                                    <label for="" class="">Extension</label>
                                                    <select name="extension_rule_site" id="extension_rule_site" class="tselect2-option form-control select-search_site">
                                                        <option value="all">All</option>
                                                        <option value="custom">Custom</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row row_extension_custom_set d-none">
                                            <div class="col-lg-4">
                                                <div class="form-group m-b-md">
                                                    <label for="" class="">Custom</label>
                                                    <select name="custom_select_rule_site[]" id="custom_select_rule_site" class="select2-option form-control" multiple="multiple">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <h5 class="font-weight-bold">Rule</h5>
                                                    </div>
                                                    <div class="col-lg-6 text-right">
                                                        <button type="button" id="btn_select_all_master" class="btn btn-info btn-responsive">เลือกทั้งหมด</button>
                                                    </div>
                                                </div>
                                                <div class="box-item-keyword">
                                                    <ul id="keyword_rule" class="main-list keyword-list">
                                                        @foreach($master_rule as $rule)
                                                        <li class="item-list item--keyword" data-id="{{$rule->id}}">
                                                            <div class="left-side-item">
                                                                <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                                                <span class="text-keyword">{{@$rule->get_name_category->name}} - {{$rule->rule_name}}</span>
                                                            </div>
                                                            <div class="action-keyword">
                                                                
                                                                <a href="#" class="text-white delete_rule_site_master" data-delete_rule_site_master="{{$rule->id}}" data-mode_delete="master"><i class="fas fa-trash-alt"></i></a>
                                                            </div>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <h5 class="font-weight-bold" id="txt_name_site">&nbsp;</h5>
                                                    </div>
                                                    <div class="col-lg-6 text-right">
                                                        <button type="button" id="btn_delete_all_site" class="btn btn-danger btn-responsive">ลบทั้งหมด</button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="hd_site_id" id="hd_site_id" value="">
                                                <div class="box-item-keyword">
                                                    <ul id="site_rule" class="main-list site-list" height="100%">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </section>
                            </div>

                            <div class="tab-pane" id="tab_agent">
                                <section class="panel panel-default">
                                    <header class="panel-heading font-bold panel-header-blue">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <i class="fas fa-table"></i> Table Master Rule
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">

                                        <div class="row m-b-10">
                                            <div class="col-md-12">
                                                <h5 class="font-weight-bold">Severity</h5>
                                                <div class="st-dt-leak">
                                                    <span class="st-dt vrh" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip vrh'>Critical</div><div class='text-st-tooltip'>Criticalข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Critical</span>
                                                    <span class="st-dt high" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip high'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">High</span>
                                                    <span class="st-dt md" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Medium</span>
                                                    <span class="st-dt low" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของลูกค้าเช่น ข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">Low</span>
                                                    <span class="st-dt vrl" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip vrl'>Informational</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลที่เป็นข้อมูลทั่วไปหรือเป็นข่าวที่ยังไม่ได้รับการยืนยันว่าเป็นข้อมูลรั่วไหลจริง</div></div>">Informational</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="table-agent-rule" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Rule Category</th>
                                                        <th>File Name</th>
                                                        <th>Rule Name</th>
                                                        <th>Description</th>
                                                        <th>Severity</th>
                                                        <th>Status</th>
                                                        <th>Last Update</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>                                                  
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-pane" id="tab_category">
                                <section class="panel panel-default">
                                    <header class="panel-heading font-bold panel-header-blue">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <i class="fas fa-table"></i> Table Category (YARA + Ssdeep)
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="table-category-rule" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Name</th>
                                                        <th>YARA rules</th>
                                                        <th>Ssdeep packs</th>
                                                        <th>Update</th>
                                                        <th>Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>                                                  
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-pane hide" id="tab_extention" style="display:none;">
                                <section class="panel panel-default">
                                    <header class="panel-heading font-bold panel-header-blue">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <i class="fas fa-table"></i> Table Extention
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="table-extention-rule" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Name</th>
                                                        <th>Update</th>
                                                        <th>Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>                                                  
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-pane" id="tab_ssdeep_site">
                                <section class="panel panel-default">
                                    <header class="panel-heading font-bold panel-header-blue">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <i class="fas fa-table"></i> Ssdeep Site
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="form-group m-b-md">
                                                    <label for="" class="">Site</label>
                                                    <select name="search_ssdeep_site" id="search_ssdeep_site" class="tselect2-option form-control">
                                                        <option value="" disabled selected>Select Site</option>
                                                        @if (!empty($sites_list))
                                                            @foreach ($sites_list as $ssSite)
                                                                <option value="{{ $ssSite->id }}">{{ $ssSite->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group m-b-md">
                                                    <label for="" class="">Keyword</label>
                                                    <input type="text" class="form-control" name="keyword_ssdeep_search" id="keyword_ssdeep_search" placeholder="Search version / title / category">
                                                </div>
                                            </div>
                                            <div class="col-lg-2">
                                                <button type="button" id="btn_search_ssdeep" class="btn btn-info btn-responsive btn-fz-13" style="margin-top: 22px;">
                                                    <i class="fas fa-search"></i>
                                                    @langapp('apply')
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <h5 class="font-weight-bold">Master Packs</h5>
                                                    </div>
                                                    <div class="col-lg-6 text-right">
                                                        <button type="button" id="btn_select_all_ssdeep_master" class="btn btn-info btn-responsive">เลือกทั้งหมด</button>
                                                    </div>
                                                </div>
                                                <div class="box-item-keyword">
                                                    <ul id="keyword_ssdeep" class="main-list keyword-list">
                                                        @foreach(($master_ssdeep ?? []) as $pack)
                                                        <li class="item-list item--keyword" data-id="{{ $pack->id }}">
                                                            <div class="left-side-item">
                                                                <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                                                <span class="text-keyword">{{ $pack->version }}@if($pack->file_name) — {{ $pack->file_name }}@endif</span>
                                                            </div>
                                                            <div class="action-keyword">
                                                                <a href="#" class="text-white delete_ssdeep_site_master" data-delete_ssdeep_id="{{ $pack->id }}" data-mode_delete="master"><i class="fas fa-trash-alt"></i></a>
                                                            </div>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <h5 class="font-weight-bold" id="txt_name_ssdeep_site">&nbsp;</h5>
                                                    </div>
                                                    <div class="col-lg-6 text-right">
                                                        <button type="button" id="btn_delete_all_ssdeep_site" class="btn btn-danger btn-responsive">ลบทั้งหมด</button>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="hd_ssdeep_site_id" id="hd_ssdeep_site_id" value="">
                                                <div class="box-item-keyword">
                                                    <ul id="site_ssdeep" class="main-list site-list" height="100%">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-pane" id="tab_ssdeep_pack">
                                <section class="panel panel-default">
                                    <header class="panel-heading font-bold panel-header-blue">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <i class="fas fa-table"></i> Table Master Ssdeep
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                @php $ssdeepAutoDistOn = !empty($ssdeep_auto_distribute); @endphp
                                                <button type="button"
                                                    id="btn_ssdeep_auto_distribute"
                                                    class="btn btn-xs {{ $ssdeepAutoDistOn ? 'btn-success' : 'btn-default' }}"
                                                    data-enabled="{{ $ssdeepAutoDistOn ? '1' : '0' }}"
                                                    title="เมื่อเปิด: pack ใหม่จาก Candidates จะถูกแจกเข้าทุก Site อัตโนมัติ">
                                                    <i class="fas {{ $ssdeepAutoDistOn ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                    Auto แจกจ่ายเข้า Site
                                                    <span class="badge" id="ssdeep_auto_dist_badge">{{ $ssdeepAutoDistOn ? 'ON' : 'OFF' }}</span>
                                                </button>
                                                <button type="button"
                                                    id="btn_ssdeep_cleanup_duplicates"
                                                    class="btn btn-danger btn-xs"
                                                    title="ปิด pack Auto ที่ซ้ำต่อ category / sha256 ซ้ำ และลบไฟล์ที่ไม่ใช้แล้ว">
                                                    <i class="fas fa-trash-alt"></i> ลบไฟล์ซ้ำ
                                                </button>
                                                <button type="button" id="btn_bulk_edit_ssdeep_site" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#bulk_edit_ssdeep_pack_modal">
                                                    <i class="fas fa-layer-group"></i> เปลี่ยนทุกไฟล์ให้เป็น Site เดียวกันหมด
                                                </button>
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="table-ssdeep-pack" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Category</th>
                                                        <th>Title</th>
                                                        <th>Version</th>
                                                        <th>File Name</th>
                                                        <th>Format</th>
                                                        <th>SHA256</th>
                                                        <th>Signatures</th>
                                                        <th>Source</th>
                                                        <th>Status</th>
                                                        <th>Last Update</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div class="tab-pane" id="tab_ssdeep_candidate">
                                <section class="panel panel-default">
                                    <header class="panel-heading font-bold panel-header-blue">
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <i class="fas fa-fingerprint"></i> Ssdeep Candidates
                                                <small class="text-muted">(detections → auto pack; hash ซ้ำจะถูกลบทิ้ง)</small>
                                            </div>
                                            <div class="col-xs-6 text-right">
                                                <button type="button"
                                                    id="btn_ssdeep_candidate_cleanup_duplicates"
                                                    class="btn btn-danger btn-xs"
                                                    title="ลบ candidate ที่ hash ซ้ำ หรือมีใน Master pack แล้ว ออกจากตารางทั้งหมด">
                                                    <i class="fas fa-trash-alt"></i> ลบไฟล์ซ้ำทั้งหมด
                                                </button>
                                            </div>
                                        </div>
                                    </header>
                                    <div class="panel-body">
                                        <div class="row" style="margin-bottom:12px;">
                                            <div class="col-lg-3">
                                                <label>Site</label>
                                                <select id="cand_filter_site" class="form-control">
                                                    <option value="">All sites</option>
                                                    @foreach(($sites_list ?? []) as $s)
                                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <label>Status</label>
                                                <select id="cand_filter_status" class="form-control">
                                                    <option value="">All</option>
                                                    <option value="promoted">promoted</option>
                                                    <option value="queued">queued</option>
                                                    <option value="failed">failed</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label>Keyword</label>
                                                <input type="text" id="cand_filter_keyword" class="form-control" placeholder="file / rule / ssdeep / hash">
                                            </div>
                                            <div class="col-lg-2">
                                                <label>&nbsp;</label>
                                                <button type="button" id="btn_search_ssdeep_candidate" class="btn btn-info btn-block">
                                                    <i class="fas fa-search"></i> Apply
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="table-ssdeep-candidate" style="width: 100%">
                                                <thead>
                                                    <tr>
                                                        <th>No.</th>
                                                        <th>Site</th>
                                                        <th>Agent</th>
                                                        <th>File</th>
                                                        <th>Rule</th>
                                                        <th>Engine</th>
                                                        <th>Ssdeep</th>
                                                        <th>Source</th>
                                                        <th>Status</th>
                                                        <th>Note</th>
                                                        <th>Detected</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </section>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <div class="modal in fixed-left" id="add_category_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">

                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                            title="Fullscreen" data-placement="right"></i>
                        Add Category
                    </h4>
                </div>
                <form id='form_add_category' enctype="multipart/form-data">
                    <div class="modal-body">
                        <p class="text-muted" style="margin-top:0;">
                            Shared category for <strong>Master Rule (YARA)</strong> and <strong>Master Ssdeep</strong> packs.
                        </p>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Name <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-lg-8 mb-1">
                                            <input type="text" name="category_name" id="category_name" class="form-control">
                                            <span style="color:red;" id="error_category_name" class="d-none"><small>Please enter your name category</small></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Status </label>
                            <div class="col-lg-8">
                                <label class="switch">
                                    <input type="checkbox" id="status" name="status" checked value="1">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                        <button type="button" value="Submit" required class="btn btn-info btn-rounded" id="btn_save_category">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="add_extension_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">

                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                            title="Fullscreen" data-placement="right"></i>
                        Add extension
                    </h4>
                </div>
                <form id='form_add_extension' enctype="multipart/form-data">
                    <div class="modal-body">

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Name <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-lg-8 mb-1">
                                            <input type="text" name="extension_name" id="extension_name" class="form-control">
                                            <span style="color:red;" id="error_extension_name" class="d-none"><small>Please enter your name extension</small></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Status </label>
                            <div class="col-lg-8">
                                <label class="switch">
                                    <input type="checkbox" id="status" name="status" checked value="1">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                        <button type="button" value="Submit" required class="btn btn-info btn-rounded" id="btn_save_extension">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="add_rule_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">

                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                            title="Fullscreen" data-placement="right"></i>
                        Add Rule
                    </h4>
                </div>
                <form id='form_add_rule' enctype="multipart/form-data">
                    <div class="modal-body">

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Rule Category <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="col-lg-8 mb-1">
                                            
                                            <select name="name" id="name" class="form-control check_test_select">
                                                <option value="">Choose an Category</option>
                                                
                                            </select>
                                            <span id="error_name" style="color:red;"></span>
                                        </div>
                                        <div class="col-lg-4 mb-1">
                                            <button type="button" data-toggle="collapse" href="#demo" class="btn btn-{{ get_option('theme_color')  }} add-assets-new">
                                                <i class="fas fa-plus"></i>
                                                &nbsp; Add New
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">

                                <div id="demo" class="collapse box">

                                    <fieldset class="collapsible">
                    
                                        <legend>Add Category</legend>
                                        <div class="form-group row">
                                            <label style="padding-top: 7px" class="col-lg-3 control-label">Name <span
                                                    class="text-danger">*</span> </label>
                                            <div class="col-lg-8">
                                                <input type="text" name="name_new" id="name_new" class="form-control check_test">
                                                <span style="color:red;" id="check_name_new" class="d-none"><small>Please enter your name category</small></span>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" id="close_add_category" class="btn btn-danger btn-rounded" data-toggle="collapse"
                                                data-target="#demo">
                                                <i class="fas fa-times"></i>
                                                Close
                                            </button>
                                            <button type="button" onclick="add_new_category()" class="btn btn-info btn-rounded">
                                                <i class="fas fa-paper-plane"></i>
                                                Save
                                            </button>
                                        </div>
                                        <hr>
                    
                                    </fieldset>
                    
                                </div>

                            </div>
                            <div class="col-lg-3">
                            </div>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-lg-12 mb-1">
                                        <input type="file" name="file_rule_name" id="file_rule_name" class="form-control" accept="zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed">
                                        <span id="error_file" style="color:red;"></span>
                                    </div>
                                    <div class="col-lg-12">
                                        <span style="color:red;">รองรับเฉพาะไฟล์ .zip เท่านั้น</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="div_rule" class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Rule  <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">

                                <table id="rule_item" class="table mb-0">
                                    <tbody>
                                        <tr id="tr_no_data">
                                            <td class="text-center">
                                                <span> Select file for data.. </span>
                                            </td>
                                        </tr>
                                        
                                    </tbody>
                                </table>
                                <span id="error_detail" style="color:red;"></span>
                                
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Site </label>
                            <div class="col-lg-8">
                                <span class="checkbox">
                                    <label>
                                        <input type="checkbox" name="site[]" value="all">
                                        <span class="label-text" data-rel="tooltip" title="" data-original-title="">
                                            All Site
                                        </span>
                                    </label>
                                </span>
                                @foreach ($select_site as $key => $name)
                                
                                <span class="checkbox">
                                    <label>
                                        <input type="checkbox" name="site[]" value="{{@$key}}">
                                        <span class="label-text" data-rel="tooltip" title="" data-original-title="">
                                            {{@$name}}
                                        </span>
                                    </label>
                                </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Status </label>
                            <div class="col-lg-8">
                                <label class="switch">
                                    <input type="checkbox" id="status" name="status" checked value="1">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                        <button type="button" value="Submit" required class="btn btn-info btn-rounded" id="btn_save_rule">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="delete_select" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning')  </p>
                    </div>
                    <input type="hidden" id="delete_rule_id" name="delete_rule_id">
                    <input type="hidden" id="delete_mode" name="delete_mode">
                    <input type="hidden" id="delete_site" name="delete_site">
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" id="btn_delete_rule_site" class="btn btn-info submit btn-rounded delete-select"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="add_ssdeep_pack_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" data-rel="tooltip"
                            title="Fullscreen" data-placement="right"></i>
                        Add Ssdeep Pack
                    </h4>
                </div>
                <form id="form_add_ssdeep_pack" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Category <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <select name="category" id="ssdeep_category" class="form-control">
                                    <option value="">Select category</option>
                                    @foreach(($select_category ?? []) as $cat)
                                        <option value="{{ strtolower($cat['name']) }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                                <span id="error_ssdeep_category" style="color:red;"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Title <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="title" id="ssdeep_title" class="form-control" placeholder="e.g. Webshell fuzzy hashes Aug 2026">
                                <span id="error_ssdeep_title" style="color:red;"></span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Version <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="version" id="ssdeep_version" class="form-control" placeholder="e.g. webshells-2026.08.03">
                                <span id="error_ssdeep_version" style="color:red;"></span>
                                <small class="text-muted">Use &lt;category&gt;-&lt;YYYY.MM.DD&gt;</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Description
                            </label>
                            <div class="col-lg-9">
                                <textarea name="description" id="ssdeep_description" class="form-control" rows="2" placeholder="What this pack covers (optional)"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Format
                            </label>
                            <div class="col-lg-9">
                                <select name="format" id="ssdeep_format" class="form-control">
                                    <option value="">Auto detect</option>
                                    <option value="sqlite_zip">sqlite_zip (.zip)</option>
                                    <option value="sqlite">sqlite (.db)</option>
                                    <option value="json">json</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Pack File <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <input type="file" name="file_ssdeep" id="file_ssdeep" class="form-control" accept=".db,.zip,.json,application/zip,application/json">
                                <span id="error_ssdeep_file" style="color:red;"></span>
                                <span class="text-muted">Prefer ssdeep_&lt;category&gt;_&lt;YYYYMMDD&gt;.zip · รองรับ .db / .zip / .json</span>
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Site </label>
                            <div class="col-lg-8">
                                <span class="checkbox">
                                    <label>
                                        <input type="checkbox" name="site[]" value="all">
                                        <span class="label-text">All Site</span>
                                    </label>
                                </span>
                                @if (!empty($sites_list))
                                    @foreach ($sites_list as $ssSite)
                                    <span class="checkbox">
                                        <label>
                                            <input type="checkbox" name="site[]" value="{{ $ssSite->id }}">
                                            <span class="label-text">{{ $ssSite->name }}</span>
                                        </label>
                                    </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Status </label>
                            <div class="col-lg-8">
                                <label class="switch">
                                    <input type="checkbox" id="ssdeep_status" name="status" checked value="1">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                        <button type="button" class="btn btn-info btn-rounded" id="btn_save_ssdeep_pack">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="edit_ssdeep_pack_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-edit text-white"></i> Edit Ssdeep Pack & Site Assignments
                    </h4>
                </div>
                <form id="form_edit_ssdeep_pack">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" id="edit_ssdeep_id">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Version
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="version" id="edit_ssdeep_version" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Category
                            </label>
                            <div class="col-lg-9">
                                <select name="category" id="edit_ssdeep_category" class="form-control">
                                    <option value="">-</option>
                                    @foreach(($select_category ?? []) as $cat)
                                        <option value="{{ strtolower($cat['name']) }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Title
                            </label>
                            <div class="col-lg-9">
                                <input type="text" name="title" id="edit_ssdeep_title" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                Description
                            </label>
                            <div class="col-lg-9">
                                <textarea name="description" id="edit_ssdeep_description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label style="padding-top: 7px" class="col-lg-3 control-label">
                                File Name
                            </label>
                            <div class="col-lg-9" style="padding-top: 7px">
                                <strong id="edit_ssdeep_file_name">-</strong>
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Site (เลือก Site ที่ต้องการ)</label>
                            <div class="col-lg-8">
                                <span class="checkbox">
                                    <label>
                                        <input type="checkbox" name="site[]" value="all" id="edit_ssdeep_site_all">
                                        <span class="label-text">All Site</span>
                                    </label>
                                </span>
                                @if (!empty($sites_list))
                                    @foreach ($sites_list as $ssSite)
                                    <span class="checkbox">
                                        <label>
                                            <input type="checkbox" name="site[]" value="{{ $ssSite->id }}" class="edit_ssdeep_site_item">
                                            <span class="label-text">{{ $ssSite->name }}</span>
                                        </label>
                                    </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Status </label>
                            <div class="col-lg-8">
                                <label class="switch">
                                    <input type="checkbox" id="edit_ssdeep_status" name="status" value="1">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i> Close
                        </button>
                        <button type="button" class="btn btn-warning btn-rounded" id="btn_update_ssdeep_pack">
                            <i class="fas fa-paper-plane"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="bulk_edit_ssdeep_pack_modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-layer-group text-white"></i> จัดการ Site ให้กับ Ssdeep Pack ทั้งหมด
                    </h4>
                </div>
                <form id="form_bulk_edit_ssdeep_pack">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> การตั้งค่าในหน้านี้จะมีผลกับ <strong>Ssdeep Pack ทั้งหมดที่เปิดใช้งานในระบบ</strong>
                        </div>
                        <div class="form-group row" style="padding-top: 7px">
                            <label class="col-lg-3 control-label">Site ที่ต้องการใช้</label>
                            <div class="col-lg-8">
                                <span class="checkbox">
                                    <label>
                                        <input type="checkbox" name="site[]" value="all" id="bulk_ssdeep_site_all">
                                        <span class="label-text">All Site (ทุก Site)</span>
                                    </label>
                                </span>
                                @if (!empty($sites_list))
                                    @foreach ($sites_list as $ssSite)
                                    <span class="checkbox">
                                        <label>
                                            <input type="checkbox" name="site[]" value="{{ $ssSite->id }}" class="bulk_ssdeep_site_item">
                                            <span class="label-text">{{ $ssSite->name }}</span>
                                        </label>
                                    </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i> Close
                        </button>
                        <button type="button" class="btn btn-warning btn-rounded" id="btn_save_bulk_ssdeep_site">
                            <i class="fas fa-paper-plane"></i> บันทึกข้อมูลทั้งหมด
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="delete_ssdeep_select" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning')</p>
                    </div>
                    <input type="hidden" id="delete_ssdeep_id" name="delete_ssdeep_id">
                    <input type="hidden" id="delete_ssdeep_mode" name="delete_ssdeep_mode">
                    <input type="hidden" id="delete_ssdeep_site" name="delete_ssdeep_site">
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" id="btn_delete_ssdeep_site" class="btn btn-info submit btn-rounded"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@include('stacks.css.summernote')
@include('stacks.css.highchart')

@include('stacks.css.c3')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />

@include('stacks.css.multitext')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
@include('stacks.js.datepicker')
@include('scripts.summernote')
@include('stacks.js.markdown')
@include('stacks.js.hidesettings')
@include('stacks.js.daterangpicker')
@include('stacks.js.activebutton')
@include('stacks.js.advanced_search')
@include('stacks.js.highchart')
@include('stacks.js.c3')
@include('stacks.js.multitext')
@include('stacks.js.sort')

<script>

    function delete_rule_site_master()
    {
        $('.delete_rule_site_master').off('click').on('click', function(){
            let rule_id = $(this).data('delete_rule_site_master');
            let mode = $(this).data('mode_delete');
            let site_id = $('#hd_site_id').val();
            $('#delete_site').val(site_id);
            $('#delete_mode').val(mode);
            $('#delete_rule_id').val(rule_id);
            $("#delete_select").modal("show");
        });
    }

    function bind_delete_ssdeep_site_master(){
        $('.delete_ssdeep_site_master').off('click').on('click', function(e){
            e.preventDefault();
            $('#delete_ssdeep_id').val($(this).data('delete_ssdeep_id'));
            $('#delete_ssdeep_mode').val($(this).data('mode_delete'));
            $('#delete_ssdeep_site').val($('#hd_ssdeep_site_id').val());
            $('#delete_ssdeep_select').modal('show');
        });
    }
    var tbl_ssdeep_pack = null;

    function reload_ssdeep_pack_table(){
        if(tbl_ssdeep_pack){
            tbl_ssdeep_pack.ajax.reload(null, false);
        }
    }

    function saveSsdeepPack(){
        var category = String($('#ssdeep_category').val() || '').trim();
        var title = String($('#ssdeep_title').val() || '').trim();
        var version = String($('#ssdeep_version').val() || '').trim();
        var fileInput = document.getElementById('file_ssdeep');
        $('#error_ssdeep_category').empty();
        $('#error_ssdeep_title').empty();
        $('#error_ssdeep_version').empty();
        $('#error_ssdeep_file').empty();

        if(!category){
            $('#error_ssdeep_category').text('Category is required.');
            toastr.error('กรุณาเลือก Category');
            return false;
        }
        if(!title){
            $('#error_ssdeep_title').text('Title is required.');
            toastr.error('กรุณากรอก Title');
            return false;
        }
        if(!version){
            $('#error_ssdeep_version').text('Version is required.');
            toastr.error('กรุณากรอก Version');
            return false;
        }
        if(!fileInput || !fileInput.files || !fileInput.files.length){
            $('#error_ssdeep_file').text('Please select a pack file.');
            toastr.error('กรุณาเลือกไฟล์ pack');
            return false;
        }

        var formData = new FormData(document.getElementById('form_add_ssdeep_pack'));
        formData.set('status', $('#ssdeep_status').is(':checked') ? '1' : '0');

        $('#btn_save_ssdeep_pack').html('<i class="fas fa-spinner fa-spin"></i> Saving...').attr('disabled', true);

        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_insert') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){
                $('#btn_save_ssdeep_pack').html('<i class="fas fa-paper-plane"></i> Save').attr('disabled', false);
                if(response.status === 'success'){
                    toastr.success(response.message);
                    $('#add_ssdeep_pack_modal').modal('hide');
                    reload_ssdeep_pack_table();
                } else if(response.status === '422'){
                    if(response.errors && response.errors.version){
                        $('#error_ssdeep_version').text(response.errors.version[0]);
                    }
                    if(response.errors && response.errors.file_ssdeep){
                        $('#error_ssdeep_file').text(response.errors.file_ssdeep[0]);
                    }
                    toastr.error(response.message || 'Validation failed');
                } else {
                    toastr.error(response.message || 'Upload failed');
                }
            },
            error: function(xhr){
                $('#btn_save_ssdeep_pack').html('<i class="fas fa-paper-plane"></i> Save').attr('disabled', false);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : ('Upload failed (' + xhr.status + ')');
                toastr.error(msg);
            }
        });
        return false;
    }

    $(document).ready(function(){
        delete_rule_site_master();
        bind_delete_ssdeep_site_master();

        $('#btn_save_ssdeep_pack').on('click', function(e){
            e.preventDefault();
            saveSsdeepPack();
        });

        $('#file_ssdeep').on('change', function(){
            var file = this.files && this.files[0] ? this.files[0] : null;
            if(!file){ return; }
            if(!$('#ssdeep_version').val()){
                var base = file.name.replace(/\.[^.]+$/, '');
                $('#ssdeep_version').val(base);
            }
            var ext = (file.name.split('.').pop() || '').toLowerCase();
            if(!$('#ssdeep_format').val()){
                if(ext === 'json'){ $('#ssdeep_format').val('json'); }
                else if(ext === 'db'){ $('#ssdeep_format').val('sqlite'); }
                else if(ext === 'zip'){ $('#ssdeep_format').val('sqlite_zip'); }
            }
        });
    });

    $('.select-2--rule').select2();

    tbl_all_rule = $('#table-agent-rule').DataTable({
        ajax:{
            url: "{{ route('agentmanagement.agent_rule_tbl_all_rule') }}",
            type: "get",
            data: function(d) {

            },
        },
        columns:[
            { data: 'DT_Row_Index' },
            { data: 'name' },
            { data: 'c_file_name' },
            { data: 'c_rule_name' },
            { data: 'c_description' },
            { data: 'c_severity' },
            { data: 'c_status' },
            { data: 'updated_at' },
            { data: 'c_action' }
        ]
    });

    tbl_category_rule = $('#table-category-rule').DataTable({
        ajax:{
            url: "{{ route('agentmanagement.tbl_category_rule') }}",
            type: "get",
            data: function(d) {

            },
        },
        columns:[
            { data: 'DT_Row_Index' },
            { data: 'name' },
            { data: 'c_yara_count' },
            { data: 'c_ssdeep_count' },
            { data: 'updated_at' },
            { data: 'c_status' },
            { data: 'c_action' }
        ]
    });

    tbl_extention_rule = $('#table-extention-rule').DataTable({
        ajax:{
            url: "{{ route('agentmanagement.tbl_extention_rule') }}",
            type: "get",
            data: function(d) {

            },
        },
        columns:[
            { data: 'DT_Row_Index' },
            { data: 'name' },
            { data: 'updated_at' },
            { data: 'c_status' },
            { data: 'c_action' }
        ]
    });

    function ensure_ssdeep_pack_table(){
        if(tbl_ssdeep_pack){
            tbl_ssdeep_pack.ajax.reload(null, false);
            return;
        }
        tbl_ssdeep_pack = $('#table-ssdeep-pack').DataTable({
            ajax:{
                url: "{{ route('agentmanagement.ssdeep_pack_tbl') }}",
                type: "get",
            },
            columns:[
                { data: 'DT_Row_Index' },
                { data: 'c_category' },
                { data: 'c_title' },
                { data: 'c_version' },
                { data: 'c_file_name' },
                { data: 'c_format' },
                { data: 'c_sha256' },
                { data: 'c_signature_count' },
                { data: 'c_source' },
                { data: 'c_status' },
                { data: 'updated_at' },
                { data: 'c_action' }
            ]
        });
    }

    $('#tab_ssdeep_pack_click').on('shown.bs.tab click', function(){
        ensure_ssdeep_pack_table();
    });

    var tbl_ssdeep_candidate = null;
    function ensure_ssdeep_candidate_table(){
        if(tbl_ssdeep_candidate){
            tbl_ssdeep_candidate.ajax.reload(null, false);
            return;
        }
        tbl_ssdeep_candidate = $('#table-ssdeep-candidate').DataTable({
            ajax:{
                url: "{{ route('agentmanagement.ssdeep_candidate_tbl') }}",
                type: "get",
                data: function(d){
                    d.site_id = $('#cand_filter_site').val() || '';
                    d.status = $('#cand_filter_status').val() || '';
                    d.keyword = $('#cand_filter_keyword').val() || '';
                }
            },
            order: [[10, 'desc']],
            columns:[
                { data: 'DT_Row_Index' },
                { data: 'c_site' },
                { data: 'c_agent' },
                { data: 'c_file' },
                { data: 'c_rule' },
                { data: 'c_engine' },
                { data: 'c_ssdeep' },
                { data: 'c_source' },
                { data: 'c_status' },
                { data: 'c_note' },
                { data: 'c_detected' }
            ]
        });
    }

    $('#tab_ssdeep_candidate_click').on('shown.bs.tab click', function(){
        ensure_ssdeep_candidate_table();
    });
    $('#btn_search_ssdeep_candidate').on('click', function(){
        ensure_ssdeep_candidate_table();
        if(tbl_ssdeep_candidate){
            tbl_ssdeep_candidate.ajax.reload();
        }
    });

    $('#btn_ssdeep_candidate_cleanup_duplicates').off('click').on('click', function(){
        if(!confirm('ลบ candidate ที่ซ้ำออกทั้งหมดจากแท็บนี้?\n\n- hash ซ้ำในตาราง (เหลือ 1 ถ้ายังไม่ได้อยู่ใน pack)\n- hash ที่มีใน Master Ssdeep แล้ว → ลบออกหมด\n- status เก่าแบบ duplicate/skipped')){
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_candidate_cleanup_duplicates') }}",
            type: "POST",
            data: {},
            success: function(response){
                $btn.prop('disabled', false);
                if(response.status === 'success'){
                    toastr.success(response.message);
                    ensure_ssdeep_candidate_table();
                    if(tbl_ssdeep_candidate){
                        tbl_ssdeep_candidate.ajax.reload();
                    }
                } else {
                    toastr.error(response.message || 'Cleanup failed');
                }
            },
            error: function(xhr){
                $btn.prop('disabled', false);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Cleanup failed';
                toastr.error(msg);
            }
        });
    });
    $('#cand_filter_keyword').on('keypress', function(e){
        if(e.which === 13){
            $('#btn_search_ssdeep_candidate').trigger('click');
        }
    });

    $('#add_rule_modal').on('hidden.bs.modal', function () {
        $('#form_add_rule')[0].reset();

        $('#error_name').empty();
        $('#error_file').empty();
        $('#error_detail').empty();

        $('#close_add_category').trigger('click');

        $('#btn_save_rule').html('<i class="fas fa-paper-plane"></i> Save');
        $('#btn_save_rule').attr('disabled', false);

        let html_reset_tbl_rule = 
        `
        <tr id="tr_no_data">
            <td class="text-center">
                <span> Select file for data.. </span>
            </td>
        </tr>
        `;

        $('#rule_item tbody').empty();
        $('#rule_item tbody').append(html_reset_tbl_rule);

    });

    $('#add_category_modal').on('hidden.bs.modal', function () {
        $('#form_add_category')[0].reset();

        $('#btn_save_category').html('<i class="fas fa-paper-plane"></i> Save');
        $('#btn_save_category').attr('disabled', false);

    });

    $('#add_extension_modal').on('hidden.bs.modal', function () {
        $('#form_add_extension')[0].reset();

        $('#btn_save_extension').html('<i class="fas fa-paper-plane"></i> Save');
        $('#btn_save_extension').attr('disabled', false);

    });

    $('#add_rule_modal').on('show.bs.modal', function () {

        $.ajax({
            url: "{{ route('agentmanagement.get_select_category_rule') }}",
            type: "get",
            data: {

            },
            success:function(response)
            {
                let html = `
                    <option value="">Choose an Category</option>
                `;

                const select_category = response.select_category;
                for(let i in select_category)
                {
                    html += `
                        <option value="${select_category[i].id}">${select_category[i].name}</option>
                    `;
                }

                $('#name').empty().append(html);
            }
        });

    });

    function refresh_shared_category_selects() {
        $.ajax({
            url: "{{ route('agentmanagement.get_select_category_rule') }}",
            type: "get",
            success: function (response) {
                const cats = response.select_category || [];
                let yaraHtml = '<option value="">Choose an Category</option>';
                let ssdeepHtml = '<option value="">Select category</option>';
                let ssdeepEditHtml = '<option value="">-</option>';
                for (let i in cats) {
                    const id = cats[i].id;
                    const name = cats[i].name;
                    const val = String(name || '').toLowerCase();
                    yaraHtml += '<option value="' + id + '">' + name + '</option>';
                    ssdeepHtml += '<option value="' + val + '">' + name + '</option>';
                    ssdeepEditHtml += '<option value="' + val + '">' + name + '</option>';
                }
                if ($('#name').length) {
                    $('#name').empty().append(yaraHtml);
                }
                if ($('#ssdeep_category').length) {
                    const cur = $('#ssdeep_category').val();
                    $('#ssdeep_category').empty().append(ssdeepHtml).val(cur);
                }
                if ($('#edit_ssdeep_category').length) {
                    const curEdit = $('#edit_ssdeep_category').val();
                    $('#edit_ssdeep_category').empty().append(ssdeepEditHtml).val(curEdit);
                }
            }
        });
    }

    function update_status_rule(id)
    {
        let chk_status = $("#status_rule_" + id).is(":checked") ? 1 : 0;

        $.ajax({
            url: "{{ route('agentmanagement.status_rule_update') }}",
            type: "POST",
            data: {
                id:id,
                chk_status:chk_status,
            },
            success:function(response)
            {
                toastr.success(response.message);
            }
        });
    }

    $('#btn_save_category').click(function(e){
        e.preventDefault();

        let category_name = $('#category_name').val();

        if(category_name)
        {
            var formData = new FormData(document.getElementById("form_add_category"));

            $('#btn_save_category').html('Processing.. <i class="fas fa-spin fa-spinner"></i>');
            $('#btn_save_category').attr('disabled', true);

            $.ajax({
                url: "{{ route('agentmanagement.category_insert') }}",
                type: 'post',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforesend: function(){
                    
                },
                success:function(response){

                    if(response.status == 'success')
                    {
                        toastr.success(response.message);

                        $('#btn_save_category').html('Success');

                        $('#add_category_modal').modal("hide");

                        tbl_category_rule.ajax.reload();
                        tbl_all_rule.ajax.reload();
                        refresh_shared_category_selects();

                        setTimeout(function(){
                            
                        }, 3000);
                    }
                    else
                    {
                        $('#btn_save_category').html('Try again');
                        $('#btn_save_category').attr('disabled', false);

                        toastr.error(response.message);
                    }

                }
            });
        }
        else
        {
            input_check_err('#category_name', '#error_category_name')
        }

    });

    $('#btn_save_extension').click(function(e){
        e.preventDefault();

        let extension_name = $('#extension_name').val();

        if(extension_name)
        {
            var formData = new FormData(document.getElementById("form_add_extension"));

            $('#btn_save_extension').html('Processing.. <i class="fas fa-spin fa-spinner"></i>');
            $('#btn_save_extension').attr('disabled', true);

            $.ajax({
                url: "{{ route('agentmanagement.extension_insert') }}",
                type: 'post',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforesend: function(){
                    
                },
                success:function(response){

                    if(response.status == 'success')
                    {
                        toastr.success(response.message);

                        $('#btn_save_extension').html('Success');

                        $('#add_extension_modal').modal("hide");

                        tbl_extention_rule.ajax.reload();

                        setTimeout(function(){
                            
                        }, 3000);
                    }
                    else
                    {
                        $('#btn_save_extension').html('Try again');
                        $('#btn_save_extension').attr('disabled', false);

                        toastr.error(response.message);
                    }

                }
            });
        }
        else
        {
            input_check_err('#extension_name', '#error_extension_name')
        }

    });

    function update_status_category(id)
    {
        let chk_status = $("#status_" + id).is(":checked") ? 1 : 0;

        $.ajax({
            url: "{{ route('agentmanagement.status_category_update') }}",
            type: "POST",
            data: {
                id:id,
                chk_status:chk_status,
            },
            success:function(response)
            {
                toastr.success(response.message);
            }
        });
    }

    function update_status_extention(id)
    {
        let chk_status = $("#extension_status_" + id).is(":checked") ? 1 : 0;

        $.ajax({
            url: "{{ route('agentmanagement.status_category_update') }}",
            type: "POST",
            data: {
                id:id,
                chk_status:chk_status,
            },
            success:function(response)
            {
                toastr.success(response.message);
            }
        });
    }

    $('#btn_save_rule').click(function(e){
        e.preventDefault();

        var formData = new FormData(document.getElementById("form_add_rule"));

        $('#btn_save_rule').html('Processing.. <i class="fas fa-spin fa-spinner"></i>');
        $('#btn_save_rule').attr('disabled', true);

        $.ajax({
            url: "{{ route('agentmanagement.agent_rule_insert') }}",
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforesend: function(){
                
            },
            success:function(response){

                if(response.status == 'success')
                {
                    toastr.success(response.message);

                    tbl_all_rule.ajax.reload();

                    $('#btn_save_rule').html('Success');
                    $('#add_rule_modal').modal("hide");

                    setTimeout(function(){
                        
                    }, 3000);
                }
                else if(response.status == '422')
                {
                    $('#btn_save_rule').html('Try again');
                    $('#btn_save_rule').attr('disabled', false);

                    var errors = response.errors;
                    console.log(errors);

                    toastr.error(response.message);

                    $('#error_name').empty();
                    $('#error_file').empty();
                    $('#error_detail').empty();

                    if(errors.name)
                    {
                        $('#error_name').append(errors.name[0]);
                    }
                    if(errors.file_rule_name)
                    {
                        $('#error_file').append(errors.file_rule_name[0]);
                    }
                    if(errors.detail)
                    {
                        $('#error_detail').append(errors.detail[0]);
                    }
                }
                else
                {
                    $('#btn_save_rule').html('Try again');
                    $('#btn_save_rule').attr('disabled', false);

                    toastr.error(response.message);
                }

            }
        });
    });

    let row_append_rule = 0;

    $('#file_rule_name').change(function(){
        
        var formData = new FormData(document.getElementById("form_add_rule"));

        $.ajax({
            url: "{{ route('agentmanagement.agent_rule_get_zip')}}",
            type: 'post',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforesend: function(){

            },
            success: function(response){

                $('#error_file').empty();
                $('#div_rule').removeClass('d-none');

                var html = ``;
                if(response.name_file.length > 0)
                {
                    const name_file = response.name_file;
                    for(let i in name_file)
                    {
                        const name_file_arr = name_file[i];
    
                        html += 
                        `
                        <tr id="tr_${row_append_rule}">
                            <td style="width: 25%">
                                <input type="text" id="detail_${i}_file_name" name="detail[${i}][file_name]" value="${name_file_arr.file_name}" class="form-control" readonly>
                                <input type="hidden" id="detail_${i}_rule_name" name="detail[${i}][rule_name]" value="${name_file_arr.rule_name}">
                        `;

                        if(name_file_arr.status == 1)
                        {
                            html += 
                            `
                                <span style="color:red;">มีข้อมูลในฐานข้อมูลแล้ว</span>
                            `;
                        }

                        html += 
                        `
                            </td>
                            <td style="width: 25%">
                                <input type="text" id="detail_${i}_description" name="detail[${i}][description]" class="form-control">
                            </td>
                            <td style="width: 25%">
                                <select class="select-2--rule form-control" id="detail_${i}_severity" name="detail[${i}][severity]">
                                    <option value="Information">Information</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </td>
                            <td style="width: 25%">
                                <button type="button" class="btn btn-danger btn-xs btn_remove_row" onclick="delete_row('#tr_${row_append_rule}')"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        `;

                        row_append_rule++;
                    }

                    $('#error_detail').empty();

                }
                else
                {
                    html += 
                    `
                    <tr>
                        <td style="text-align: center;">
                            <span> Not Data... </span>
                        </td>
                    </tr>
                    `;
                }

                $('#tr_no_data').remove();
                
                $('#rule_item tbody').append(html);
            }
        });
    });

    $('.loadrule').hide();
    const chart_top_rule = Highcharts.chart('chart-top-rule-cate', {
        chart: {
            type: 'column',
                scrollablePlotArea: {
                minWidth: 400,
            },
        },
        title: {
            text: null
        },
        xAxis: {
            type: 'category',
            crosshair: true,
            labels: {
                overflow: 'justify',
                autoRotation: false,
                textAlign: 'center',
            }

        },
        yAxis: {
            min: 0,
            title: {
            text: 'Values'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            },
            series:{
                pointWidth: 30,
                color : '#ffc107',
                align: 'center',
                cursor: 'pointer'
            },
            style:{
                background: '#fff'
            }
        },
        legend: {
            enabled: false
        },
        series: [{
            name: 'Population',
            data: [["Email",11],["CVE",14],["Malware",7],["Crypto",0],["Deprecated",0],["Maldocs",0]],
            dataLabels: {
                enabled: true,
                color: '#333',
                align: 'center',
                format: '{point.y}',
                y: 0, 
                style: {
                    fontSize: '13px',
                    fontFamily: 'Verdana, sans-serif',
                }
            }
        }]
    });

    function add_rule(){
        let html = ``;
        html += 
        `
        <tr>
            <td style="width: 33.33%">
                <select class="select-2--rule form-control" id="">
                    <option value=""></option>
                </select>
            </td>
            <td style="width: 33.33%">
                <input type="text" id="" class="form-control">
            </td>
            <td style="width: 33.33%">
                <select class="select-2--rule form-control" id="">
                    <option value=""></option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger delete_rule"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        `;

        $('#rule_item tbody').append(html);

        $('.delete_rule').click(function(){
            $(this).closest('tr').remove();
        });
    }

    $(function(){
        $('.delete_rule').click(function(){
            $(this).closest('tr').remove();
        });
    });

    $('#btn_search_rule').click(function(){

        let search_site = $('#search_site :selected').val();
        let keyword_search = $('#keyword_search').val();

        if(search_site)
        {
            $.ajax({
                url: "{{ route('agentmanagement.get_rule_site') }}",
                type: "get",
                data: {
                    search_site:search_site,
                    keyword_search:keyword_search
                },
                success:function(response){

                    $('#txt_name_site').empty().append(response.data_site.name);
                    $('#hd_site_id').val(response.data_site.id);

                    $('#keyword_rule').empty().append(response.html_master_rule);
                    $('#site_rule').empty().append(response.html);

                    delete_rule_site_master();

                    $('.row_extension_set').removeClass('d-none');

                    let type_extension = (response.extension_all == 1 ? 'all' : 'custom');

                    $('#extension_rule_site').val(type_extension).trigger('change');

                }
            });
        }
        else
        {
            toastr.error('Please select site.');
        }

    });

    $('#extension_rule_site').change(function(){

        let value_type = $(this).val();
        let value_site = $('#hd_site_id').val();

        if(value_type == 'custom')
        {
            $('.row_extension_custom_set').removeClass('d-none');
        }
        else
        {
            $('.row_extension_custom_set').addClass('d-none');
        }

        $.ajax({
            url: "{{ route('agentmanagement.get_extension_rule_site') }}",
            type: 'get',
            data:{
                value_type:value_type,
                value_site:value_site
            },
            success: function(response){

                toastr.success('Update extension success.');

                let html = `
                    <option value="" disabled>Choose an Extension</option>
                `;

                const select_extension = response.select_extension;
                for(let i in select_extension)
                {
                    html += `
                        <option value="${select_extension[i].id}" 
                    `;

                    if(jQuery.inArray(select_extension[i].id, response.select_type_extension) !== -1)
                    {
                        html += `selected`;
                    }

                    html += `
                        >${select_extension[i].name}</option>
                    `;
                }

                $('#custom_select_rule_site').empty().append(html);

            }
        });

    });

    $('#custom_select_rule_site').change(function(){

        let value_site = $('#hd_site_id').val();
        let arr_extension = [];

        $('#custom_select_rule_site :selected').each(function(){ 
            
            arr_extension.push($(this).val());

        });

        $.ajax({
            url: "{{ route('agentmanagement.update_site_extension')}}",
            type: "POST",
            data: {
                value_site:value_site,
                arr_extension:arr_extension,
            },
            success:function(response)
            {
                toastr.success('Update extension success.');
            }
        });

    });

    function check_insert_rule_process(from_id,to_id,attributes_id) {
        let result = 0;

        let site_id = $('#hd_site_id').val();

        if(site_id)
        {
            $.ajax({
                type:"POST",
                url:"{{ route('agentmanagement.check_insert_rule_process') }}",
                data:{
                    code_site:site_id,
                    from_id:from_id,
                    to_id:to_id,
                    attributes_id:attributes_id
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) {
                    loading('stop_load');
    
                    if(response.status == 'success')
                    {
                        $('#btn_search_rule').trigger('click');
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                    }

                },
                error: function (error){
                    console.log(error);
                    result = 0;
                    loading('stop_load');
                    var errors = error.response.data.errors;
                    var errorsHtml = '';
                    $.each(errors, function (key, value) {
                        errorsHtml += '<li>' + value[0] + '</li>';
                    });
                    toastr.error(errorsHtml, '@langapp('response_status') ');
                }
            });
            return result;
        }
        else
        {
            toastr.error('Please select site.');
        }

    }

    $('#btn_delete_rule_site').click(function(){

        let delete_site = $('#delete_site').val();
        let delete_mode = $('#delete_mode').val();
        let delete_rule_id = $('#delete_rule_id').val();

        $.ajax({
            url: "{{ route('agentmanagement.delete_rule_site')}}",
            type: "POST",
            data: {
                delete_site: delete_site,
                delete_mode: delete_mode,
                delete_rule_id: delete_rule_id
            },
            success: function(response){

                if(response.status == 'success')
                {
                    $("#delete_select").modal("hide");
                    if(delete_mode == 'master')
                    {
                        $('li[data-id='+delete_rule_id+']').remove();
                    }
                    else
                    {
                        $('#btn_search_rule').trigger('click');
                    }
                    toastr.success(response.message);
                }
                else
                {
                    toastr.error(response.message);
                }

            }
        });

    });

    $('#btn_select_all_master').click(function(){

        let site_id = $('#hd_site_id').val();

        if(site_id)
        {
            $.ajax({
                url: "{{ route('agentmanagement.select_rule_all_master') }}",
                type: "POST",
                data: {
                    site_id:site_id
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) 
                {
                    loading('stop_load');
    
                    if(response.status == 'success')
                    {
                        $('#btn_search_rule').trigger('click');
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }
        else
        {
            toastr.error('Please select site.');
        }

    });

    $('#btn_delete_all_site').click(function(){

        let site_id = $('#hd_site_id').val();

        if(site_id)
        {
            $.ajax({
                url: "{{ route('agentmanagement.delete_rule_all_site') }}",
                type: "POST",
                data: {
                    site_id:site_id
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) 
                {
                    loading('stop_load');

                    if(response.status == 'success')
                    {
                        $('#btn_search_rule').trigger('click');
                        toastr.success(response.message);
                    }
                    else
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }
        else
        {
            toastr.error('Please select site.');
        }

    });
   
    var keyword_rule = document.getElementById('keyword_rule'),
	site_rule = document.getElementById('site_rule');

    new Sortable(keyword_rule, {
        group: {
            name: 'shared',
            pull: 'clone',
            put: false,
            revertClone: true
        },
        animation: 150,
        sort: false,
        dataIdAttr: 'data-id',
        removeCloneOnHide: true
    });

    new Sortable(site_rule, {
        group: {
            name: 'shared'
        },
        sort: false,
        animation: 150,
        dataIdAttr: 'data-id',
        onAdd: function (evt) {
            let from_id = evt.from.id;
            let to_id = evt.to.id;
            let attributes_id = evt.item.attributes['data-id'].value;
            check_insert_rule_process(''+from_id+'',''+to_id+'',attributes_id);
        },
    });

    function add_new_category()
    {
        let select_category_name = $('#name :selected').val();
        let new_category_name = $('#name_new').val();

        if(new_category_name)
        {
            $.ajax({
                url: "{{ route('agentmanagement.add_new_category') }}",
                type: 'POST',
                data: {
                    new_category_name:new_category_name
                },
                success:function(response)
                {
                    if(response.status == 'success')
                    {

                        toastr.success(response.message);

                        let html = `
                            <option value="">Choose an Category</option>
                        `;

                        const select_category = response.select_category;
                        for(let i in select_category)
                        {
                            html += `
                                <option value="${select_category[i].id}" ${select_category_name == select_category[i].id ? 'selected' : ''}>${select_category[i].name}</option>
                            `;
                        }

                        $('#name').empty().append(html);

                        $('#name_new').val('');
                        $('#check_name_new').addClass('d-none');
                        $('#close_add_category').trigger('click');

                        tbl_category_rule.ajax.reload();
                        tbl_all_rule.ajax.reload();

                    }
                    else if(response.status == 'error')
                    {
                        toastr.error(response.message);
                    }
                }
            });
        }
        else
        {
            input_check_err('#name_new', '#check_name_new')
        }

    }

    function input_check_err(id_input, id_err)
    {
        $(id_err).removeClass('d-none');

        $(id_input).keyup(function(){
            if($(id_input).val().length == 0)
            {
                $(id_err).removeClass('d-none');
            }
            else
            {
                $(id_err).addClass('d-none');
            }
        });
    }

        function delete_row(id)
    {
        $(id).remove();     
    }

    $('#add_ssdeep_pack_modal').on('hidden.bs.modal', function () {
        $('#form_add_ssdeep_pack')[0].reset();
        $('#error_ssdeep_version').empty();
        $('#error_ssdeep_category').empty();
        $('#error_ssdeep_title').empty();
        $('#error_ssdeep_file').empty();
        $('#btn_save_ssdeep_pack').html('<i class="fas fa-paper-plane"></i> Save').attr('disabled', false);
    });

    $('#btn_search_ssdeep').off('click').on('click', function(){
        let search_site = $('#search_ssdeep_site').val();
        let keyword_search = $('#keyword_ssdeep_search').val();

        if(!search_site){
            toastr.error('Please select site.');
            return;
        }

        $.ajax({
            url: "{{ route('agentmanagement.get_ssdeep_site') }}",
            type: "get",
            data: {
                search_site: search_site,
                keyword_search: keyword_search
            },
            success: function(response){
                if(response.data_site){
                    $('#txt_name_ssdeep_site').empty().append(response.data_site.name);
                    $('#hd_ssdeep_site_id').val(response.data_site.id);
                }
                $('#keyword_ssdeep').empty().append(response.html_master || '');
                $('#site_ssdeep').empty().append(response.html || '');
                bind_delete_ssdeep_site_master();
            },
            error: function(xhr){
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load ssdeep packs';
                toastr.error(msg);
            }
        });
    });

    function ssdeep_assign_site(attributes_id){
        let site_id = $('#hd_ssdeep_site_id').val();
        if(!site_id){
            toastr.error('Please select site.');
            return;
        }
        $.ajax({
            type: "POST",
            url: "{{ route('agentmanagement.ssdeep_assign_site') }}",
            data: {
                site_id: site_id,
                ssdeep_file_id: attributes_id
            },
            beforeSend: function(){ loading('load'); },
            success: function(response){
                loading('stop_load');
                if(response.status == 'success'){
                    $('#btn_search_ssdeep').trigger('click');
                    reload_ssdeep_pack_table();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(){
                loading('stop_load');
                toastr.error('Assign failed');
            }
        });
    }

    $('#btn_delete_ssdeep_site').off('click').on('click', function(){
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_unassign_site') }}",
            type: "POST",
            data: {
                site_id: $('#delete_ssdeep_site').val(),
                ssdeep_file_id: $('#delete_ssdeep_id').val(),
                delete_mode: $('#delete_ssdeep_mode').val()
            },
            success: function(response){
                if(response.status == 'success'){
                    $('#delete_ssdeep_select').modal('hide');
                    $('#btn_search_ssdeep').trigger('click');
                    reload_ssdeep_pack_table();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });

    $('#btn_select_all_ssdeep_master').off('click').on('click', function(){
        let site_id = $('#hd_ssdeep_site_id').val();
        if(!site_id){
            toastr.error('Please select site.');
            return;
        }
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_assign_all_master') }}",
            type: "POST",
            data: { site_id: site_id },
            beforeSend: function(){ loading('load'); },
            success: function(response){
                loading('stop_load');
                if(response.status == 'success'){
                    $('#btn_search_ssdeep').trigger('click');
                    reload_ssdeep_pack_table();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(){
                loading('stop_load');
                toastr.error('Assign all failed');
            }
        });
    });

    $('#btn_delete_all_ssdeep_site').off('click').on('click', function(){
        let site_id = $('#hd_ssdeep_site_id').val();
        if(!site_id){
            toastr.error('Please select site.');
            return;
        }
        if(!confirm('Remove all ssdeep packs from this site?')){
            return;
        }
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_delete_all_site') }}",
            type: "POST",
            data: { site_id: site_id },
            beforeSend: function(){ loading('load'); },
            success: function(response){
                loading('stop_load');
                if(response.status == 'success'){
                    $('#btn_search_ssdeep').trigger('click');
                    reload_ssdeep_pack_table();
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(){
                loading('stop_load');
                toastr.error('Delete all failed');
            }
        });
    });

    function update_ssdeep_pack_status(id){
        let chk = $('#ssdeep_status_'+id).is(':checked') ? 1 : 0;
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_status') }}",
            type: "POST",
            data: { id: id, chk_status: chk },
            success: function(response){
                toastr.success(response.message);
                if($('#hd_ssdeep_site_id').val()){
                    $('#btn_search_ssdeep').trigger('click');
                }
            }
        });
    }

    function apply_ssdeep_auto_distribute_ui(enabled){
        var $btn = $('#btn_ssdeep_auto_distribute');
        $btn.attr('data-enabled', enabled ? '1' : '0');
        $btn.removeClass('btn-success btn-default').addClass(enabled ? 'btn-success' : 'btn-default');
        $btn.find('i').removeClass('fa-toggle-on fa-toggle-off').addClass(enabled ? 'fa-toggle-on' : 'fa-toggle-off');
        $('#ssdeep_auto_dist_badge').text(enabled ? 'ON' : 'OFF');
    }

    $('#btn_ssdeep_auto_distribute').off('click').on('click', function(){
        var $btn = $(this);
        var currentlyOn = $btn.attr('data-enabled') === '1';
        var nextOn = currentlyOn ? 0 : 1;
        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_auto_distribute') }}",
            type: "POST",
            data: { chk_status: nextOn },
            success: function(response){
                $btn.prop('disabled', false);
                if(response.status === 'success'){
                    apply_ssdeep_auto_distribute_ui(typeof response.enabled !== 'undefined' ? !!response.enabled : nextOn === 1);
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Update failed');
                }
            },
            error: function(xhr){
                $btn.prop('disabled', false);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Update failed';
                toastr.error(msg);
            }
        });
    });

    $('#btn_ssdeep_cleanup_duplicates').off('click').on('click', function(){
        if(!confirm('ลบถาวร pack/candidate ที่ซ้ำ?\n\n- Master Ssdeep Auto: เหลือ 1 ต่อ category (ลบแถว+ไฟล์จริง)\n- sha256 ซ้ำ: เหลือ 1\n- Candidates: ลบ hash ซ้ำออกจากตาราง\n- รวมตัวที่เคยปิด active ไว้ด้วย')){
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_cleanup_duplicates') }}",
            type: "POST",
            data: {},
            success: function(response){
                $btn.prop('disabled', false);
                if(response.status === 'success'){
                    toastr.success(response.message);
                    reload_ssdeep_pack_table();
                    if(typeof tbl_ssdeep_candidate !== 'undefined' && tbl_ssdeep_candidate){
                        tbl_ssdeep_candidate.ajax.reload(null, false);
                    }
                } else {
                    toastr.error(response.message || 'Cleanup failed');
                }
            },
            error: function(xhr){
                $btn.prop('disabled', false);
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Cleanup failed';
                toastr.error(msg);
            }
        });
    });

    function delete_ssdeep_pack(id){
        if(!confirm('Delete this ssdeep pack permanently (row + file)?')){
            return;
        }
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_delete') }}",
            type: "POST",
            data: { id: id },
            success: function(response){
                toastr.success(response.message);
                reload_ssdeep_pack_table();
                if($('#hd_ssdeep_site_id').val()){
                    $('#btn_search_ssdeep').trigger('click');
                }
            }
        });
    }

    function edit_ssdeep_pack(id) {
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_get') }}",
            type: "GET",
            data: { id: id },
            success: function(res) {
                if (res.status === 'success') {
                    var pack = res.pack;
                    var siteIds = res.site_ids || [];
                    $('#edit_ssdeep_id').val(pack.id);
                    $('#edit_ssdeep_version').val(pack.version);
                    $('#edit_ssdeep_category').val(pack.category || '');
                    $('#edit_ssdeep_title').val(pack.title || '');
                    $('#edit_ssdeep_description').val(pack.description || '');
                    $('#edit_ssdeep_file_name').text(pack.file_name || '-');
                    $('#edit_ssdeep_status').prop('checked', pack.status === 'Y');

                    $('input[name="site[]"].edit_ssdeep_site_item').prop('checked', false);
                    $('#edit_ssdeep_site_all').prop('checked', false);

                    siteIds.forEach(function(sId) {
                        $('input[name="site[]"].edit_ssdeep_site_item[value="' + sId + '"]').prop('checked', true);
                    });

                    $('#edit_ssdeep_pack_modal').modal('show');
                } else {
                    toastr.error(res.message || 'Failed to load pack details');
                }
            },
            error: function() {
                toastr.error('Failed to load pack details');
            }
        });
    }
    window.edit_ssdeep_pack = edit_ssdeep_pack;
    window.delete_ssdeep_pack = delete_ssdeep_pack;

    $(document).off('click', '.btn-edit-ssdeep-pack').on('click', '.btn-edit-ssdeep-pack', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) {
            edit_ssdeep_pack(id);
        }
    });

    $(document).off('click', '.btn-delete-ssdeep-pack').on('click', '.btn-delete-ssdeep-pack', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) {
            delete_ssdeep_pack(id);
        }
    });

    $('#btn_update_ssdeep_pack').off('click').on('click', function() {
        var formData = $('#form_edit_ssdeep_pack').serialize();
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_update') }}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#btn_update_ssdeep_pack').html('<i class="fas fa-spinner fa-spin"></i> Saving...').attr('disabled', true);
            },
            success: function(res) {
                $('#btn_update_ssdeep_pack').html('<i class="fas fa-paper-plane"></i> Save Changes').attr('disabled', false);
                if (res.status === 'success') {
                    $('#edit_ssdeep_pack_modal').modal('hide');
                    reload_ssdeep_pack_table();
                    if ($('#hd_ssdeep_site_id').val()) {
                        $('#btn_search_ssdeep').trigger('click');
                    }
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                $('#btn_update_ssdeep_pack').html('<i class="fas fa-paper-plane"></i> Save Changes').attr('disabled', false);
                toastr.error('Update failed');
            }
        });
    });

    $('#btn_save_bulk_ssdeep_site').off('click').on('click', function() {
        var formData = $('#form_bulk_edit_ssdeep_pack').serialize();
        $.ajax({
            url: "{{ route('agentmanagement.ssdeep_pack_bulk_update_sites') }}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#btn_save_bulk_ssdeep_site').html('<i class="fas fa-spinner fa-spin"></i> Saving...').attr('disabled', true);
            },
            success: function(res) {
                $('#btn_save_bulk_ssdeep_site').html('<i class="fas fa-paper-plane"></i> บันทึกข้อมูลทั้งหมด').attr('disabled', false);
                if (res.status === 'success') {
                    $('#bulk_edit_ssdeep_pack_modal').modal('hide');
                    reload_ssdeep_pack_table();
                    if ($('#hd_ssdeep_site_id').val()) {
                        $('#btn_search_ssdeep').trigger('click');
                    }
                    toastr.success(res.message);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                $('#btn_save_bulk_ssdeep_site').html('<i class="fas fa-paper-plane"></i> บันทึกข้อมูลทั้งหมด').attr('disabled', false);
                toastr.error('Bulk update failed');
            }
        });
    });

    var keyword_ssdeep = document.getElementById('keyword_ssdeep'),
        site_ssdeep = document.getElementById('site_ssdeep');

    new Sortable(keyword_ssdeep, {
        group: {
            name: 'ssdeep_shared',
            pull: 'clone',
            put: false,
            revertClone: true
        },
        animation: 150,
        sort: false,
        dataIdAttr: 'data-id',
        removeCloneOnHide: true
    });

    new Sortable(site_ssdeep, {
        group: {
            name: 'ssdeep_shared'
        },
        sort: false,
        animation: 150,
        dataIdAttr: 'data-id',
        onAdd: function (evt) {
            let site_id = $('#hd_ssdeep_site_id').val();
            if(!site_id){
                toastr.error('Please select site.');
                if(evt.item && evt.item.parentNode){
                    evt.item.parentNode.removeChild(evt.item);
                }
                return;
            }
            let attributes_id = evt.item.attributes['data-id'].value;
            ssdeep_assign_site(attributes_id);
        },
    });

</script>

@endpush
@endsection
