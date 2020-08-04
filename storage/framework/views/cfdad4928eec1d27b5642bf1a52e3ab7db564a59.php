<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(get_option('rtl') == 'TRUE' ? 'rtl' : 'ltr'); ?>" class="app">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title>Workice Installer</title>
        <link rel="icon" type="image/png" href="<?php echo e(asset('images/logo_favicon.png')); ?>">
        <link href="<?php echo e(getAsset('css/theme.css')); ?>" rel="stylesheet"/>
        <link href="<?php echo e(getAsset('css/login.css')); ?>" rel="stylesheet"/>
        <link href="<?php echo e(getAsset('plugins/fuelux/fuelux.min.css')); ?>" rel="stylesheet"/>
        <?php echo $__env->yieldContent('style'); ?>
        <script>
        window.Laravel = <?php echo json_encode(
            [
            'csrfToken' => csrf_token(),
            ]
        ); ?>
        </script>
    </head>
    <body>
        
        <?php echo $__env->yieldContent('content'); ?>
        <script src="<?php echo e(getAsset('js/app.js')); ?>"></script>
        <script src="<?php echo e(getAsset('js/theme.js')); ?>"></script>
        <?php echo $__env->yieldContent('scripts'); ?>
        
    </body>
</html>