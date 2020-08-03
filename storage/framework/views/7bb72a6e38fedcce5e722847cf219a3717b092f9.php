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
            <li data-target="#step2">Permissions</li>
            <li data-target="#step3" class="active">Configuration</li>
          </ul>
        </div>
        <div class="step-content clearfix">
          <?php if(session('message')['status']): ?>
          <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>
            ✘ Database connection/migration failed..
          </div>
          <?php endif; ?>
          
          <form method="post" action="<?php echo e(route('LaravelInstaller::environmentSaveWizard')); ?>" class="config-form tabs-wrap">
            <h4 class="text-muted">➤
            Environment
            </h4>
            
            <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
            <div class="form-group <?php echo e($errors->has('app_name') ? ' has-error ' : ''); ?>">
              <label for="app_name">
                App Name <span class="text-danger">*</span>
              </label>
              <input type="text" name="app_name" id="app_name" class="form-control" value="<?php echo e(old('app_name')); ?>" placeholder="Workice" required>
              <?php if($errors->has('app_name')): ?>
              <span class="text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('app_name')); ?>

              </span>
              <?php endif; ?>
            </div>
            
            
            
            <div class="form-group <?php echo e($errors->has('app_url') ? ' has-error ' : ''); ?>">
              <label for="app_url">
                App Url <span class="text-danger">*</span>
              </label>
              <input type="url" class="form-control" name="app_url" id="app_url" value="<?php echo e(request()->getSchemeAndHttpHost()); ?>" placeholder="https://portal.example.com" required>
              <?php if($errors->has('app_url')): ?>
              <span class="help-block text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('app_url')); ?>

              </span>
              <?php endif; ?>
            </div>
            
            
            
            <h4 class="text-muted">➤
            Database
            </h4>
            <div class="form-group <?php echo e($errors->has('database_connection') ? ' has-error ' : ''); ?>">
              <label for="database_connection">
               Database Connection <span class="text-danger">*</span>
              </label>
              <select name="database_connection" class="form-control" id="database_connection">
                <option value="mysql" selected>Mysql</option>
                <option value="sqlite">Sqlite</option>
                <option value="pgsql">Pgsql</option>
                <option value="sqlsrv">Sqlsrv</option>
              </select>
              <?php if($errors->has('database_connection')): ?>
              <span class="text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('database_connection')); ?>

              </span>
              <?php endif; ?>
            </div>
            <div class="form-group <?php echo e($errors->has('database_hostname') ? ' has-error ' : ''); ?>">
              <label for="database_hostname">
                Database host <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" name="database_hostname" id="database_hostname" value="<?php echo e(old('database_hostname','127.0.0.1')); ?>" placeholder="127.0.0.1" required>
              <?php if($errors->has('database_hostname')): ?>
              <span class="text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('database_hostname')); ?>

              </span>
              <?php endif; ?>
            </div>
            <div class="form-group <?php echo e($errors->has('database_port') ? ' has-error ' : ''); ?>">
              <label for="database_port">
                Database Port <span class="text-danger">*</span>
              </label>
              <input type="number" class="form-control" name="database_port" id="database_port" value="<?php echo e(old('database_port', '3306')); ?>" placeholder="3306" required>
              <?php if($errors->has('database_port')): ?>
              <span class="text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('database_port')); ?>

              </span>
              <?php endif; ?>
            </div>
            <div class="form-group <?php echo e($errors->has('database_name') ? ' has-error ' : ''); ?>">
              <label for="database_name">
                Database Name <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" name="database_name" id="database_name" value="<?php echo e(old('database_name')); ?>" placeholder="db_workice" required>
              <?php if($errors->has('database_name')): ?>
              <span class="text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('database_name')); ?>

              </span>
              <?php endif; ?>
            </div>
            <div class="form-group <?php echo e($errors->has('database_username') ? ' has-error ' : ''); ?>">
              <label for="database_username">
                Database Username <span class="text-danger">*</span>
              </label>
              <input type="text" class="form-control" name="database_username" id="database_username" value="<?php echo e(old('database_username')); ?>" placeholder="Database Username" required>
              <?php if($errors->has('database_username')): ?>
              <span class="text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('database_username')); ?>

              </span>
              <?php endif; ?>
            </div>
            <div class="form-group <?php echo e($errors->has('database_password') ? ' has-error ' : ''); ?>">
              <label for="database_password">
                Database Password <span class="text-danger">*</span>
              </label>
              <input type="password" class="form-control" name="database_password" id="database_password" value="<?php echo e(old('database_password')); ?>" placeholder="Database Password" required>
              <?php if($errors->has('database_password')): ?>
              <span class="text-danger error-block help-block">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                <?php echo e($errors->first('database_password')); ?>

              </span>
              <?php endif; ?>
            </div>
            
            
            
            <h4 class="text-muted">➤
            Account Details
            </h4>
            <div class="block">
              
              
              <div class="info">
                <div class="form-group <?php echo e($errors->has('user.name') ? ' has-error ' : ''); ?>">
                  <label for="fullname">Fullname <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="user[name]" value="<?php echo e(old('user.name')); ?>" placeholder="John Doe" required>
                  <?php if($errors->has('user.name')): ?>
                  <span class="text-danger error-block help-block">
                    <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    <?php echo e($errors->first('user.name')); ?>

                  </span>
                  <?php endif; ?>
                </div>
                <div class="form-group <?php echo e($errors->has('user.email') ? ' has-error ' : ''); ?>">
                  <label for="email">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" name="user[email]" value="<?php echo e(old('user.email')); ?>" placeholder="johndoe@example.com" required>
                  <?php if($errors->has('user.email')): ?>
                  <span class="text-danger error-block help-block">
                    <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    <?php echo e($errors->first('user.email')); ?>

                  </span>
                  <?php endif; ?>
                </div>
                <div class="form-group <?php echo e($errors->has('profile.job_title') ? ' has-error ' : ''); ?>">
                  <label for="job_title">Job Title</label>
                  <input type="text" class="form-control" name="profile[job_title]" value="<?php echo e(old('profile.job_title')); ?>" placeholder="Project Manager" required>
                  <?php if($errors->has('profile.job_title')): ?>
                  <span class="text-danger error-block help-block">
                    <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    <?php echo e($errors->first('profile.job_title')); ?>

                  </span>
                  <?php endif; ?>
                </div>
                <div class="form-group <?php echo e($errors->has('user.password') ? ' has-error ' : ''); ?>">
                  <label for="password">Admin Password</label>
                  <input type="password" class="form-control" name="user[password]" value="<?php echo e(old('user.password')); ?>" placeholder="Password" required>
                  <?php if($errors->has('user.password')): ?>
                  <span class="text-danger error-block help-block">
                    <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                    <?php echo e($errors->first('user.password')); ?>

                  </span>
                  <?php endif; ?>
                </div>
                
                
                
                
              </div>
            </div>
            
            
            
            <div class="buttons">
              <button class="btn btn-block btn-info formSaving" type="submit">
              Install
              </button>
              
            </div>
            
          </form>
          
          
          
          
        </div>
      </div>
      
      <?php if(!settingEnabled('hide_branding')): ?>
      <?php echo $__env->make('partial.branding', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
      <?php endif; ?>
      
    </section>
  </div>
</section>

<?php $__env->startSection('scripts'); ?>
  <script>
    $('.config-form').submit(function (event) {
        $(".formSaving").html('Installing..➤');
      });
  </script>
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('installer::layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>