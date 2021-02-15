@extends('websitesalepage::layouts.master')

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/websitesalepage">
                <img src="{{asset('images/logo_threat/logo.png')}}" height="45px" class="d-inline-block align-top" alt="">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse ml-auto" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><img class="d-inline-block" src="{{asset('asset_salepage/images/us.png')}}" width="20px;" alt=""> EN</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><img class="d-inline-block" src="{{asset('asset_salepage/images/thai.png')}}" width="20px;" alt=""> TH</a>
                    </li>
                    <li class="nav-item ml-4">
                        <button type="button" class="btn btn-outline-secondary px-4">Login</button>
                    </li>
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
        <h3>ระบบ Threat inSight ถูกพัฒนาขึ้นเพื่อช่วยให้องกรค์สามารถตรวจจับ 
            การโจมตีได้อย่างรวดเร็ว และยังสามารถทำงานร่วมกับการเฝ้าระวังขององค์กรได้ดี 
            และเรายังมีเทคโนโลยีที่ตรวจจับการรั่วไหลของข้อมูลขององค์กรบน Internet และ Dark Web
        </h3>
      </div>

    </div>
    <div class="move-down">
        <a href="#"><i class="fa fa-angle-down text-white fa-2x"></i></a>
    </div>
  </section><!-- #intro -->

  <main id="main">
    <section class="content-section">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-md-6">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/threatpic.png')}}" alt="">
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Threat Sharing
                        </h1>
                        <span class="secondary-text">
                            เชื่อมต่อข้อมูลภัยคุกคามและ Indicator ต่างๆ จาก ผู้ให้บริการข้อมูลต่างๆ มารวมอยู่ที่นี่ และเชื่อมต่อกับ ระบบวิเคราะห์ Log (SIEM) ได้เป็นอย่างดี
                        </span>
                    </div>

                    <div>
                        <h1 class="primary-text text-right">
                            รูปแบบของ IOC
                        </h1>
                        <ul class="list-img-ioc">
                            <li>
                                <img src="{{asset('asset_salepage/images/Group77.png')}}" alt="">
                                <p>IP Address</p>
                            </li>
                            <li>
                                <img src="{{asset('asset_salepage/images/Group78.png')}}" alt="">
                                <p>Domain</p>
                            </li>
                            <li>
                                <img src="{{asset('asset_salepage/images/Group79.png')}}" alt="">
                                <p>Hash</p>
                            </li>
                            <li>
                                <img src="{{asset('asset_salepage/images/Group88.png')}}" alt="">
                                <p>URLs</p>
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
                <div class="col-lg-8 col-md-8">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Asset Discovery
                        </h1>
                        <span class="secondary-text">
                            เราช่วยมองหาสิ่งที่สนใจ และรวบรวมเครื่อง Server และ Asset ต่างๆ ที่ให้บริการบน Internet โดยการรวบรวมจาก OSINT และสามารถนำเข้าข้อมูลเพื่อตรวจจับช่องโหว่ และภัยคุกคามต่างๆ ได้ต่อเนื่อง
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 text-center">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/AssetDiscovery.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="primary-text-blue text-center">Data Leak Detection</h1>
                </div>
            </div>
        </div>
        <div class="iceberg-section">
            <div class="container-fluid px-5">
                <div class="row">
                    <div class="col-md-4">
                        <h1 class="primary-text-blue text-center mt-3 fz-70 font-weight-500">Surface Web</h1>
                    </div>

                    <div class="col-md-4 text-center">
                        <img class="img-fluid" src="{{asset('asset_salepage/images/iceberg.png')}}" alt="">
                    </div>
                    <div class="col-md-4">
                        <span class="secondary-text">
                            ตรวจจับการรั่วไหลของข้อมูลที่ถูก Hackerนำไปเผยเแพร่บน Internet จากแหล่งข้อมูลต่างๆ
                        </span>
                        <ul class="list-img-ioc">
                            <li>
                                <img src="{{asset('asset_salepage/images/Group80.png')}}" alt="">
                                <p>Data Leak</p>
                            </li>
                            <li>
                                <img src="{{asset('asset_salepage/images/Group81.png')}}" alt="">
                                <p>Social Network</p>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <div class="bg-blue-linear">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <span class="secondary-text mb-3 text-white">
                                ตรวจจับการรั่วไหลของข้อมูลในตลาดมืด (Darkweb) ที่มีการนำข้อมูลในองค์กรมา เผยแพร่หรือจำหน่าย
                            </span>
        
                            <ul class="list-img-ioc text-center">
                                <li>
                                    <img src="{{asset('asset_salepage/images/Group84.png')}}" alt="">
                                    <p class="text-white">Credential</p>
                                </li>
                                <li>
                                    <img src="{{asset('asset_salepage/images/Group83.png')}}" alt="">
                                    <p class="text-white">Credit Card</p>
                                </li>
                                <li>
                                    <img src="{{asset('asset_salepage/images/Group82.png')}}" alt="">
                                    <p class="text-white">Finance Info</p>
                                </li>
                                <li>
                                    <img src="{{asset('asset_salepage/images/Group82.png')}}" alt="">
                                    <p class="text-white">Confidential Data</p>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4 offset-md-4 d-flex align-items-center">
                            <h1 class="fz-70 text-white font-weight-500">Dark Web</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-md-6">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Compromised Detection
                        </h1>
                        <span class="secondary-text">
                            ตรวจจับการถูกโจมตีจาก Hacker และการยึดเครื่องหรือฝัง Backdoor ในเครื่อง Server โดยการใช้ Indicator จากฐานข้อมูลเชิงลึก
                        </span>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 text-center">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/dash.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-md-6 text-center">
                    <img class="img-fluid" src="{{asset('asset_salepage/images/defaced.png')}}" alt="">
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="mb-5">
                        <h1 class="primary-text">
                            Web Defaced Detection
                        </h1>
                        <span class="secondary-text">
                            เฝ้าระวังการโจมตีด้วยเทคนิคการเปลี่ยนหน้าเว็บไซต์ (Web Defaced) เพื่อรักษาความน่าเชื่อถือขององค์กร
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="content-section py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="primary-text text-center m-0">
                        Advanced Vulnerability Detection
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-blue">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="primary-text text-center text-white">
                        Vulnerability Detection
                    </h1>
                    <span class="secondary-text text-white mb-4">
                        ระบบ Threat inSight สามารถช่วยตรวจจับช่องโหว่ของระบบโดยอ้างอิงจากฐานข้อมูลช่องโหว่ (CVE) ที่เป็นมาตรฐานสากล
                    </span>

                    <img class="img-fluid" src="{{asset('asset_salepage/images/smartmockups_kl0coiz8.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section bg-c-muted">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 text-center">
                    <div class="mb-5">
                        <h1 class="primary-text text-center">
                            Mis-Configuration Detection
                        </h1>
                        <span class="secondary-text">
                            ระบบสามารถตรวจจับ Configuration ที่ไม่ปลอดภัยโดยอ้างอิงตาม Security Guideline จากสถาบัน CIS และสามารถปรับแก้ไข Configuration ให้ปลอดภัยด้วยระบบ
                        </span>
                    </div>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/Icom.png')}}" alt="">
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-md-6 text-center">
                    <h1 class="primary-text text-center">
                        Cybersecurity News Feed
                    </h1>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/cyber.png')}}" alt="">
                </div>
                <div class="col-lg-6 col-md-6 text-center">
                    <div class="mb-5">
                        <span class="secondary-text">
                            อัพเดทข่าวสารด้วย Cybersecurity จากแหล่งข่าวต่างๆ ทั่วโลก และสามารถแจ้งข่าวสารผ่านทาง Email
                        </span>
                    </div>
                    <ul class="cyber-news-feed">
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
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-6 col-md-6 text-center">
                    <div class="mb-4">
                        <span class="secondary-text text-center">
                            ตรวจจับการโจมตีผู้ใช้งานด้วยเทคนิค Social Engineering เช่น
                        </span>
                    </div>
                    <div>
                        <ul class="list-img-ioc justify-content-center">
                            <li>
                                <img src="{{asset('asset_salepage/images/Group87.png')}}" alt="">
                                <p>Phishing</p>
                            </li>
                            <li>
                                <img src="{{asset('asset_salepage/images/Group86.png')}}" alt="">
                                <p>Fake Mobile <br> Application</p>
                            </li>
                            <li>
                                <img src="{{asset('asset_salepage/images/Group75.png')}}" alt="">
                                <p>Drive-By <br>Download</p>
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="col-lg-6 col-md-6 text-center">
                    <h1 class="primary-text text-center">
                        Social Engineering Detection
                    </h1>
                    <img class="img-fluid" src="{{asset('asset_salepage/images/vec.png')}}" alt="">
                </div>
          
            </div>
        </div>
    </section>
   


    <!--==========================
      Contact Section
    ============================-->
    <section id="contact">
      <div class="container-fluid">

        <div class="section-header">
          <h3>Contact Us</h3>
        </div>

        <div class="row wow fadeInUp">

          <div class="col-lg-6">
            <div class="map mb-4 mb-lg-0">
              <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d12097.433213460943!2d-74.0062269!3d40.7101282!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xb89d1fe6bc499443!2sDowntown+Conference+Center!5e0!3m2!1smk!2sbg!4v1539943755621" frameborder="0" style="border:0; width: 100%; height: 312px;" allowfullscreen></iframe>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="row">
              <div class="col-md-5 info">
                <i class="ion-ios-location-outline"></i>
                <p>A108 Adam Street, NY 535022</p>
              </div>
              <div class="col-md-4 info">
                <i class="ion-ios-email-outline"></i>
                <p>info@example.com</p>
              </div>
              <div class="col-md-3 info">
                <i class="ion-ios-telephone-outline"></i>
                <p>+1 5589 55488 55</p>
              </div>
            </div>

            <div class="form">
              <div id="sendmessage">Your message has been sent. Thank you!</div>
              <div id="errormessage"></div>
              <form action="" method="post" role="form" class="contactForm">
                <div class="form-row">
                  <div class="form-group col-lg-6">
                    <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" data-rule="minlen:4" data-msg="Please enter at least 4 chars" />
                    <div class="validation"></div>
                  </div>
                  <div class="form-group col-lg-6">
                    <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" data-rule="email" data-msg="Please enter a valid email" />
                    <div class="validation"></div>
                  </div>
                </div>
                <div class="form-group">
                  <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" data-rule="minlen:4" data-msg="Please enter at least 8 chars of subject" />
                  <div class="validation"></div>
                </div>
                <div class="form-group">
                  <textarea class="form-control" name="message" rows="5" data-rule="required" data-msg="Please write something for us" placeholder="Message"></textarea>
                  <div class="validation"></div>
                </div>
                <div class="text-center"><button type="submit" title="Send Message">Send Message</button></div>
              </form>
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
    <div class="footer-top">
      <div class="container">
        <div class="row">

          <div class="col-lg-4 col-md-6 footer-info">
            <h3>NewBiz</h3>
            <p>Cras fermentum odio eu feugiat lide par naso tierra. Justo eget nada terra videa magna derita valies darta donna mare fermentum iaculis eu non diam phasellus. Scelerisque felis imperdiet proin fermentum leo. Amet volutpat consequat mauris nunc congue.</p>
          </div>

          <div class="col-lg-2 col-md-6 footer-links">
            <h4>Useful Links</h4>
            <ul>
              <li><a href="#">Home</a></li>
              <li><a href="#">About us</a></li>
              <li><a href="#">Services</a></li>
              <li><a href="#">Terms of service</a></li>
              <li><a href="#">Privacy policy</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-contact">
            <h4>Contact Us</h4>
            <p>
              A108 Adam Street <br>
              New York, NY 535022<br>
              United States <br>
              <strong>Phone:</strong> +1 5589 55488 55<br>
              <strong>Email:</strong> info@example.com<br>
            </p>

            <div class="social-links">
              <a href="#" class="twitter"><i class="fa fa-twitter"></i></a>
              <a href="#" class="facebook"><i class="fa fa-facebook"></i></a>
              <a href="#" class="instagram"><i class="fa fa-instagram"></i></a>
              <a href="#" class="google-plus"><i class="fa fa-google-plus"></i></a>
              <a href="#" class="linkedin"><i class="fa fa-linkedin"></i></a>
            </div>

          </div>

          <div class="col-lg-3 col-md-6 footer-newsletter">
            <h4>Our Newsletter</h4>
            <p>Tamen quem nulla quae legam multos aute sint culpa legam noster magna veniam enim veniam illum dolore legam minim quorum culpa amet magna export quem marada parida nodela caramase seza.</p>
            <form action="" method="post">
              <input type="email" name="email"><input type="submit"  value="Subscribe">
            </form>
          </div>

        </div>
      </div>
    </div>

    <div class="container">
      <div class="copyright">
        &copy; Copyright <strong>NewBiz</strong>. All Rights Reserved
      </div>
      <div class="credits">
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>
  </footer>
  <!-- #footer -->
@stop
