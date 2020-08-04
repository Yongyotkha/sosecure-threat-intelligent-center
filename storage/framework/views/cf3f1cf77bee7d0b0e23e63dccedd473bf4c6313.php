<?php $__env->startSection('content'); ?>
<section id="content" class="wrapper-md installer content">
  <div id="login-form" class="container aside-xxl animated fadeInUp">
    <section class="panel panel-default bg-white m-t-sm b-r-xs">
      <header class="panel-heading text-center login-heading">
        <strong>Workice CRM Installation</strong>
      </header>
      <div class="wizard">
        <div class="wizard-steps clearfix" id="form-wizard">
          <ul class="steps">
                    <li data-target="#step1">Requirements</li>
                    <li data-target="#step2" class="active">Permissions</li>
                    <li data-target="#step3">Configuration</li>
                  </ul>
        </div>
        <div class="step-content clearfix">
          
          <ul class="list">
            <?php $__currentLoopData = $permissions['permissions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="list__item list__item--permissions <?php echo e($permission['isSet'] ? 'success' : 'error'); ?>">
             ➤ <?php echo e($permission['folder']); ?>

              <span class="pull-right">
                <span class="text-<?php echo e($permission['isSet']  ? 'success' : 'danger'); ?>"><?php echo e($permission['isSet'] ? '✔' : '✘'); ?></span>
                <?php echo e($permission['permission']); ?>

              </span>
            </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </ul>
          <?php if( ! isset($permissions['errors'])): ?>
          <div class="buttons m-t-xs">
            <a href="<?php echo e(route('LaravelInstaller::environment')); ?>" class="btn btn-info btn-block">
              Next
            </a>
          </div>
          <?php endif; ?>
          
        </div>
      </div>
      
      <?php if(!settingEnabled('hide_branding')): ?>
      <?php echo $__env->make('partial.branding', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
      <?php endif; ?>
      
    </section>
  </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('installer::layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>