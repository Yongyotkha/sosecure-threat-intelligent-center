<header class="bg-<?php echo e(get_option('top_bar_color')); ?> header navbar navbar-fixed-top-xs nav-z">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen" data-target="#nav">
                <?php echo e(svg_image('solid/bars')); ?>
            </a>
            <a href="<?php echo e(url('/')); ?>" class="navbar-brand">
                <?php $display = get_option('logo_or_icon'); ?>
                <?php if($display == 'logo' || $display == 'logo_title'): ?>
                <img src="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('company_logo'))); ?>" class="m-r-sm" onerror="setDefaultPic(this)">
                <?php elseif($display == 'icon' || $display == 'icon_title'): ?>
                <i class="fa <?php echo e(get_option('site_icon')); ?>"></i>
                <?php endif; ?>
                <?php if($display == 'logo_title' || $display == 'icon_title'): ?>
                <?php if(get_option('website_name') == ''): ?>
                <?php echo e(get_option('company_name')); ?>

                <?php else: ?>
                <?php echo e(get_option('website_name')); ?>

                <?php endif; ?>
                <?php endif; ?>
            </a>
            <a class="btn btn-link visible-xs" data-toggle="dropdown" data-target=".nav-user">
                <?php echo e(svg_image('solid/cog')); ?>
            </a>
        </div>

        <ul class="nav navbar-nav hidden-xs" id="todolist">
            <li class="">
                <div style="margin-top:8px;margin-left:0px;">
                    
                    <a class="btn btn-link" id="collapse-menu">
                        <?php echo e(svg_image('solid/bars')); ?>
                    </a>
                </div>
            </li>
        </ul>
        <ul class="nav navbar-nav hidden-xs navbar-center">
            <?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
            <li class="dropdown hidden-xs">
                <form action="<?php echo e(route('search.app')); ?>" method="POST" role="search">
                    <?php echo csrf_field(); ?>

                    <div class="form-group form-custom" style="margin-bottom:0;">
                        <div class="input-group w-400px" style="width:400px;padding: .8rem;">
                            <span class="input-group-btn icon-search">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control form-transparent" name="keyword" placeholder="Type tag keyword">
                            
                        </div>
                    </div>
                </form>
            </li>
            <?php endif; ?>
        </ul>

        <ul class="nav navbar-nav navbar-right hidden-xs nav-user">

            <?php if(count(runningTimers()) > 0): ?>
            <li class="">
                <a href="<?php echo e(route('timetracking.timers')); ?>" title="<?php echo trans('app.'.'timers'); ?>" data-toggle="ajaxModal" data-rel="tooltip" data-placement="bottom">  
                    <?php echo e(svg_image('solid/clock', 'fa-spin fa-lg text-warning')); ?>
                    <span class="badge badge-sm up bg-info m-l-n-sm display-inline"><?php echo e(count(runningTimers())); ?></span>
                </a>
            </li>
            <?php endif; ?>

            <?php echo $__env->make('partial.notifications', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            



            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <span class="thumb-sm avatar pull-left">
                        <img src="<?php echo e(avatar()); ?>" class="img-circle">
                    </span>
                    <?php echo e(Auth::user()->name); ?> <b class="caret"></b>
                </a>
                <ul class="dropdown-menu animated fadeInRight">
                    <li class="arrow top"></li>
                    <li><a href="<?php echo e(route('users.profile')); ?>"><?php echo trans('app.'.'settings'); ?></a></li>
                    <li><a href="<?php echo e(route('tell.friend')); ?>" data-toggle="ajaxModal"><?php echo trans('app.'.'tell_friend'); ?>  </a></li>
                    <li><a href="<?php echo e(route('users.reminders')); ?>"><?php echo trans('app.'.'reminders'); ?></a></li>
                    <li><a href="<?php echo e(route('users.notifications')); ?>"><?php echo trans('app.'.'notifications'); ?> </a></li>
                    <li><a href="<?php echo e(route('extras.user.templates')); ?>"><?php echo trans('app.'.'canned_responses'); ?></a></li>
                    <?php if (\Illuminate\Support\Facades\Blade::check('admin')): ?>
                    <li><a href="<?php echo e(route('support.ticket')); ?>" data-toggle="ajaxModal">Need Help?</a></li>
                    <?php endif; ?>
                    <li class="divider"></li>
                    <?php if(Auth::user()->isImpersonating()): ?>
                    <li><a href="<?php echo e(route('users.stopimpersonate')); ?>"><?php echo trans('app.'.'stop_impersonate'); ?></a></li>
                    <?php endif; ?>
                    <li>
                        <a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            <?php echo e(svg_image('solid/sign-out-alt')); ?> Logout
                        </a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="display-none">
                            <?php echo e(csrf_field()); ?>

                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>