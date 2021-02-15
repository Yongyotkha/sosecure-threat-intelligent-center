<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Module SalePage</title>

       <!-- Google Fonts -->
       <link rel="preconnect" href="https://fonts.gstatic.com">
       <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;1,200;1,300;1,400;1,500;1,600&display=swap" rel="stylesheet">

        <!-- Bootstrap CSS File -->
        <link href="{{ asset('asset_salepage/lib/bootstrap/css/bootstrap.css')}}" rel="stylesheet">

        <!-- Libraries CSS Files -->
        <link href="{{ asset('asset_salepage/lib/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
        <link href="{{ asset('asset_salepage/lib/animate/animate.min.css')}}" rel="stylesheet">
        <link href="{{ asset('asset_salepage/lib/ionicons/css/ionicons.min.css')}}" rel="stylesheet">
        <link href="{{ asset('asset_salepage/lib/owlcarousel/assets/owl.carousel.min.css')}}" rel="stylesheet">
        <link href="{{ asset('asset_salepage/lib/lightbox/css/lightbox.min.css')}}" rel="stylesheet">

        <!-- Main Stylesheet File -->
        <link href="{{ asset('asset_salepage/css/style.css')}}" rel="stylesheet">

    </head>
    <body>
        @yield('content')

        <script src="{{ asset('asset_salepage//js/salepage.js') }}"></script>
        <!-- JavaScript Libraries -->
        <script src="{{ asset('asset_salepage/lib/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/jquery/jquery-migrate.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/easing/easing.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/mobile-nav/mobile-nav.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/wow/wow.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/waypoints/waypoints.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/counterup/counterup.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/owlcarousel/owl.carousel.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/isotope/isotope.pkgd.min.js') }}"></script>
        <script src="{{ asset('asset_salepage/lib/lightbox/js/lightbox.min.js') }}"></script>

        <!-- Template Main Javascript File -->
        <script src="{{ asset('asset_salepage/js/main.js') }}"></script>

    </body>
</html>
