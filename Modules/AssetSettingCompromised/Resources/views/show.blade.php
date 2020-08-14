@extends('layouts.app')
@section('content')
<style>
    #chart-show-pie-1 .chart-legend,
    #chart-show-pie-2 .chart-legend,
    #chart-show-pie-3 .chart-legend
    {
        display: none
    }
</style>
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header panel-heading bg-white b-b b-light haed-d-flex">
                <div class="bc-head m-none">@langapp('compromised') > Assets Setting > baac.or.th</div>
                <div class="d-flex">
                    <div class="">
                        <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm" data-rel="tooltip" title="@langapp('create')" data-placement="top">
                            @icon('solid/plus') @langapp('create') Asset
                        </a>
    
                        <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm" data-rel="tooltip" title="@langapp('create')" data-placement="top">
                            @icon('solid/plus') @langapp('create') Group
                        </a>
    
                        <a href="{{ route('deals.index', ['view' => 'table']) }}" data-rel="tooltip" title="Table" data-placement="bottom" class="btn btn-sm btn-default">
                            @icon('solid/th')
                        </a>
                        <a href="{{ route('deals.index', ['view' => 'kanban']) }}" data-rel="tooltip" title="Kanban" data-placement="bottom" class="btn btn-sm btn-default">
                            @icon('solid/align-justify')
                        </a>
                    </div>
    
                    <div class="btn-group">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle btn-responsive"
                        data-toggle="dropdown">@icon('solid/ellipsis-h')
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="{{ route('deals.index', ['view' => 'forecast']) }}">@icon('solid/clock', 'text-muted') @langapp('forecasted')</a></li>
                            <li><a href="{{ route('deals.import') }}" data-toggle="ajaxModal">@icon('solid/cloud-upload-alt', 'text-muted') @langapp('import')</a></li>
                            <li><a href="{{ route('deals.export')  }}">@icon('solid/cloud-download-alt', 'text-muted') @langapp('export')</a></li>
                            <li><a href="{{ route('deals.index', ['filter' => 'won'])  }}">@icon('solid/check', 'text-muted') @langapp('won')</a></li>
                            <li><a href="{{ route('deals.index', ['filter' => 'lost'])  }}">@icon('solid/times', 'text-muted') @langapp('lost')</a></li>
                            <li><a href="{{ route('deals.index', ['filter' => 'archived'])  }}">@icon('solid/archive', 'text-muted') @langapp('archived')</a></li>
                        </ul>
                    </div>
                </div>
               

            </header>

            <div class="scrollable wrapper"style="padding-top:0">
                <div class="container-fluid" style="padding: 0">
                    <div class="row" style="margin:0">
                        <div class="bd-ol-ct m-t-xs">
                            <div class="row" style="margin:0">
                                <div class="col-lg-6 col-md-6">
                                    <div class="m-xs padder-v pallete bg-dark">
                                        <a class="clear" href="#">
                                            <span class="fa-stack text-center fa-2x pull-left m-r-sm">
                                                <i class="fab fa-expeditedssl"></i>
                                            </span>
                                            <small class="text-uc">Total Item </small>
                                            <span class="h4 block m-t-xs">200</span>
                                        </a>
                                    </div>
                                   
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <div class="m-xs padder-v pallete bg-dark">
                                        <a class="clear" href="#">
                                            <span class="fa-stack text-center fa-2x pull-left m-r-sm">
                                                <i class="fas fa-users"></i>
                                            </span>
                                            <small class="text-uc">Total Group </small>
                                            <span class="h4 block m-t-xs">10</span>
                                        </a>
                                    </div>   
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <div class="m-xs padder-v pallete bg-dark">
                                        <a class="clear" href="#">
                                            <span class="fa-stack text-center fa-2x pull-left m-r-sm">
                                                <i class="fas fa-bug"></i>
                                            </span>
                                            <small class="text-uc">Total Item Scan 195</small>
                                            <span class="h4 block m-t-xs">last : 2020-08-18 16:11</span>
                                        </a>
                                        <div class="btn-absolute">
                                            <button type="submit" id="button" class="btn btn-sm btn-default" value="">
                                                <span data-rel="tooltip" title="" data-original-title="Re-run">@icon('solid/redo')</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <div class="m-xs padder-v pallete bg-dark">
                                        <a class="clear" href="#">
                                            <span class="fa-stack text-center fa-2x pull-left m-r-sm">
                                                <i class="fas fa-plus-circle"></i>
                                            </span>
                                            <small class="text-uc">Total Item Add</small>
                                            <span class="h4 block m-t-xs">5</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class="row" style="margin:0;">
                        <div class="bd-ol-ct m-t-md">
                            <div class="row">
                                <div class="col-md-6 m-b-md">
                                    <select name="" id="" class="form-control">
                                        <option value="">Group All</option>
                                    </select>
                                </div>
                                <div class="col-md-6 m-b-md">
                                    <input type="text" class="form-control" placeholder="keyword">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <div class="btn-group">
                                        <button class="btn btn-info">
                                            <i class="fas fa-search"></i>
                                            <span> Search </span>
                                        </button>
                                        <button class="btn btn-default">
                                            <i class="fas fa-broom"></i>
                                            <span> Clear </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row m-t-md overflow-x-auto">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body collapse in">
                                    <div class="card-block">
                                        <div class="overflow-hidden">
                                            <div id="todo-lists-basic-demo"class="lobilists-wrapper lobilists single-line sortable ps-container ps-theme-dark ps-active-x">
                                                <div class="lobilist-wrapper ps-container ps-theme-dark ps-active-y kanban-col">
                                                    <div id="lobilist-list-0"
                                                        class="lobilist lobilist-default">
                                                        <div class="lobilist-header ui-sortable-handle">
                                                            <div class="lobilist-title text-ellipsis text-uc text-muted">
                                                                <span class="arrow right"></span> INTERNEL
                                                            </div>
                                                        </div>
                                                        <div class="lobilist-body scrumboard slim-scroll" data-disable-fade-out="true"
                                                            data-distance="0" data-size="3px" data-height="550"
                                                            data-color="#333333">
                                                            <ul class="lobilist-items ui-sortable list" id="">
                                                                <li id="" draggable="true" class="lobilist-item kanban-entry grab custom-lobilist-item">
                                                                    <div class="lobilist-item-title text-ellipsis m-l-xs font14">
                                                                        <a href="" class="text-danger">baac.or.th</a>
                                                                    </div>
                                                                    <span class="thumb-xs avatar lobilist-check lobilist-check-top">
                                                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                                                    </span>
                                                                    <div class="drag-handler"></div>
                                                                </li>
                                                                <li id="" draggable="true" class="lobilist-item kanban-entry grab custom-lobilist-item">
                                                                    <div class="lobilist-item-title text-ellipsis m-l-xs font14">
                                                                        <a href="" class="text-danger">ns1.baac.or.th</a>
                                                                    </div>
                                                                    <span class="thumb-xs avatar lobilist-check lobilist-check-top">
                                                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                                                    </span>
                                                                    <div class="drag-handler"></div>
                                                                </li>
                                                                <li id="" draggable="true" class="lobilist-item kanban-entry grab custom-lobilist-item">
                                                                    <div class="lobilist-item-title text-ellipsis m-l-xs font14">
                                                                        <a href="" class="text-danger">202.94.73.42</a>
                                                                    </div>
                                                                    <span class="thumb-xs avatar lobilist-check lobilist-check-top">
                                                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                                                    </span>
                                                                    <div class="drag-handler"></div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        {{-- <div class="lobilist-footer">
                                                            <strong>123</strong>
                                                            <strong class="pull-right">Deal(s)</strong>
                                                        </div> --}}
                                                    </div>
                                                </div>
        
                                                <div class="lobilist-wrapper ps-container ps-theme-dark ps-active-y kanban-col">
                                                    <div id="lobilist-list-0"
                                                        class="lobilist lobilist-default">
                                                        <div class="lobilist-header ui-sortable-handle">
                                                            <div class="lobilist-title text-ellipsis text-uc text-muted">
                                                                <span class="arrow right"></span> EMAIL ADDRESS
                                                            </div>
                                                        </div>
                                                        <div class="lobilist-body scrumboard slim-scroll" data-disable-fade-out="true"
                                                            data-distance="0" data-size="3px" data-height="550"
                                                            data-color="#333333">
                                                            <ul class="lobilist-items ui-sortable list" id="">
                                                                <li id="" draggable="true" class="lobilist-item kanban-entry grab custom-lobilist-item">
                                                                    <div class="lobilist-item-title text-ellipsis m-l-xs font14">
                                                                        <a href="" class="text-danger">anita@baac.or.th</a>
                                                                    </div>
                                                                    <span class="thumb-xs avatar lobilist-check lobilist-check-top">
                                                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                                                    </span>
                                                                    <div class="drag-handler"></div>
                                                                </li>
                                                                <li id="" draggable="true" class="lobilist-item kanban-entry grab custom-lobilist-item">
                                                                    <div class="lobilist-item-title text-ellipsis m-l-xs font14">
                                                                        <a href="" class="text-danger">boonthai@baac.or.th</a>
                                                                    </div>
                                                                    <span class="thumb-xs avatar lobilist-check lobilist-check-top">
                                                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                                                    </span>
                                                                    <div class="drag-handler"></div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        {{-- <div class="lobilist-footer">
                                                            <strong>123</strong>
                                                            <strong class="pull-right">Deal(s)</strong>
                                                        </div> --}}
                                                    </div>
                                                </div>
        
                                                <div class="lobilist-wrapper ps-container ps-theme-dark ps-active-y kanban-col">
                                                    <div id="lobilist-list-0"
                                                        class="lobilist lobilist-default">
                                                        <div class="lobilist-header ui-sortable-handle">
                                                            <div class="lobilist-title text-ellipsis text-uc text-muted">
                                                                <span class="arrow right"></span> DNS TXT RECORD
                                                            </div>
                                                        </div>
                                                        <div class="lobilist-body scrumboard slim-scroll" data-disable-fade-out="true"
                                                            data-distance="0" data-size="3px" data-height="550"
                                                            data-color="#333333">
                                                            <ul class="lobilist-items ui-sortable list" id="">
                                                                <li id="" draggable="true" class="lobilist-item kanban-entry grab custom-lobilist-item">
                                                                    <div class="lobilist-item-title text-ellipsis m-l-xs font14">
                                                                        <a href="" class="text-danger">Bank for Agriuclture and agricutural Coop</a>
                                                                    </div>
                                                                    <span class="thumb-xs avatar lobilist-check lobilist-check-top">
                                                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                                                    </span>
                                                                    <div class="drag-handler"></div>
                                                                </li>
                                                                <li id="" draggable="true" class="lobilist-item kanban-entry grab custom-lobilist-item">
                                                                    <div class="lobilist-item-title text-ellipsis m-l-xs font14">
                                                                        <a href="" class="text-danger">MS=ms24090778</a>
                                                                    </div>
                                                                    <span class="thumb-xs avatar lobilist-check lobilist-check-top">
                                                                        <i class="fas fa-user-circle fa-2x text-info"></i>
                                                                    </span>
                                                                    <div class="drag-handler"></div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        {{-- <div class="lobilist-footer">
                                                            <strong>123</strong>
                                                            <strong class="pull-right">Deal(s)</strong>
                                                        </div> --}}
                                                    </div>
                                                </div>
        
                                                <div class="modal modal-static fade" id="processing-modal"
                                                    role="dialog" aria-hidden="true">
                                                    <div class="modal-dialog processing-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-body">
                                                                <div class="text-center">
                                                                    @icon('solid/sync-alt', 'fa-4x fa-spin')
                                                                    <h4>Processing...</h4>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


            
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')

@endpush

@push('pagescript')
<script type="text/javascript">
$(document).ready(function () {
var kanbanCol = $('.scrumboard');
draggableInit();
});
function draggableInit() {
var sourceId;
$('[draggable=true]').bind('dragstart', function (event) {
sourceId = $(this).parent().attr('id');
event.originalEvent.dataTransfer.setData("text/plain", event.target.getAttribute('id'));
});
$('.scrumboard').bind('dragover', function (event) {
event.preventDefault();
});
$('.scrumboard').bind('drop', function (event) {
var children = $(this).children();
var targetId = children.attr('id');
if (sourceId != targetId) {
var elementId = event.originalEvent.dataTransfer.getData("text/plain");
$('#processing-modal').modal('toggle');
setTimeout(function () {
var element = document.getElementById(elementId);
deal_id = element.getAttribute('id');
$.ajax({
type: "POST",
url: "{{ route('deals.movestage') }}",
data: {
'id': deal_id,
'_token': '{{ csrf_token() }}',
'target': targetId
},
success: function (msg) {
toastr.success(msg, '@langapp('success') ');
}
});
children.prepend(element);
$('#processing-modal').modal('toggle');
}, 1000);
}
event.preventDefault();
});
}
</script>

@endpush
@endsection