@extends('websitesalepage::layouts.master')
@section('content')

<style>/* unvisited link */
    a:link {
      color: white;
    }
    
    /* visited link */
    a:visited {
      color: white;
    }
    
    /* mouse over link */
    a:hover {
      color: white;
    }
    
    /* selected link */
    a:active {
      color: white;
    }

    #btn_c_sale:hover {
        background: #007bff;
        color: #fff !important;
    }
    .con1 {
        border: 1px solid rgba(0, 0, 0, 0.5);
        font-weight: 300;
        border-radius: 20px;
        cursor: pointer;
        font-family: "Nunito";
        transition: background-color 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms, box-shadow 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms, border 250ms cubic-bezier(0.4, 0, 0.2, 1) 0ms;
        font-size: 1.7rem;
        color: #1976d2;
        padding: 1rem 4rem;
        margin-top: 1rem;
        /*text-transform: uppercase;*/
    }
</style>

@php 
    if(Auth::check()) {
        // if(Session::has('check_login_page')){
        //     Session::forget('check_login_page');
        //     $check_goto_menu = session('check_goto_menu');
        //     if($check_goto_menu) {
        //         header('Location: '.site_url($check_goto_menu));
        //         dd($check_goto_menu);
        //     }
        // } 

        if(isset($_COOKIE['check_login_page'])) {
            setcookie("check_login_page", "", time() - 3600);
            $check_goto_menu = session('check_goto_menu');
            header('Location: '.site_url($check_goto_menu));
            dd($check_goto_menu);
            
        } else {
            
        }


    }
@endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/websitesalepage">
                <img src="{{asset('images/logo_threat/logo.png')}}" height="45px" class="d-inline-block align-top" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse ml-auto" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('web.index_en')}}"><img class="d-inline-block" src="{{asset('asset_salepage/images/us.png')}}" width="20px;" alt=""> EN</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('web.index_th')}}"><img class="d-inline-block" src="{{asset('asset_salepage/images/thai.png')}}" width="20px;" alt=""> TH</a>
                    </li>
                    @if (Auth::check())
                        <li class="nav-item ml-lg-2 mb-lg-2 ml-0">
                            <a href="{{route('dashboardnew.index')}}"><button type="button" class="btn btn-outline-secondary px-4 mr-2">Dashboard</button></a>
                            <a href="{{route('logout')}}"><button type="button" class="btn btn-outline-secondary px-4">Logout</button></a>
                        </li>
                    @else
                        <li class="nav-item ml-lg-4 ml-0">
                            <a href="{{route('login')}}"><button type="button" class="btn btn-outline-secondary px-4">Login</button></a>
                        </li> 
                    @endif          
                </ul>
            </div>
        </div>
    </nav>

  <!--==========================
    Intro Section
  ============================-->
  <section id="intro" class="clearfix" style="background-image:url('{{asset('asset_salepage/images/bg.png')}}')">
    <div class="container">
      <div class="intro-info">
        <h2>Intelligence Detection</h2>
        <h3>
            Threat insight System is improved for helping organizations detect threats faster and also working with
            surveillance in the same time. We have data leak detection technology to protect data 
            of organizationson internet and dark web.
        </h3>
      </div>

    </div>
    <div class="move-down">
        <a href="#start-section"><i class="fa fa-angle-down text-white fa-2x"></i></a>
    </div>
  </section><!-- #intro -->

  <main id="main">
    <section id="start-section" class="content-section">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-7 col-md-12 mb-5 text-center">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/group98.png')}}" alt="Threat Sharing">
                </div>
                <div class="col-lg-5 col-md-12 mb-3">
                    <div class="mb-5">
                        <h1 class="primary-text text-lg-right text-center">
                            Threat Sharing
                        </h1>
                        <span class="secondary-text text-lg-right text-center">
                            Threat data connect and indicators from data providers gathering in threat sharing and connect with Log (SIEM) system as well.
                        </span>
                    </div>

                    <div>
                        <h1 class="primary-text text-lg-right text-center">
                            Type of IOC
                        </h1>
                        <ul class="list-img-ioc show-mb-row">
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/Ip.png')}}" alt="IP Address">
                                <p>IP Address</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/Domain.png')}}" alt="Domain">
                                <p>Domain</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/hash.png')}}" alt="Hash">
                                <p>Hash</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/Urls.png')}}" alt="URLs">
                                <p>URLs</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/iconOrther.png')}}" alt="URLs">
                                <p>Other</p>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </section>




    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-8 col-lg-8 order-lg-1 order-2 text-lg-left text-center">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Asset Discovery
                        </h1>
                        <span class="secondary-text">
                            To help you finding any interesting information and gathering server and asset which service on theinternet and collecting from OSINT. In the same way, we are able to import data for gap detection and threats continuously.
                        </span>
                    </div>

                    <ul class="list-img-ioc justify-content-start">
                        <li>
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/ad_Ip.png')}}" alt="IP Address">
                            <p>IP Address</p>
                        </li>
                        <li>
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/ad_subdomain.png')}}" alt="Sub-Domian">
                            <p>Sub-Domian</p>
                        </li>
                        <li>
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/ad_email.png')}}" alt="Email">
                            <p>Email</p>
                        </li>
                        <li>
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/ad_discovery.png')}}" alt="Discovery Tool">
                            <p>Discovery Tool</p>
                        </li>
                    </ul>

                </div>
                <div class="col-lg-4 col-lg-4 order-lg-2 order-1 text-center mb-lg-0 mb-5">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/c.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section" style="padding-bottom: 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="primary-text-blue text-center font-weight-bold">Data Leak Detection</h1>
                </div>
            </div>
        </div>

            <div class="container">
                <div class="row">
                    <div class="col-lg-4 order-lg-1 order-1">
                        <h1 class="primary-text-blue text-center mt-3 fz-70 font-weight-500">Surface Web</h1>
                    </div>

                <div class="col-lg-4 offset-lg-4 order-lg-3 order-2">
                    <span class="secondary-text text-blue text-lg-left text-center">
                            Data leak detection form dark web that bring data of organization for publishing and selling.
                        </span>
                        <ul class="list-img-ioc mt-3">
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/dd_web-site.png')}}" style="max-width: 75px" alt="Web Site">
                                <p>Web Site</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/dd_community.png')}}" style="max-width: 75px" alt="Communities">
                                <p>Communities</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/Group99.png')}}" style="max-width: 75px" alt="Social Network">
                                <p>Social Network</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        <div class="iceberg-section">           
            <div class="bg-blue-linear px-5">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4 order-lg-1 order-2 d-flex flex-column align-items-center justify-content-center">
                            <span class="secondary-text mb-3 text-white text-lg-left text-center">
                                We use indicators from insight data to detect threat from hacker stealing data or infecting Backdoor on server.
                            </span>
        
                            <ul class="list-img-ice text-center">
                                <li>
                                    <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/Credential.png')}}" alt="Credential">
                                    <p class="text-white">Credential</p>
                                </li>
                                <li>
                                    <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/Creditcard.png')}}" alt="Credit Card">
                                    <p class="text-white">Credit Card</p>
                                </li>
                                <li>
                                    <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/financeinfo.png')}}" alt="Finance Info">
                                    <p class="text-white">Finance Info</p>
                                </li>
                                <li>
                                    <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/confidential.png')}}" alt="Confidential Data">
                                    <p class="text-white">Confidential Data</p>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-lg-4 text-center order-lg-2 order-3 d-lg-block d-none translatY-50">
                            <img class="img-fluid" src="{{asset('asset_salepage/images/iceberg.png')}}" alt="">
                        </div>

                        <div class="order-lg-2 order-1 col-lg-4 d-flex align-items-center justify-content-lg-start justify-content-center">
                            <h1 class="fz-70 text-white font-weight-500">Dark Web</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section" style="padding-top: 50px; padding-bottom: 50 px;">
        <div class="container">
            <div class="row d-flex align-items-start">
                <div class="col-lg-6 mb-3 order-lg-1 order-2 text-lg-left text-center">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Compromised Detection
                        </h1>
                        <span class="secondary-text">
                            We use indicators from insight data to detect threat from hacker stealing data or infecting Backdoor on server.
                        </span>
                    </div>

                    <ul class="list-img-ioc justify-content-start">
                        <li>
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/cd_Threat Hunting.png')}}" alt="Threat Hunting">
                            <p>Threat Hunting</p>
                        </li>
                        <li>
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/cd_Compromise_0.png')}}" alt="Compromised">
                            <p>Compromised</p>
                        </li>
                        <li>
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/cd_backdoor.png')}}" alt="Backdoor">
                            <p>Backdoor</p>
                        </li>
                    </ul>

                </div>
                <div class="col-lg-6 mb-3 order-lg-2 order-1 text-center mb-lg-0">
                    <img class="img-fluid" style="max-width: 50%" src="{{asset('asset_salepage/images/icon/cd_Group.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-lg-6 mb-3 text-center order-lg-1 order-2">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/defaced.png')}}" alt="">
                </div>
                <div class="col-lg-6 col-lg-6 mb-3 order-lg-2 order-1 text-lg-left text-center">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Web Defaced Detection
                        </h1>
                        <span class="secondary-text">
                            To detect attack website from attacker by using web defacement technique and the organization still maintain credibility.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!--==========================
      Threat Hunting
    ============================-->
    <section id="threat_hunting" class="content-section">
        <div class="container">
  
          <div class="section-header">
              {{-- <h1  class="primary-text fontw-weight-bold text-center">Contact Sales</h1> --}}
              <h1 class="primary-text fontw-weight-bold text-center">Threat Hunting</h1>
          </div>
  
          <div class="row">
              <div class="col-lg-6">
                  <div class="" style="text-align: center;">
                    <img class="img-fluid" style="width: 480px;" src="{{asset('asset_salepage/images/Threat_Hunting_Flow.png')}}" alt="">
                  </div>
              </div>
  
              <div class="col-lg-6">
                  <div class="" style="text-align: center;">
                    <img class="img-fluid" style="width: 430px;" src="{{asset('asset_salepage/images/Threat_Hunting.png')}}" alt="">
                  </div>
                  <ul class="list-img-ioc" style="justify-content: center;">
                      <li style="width: 200px;"><img class="img-fluid" style="max-width: 100px; width: 70px;" src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="AgentBasedDetection"><p>Agent - Based Detection</p></li>
                      <li style="width: 200px;"><img class="img-fluid" style="max-width: 100px; width: 70px;" src="{{asset('asset_salepage/images/Compromised.png')}}" alt="Compromised"><p>Compromised Detection File System Registry</p></li>
                  </ul>
              </div>
          </div>
  
        </div>
    </section><!-- #Threat Hunting -->


    {{-- <section class="content-section py-4" style="background-color: #f2f2f2;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="primary-text text-center m-0">
                        Advanced Vulnerability Detection
                    </h1>
                </div>
            </div>
        </div>
    </section> --}}

    <section class="content-section bg-dark" style="padding: 80px 0 0 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="primary-text text-center text-white">
                        Advanced Vulnerability Detection
                    </h1>
                    <span class="secondary-text text-white mb-4">
                        Threat Insight System is able to detect the gap of system. This system is based on database of the Common Vulnerabilities and Exposures (CVE) system.
                    </span>

                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/Group97.png')}}" alt="">
                </div>
                <div class="col-lg-12 text-center" style="margin-top: 32px;">
                    <ul class="list-img-ioc" style="justify-content: center;">
                        <li style="width: 110px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/PassiveDetection.png')}}" alt="Passive Detection"><!--<p>Passive Detection</p>--></li>
                        <li style="width: 110px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/ActiveDetection.png')}}" alt="Active Detection"><!--<p>Active Detection</p>--></li>
                        {{-- <li style="width: 200px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/cicle.png')}}" alt="Mis-Configuration Detection"><!--<p>Mis-Configuration Detection</p>--></li>
                        <li style="width: 200px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/cicle.png')}}" alt="Hardening and Remediation"><!--<p>Hardening and Remediation</p>--></li> --}}
                    </ul>
                </div>
                <div class="col-lg-12 text-center" style="">
                    <ul class="list-img-ioc" style="justify-content: center;">
                        <li style="width: 110px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/MisConfigurationDetection.png')}}" alt="Mis-Configuration Detection"><!--<p>Mis-Configuration Detection</p>--></li>
                        <li style="width: 110px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/HardeningAndRemediation.png')}}" alt="Hardening and Remediation"><!--<p>Hardening and Remediation</p>--></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-lg-12 text-center">
                    <div class="mb-5">
                        <div class="col-lg-12 col-lg-12 mb-3 text-center order-lg-2 order-1" style="display: inline-block;">
                            <img class="img-fluid" style="max-width: 750px;" src="{{asset('asset_salepage/images/os_type.jpg')}}" alt="">
                            {{-- <h1 class="primary-text text-center"> Windows </h1> --}}
                        </div>
                        {{-- <div class="col-lg-6 col-lg-6 mb-3 text-center order-lg-2 order-1" style="display: inline-block;">
                            <img class="img-fluid" style="max-width: 200px;" src="{{asset('asset_salepage/images/icon/linux-logo.png')}}" alt="">
                            <h1 class="primary-text text-center"> Linux </h1>
                        </div> --}}
                        {{-- <span class="secondary-text">
                          Operating System  
                        </span> --}}
                    </div>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/wl_windows_linux.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-lg-12 text-center">
                    <div class="mb-5">
                        <h1 class="primary-text text-center">
                            Mis-Configuration Detection
                        </h1>
                        <span class="secondary-text">
                            The system can detect unsafe configuration based on Security Guideline from CIS center. Also, editing configuration for safety from the system.
                        </span>
                    </div>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/Icom.png')}}" alt="Mis-Configuration Detection">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-lg-6 mb-3 text-center order-lg-1 order-2">
                    <div class="mb-4 text-lg-left text-center">
                        <span class="secondary-text">
                            Detection user attack by using social engineering technique such as Phishing, Fake mobile application, Drive-By-Download.
                        </span>
                    </div>
                    <div>
                        <ul class="list-img-ioc justify-content-start">
                            <li>
                                <img class="img-fluid mw-100" src="{{asset('asset_salepage/images/icon/phising.png')}}" alt="Phishing">
                                <p>Phishing</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-100" src="{{asset('asset_salepage/images/icon/mobile.png')}}" alt="Mobile">
                                <p>Fake Mobile <br> Application</p>
                            </li>
                            <li>
                                <img class="img-fluid mw-100" src="{{asset('asset_salepage/images/icon/drive.png')}}" alt="Drive">
                                <p>Drive-By <br>Download</p>
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="col-lg-6 col-lg-6 mb-3 text-center order-lg-2 order-1">
                    <h1 class="primary-text text-center">
                        Social Engineering Detection
                    </h1>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/vec.png')}}" alt="">
                </div>
            </div>
            {{-- <div class="row">
                <div class="col-lg-12" style="text-align: center;">
                    <a id="btn_c_sale" href="#contact" class="con1" style="color: #333;">Contact Sales</a>
                </div>
            </div> --}}
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-lg-6 mb-3 text-center">
                    <h1 class="primary-text text-center">
                        Cybersecurity News Feed
                    </h1>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/cyber.png')}}" alt="">
                </div>
                <div class="col-lg-6 col-lg-6 mb-3 text-center">
                    <div class="mb-5">
                        <span class="secondary-text">
                            Update news about cybersecurity from others source around the world and getting notification via Email.
                        </span>
                    </div>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/Group106.png')}}" alt="Mis-Configuration Detection">
                    {{-- <ul class="cyber-news-feed">
                        <li>
                            <img class="img-fluid" src="{{asset('asset_salepage/images/posttoday-v1.png')}}" alt="">
                        </li>
                        <li>
                            <img class="img-fluid" src="{{asset('asset_salepage/images/Trend-Micro-Logo.svg.png')}}" alt="">
                        </li>
                        <li>
                            <img class="img-fluid" src="{{asset('asset_salepage/images/sanook.png')}}" alt="">
                        </li>
                        <li>
                            <img class="img-fluid" src="{{asset('asset_salepage/images/krebsan.png')}}" alt="">
                        </li>
                        <li>
                            <img class="img-fluid" src="{{asset('asset_salepage/images/thairath.png')}}" alt="">
                        </li>
                        <li>
                            <img class="img-fluid" src="{{asset('asset_salepage/images/kapook.png')}}" alt="">
                        </li>
                        <li>
                            <img class="img-fluid" src="{{asset('asset_salepage/images/prachachat-logo.png')}}" alt="">
                        </li>
                    </ul> --}}
                </div>
            </div>
        </div>
    </section>
   


    <!--==========================
      Contact Section
    ============================-->
    <section id="contact">
      <div class="container">

        <div class="section-header">
          {{-- <h1  class="primary-text fontw-weight-bold text-center">Contact Sales</h1> --}}
          <h1  class="primary-text fontw-weight-bold text-center">Contact Us</h1>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="contact-container">
                   <ul class="contact-list">
                       <li><p class="mb-3"><span class="icon-contact"><i class="fa fa-map-marker"></i></span> 410/ 34 Ratchadaphisek Rd, Khwaeng Samsen Nok, Khet Huai Khwang, Krung Thep Maha Nakhon 10310</p></li>
                       <li><p class="mb-3"><span class="icon-contact"><i class="fa fa-envelope"></i></span> Support@sosecure.co.th</p></li>
                       <li><p class="mb-3"><span class="icon-contact"><i class="fa fa-phone"></i></span> 061 564 5294</p></li>
                       <li><p class="mb-3"><span class="icon-contact"><svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="line" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-line fa-w-14 fa-2x" style="width: 22px;"><path fill="currentColor" d="M272.1 204.2v71.1c0 1.8-1.4 3.2-3.2 3.2h-11.4c-1.1 0-2.1-.6-2.6-1.3l-32.6-44v42.2c0 1.8-1.4 3.2-3.2 3.2h-11.4c-1.8 0-3.2-1.4-3.2-3.2v-71.1c0-1.8 1.4-3.2 3.2-3.2H219c1 0 2.1.5 2.6 1.4l32.6 44v-42.2c0-1.8 1.4-3.2 3.2-3.2h11.4c1.8-.1 3.3 1.4 3.3 3.1zm-82-3.2h-11.4c-1.8 0-3.2 1.4-3.2 3.2v71.1c0 1.8 1.4 3.2 3.2 3.2h11.4c1.8 0 3.2-1.4 3.2-3.2v-71.1c0-1.7-1.4-3.2-3.2-3.2zm-27.5 59.6h-31.1v-56.4c0-1.8-1.4-3.2-3.2-3.2h-11.4c-1.8 0-3.2 1.4-3.2 3.2v71.1c0 .9.3 1.6.9 2.2.6.5 1.3.9 2.2.9h45.7c1.8 0 3.2-1.4 3.2-3.2v-11.4c0-1.7-1.4-3.2-3.1-3.2zM332.1 201h-45.7c-1.7 0-3.2 1.4-3.2 3.2v71.1c0 1.7 1.4 3.2 3.2 3.2h45.7c1.8 0 3.2-1.4 3.2-3.2v-11.4c0-1.8-1.4-3.2-3.2-3.2H301v-12h31.1c1.8 0 3.2-1.4 3.2-3.2V234c0-1.8-1.4-3.2-3.2-3.2H301v-12h31.1c1.8 0 3.2-1.4 3.2-3.2v-11.4c-.1-1.7-1.5-3.2-3.2-3.2zM448 113.7V399c-.1 44.8-36.8 81.1-81.7 81H81c-44.8-.1-81.1-36.9-81-81.7V113c.1-44.8 36.9-81.1 81.7-81H367c44.8.1 81.1 36.8 81 81.7zm-61.6 122.6c0-73-73.2-132.4-163.1-132.4-89.9 0-163.1 59.4-163.1 132.4 0 65.4 58 120.2 136.4 130.6 19.1 4.1 16.9 11.1 12.6 36.8-.7 4.1-3.3 16.1 14.1 8.8 17.4-7.3 93.9-55.3 128.2-94.7 23.6-26 34.9-52.3 34.9-81.5z" class=""></path></svg></span> @sosecure</p></li>
                       <li style="margin-top: 5px;"><p class="mb-3"><span class="icon-contact"><i class="fa fa-facebook-square" style="font-size: 22px !important;" aria-hidden="true"></i></span> <a href="https://www.facebook.com/s0secure" target="_blank">www.facebook.com/s0secure</a></p></li>
                    </ul>

                   <div class="icon-email">
                        <img class="img-fluid" src="{{asset('asset_salepage/images/icon/Group25.png')}}" alt="" style="max-width: 130px">
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="map mb-4 mb-lg-0">
                    <iframe src="https://www.google.com/maps?q=410%2F%2034%20Ratchadaphisek%20Rd%2C%20Khwaeng%20Samsen%20Nok%2C%20Khet%20Huai%20Khwang%2C%20Krung%20Thep%20Maha%20Nakhon%2010310&z=14&t=&ie=UTF8&output=embed" frameborder="0" style="border:0; width: 100%; height: 293px;" allowfullscreen></iframe>
                </div>
            </div>
        </div>

      </div>
    </section><!-- #contact -->

  </main>

  <!--==========================
    Footer
  ============================-->
  <footer id="footer">
    <div class="container">
      <div class="copyright">
        &copy; Copyright <strong>SOSECURE Threat inSight</strong>. All Rights Reserved
      </div>
    </div>
  </footer>
@stop
