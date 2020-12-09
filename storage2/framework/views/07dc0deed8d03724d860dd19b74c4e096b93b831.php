<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(get_option('rtl') == 'TRUE' ? 'rtl' : 'ltr'); ?>" class="app">
<head>
    <script>
        function setDefaultPic(source) {
        source.src = '<?php echo e(asset("images/image-not-found.jpg")); ?>';
        source.onerror = "";
        }
    </script>

    <meta charset="utf-8"/>
    <meta name="author" content="<?php echo e(get_option('site_author')); ?>">
    <meta name="keywords" content="<?php echo e(get_option('site_keywords')); ?>">
    <meta name="description" content="<?php echo e(get_option('site_desc')); ?>">
    <meta property="og:url" content="<?php echo $__env->yieldContent('url_share'); ?>">
    <meta property="og:image" content="<?php echo $__env->yieldContent('image'); ?>">
    
    <?php $favicon = get_option('site_favicon');
    $ext = substr($favicon, -4); ?>
    <?php if($ext == '.ico'): ?>
        <link rel="shortcut icon" href="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon'))); ?>">
    <?php endif; ?>
    <?php if($ext == '.png'): ?> 
        <link rel="icon" type="image/png" href="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon'))); ?>">
    <?php endif; ?>
    <?php if($ext == '.jpg' || $ext == 'jpeg'): ?> 
        <link rel="icon" type="image/jpeg" href="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon'))); ?>">
    <?php endif; ?>
    <?php if(get_option('site_appleicon') != ''): ?>
        <link rel="apple-touch-icon" href="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('site_appleicon'))); ?>"/>
        <link rel="apple-touch-icon" sizes="72x72"
              href="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('site_appleicon'))); ?>"/>
        <link rel="apple-touch-icon" sizes="114x114"
              href="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('site_appleicon'))); ?>"/>
        <link rel="apple-touch-icon" sizes="144x144"
              href="<?php echo e(getStorageUrl(config('system.media_dir').'/'.get_option('site_appleicon'))); ?>"/>
    <?php endif; ?>

    <meta name="userId" content="<?php echo e(Auth::check() ? Auth::id() : ''); ?>">

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <title><?php echo e(count(Auth::user()->unreadNotifications) > 0 ? '('.count(Auth::user()->unreadNotifications).')' : ''); ?> <?php echo e(get_option('company_name')); ?> - <?php echo e($page); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <link rel="stylesheet" href="<?php echo e(getAsset('css/theme.css')); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php echo e(getAsset('css/custom.css')); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php echo e(getAsset('jquery-easy-loading/src/loading.css')); ?>" type="text/css"/>

    <link rel="stylesheet" href="<?php echo e(getAsset('plugins/apps/pace.css')); ?>" type="text/css"/>

    <?php if(config('system.material_design')): ?>
    <link rel="stylesheet" href="<?php echo e(getAsset('css/propeller.min.css')); ?>" type="text/css"/>
    <?php endif; ?>
    <?php if(isset($sign)): ?>
    <link href="//fonts.googleapis.com/css?family=Mr+Dafoe" rel="stylesheet">
    <?php endif; ?>
    <?php if(isset($help)): ?> 
    <link rel="stylesheet" href="<?php echo e(getAsset('plugins/intro/introjs.min.css')); ?>" type="text/css"/>
    <?php endif; ?>
    <?php if(isset($signature)): ?> 
        <link href="//fonts.googleapis.com/css?family=Dawning+of+a+New+Day" rel="stylesheet">
    <?php endif; ?>
    <?php if(config('system.drift_enabled')): ?>
        <?php echo $__env->make('partial.drift', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>
    <?php if(config('system.crisp_enabled')): ?>
        <?php echo $__env->make('partial.crisp', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>
    <?php if(config('system.enable_onesignal')): ?>
        <?php echo $__env->make('partial.onesignal', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>

    <?php if(config('system.enable_tawk')): ?>
        <?php echo $__env->make('partial.tawk', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>


    <?php echo $__env->yieldPushContent('pagestyle'); ?>

    <link rel="stylesheet" href="<?php echo e(getAsset('css/app.css')); ?>" type="text/css"/>

    <link rel="stylesheet" href="<?php echo e(getAsset('storage/css/style.css')); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php echo e(getAsset('css/sofia.css')); ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php echo e(getAsset('plugins/cropperjs-master/dist/cropper.min.css')); ?>" type="text/css"/>

    
    <?php
    $family = 'Sofia';
    $font = get_option('system_font');
    switch ($font) {
        case 'open_sans':
            $family = 'Open Sans';
            echo "<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,latin-ext,greek-ext,cyrillic-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'open_sans_condensed':
            $family = 'Open Sans Condensed';
            echo "<link href='//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700&subset=latin,greek-ext,cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'roboto':
            $family = 'Roboto';
            echo "<link href='//fonts.googleapis.com/css?family=Roboto:400,300,500,700&subset=latin,greek-ext,cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'roboto_condensed':
            $family = 'Roboto Condensed';
            echo "<link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700&subset=latin,greek-ext,cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'ubuntu':
            $family = 'Ubuntu';
            echo "<link href='//fonts.googleapis.com/css?family=Ubuntu:400,300,500,700&subset=latin,greek-ext,cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'lato':
            $family = 'Lato';
            echo "<link href='//fonts.googleapis.com/css?family=Lato:100,300,400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'oxygen':
            $family = 'Oxygen';
            echo "<link href='//fonts.googleapis.com/css?family=Oxygen:400,300,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'pt_sans':
            $family = 'PT Sans';
            echo "<link href='//fonts.googleapis.com/css?family=PT+Sans:400,700&subset=latin,cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'source_sans':
            $family = 'Source Sans Pro';
            echo "<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:400,700&subset=latin,cyrillic-ext,latin-ext' rel='stylesheet' type='text/css'>";
            break;
        case 'muli':
            $family = 'Muli';
            echo "<link href='//fonts.googleapis.com/css?family=Muli' rel='stylesheet'>";
            break;
        case 'miriam':
            $family = 'Miriam Libre';
            echo "<link href='//fonts.googleapis.com/css?family=Miriam+Libre' rel='stylesheet'>";
            break;
        case 'poppins':
            $family = 'Poppins';
            echo "<link href='//fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap' rel='stylesheet'>";
            break;
    }
    ?>


    <style type="text/css">
        body {
            font-family: '<?php echo e($family); ?>';
        }
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
            font-family: '<?php echo e($family); ?>', sans-serif;
        }
        .inv-bg { background-color: <?php echo e(get_option('invoice_color')); ?>; }
        .est-bg { background-color: <?php echo e(get_option('estimate_color')); ?>; color: #fff; }
        #nest6 {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        height: 50px;
        width: 50px;
        margin: -25px 0 0 -25px;
        border: 2px solid transparent;
        border-top-color: #0b96c5;
        border-radius: 50%;
        -webkit-animation: spin12 2s linear infinite;
                animation: spin12 2s linear infinite;
        z-index: 1000;
    }

    #nest6:before {
        content: "";
        position: absolute;
        top: 7px;
        right: 7px;
        bottom: 7px;
        left: 7px;
        border: 2px solid transparent;
        border-radius: 50%;
        border-top-color: #2b629c;
        -webkit-animation: spin12 3s linear infinite;
                animation: spin12 3s linear infinite;
    }

    #nest6:after {
        content: "";
        position: absolute;
        top: 15px;
        right: 15px;
        bottom: 15px;
        left: 15px;
        border: 2px solid transparent;
        border-radius: 50%;
        background: #33b4d7;
        border-top-color: #b6d7e0;
        -webkit-animation: spin12 1.5s linear infinite;
                animation: spin12 1.5s linear infinite;
    }

    @-webkit-keyframes spin12 {
        from {
            -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(359deg);
                    transform: rotate(359deg);
        }
    }
    @keyframes  spin12 {
        from {
            -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
            -webkit-transform: rotate(0deg);
                    transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(359deg);
                    transform: rotate(359deg);
            -webkit-transform: rotate(359deg);
                    transform: rotate(359deg);
        }
    }
    .overlay {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        background-color: rgba(0,0,0,0.4);
        backdrop-filter: blur(1.5px);
        z-index: 10000;
    }
    .center_text{
        position: absolute;
        top: 50%;
        left: 50%;
        margin: 30px 0 0 -40px;
        font-weight: 900;
        color: white;
    }
    </style>


    <!--[if lt IE 9]>
    <script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/r29/html5.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="overlay">
        <div id="nest6"></div>
        <h3 class="center_text">WAITING</h3>
    </div>
    <?php echo $__env->make('cookie_consent', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    
<section class="vbox" id="app">


    <?php echo $__env->make('partial.top_header', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

    <section class="">
        <section class="hbox stretch">




            <?php echo $__env->make('partial.main_menu', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>


            <?php echo $__env->yieldContent('content'); ?>


            <?php echo $__env->make('partial.notifier', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>


        </section>
    </section>
</section>

<script src="<?php echo e(getAsset('js/app.js')); ?>"></script>

<script src="<?php echo e(getAsset('js/theme.js')); ?>"></script>

<script src="<?php echo e(getAsset('jquery-easy-loading/dist/jquery.loading.js')); ?>" type="text/javascript"></script>


<script>
    var locale = '<?php echo trans('app.'.'lang_code'); ?> ';
    var base_url = '<?php echo e(url('/')); ?>';

      axios.defaults.headers.common['Content-Language'] = '<?php echo e(app()->getLocale()); ?>';
    function loading(mode){
        if(mode == 'load'){
            $('.overlay').css('display', 'block');
            $('#nest6').css('display', 'block');
        }else if(mode == 'stop_load'){
            $('.overlay').css('display', 'none');
            $('#nest6').css('display', 'none');
        }
    }

    
        function f_loading(type, control_id){
            if(type == 1){
                $('body').loading({
                    message: f_loading_style(0)
                });
            }else if(type == 2){
                $('body').loading({
                    message: f_loading_style(2)
                });
            }else if(type == 9){
                $('body').loading({
                    message: f_loading_style(9),
                    theme: "dark"
                });
            }else if(type == 10){
                $('body').loading({
                    message: f_loading_style(10),
                    theme: "dark"
                });
            }else{
                $(control_id).loading({
                    message: f_loading_style(1)
                });
            }
        }

        function f_loading_stop(type, control_id){
            if(type == 1){
                $('body').loading('stop');
            }else if(type == 2){
                $('body').loading('stop');
            }else if(type == 9){
                $('body').loading('stop');
            }else if(type == 10){
                $('body').loading('stop');
            }else{
                $(control_id).loading('stop');
            }
        }

        function f_loading_style(mode){
            var msg = '<div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>';
            if(mode == 1){
                msg =  '<div class="spinner-1"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>';
            }else if(mode == 2){
                msg =  '<div class="spinner-1"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>';
            }else if(mode == 9){
                msg =  '<div class="spinnerClock"><div class="spinnerClock__clock"></div><div class="spinnerClock__minHand"></div> <div class="spinnerClock__secHand"></div></div><h3 style="letter-spacing: 1px;">กรุณารอสักครู่...</h3>';
            }else if(mode == 10){
                msg =  '<div class="spinnerClock"><div class="spinnerClock__clock"></div><div class="spinnerClock__minHand"></div> <div class="spinnerClock__secHand"></div></div><h3 style="letter-spacing: 1px;">ระบบกำลัง Duplicate Course กรุณารอสักครู่...</h3>';
            }else if(mode == 0){
                msg = '<div class="spinner"><div class="double-bounce1"></div><div class="double-bounce2"></div></div>';
            }else{

            }
            return msg;
        }
    


</script>

<?php if(config('system.pusher_enabled')): ?>
    <?php echo $__env->make('partial.pusher', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php endif; ?>


<?php if(config('system.material_design')): ?>
<script type="text/javascript" src="<?php echo e(getAsset('js/propeller.min.js')); ?>"></script>
<?php endif; ?>


<script src="<?php echo e(getAsset('js/plugins.js')); ?>"></script>
<?php if(isset($help)): ?> 
    <script src="<?php echo e(getAsset('plugins/intro/intro.min.js')); ?>"></script>
    <script src="<?php echo e(getAsset('plugins/intro/demo.js')); ?>"></script>
<?php endif; ?>
<?php echo Toastr::message(); ?>


<?php echo $__env->yieldPushContent('pagescript'); ?>
<?php echo $__env->make('partial.ajaxify', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<script>
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
$(document).ready(function(){
    $( ".comment-item table" ).addClass( "table table-striped" );

    $('.money').maskMoney({allowZero: true, thousands: '', allowNegative: true});

    if($('.nav-w-children li').hasClass('active')){
        var el = $('.nav-w-children').attr('id');
        $('#'+el).addClass( "active" );
    }
    
    toastr.options.positionClass = '<?php echo e(config('toastr.options.positionClass')); ?>';
    $.fn.modal.prototype.constructor.Constructor.DEFAULTS.backdrop = 'static';
    $.fn.modal.prototype.constructor.Constructor.DEFAULTS.keyboard =  false;
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    
    $('.clickable tr').click(function () {
            var href = $(this).find("a").attr("href");
            if (href) {
                window.location = href;
            }
    });
    $('#clear-alerts').click(function () {
        axios.get('<?php echo e(route('users.notifications.clear')); ?>').then(function (response) {
            toastr.success('Notifications cleared successfully', '<?php echo trans('app.'.'response_status'); ?> ');
          })
          .catch(function (error) {
            toastr.error('Error clearing notifications', '<?php echo trans('app.'.'response_status'); ?> ');
        });
    }); 

    $('#collapse-menu').click(function(){
        $('#nav').toggle();
    });
});

</script>




</body>
</html>
