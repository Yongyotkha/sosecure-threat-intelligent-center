<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Intelligence Detection</title>

        {{-- <link rel="icon" href="{{ asset('favicon.ico')}}"> --}}

        <?php $favicon = get_option('site_favicon');
        $ext = substr($favicon, -4); ?>
        @if ($ext == '.ico')
            <link rel="shortcut icon" href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}">
        @endif
        @if ($ext == '.png') 
            <link rel="icon" type="image/png" href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}">
        @endif
        @if ($ext == '.jpg' || $ext == 'jpeg') 
            <link rel="icon" type="image/jpeg" href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}">
        @endif
        @if (get_option('site_appleicon') != '')
            <link rel="apple-touch-icon" href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}"/><!--get_option('site_appleicon') -->
            <link rel="apple-touch-icon" sizes="72x72"
                  href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}"/>
            <link rel="apple-touch-icon" sizes="114x114"
                  href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}"/>
            <link rel="apple-touch-icon" sizes="144x144"
                  href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}"/>
        @endif

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600&display=swap" rel="stylesheet">

        <!-- Bootstrap CSS File -->
        <link href="{{ asset('asset_salepage/lib/bootstrap/css/bootstrap.min.css')}}?v=2" rel="stylesheet">
        <!-- Libraries CSS Files -->
        <link href="{{ asset('asset_salepage/lib/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet preload">
        <!-- Main Stylesheet File -->
        <link href="{{ asset('asset_salepage/css/style.css')}}" rel="stylesheet">
    </head>
    <body>
        @yield('content')

        <!-- JavaScript Libraries -->
        <script src="{{ asset('asset_salepage/lib/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/jquery/jquery-migrate.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <!-- Template Main Javascript File -->
        <script src="{{ asset('asset_salepage/js/main.js') }}"></script>

    </body>
</html>
