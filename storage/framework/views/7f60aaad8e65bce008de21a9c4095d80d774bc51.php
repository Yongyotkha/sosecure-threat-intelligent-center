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
                    <li data-target="#step1" class="active">Requirements</li>
                    <li data-target="#step2">Permissions</li>
                    <li data-target="#step3">Configuration</li>
                  </ul>
                </div>
                <div class="step-content clearfix">
                  

                 <?php $__currentLoopData = $requirements['requirements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $requirement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <ul class="list">
            <li class="list__item list__title <?php echo e($phpSupportInfo['supported'] ? 'success' : 'error'); ?>">
                <strong><?php echo e(ucfirst($type)); ?></strong>
                <?php if($type == 'php'): ?>
                    <strong>
                        <small>
                            (version <?php echo e($phpSupportInfo['minimum']); ?> required)
                        </small>
                    </strong>
                    <span class="pull-right">
                        <strong>
                            <?php echo e($phpSupportInfo['current']); ?>

                        </strong>
                        <span class="text-<?php echo e($phpSupportInfo['supported']  ? 'success' : 'danger'); ?>"><?php echo e($phpSupportInfo['supported'] ? '✔' : '✘'); ?></span>
                    </span>
                <?php endif; ?>
            </li>
            <?php $__currentLoopData = $requirements['requirements'][$type]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $extention => $enabled): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="list__item <?php echo e($enabled ? 'success' : 'error'); ?>">
                    <?php echo e($extention); ?>

                    <span class="pull-right">
                        <span class="text-<?php echo e($enabled  ? 'success' : 'danger'); ?>"><?php echo e($enabled ? '✔' : '✘'); ?></span>
                </span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if( ! isset($requirements['errors']) && $phpSupportInfo['supported'] ): ?>
        <div class="buttons m-t-xs">
            <a class="btn btn-info btn-block" href="<?php echo e(route('LaravelInstaller::permissions')); ?>">
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