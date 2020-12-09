<?php $__env->startSection('content'); ?>
<section id="content" class="bg">
    <section class="vbox">
        

        <section class="scrollable wrapper bg-grey pd-0">
            <div class="container d-flex-center" style="height: 100%;width:80%">
                <div class="panel panel-default d-flex-center" style="height: 90%;width:100%">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <img src="<?php echo e(getAsset('images/logo_threat/logo.png')); ?>" alt="" style="max-width: 600px;margin-bottom:2rem;">
                                <h1>SOSECURE Threat inSight - CENTER</h1>
                                <h1>ยินดีต้อนรับคุณ</h1>
                                <h3><?php echo e(Auth::user()->name); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>



<?php $__env->startPush('pagestyle'); ?>
    <?php echo $__env->make('stacks.css.datatables', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('pagescript'); ?>
<?php echo $__env->make('stacks.js.datatables', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<script>

</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>