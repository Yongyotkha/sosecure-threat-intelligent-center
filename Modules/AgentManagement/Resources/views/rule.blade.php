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
                    
                    {{-- <button type="button" id="btn_del_select" class="btn btn-sm btn-danger"
                    value="bulk-delete" disabled>
                    <span data-rel="tooltip" title="Delete" data-placement="top">@icon('solid/trash-alt')<span class="hide-text">@langapp('delete')</span></span>
                    </button>

                    <button id="btn-change-status" class="btn btn-sm btn-{{ get_option('theme_color')  }}"
                        data-toggle="modal" data-target="#change_status" disabled>
                        <span data-rel="tooltip" title="Change Status" data-placement="bottom"><i class="fas fa-exchange-alt"></i><span class="hide-text">Change Status</span></span>
                    </button> --}}
     

                </div>     
            </div>
        </header>

        {{-- Tab Content --}}
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
                            <li><a href="#tab_agent" data-toggle="tab" id="tab_agent_click">All Rule</a></li>   
                            <li><a href="#tab_category" data-toggle="tab" id="tab_category_click">Category</a></li>   
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
                                                    <select name="site" id="site" class="tselect2-option form-control select-site" >
                                                        <option value="">All Site</option>
                                                            @if ($site_settings)
                                                            @foreach ($site_settings as $site_settings)
                                                                <option value="{{$site_settings->id}}" {{$site_settings->id == 59 ? 'selected' : '' }}>{{$site_settings->name}}</option>
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
                                                <button type="button" class="btn btn-info btn-responsive btn-fz-13" style="margin-top: 22px;">
                                                    <i class="fas fa-search"></i>
                                                    @langapp('apply')
                                                </button>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h5 class="font-weight-bold">Rule</h5>
                                                <div class="box-item-keyword">
                                                    <!-- Mockup Data -->
                                                    <?php
                                                        $data_email = [
                                                        'EMAIL_Cryptowall',
                                                        'Email_PHP_Mailer',
                                                        'Email_fake_it_maintenance_bulletin',
                                                        'Email_generic_phishing',
                                                        'Email_quota_limit_warning',
                                                        'attachment',
                                                        'attachment',
                                                        'attachment',
                                                        'image',
                                                        'scam',
                                                        'urls',
                                                        ];
                                                        $length_data_email = count($data_email);

                                                        $data_cve = [
                                                            // Group CVE
                                                            'CVE-2010-0805',
                                                            'CVE-2010-0887',
                                                            'CVE-2010-1297',
                                                            'CVE-2012-0158',
                                                            'CVE-2013-0074',
                                                            'CVE-2013-0422',
                                                            'CVE-2015-1701',
                                                            'CVE-2015-2426',
                                                            'CVE-2015-2545',
                                                            'CVE-2015-5119',
                                                            'CVE-2016-5195',
                                                            'CVE-2017-11882',
                                                            'CVE-2018-20250',
                                                            'CVE-2018-4878',
                                                        ];

                                                        $length_data_cve = count($data_cve);

                                                    ?>
                                                    <ul id="keyword_rule" class="main-list keyword-list">
                                                        <!-- Mockup Data -->
                                                        <?php
                                                            for($i = 0 ; $i < $length_data_email ; $i++)  {
                                                        ?>
                                                        <li class="item-list item--keyword">
                                                            <div class="left-side-item">
                                                                <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                                                <span class="text-keyword"><?php echo $data_email[$i] ?></span>
                                                            </div>
                                                            <div class="action-keyword">
                                                                <a href="#" class="text-white m-r-xs edit-keyword" data-target="#edit_keyword" data-toggle="modal"><i class="fas fa-ellipsis-v"></i></a>
                                                                <a href="#" class="text-white delete-item-keyword"><i class="fas fa-trash-alt"></i></a>
                                                            </div>
                                                        </li>
                                                        <?php }  ?>
                                                        <!-- Mockup Data -->
                                                        <?php
                                                            for($i = 0 ; $i < $length_data_cve ; $i++)  {
                                                        ?>
                                                        <li class="item-list item--keyword">
                                                            <div class="left-side-item">
                                                                <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                                                <span class="text-keyword"><?php echo $data_cve[$i] ?></span>
                                                            </div>
                                                            <div class="action-keyword">
                                                                <a href="#" class="text-white m-r-xs edit-keyword" data-target="#edit_keyword" data-toggle="modal"><i class="fas fa-ellipsis-v"></i></a>
                                                                <a href="#" class="text-white delete-item-keyword"><i class="fas fa-trash-alt"></i></a>
                                                            </div>
                                                        </li>
                                                        <?php }  ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <h5 class="font-weight-bold">บริษัท เมจิกเทคโซลูชั่น จำกัด</h5>
                                                <div class="box-item-keyword">
                                                    <ul id="site_rule" class="main-list site-list">
                                                        <!-- Mockup Data -->
                                                        <?php
                                                            for($i = 0 ; $i < $length_data_email ; $i++)  {
                                                        ?>
                                                        <li class="item-list item--keyword">
                                                            <div class="left-side-item">
                                                                <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                                                <span class="text-keyword"><?php echo $data_email[$i] ?></span>
                                                            </div>
                                                            <div class="action-keyword">
                                                                <a href="#" class="text-white m-r-xs edit-keyword" data-target="#edit_keyword" data-toggle="modal"><i class="fas fa-ellipsis-v"></i></a>
                                                                <a href="#" class="text-white delete-item-keyword"><i class="fas fa-trash-alt"></i></a>
                                                            </div>
                                                        </li>
                                                        <?php }  ?>

                                                         <!-- Mockup Data -->
                                                        <?php
                                                            for($i = 0 ; $i < $length_data_cve ; $i++)  {
                                                        ?>
                                                        <li class="item-list item--keyword">
                                                            <div class="left-side-item">
                                                                <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                                                <span class="text-keyword"><?php echo $data_cve[$i] ?></span>
                                                            </div>
                                                            <div class="action-keyword">
                                                                <a href="#" class="text-white m-r-xs edit-keyword" data-target="#edit_keyword" data-toggle="modal"><i class="fas fa-ellipsis-v"></i></a>
                                                                <a href="#" class="text-white delete-item-keyword"><i class="fas fa-trash-alt"></i></a>
                                                            </div>
                                                        </li>
                                                        <?php }  ?>
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
                                                <i class="fas fa-table"></i> Table All Rule
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
                                                <i class="fas fa-table"></i> Table Category
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
                                            {{-- <input type="text" name="name" id="name" class="form-control"> --}}
                                            <select name="name" id="name" class="form-control check_test_select">
                                                <option value="">Choose an Category</option>
                                                {{-- @foreach ($select_category as $category)
                                                <option value="{{$category['id']}}">{{$category['name']}}</option>
                                                @endforeach --}}
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
                                        {{-- <tr>
                                            <td style="width: 33.33%">
                                                <select class="select-2--rule form-control" id="">
                                                    <option value=""></option>
                                                </select>
                                            </td>
                                            <td  style="width: 33.33%">
                                                <input type="text" id="" class="form-control">
                                            </td>
                                            <td  style="width: 33.33%">
                                                <select class="select-2--rule form-control" id="">
                                                    <option value=""></option>
                                                </select>
                                            </td>
                                            <td>
                                                <button ype="button" class="btn btn-sm btn-danger delete_rule"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr> --}}
                                    </tbody>
                                </table>
                                <span id="error_detail" style="color:red;"></span>
                                {{-- <button type="button" class="btn btn-sm btn-info btn-block" onclick="add_rule();">Add</button> --}}
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
                                {{-- <label>
                                    <input type="checkbox" id="site_{{@$key}}" name="site[]" value="{{@$key}}">
                                    <span>{{@$name}}</span>
                                </label> --}}
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
            { data: 'updated_at' },
            { data: 'c_status' },
            { data: 'c_action' }
        ]
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

    $('#add_rule_modal').on('show.bs.modal', function () {

        $('#demo').hide();

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

                        tbl_category_rule.ajax.reload();
                        tbl_all_rule.ajax.reload();

                        setTimeout(function(){
                            {{-- window.location.href = response.route; --}}
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

                    $('#btn_save_rule').html('Success');

                    setTimeout(function(){
                        {{-- window.location.href = response.route; --}}
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

                    {{-- delete_row(); --}}
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
                {{-- $('#rule_item tbody').empty(); --}}
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
        dataIdAttr: 'data-id'
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

                        toastr.error(response.message);

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
        {{-- var whichtr = $(this).closest('tr');
        alert('1'); --}}
        $(id).remove();     
    }

</script>

@endpush
@endsection


