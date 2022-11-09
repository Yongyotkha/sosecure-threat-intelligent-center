<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="mtsc.co.th">
    <meta name="keywords" content="">
    <meta property="og:title" name="title" content="@yield('title')">
    <meta property="og:description"  name="description" content="@yield('description')">
    <meta property="og:url" content="@yield('url_share')">
    <meta property="og:image" content="@yield('image')">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>inSight - @yield('metatitle')</title>
    <link href="//fonts.googleapis.com/css?family=Roboto:100,300,400,500,300i" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Mr+Dafoe" rel="stylesheet">
    <link rel="stylesheet" href="{{ getAsset('css/theme.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ getAsset('css/custom.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ getAsset('css/app.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ getAsset('storage/css/style.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ getAsset('css/sofia.css') }}" type="text/css"/>
    <link rel="stylesheet" href="{{ getAsset('plugins/cropperjs-master/dist/cropper.min.css') }}" type="text/css"/>
    
    <link rel="icon" type="image/png" href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}">
    <link rel="apple-touch-icon" href="{{ getStorageUrl(config('system.media_dir').'/'.get_option('site_favicon')) }}"/><!--get_option('site_appleicon') -->
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
        font-family: '{{ $family }}';
    }
    h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
        font-family: '{{ $family }}', 'Roboto';
    }
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
    @keyframes spin12 {
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
        z-index: 1000;
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
  </head>
  <body>
    <div class="overlay">
        <div id="nest6"></div>
        <h3 class="center_text">WAITING</h3>
    </div>
<section class="vbox" id="app">



    
        <section class="hbox stretch">


            @yield('content')




        </section>
</section>

<script src="{{ getAsset('js/app.js') }}"></script>
<script src="{{ getAsset('plugins/cropperjs-master/dist/cropper.min.js') }}"></script>

<script src="{{ getAsset('js/theme.js') }}"></script>
    @push('pagescript')
    @include('stacks.js.markdown')
    @include('partial.ajaxify')
    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function(){
        toastr.options.positionClass = '{{ config('toastr.options.positionClass') }}';
    });
    function setDefaultPic(source) {
        source.src = '{{asset("images/image-not-found.jpg")}}';
        source.onerror = "";
    }
    function loading(mode){
        if(mode == 'load'){
            $('.overlay').css('display', 'block');
            $('#nest6').css('display', 'block');
        }else if(mode == 'stop_load'){
            $('.overlay').css('display', 'none');
            $('#nest6').css('display', 'none');
        }
    }
    </script>
    {!! Toastr::message() !!}
    @endpush
    @stack('pagescript')

</body>
</html>
