<ul class="streamline streamline-dotted m-t-lg list-feed">
    
    <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <li class="border-<?php echo e(get_option('theme_color')); ?> small sl-item">
        <div class="text-muted">
            <strong><?php echo e($activity->user->name); ?></strong>
            <span class="pull-right"><?php echo e(dateElapsed($activity->created_at)); ?></span>
            
        </div>
        <span class="">
            <?php echo trans('activity.'.$activity->action, ['value1' => '<span class="text-info">'.$activity->value1.'</span>', 'value2' => '<span class="text-success">'.$activity->value2.'</span>']); ?>
        </span>
    </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    
</ul>