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


/*---section os-----------------------*/
    .css-2u2ye4 {
        display: grid;
        grid-auto-flow: row;
        grid-auto-rows: 1fr;
        grid-gap: 5px;
        max-width: 1168px;
        text-align: left;
        margin: 64px auto 0 auto;
        -webkit-box-pack: justify;
        -webkit-justify-content: space-between;
        -ms-flex-pack: justify;
        justify-content: space-between;
        -webkit-animation: animation-o23fxl 0.2s ease-in forwards;
        animation: animation-o23fxl 0.2s ease-in forwards;
    }

    .css-4oj96i {
        background: #fff;
        border: 1px solid #e5e8ed;
        border-radius: 5px;
        padding: 24px;
        margin-bottom: 0 !important;
        cursor: pointer;
        box-shadow: 0 2px 4px rgb(3 27 78 / 6%) !important;
        -webkit-transition: all 0.5s ease;
        transition: all 0.5s ease;
    }

    .box {
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 2px 3px rgb(10 10 10 / 10%), 0 0 0 1px rgb(10 10 10 / 10%);
        color: #4d4f03;
        display: block;
        padding: 1.25rem;
    }
    .css-1iww2db {
        position: relative;
        height: 100%;
    }
    .css-2u2ye4 .box {
        padding: 30px !important;
    }
    .css-1iww2db {
        position: relative;
        height: 100%;
    }

    .css-1pq2esu.blur-up.lazyloaded {
        -webkit-filter: blur(0);
        -webkit-filter: blur(0);
        filter: blur(0);
    }
    
    .css-1pq2esu.blur-up.lazyloaded {
        -webkit-filter: blur(0);
        -webkit-filter: blur(0);
        filter: blur(0);
    }
    .css-1pq2esu.blur-up {
        /* -webkit-filter: blur(5px);
        -webkit-filter: blur(5px);
        filter: blur(5px); */
        -webkit-transition: filter 100ms,-webkit-filter 100ms;
        transition: filter 100ms,-webkit-filter 100ms;
    }
    .css-1iww2db img {
        max-width: 100%;
        margin-bottom: 10px;
    }
    .css-2u2ye4 img {
        max-height: 50px;
        min-height: 40px;
    }
    .css-2u2ye4 .box p {
        margin-bottom: 0;
    }
    .p-os {
        font-family: 'Prompt', sans-serif !important;
        /* font-family: Inter-Regular,"system-ui",-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Helvetica,Arial,"sans-serif"; */
        color: #5b6987;
        font-size: 16px;
        line-height: 160%;
        font-weight: 400;
        font-style: normal;
    }
    .zoom1:hover {
        -ms-transform: scale(1.1); /* IE 9 */
        -webkit-transform: scale(1.1); /* Safari 3-8 */
        transform: scale(1.1);
    }
    /*--end-section os-----------------------*/

    @media only screen and (max-width: 1000px) {
        #area_logo_os{
            /* overflow-y: auto; */
        }
    }

    @media only screen and (min-width: 600px) {
        .logo_v_dtech{
            /* overflow-y: auto; */
            width:150px !important;
        }
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

    <nav class="navbar navbar-expand-xl navbar-dark bg-dark fixed-top">
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
                        <li class="nav-item ml-xl-2 mb-xl-2 ml-0">
                            <a href="{{route('dashboardnew.index')}}"><button type="button" class="btn btn-outline-secondary px-4 mr-2">Dashboard</button></a>
                            <a href="{{route('logout')}}"><button type="button" class="btn btn-outline-secondary px-4">Logout</button></a>
                        </li>
                    @else
                        <li class="nav-item ml-xl-4 ml-0">
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
        <h3>ระบบ Threat inSight ถูกพัฒนาขึ้นเพื่อช่วยให้องค์กรสามารถตรวจจับ 
            การโจมตีได้อย่างรวดเร็ว และยังสามารถทำงานร่วมกับการเฝ้าระวังขององค์กรได้ดี 
            เรายังมีเทคโนโลยีที่ตรวจจับการรั่วไหลข้อมูลขององค์กรบน Internet และ Dark Web
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
                <div class="col-xl-7 col-lg-12 col-md-12 mb-5 text-center">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/t4.png')}}" alt="Threat Sharing">
                </div>
                <div class="col-xl-5 col-lg-12 col-md-12 mb-3">
                    <div class="mb-5">
                        <h1 class="primary-text text-xl-right text-center">
                            Threat Sharing
                        </h1>
                        <span class="secondary-text text-xl-right text-center">
                            เชื่อมต่อข้อมูลภัยคุกคามและ Indicator ต่างๆ จาก ผู้ให้บริการข้อมูลต่างๆ มารวมอยู่ที่นี่ และเชื่อมต่อกับ ระบบวิเคราะห์ Log (SIEM) ได้เป็นอย่างดี
                        </span>
                    </div>

                    <div>
                        <h1 class="primary-text text-xl-right text-center">
                            รูปแบบของ IOC
                        </h1>
                        <ul class="list-img-ioc show-mb-row justify-content-xl-end justify-content-center">
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
                <div class="col-xl-8 col-xl-8 order-xl-1 order-2 text-xl-left text-center">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Asset Discovery
                        </h1>
                        <span class="secondary-text">
                            เราช่วยมองหาสิ่งที่สนใจ และรวบรวมเครื่อง Server Asset ต่างๆ ที่ให้บริการบน Internet โดยการรวบรวมจาก OSINT และสามารถนำเข้าข้อมูลเพื่อตรวจจับช่องโหว่ ภัยคุกคามต่างๆ ได้ต่อเนื่อง
                        </span>
                    </div>

                    <ul class="list-img-ioc justify-content-xl-start justify-content-center">
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
                <div class="col-xl-4 col-xl-4 order-xl-2 order-1 text-center mb-xl-0 mb-5">
                    <img class="img-fluid" style="max-width: 70%" src="{{asset('asset_salepage/images/c.png')}}" alt="">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="https://line.me/ti/p/@sosecure" class="btn btn-dark px-5 py-3">ติดต่อสอบถาม</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section" style="padding-bottom: 0;">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <h1 class="primary-text-blue text-center font-weight-bold">Data Leak Detection</h1>
                    <div class="d-sm-none d-block text-center">
                        <img class="img-fluid" src="{{asset('asset_salepage/images/icebergline2.png')}}" alt="" style="max-width: 150px">
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-xl-4 order-xl-1 order-1">
                    <h1 class="primary-text-blue text-center mt-3 fz-70 font-weight-500">Surface Web</h1>
                </div>

                <div class="col-xl-4 offset-xl-4 order-xl-3 order-2">
                    <span class="secondary-text text-blue text-xl-left text-center d-block">
                        ตรวจจับการรั่วไหลของข้อมูลที่ถูก Hacker<br> นำไปเผยเแพร่บน Internet จากแหล่งข้อมูลต่างๆ
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
                        <div class="col-xl-4 order-xl-1 order-2 d-flex flex-column align-items-center justify-content-center">
                            <span class="secondary-text mb-3 text-white text-xl-left text-center">
                                ตรวจจับการรั่วไหลของข้อมูลในตลาดมืด (Darkweb) ที่มีการนำข้อมูลในองค์กรมา<br> เผยแพร่หรือจำหน่าย
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
                        
                        <div class="col-xl-4 text-center order-xl-2 order-3 d-xl-block d-none translatY-50">
                            <img class="img-fluid" src="{{asset('asset_salepage/images/iceberg.png')}}" alt="">
                        </div>

                        <div class="order-xl-2 order-1 col-xl-4 d-xl-flex align-items-center justify-content-xl-start justify-content-center">
                            <div>
                                <h1 class="fz-70 text-white font-weight-500 text-center">Dark Web</h1>
                            </div>

                            <div class="d-sm-none d-block text-center">
                                <img class="img-fluid" src="{{asset('asset_salepage/images/icebergline1.png')}}" alt="" style="max-width: 150px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <!--==========================
      Threat Hunting
    ============================-->
    <section id="threat_hunting" class="content-section bg-c-muted">
        <div class="container">
  
            <div class="col-xl-12 text-center">
                <h1 class="primary-text d-xl-none d-block"> Threat Hunting </h1>
            </div>
  
          <div class="row">
            <div class="col-xl-6">
                <div class="" style="text-align: center;">
                  <img class="img-fluid" style="max-width: 70%;" src="{{asset('asset_salepage/images/Threat_Hunting.png')}}" alt="">
                </div>
            </div>

            <div class="col-xl-6">
                <h1 class="primary-text d-xl-block d-none"> Threat Hunting </h1>
                <div>
                    <span class="secondary-text text-xl-left text-center">
                        ระบบตรวจจับภัยคุกคามและการโจมตีเชิงรุก (Threat Hunting) โดย 
                        สามารถตรวจจับการโจมตีทางไซเบอร์ต่างๆ จากฐานข้อมูล Indicator เชิงลึก
                        จาก Hacker และการโจมตีด้วย Malware
                    </span>
                </div>
                <ul class="list-img-ioc mt-4" style="justify-content: space-evenly;">
                    <li class="mw-md-25"><img class="img-fluid mw-70" src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="AgentBasedDetection">
                        <p>Persistent <br> Activities</p>
                    </li>
                        <li class="mw-md-25"><img class="img-fluid mw-70"  src="{{asset('asset_salepage/images/icon/cd_Threat Hunting.png')}}" alt="System Compromised">
                        <p>System Compromised<br> 
                            (MITRE ATT&CK)<br><br>
                            - File System <br>
                            - Executable <br>
                            - Registry 
                        </p>
                    </li>
                        <li class="mw-md-25"><img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/cd_Compromise_0.png')}}" alt="Malware Infected">
                        <p>Malware Infected <br> (Indicator)</p>
                    </li>
                </ul>
            </div>
          </div>
        </div>
    </section><!-- #Threat Hunting -->

    <section class="content-section" style="padding-top: 50px; padding-bottom: 50 px;">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-xl-12 text-center">
                    <h1 class="primary-text d-xl-none d-block">
                        Compromised Detection
                    </h1>
                </div>
                <div class="col-xl-6 mb-3 order-xl-1 order-2 text-xl-left text-center">
                    <div class="mb-5">
                        <h1 class="primary-text d-xl-block d-none">
                            Compromised Detection
                        </h1>
                        <span class="secondary-text">
                            ตรวจจับการถูกโจมตีจาก Hacker และการยึดเครื่องหรือฝัง Backdoor<br> ในเครื่อง Server โดยการใช้ Indicator จากฐานข้อมูลเชิงลึก
                        </span>
                    </div>

                    <ul class="list-img-ioc justify-content-xl-start justify-content-center">
                        <li class="mw-md-25">
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/cd_Threat Hunting.png')}}" alt="System Compromised">
                            <p>System <br>Compromised</p>
                        </li>
                        <li class="mw-md-25">
                            <img class="img-fluid mw-70" src="{{asset('asset_salepage/images/icon/cd_backdoor.png')}}" alt="Web Server Compromised">
                            <p>Web Server <br>Compromised</p>
                        </li>
                    </ul>

                </div>
                <div class="col-xl-6 mb-3 order-xl-2 order-1 text-center mb-xl-0">
                    <img class="img-fluid" style="max-width: 70%" src="{{asset('asset_salepage/images/icon/cd_Group.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
  
        <div class="col-xl-12 text-center">
            <h1 class="primary-text d-xl-none d-block">
                Automate Update Indicator
            </h1>
        </div>

          <div class="row align-items-center">
            <div class="col-xl-6">
                <div class="" style="text-align: center;">
                    <img class="img-fluid" style="max-width: 70%" src="{{asset('asset_salepage/images/Threat_Hunting_Flow.png')}}" alt="">
                </div>
            </div>

            <div class="col-xl-6">
                <h1 class="primary-text d-xl-block d-none">
                    Automate Update Indicator
                </h1>
                <div>
                    <span class="secondary-text text-xl-left text-center">
                        ระบบตรวจจับภัยคุกคามและการ โจมตีเชิงรุก (Threat Hunting) ด้วยฐานข้อมูล Indicator และตรวจจับอย่างรวดเร็ว และ Update ฐานข้อมูล แบบ Real Time
                    </span>
                </div>
                <ul class="list-img-ioc justify-content-xl-start justify-content-center mt-4">
                    <li class="mw-md-25"><img class="img-fluid mw-70" src="{{asset('asset_salepage/images/Compromised.png')}}" alt="Automate Update Indicator">
                        <p>Automate <br> Update <br> Indicator</p>
                    </li>
                    <li class="mw-md-25"><img class="img-fluid mw-70" src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="AgentBasedDetection">
                        <p>Agent-Based <br> Detection</p>
                    </li>
                </ul>
            </div>
          </div>
        </div>
    </section><!-- #Threat Hunting -->

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="col-xl-12 text-center"><h1 class="primary-text d-xl-none d-block"> Web Defaced Detection </h1></div>
            <div class="row d-flex align-items-center">
                <div class="col-xl-6 col-xl-6 mb-3 text-center order-xl-2 order-1">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/defaced.png')}}" alt="">
                </div>
                <div class="col-xl-6 col-xl-6 mb-3 order-xl-1 order-2 text-xl-left text-center">
                    <div class="mb-5">
                        <h1 class="primary-text d-xl-block d-none"> Web Defaced Detection </h1>
                        <span class="secondary-text">
                            เฝ้าระวังการโจมตีด้วยเทคนิคการเปลี่ยนหน้าเว็บไซต์ (Web Defaced)<br> เพื่อรักษาความน่าเชื่อถือขององค์กร
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="https://line.me/ti/p/@sosecure" class="btn btn-dark px-5 py-3">ติดต่อสอบถาม</a>
                </div>
            </div>

        </div>
    </section>



    {{-- <section class="content-section py-4" style="background-color: #f2f2f2;">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
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
                <div class="col-xl-12 text-center">
                    <h1 class="primary-text text-center text-white">
                        Advanced Vulnerability Detection
                    </h1>
                    <span class="secondary-text text-white mb-4">
                        ระบบ Threat inSight สามารถช่วยตรวจจับช่องโหว่ของระบบโดยอ้างอิงจากฐานข้อมูลช่องโหว่(CVE)ที่เป็นมาตรฐานสากล
                    </span>

                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/Group97.png')}}" alt="">
                </div>
                <div class="col-xl-12 text-center" style="margin-top: 32px;">
                    <ul class="list-img-ioc" style="justify-content: center;">
                        <li class="logo_v_dtech" style="color: #fff;"><img class="img-fluid" style="max-width: 90px; border-radius:50%;" src="{{asset('asset_salepage/images/icon/PassiveDetection.png')}}" alt="Passive Detection"><p style="margin-top: 10px;">Passive Detection</p></li>
                        <li class="logo_v_dtech" style="color: #fff;"><img class="img-fluid" style="max-width: 90px; border-radius:50%;" src="{{asset('asset_salepage/images/icon/ActiveDetection.png')}}" alt="Active Detection"><p style="margin-top: 10px;">Active Detection</p></li>
                        <li class="logo_v_dtech" style="color: #fff;"><img class="img-fluid" style="max-width: 90px; border-radius:50%;" src="{{asset('asset_salepage/images/icon/MisConfigurationDetection.png')}}" alt="Mis-Configuration Detection"><p style="margin-top: 10px;">Mis-Configuration Detection</p></li>
                        <li class="logo_v_dtech" style="color: #fff;"><img class="img-fluid" style="max-width: 90px; border-radius:50%;" src="{{asset('asset_salepage/images/icon/HardeningAndRemediation.png')}}" alt="Hardening and Remediation"><p style="margin-top: 10px;">Hardening and Remediation</p></li> 
                    </ul>
                </div>
                {{-- <div class="col-xl-12 text-center" style="">
                    <ul class="list-img-ioc" style="justify-content: center;">
                        <li style="width: 110px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/MisConfigurationDetection.png')}}" alt="Mis-Configuration Detection"><!--<p>Mis-Configuration Detection</p>--></li>
                        <li style="width: 110px; color: #fff;"><img class="img-fluid" style="max-width: 100px;" src="{{asset('asset_salepage/images/icon/HardeningAndRemediation.png')}}" alt="Hardening and Remediation"><!--<p>Hardening and Remediation</p>--></li>
                    </ul>
                </div> --}}
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-xl-12 text-center">
                    <div class="mb-5">
                        {{-- <div class="col-xl-12 col-xl-12 mb-3 text-center order-xl-2 order-1" style="display: inline-block;">
                            <img class="img-fluid" style="max-width:70%;" src="{{asset('asset_salepage/images/os_type.jpg')}}" alt="">
                            <h1 class="primary-text text-center"> Windows </h1>
                        </div> --}}

                        <div class="column">
                            <div class="css-nheav9">
                                <h3 class="h3 darkblue css-1w5mdtx title">Detection Support System</h3>
                                <p class="medium darkgrey css-o8e64g subtitle"></p>
                               <div data-testid="cards_container" id="area_logo_os" class="css-2u2ye4">
                                    <a class="box css-4oj96i zoom1" data-testid="card" url="#" href="javascript:void(0)">
                                        <div class="css-1iww2db">
                                        <div style="position: relative;"><img data-src="{{asset('asset_salepage/images/icon/microsoft-windows-23.svg')}}" src="{{asset('asset_salepage/images/icon/microsoft-windows-23.svg')}}" alt="card icon" class="null blur-up css-1pq2esu"></div>
                                        <p class="p-os">Windows</p>
                                        </div>
                                    </a>
                                    <a class="box css-4oj96i zoom1" data-testid="card" url="#" href="javascript:void(0)">
                                     <div class="css-1iww2db">
                                        <div style="position: relative;"><img data-src="{{asset('asset_salepage/images/icon/ubuntu.svg')}}" src="{{asset('asset_salepage/images/icon/ubuntu.svg')}}" alt="card icon" class="null blur-up css-1pq2esu"></div>
                                        <p class="p-os">Ubuntu</p>
                                     </div>
                                    </a>
                                    <a class="box css-4oj96i zoom1" data-testid="card" url="#" href="javascript:void(0)">
                                        <div class="css-1iww2db">
                                            <div style="position: relative;"><img data-src="{{asset('asset_salepage/images/icon/centOS.svg')}}" src="{{asset('asset_salepage/images/icon/centOS.svg')}}" alt="card icon" class="null blur-up css-1pq2esu"></div>
                                            <p class="p-os">CentOS</p>
                                        </div>
                                    </a>
                                    <a class="box css-4oj96i zoom1" data-testid="card" url="#" href="javascript:void(0)">
                                        <div class="css-1iww2db">
                                            <div style="position: relative;"><img data-src="{{asset('asset_salepage/images/icon/Debian.svg')}}" src="{{asset('asset_salepage/images/icon/Debian.svg')}}" alt="card icon" class="null blur-up css-1pq2esu"></div>
                                            <p class="p-os">Debian</p>
                                        </div>
                                    </a>
                                    <a class="box css-4oj96i zoom1" data-testid="card" url="#" href="javascript:void(0)">
                                        <div class="css-1iww2db">
                                            <div style="position: relative;"><img data-src="{{asset('asset_salepage/images/icon/Fedora.svg')}}" src="{{asset('asset_salepage/images/icon/Fedora.svg')}}" alt="card icon" class="null blur-up css-1pq2esu"></div>
                                            <p class="p-os">Fedora</p>
                                        </div>
                                    </a>
                                    <a class="box css-4oj96i zoom1" data-testid="card" url="#" href="javascript:void(0)">
                                        <div class="css-1iww2db">
                                            <div style="position: relative;"><img data-src="{{asset('asset_salepage/images/icon/orther11.svg')}}" src="{{asset('asset_salepage/images/icon/orther11.svg')}}" alt="card icon" class="null blur-up css-1pq2esu"></div>
                                            <p class="p-os">Other</p>
                                        </div>
                                    </a>
                               </div>
                            </div>
                         </div>

                        {{-- <div class="col-xl-6 col-xl-6 mb-3 text-center order-xl-2 order-1" style="display: inline-block;">
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

    <section class="content-section bg-c-muted" style="background-color: #ececec !important;">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-xl-12 text-center">
                    <div class="mb-5">
                        <h1 class="primary-text text-center">
                            Mis-Configuration Detection
                        </h1>
                        <span class="secondary-text">
                            ระบบสามารถตรวจจับ Configuration ที่ไม่ปลอดภัยโดยอ้างอิงตาม Security Guideline จากสถาบัน CIS<br> โดยสามารถปรับแก้ไข Configuration ให้ปลอดภัยด้วยระบบ
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
                <div class="col-xl-6 col-xl-6 mb-3 text-center order-xl-1 order-2">
                    <div class="mb-4 text-xl-left text-center">
                        <span class="secondary-text" style="padding-left: 50px;">
                            ตรวจจับการโจมตีผู้ใช้งานด้วยเทคนิค Social Engineering เช่น
                        </span>
                    </div>
                    <div>
                        <ul class="list-img-ioc justify-content-center">
                            <li>
                                <img class="img-fluid mw-100" src="{{asset('asset_salepage/images/icon/phising.png')}}" alt="Phishing">
                                <p>Phishing</p>
                            </li>
                            <li style="padding-left: 15px;">
                                <img class="img-fluid mw-100" src="{{asset('asset_salepage/images/icon/mobile.png')}}" alt="Mobile">
                                <p>Fake Mobile <br> Application</p>
                            </li>
                            <li style="padding-left: 15px;">
                                <img class="img-fluid mw-100" src="{{asset('asset_salepage/images/icon/drive.png')}}" alt="Drive">
                                <p>Drive-By <br>Download</p>
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="col-xl-6 col-xl-6 mb-3 text-center order-xl-2 order-1">
                    <h1 class="primary-text text-center">
                        Social Engineering Detection
                    </h1>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/vec.png')}}" alt="">
                </div>
            </div>
            {{-- <div class="row">
                <div class="col-xl-12" style="text-align: center;">
                    <a id="btn_c_sale" href="#contact" class="con1" style="color: #333;">Contact Sales</a>
                </div>
            </div> --}}
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-xl-6 col-xl-6 mb-3 text-center">
                    <h1 class="primary-text text-center">
                        Cybersecurity News Feed
                    </h1>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/icon/cyber.png')}}" alt="">
                </div>
                <div class="col-xl-6 col-xl-6 mb-3 text-center">
                    <div class="mb-5">
                        <span class="secondary-text">
                            อัพเดทข่าวสารด้วย Cybersecurity จากแหล่งข่าวทั่วโลก<br> โดยแจ้งเตือนข่าวสารผ่านช่องทาง Email
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

            <div class="row d-sm-block d-none">
                <div class="col-md-12 text-center">
                    <a href="https://line.me/ti/p/@sosecure" class="btn btn-dark px-5 py-3">ติดต่อสอบถาม</a>
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
            <h1 class="primary-text fontw-weight-bold text-center">Contact Us</h1>
        
            <div class="row d-sm-none d-block">
                <div class="col-md-12 text-center">
                    <a href="https://line.me/ti/p/@sosecure" class="btn btn-dark px-5 py-3">ติดต่อสอบถาม</a>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-xl-6">
                <div class="contact-container">
                   <ul class="contact-list">
                       <li><p class="mb-3"><span class="icon-contact"><i class="fa fa-map-marker"></i></span> บริษัท โซ ซีเคียว จำกัด เลขที่ 410/34 ซอย รัชดาภิเษก24 ถนน รัชดาภิเษก แขวงสามเสนนอก เขต ห้วยขวาง กรุงเทพฯ 10310</p></li>
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

            <div class="col-xl-6">
                <div class="map mb-4 mb-xl-0">
                    {{-- <iframe src="https://www.google.com/maps?q=410%2F%2034%20Ratchadaphisek%20Rd%2C%20Khwaeng%20Samsen%20Nok%2C%20Khet%20Huai%20Khwang%2C%20Krung%20Thep%20Maha%20Nakhon%2010310&z=14&t=&ie=UTF8&output=embed" frameborder="0" style="border:0; width: 100%; height: 293px;" allowfullscreen></iframe> --}}
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3874.6832906662935!2d100.57296581477854!3d13.79795599993984!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e29d6ebefb4825%3A0x3098ac660f56aea5!2sSOSecure!5e0!3m2!1sth!2ssg!4v1615884002549!5m2!1sth!2ssg" width="100%" height="293px" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
  
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-131014155-4"></script>
    <!--  Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-NZ0BXJ356B"></script>
    <script type="text/javascript">
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-131014155-4');


        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-NZ0BXJ356B');
</script>
@stop
