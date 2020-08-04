<?php $__env->startSection('content'); ?>
<section id="content" class="wrapper-md content">
    <div id="login-darken"></div>
    <div id="login-form" class="container aside-xxl animated fadeInUp">
        <span class="navbar-brand block">
            <?php $display = get_option('logo_or_icon'); ?>
            <?php if($display == 'logo' || $display == 'logo_title'): ?>
            <img src="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('company_logo'))); ?>"
            class="img-responsive <?php echo e(($display == 'logo' ? '' : 'thumb-sm m-r-sm')); ?>">
            <?php elseif($display == 'icon' || $display == 'icon_title'): ?>
            <i class="<?php echo e(get_option('site_icon')); ?>"></i>
            <?php endif; ?>
            <?php if($display == 'logo_title' || $display == 'icon_title'): ?>
            <?php if(get_option('website_name') == ''): ?>
            <?php echo e(get_option('company_name')); ?>

            <?php else: ?>
            <?php echo e(get_option('website_name')); ?>

            <?php endif; ?>
            <?php endif; ?>
        </span>
        <section class="panel panel-default bg-white m-t-sm b-r-xs">
            <header class="panel-heading text-center login-heading"><?php echo e(get_option('login_title')); ?></header>
            
            
            <?php echo Form::open(['route' => 'login', 'class' => 'panel-body wrapper-lg']); ?>

            <div class="form-group<?php echo e($errors->has('email') ? ' has-error' : ''); ?>">
                <label for="email"><?php echo trans('app.'.'email'); ?></label>
                
                <input id="email" type="email" class="form-control" name="email" value="<?php echo e(old('email')); ?>" required autofocus>
                <?php if($errors->has('email')): ?>
                <span class="help-block">
                    <strong><?php echo e($errors->first('email')); ?></strong>
                </span>
                <?php endif; ?>
                
            </div>
            <div class="form-group<?php echo e($errors->has('password') ? ' has-error' : ''); ?>">
                <label for="password"><?php echo trans('app.'.'password'); ?></label>
                <input id="password" type="password" class="form-control" name="password" required>
                <?php if($errors->has('password')): ?>
                <span class="help-block">
                    <strong><?php echo e($errors->first('password')); ?></strong>
                </span>
                <?php endif; ?>
                
            </div>
            <?php if(settingEnabled('use_recaptcha')): ?>
            <?php echo NoCaptcha::display(); ?>

            <?php if($errors->has('g-recaptcha-response')): ?>
            <span class="help-block text-danger">
                <strong><?php echo e($errors->first('g-recaptcha-response')); ?></strong>
            </span>
            <?php endif; ?>
            <?php endif; ?>
            
            <div class="form-group">
                
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?>> <?php echo trans('app.'.'remember_me'); ?>
                    </label>
                </div>
                
            </div>
            <div class="form-group">
                <?php echo renderButton(langapp('sign_in')); ?>

                
                <a class="btn btn-link pull-right m-t-xs" href="<?php echo e(route('password.request')); ?>">
                    <?php echo trans('app.'.'forgot_password'); ?>
                </a>
                
            </div>
            <?php if(settingEnabled('social_login')): ?>
                <div class="line line-dashed"></div>
                <p id="social-buttons">
                        <a href="<?php echo e(url('/redirect/twitter')); ?>" class="btn btn-sm btn-icon btn-<?php echo e(get_option('theme_color')); ?> m-xs" data-rel="tooltip" title="Login using Twitter"><?php echo e(svg_image('brands/twitter')); ?></a>
                        <a href="<?php echo e(url('/redirect/facebook')); ?>" class="btn btn-sm btn-icon btn-<?php echo e(get_option('theme_color')); ?> m-xs" data-rel="tooltip" title="Login using Facebook"><?php echo e(svg_image('brands/facebook')); ?></a>
                        <a href="<?php echo e(url('/redirect/google')); ?>" class="btn btn-sm btn-icon btn-<?php echo e(get_option('theme_color')); ?> m-xs" data-rel="tooltip" title="Login using Google"><?php echo e(svg_image('brands/google')); ?></a>
                        <a href="<?php echo e(url('/redirect/github')); ?>" class="btn btn-sm btn-icon btn-<?php echo e(get_option('theme_color')); ?> m-xs" data-rel="tooltip" title="Login using Github"><?php echo e(svg_image('brands/github')); ?></a>
                        <a href="<?php echo e(url('/redirect/linkedin')); ?>" class="btn btn-sm btn-icon btn-<?php echo e(get_option('theme_color')); ?> m-xs" data-rel="tooltip" title="Login using LinkedIn"><?php echo e(svg_image('brands/linkedin')); ?></a>
                        <a href="<?php echo e(url('/redirect/gitlab')); ?>" class="btn btn-sm btn-icon btn-<?php echo e(get_option('theme_color')); ?> m-xs" data-rel="tooltip" title="Login using Gitlab"><?php echo e(svg_image('brands/gitlab')); ?></a>
                      </p>
            <?php endif; ?>
            
            <div class="line line-dashed"></div>

            
            
            
            <?php echo Form::close(); ?>

            
            
            <?php if(!settingEnabled('hide_branding')): ?>
            <?php echo $__env->make('partial.branding', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
            <?php endif; ?>
            
        </section>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.auth', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>