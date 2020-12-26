<?php $__env->startSection('content'); ?>
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0"><?php echo e(svg_image('solid/bars')); ?></a>
                    <div class="bc-head">Data Feed (Social) </div>
                    <a href="#" class="btn btn-sm btn-<?php echo e(get_option('theme_color')); ?> pull-right" data-rel="tooltip" title="<?php echo trans('app.'.'export'); ?> CSV">
                        <?php echo e(svg_image('solid/download')); ?> CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right"><?php echo e(svg_image('solid/trash-alt')); ?> <?php echo trans('app.'.'delete'); ?></span>
                    </button>
                    <button id="btn-change-status" class="btn btn-sm btn-<?php echo e(get_option('theme_color')); ?> pull-right" data-toggle="modal" data-target="#change_status" disabled>
                        Change Status
                    </button>
                    <button id="advance-search" class="btn btn-sm btn-<?php echo e(get_option('theme_color')); ?> pull-right">
                        <span><?php echo trans('app.'.'Search_Advance'); ?></span>
                     </button>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default" id="hide-advance-search">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row m-b-md">
                                <div class="col-lg-12">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-1 col-xs-12 col-form-label">Search</label>
                                        <div class="col-sm-11 col-xs-12">
                                            <input type="text" id="news_title_search" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-3 col-xs-12 col-form-label">Source</label>
                                        <div class="col-sm-9 col-xs-12">
                                            <select id="source_select" class="form-control">
                                                <option value="1" selected>All</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div id="datafeed_date" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div style="margin-top: 8px;">
                                        <label class="mr-3">
                                            <input type="checkbox" name="" id="" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">Panding</span>
                                        </label>
                                        <label class="mr-3">
                                            <input type="checkbox" name="" id="" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">Approved</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 text-right mt-2">
                                    <button type="button" id="btn_news_search" class="btn btn-info btn-responsive">
                                        <i class="fas fa-search"></i>
                                        Search
                                    </button>
                                    <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive" style="white-space: nowrap">
                                        <i class="fas fa-broom"></i>
                                        <span> Clear </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    
                    <section class="panel panel-default">
                        <div class="table-responsive">
                            <table  class="table table-striped" id="table_data_feed">
                                <thead>
                                    <tr>
                                        <th class="no-sort w-10">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk"/>
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>Source</th>
                                        <th>Keyword Red</th>
                                        <th>Content</th>
                                        <th>Data Feed</th>
                                        <th class="no-sort"><?php echo trans('app.'.'action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <label>
                                                <input name="select_all" value="1" type="checkbox" class="select-chk"/>
                                                <span class="label-text"></span>
                                            </label>
                                        </td>
                                        <td>
                                            Pantip
                                        </td>
                                        <td>
                                            Fibre
                                        </td>
                                        <td>
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Dignissimos,
                                        </td>
                                        <td>
                                            2020-12-2020 12:12
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status">
                                                <?php echo e(svg_image('solid/check')); ?> Approved
                                            </button>
                                            <button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status">
                                                <?php echo e(svg_image('solid/times')); ?> Cancel
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <!-- Modal create_assets_vulnerability -->
    <div class="modal in fixed-left" id="change_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Confirm Information</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select name="" id="" class="form-control select2">
                                <option value="1">Approved</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="confirm-change-status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Confirm Information</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

<?php $__env->startPush('pagestyle'); ?>
    <?php echo $__env->make('stacks.css.datatables', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php echo $__env->make('stacks.css.datepicker', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php echo $__env->make('stacks.css.form', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <link rel="stylesheet" href="<?php echo e(getAsset('plugins/daterangepicker/daterangepicker.css')); ?>" type="text/css"/>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('pagescript'); ?>
<?php echo $__env->make('stacks.js.datatables', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('stacks.js.form', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('stacks.js.datepicker', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('stacks.js.daterangpicker', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('stacks.js.menusub', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('stacks.js.hidesettings', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php echo $__env->make('stacks.js.advanced_search', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<script>

$(function() {
    var table = $('#table_data_feed').DataTable({
    });
});

$('#source_select').select2();

$('#table_data_feed').on('click', '.select-chk', function () {
    if ($(this).is(':checked')) {
        $('#btn-change-status').prop("disabled", false);
    } else {
        if ($('.select-chk').filter(':checked').length < 1){
            $('#btn-change-status').attr('disabled',true);
        }
    }
});

$(function() { 
    var start = moment().startOf('hour');
    var end = moment().startOf('hour').add(32, 'hour');
    function cb(start, end) {
        $('#datafeed_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    $('#datafeed_date').daterangepicker({
        timePicker: true,
        startDate: start,
        endDate: end,
        locale: {
            format: 'M/DD hh:mm A'
        },
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);
    cb(start, end);
});

</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>