<!DOCTYPE html>
<html class="no-js" lang="">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Flat - Bootstrap 5 Template</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Place favicon.ico in the root directory -->

    <!-- ========================= CSS here ========================= -->
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="assets/css/animate.css"/>
    <link rel="stylesheet" href="assets/css/lindy-uikit.css"/>
  </head>
  <style>
/* ================= HEADER ================= */
header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 10;
    padding: 0 10px;
}

.hamburger-btn {
    display: none;
    color: #fff;
    cursor: pointer;
    font-size: 1.5rem;
}

.logo {
    display: flex;
    gap: 10px;
    align-items: center;
    text-decoration: none;
}

.logo img {
    width: 40px;
    border-radius: 50%;
}

.logo h2 {
    color: #fff;
    font-weight: 600;
    font-size: 1.7rem;
}

.links {
    display: flex;
    gap: 35px;
    list-style: none;
    align-items: center;
}

.links a {
    color: #fff;
    font-size: 1.1rem;
    font-weight: 500;
    text-decoration: none;
    transition: 0.1s ease;
}

.links a:hover {
    color: #19e8ff;
}

.login-btn {
    border: none;
    outline: none;
    background: #fff;
    color: #275360;
    font-size: 1rem;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 3px;
    cursor: pointer;
    transition: 0.15s ease;
}

.login-btn:hover {
    background: #ddd;
}

.close-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    color: #878484;
    cursor: pointer;
    z-index: 3;
}

/* ================= POPUP ================= */
.form-popup {
    position: fixed;
    top: 50%;
    left: 50%;
    z-index: 100;
    width: 100%;
    max-width: 720px;
    opacity: 0;
    pointer-events: none;
    background: #fff;
    border: 2px solid #fff;
    transform: translate(-50%, -70%);
    transition: transform 0.3s ease, opacity 0.1s;
    display: flex;
}

.show-popup .form-popup {
    opacity: 1;
    pointer-events: auto;
    transform: translate(-50%, -50%);
}

/* ================= BLUR BACKGROUND ================= */
.blur-bg-overlay {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 99;
    width: 100%;
    height: 100%;
    opacity: 0;
    pointer-events: none;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    transition: 0.1s ease;
}

.show-popup .blur-bg-overlay {
    opacity: 1;
    pointer-events: auto;
}

/* ================= FORM BOX ================= */
.form-popup .form-box {
    display: flex;
    width: 100%;
}

/* ================= FORM DETAILS ================= */
.form-box .form-details {
    width: 100%;
    max-width: 330px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2; /* text overlay ke upar */
    color: #fff;
    padding: 0 40px;
    overflow: hidden;
}

/* ===== LOGIN BACKGROUND & OVERLAY ===== */
.login .form-details {
    background: url("https://static.vecteezy.com/system/resources/thumbnails/049/218/129/small/row-of-office-computers-displaying-blue-screen-photo.jpeg") center/cover no-repeat;
}

.login .form-details::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55); /* overlay darkness */
    z-index: 1; /* overlay ke neeche text */
}

/* ===== SIGNUP BACKGROUND & OVERLAY ===== */
.signup .form-details {
    background: url("https://static.vecteezy.com/system/resources/thumbnails/037/999/169/small_2x/ai-generated-workspace-technology-highlight-the-integration-of-technology-in-the-office-background-image-generative-ai-photo.jpg") center/cover no-repeat;
    padding: 0 20px;
}

.signup .form-details::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 1;
}

/* ================= FORM CONTENT ================= */
.form-box .form-content {
    width: 100%;
    padding: 35px;
    position: relative;
    z-index: 2; /* overlay ke upar */
}

.form-box h2 {
    text-align: center;
    margin-bottom: 29px;
}

/* ================= INPUT FIELDS ================= */
form .input-field {
    position: relative;
    height: 50px;
    width: 100%;
    margin-top: 20px;
}

.input-field input {
    width: 100%;
    height: 100%;
    background: none;
    outline: none;
    font-size: 0.95rem;
    padding: 0 15px;
    border: 1px solid #717171;
    border-radius: 3px;
}

.input-field input:focus {
    border: 1px solid #00bcd4;
}

.input-field label {
    position: absolute;
    top: 50%;
    left: 15px;
    transform: translateY(-50%);
    color: #4a4646;
    pointer-events: none;
    transition: 0.2s ease;
}

.input-field input:is(:focus, :valid) {
    padding: 16px 15px 0;
}

.input-field input:is(:focus, :valid) ~ label {
    transform: translateY(-120%);
    color: #00bcd4;
    font-size: 0.75rem;
}

/* ================= LINKS ================= */
.form-box a {
    color: #00bcd4;
    text-decoration: none;
}

.form-box a:hover {
    text-decoration: underline;
}

form :where(.forgot-pass-link, .policy-text) {
    display: inline-flex;
    margin-top: 13px;
    font-size: 0.95rem;
}

/* ================= BUTTONS ================= */
form button {
    width: 100%;
    color: #fff;
    border: none;
    outline: none;
    padding: 14px 0;
    font-size: 1rem;
    font-weight: 500;
    border-radius: 3px;
    cursor: pointer;
    margin: 25px 0;
    background: #00bcd4;
    transition: 0.2s ease;
}

form button:hover {
    background: #0097a7;
}

/* ================= BOTTOM LINK ================= */
.form-content .bottom-link {
    text-align: center;
}

/* ================= SHOW/HIDE LOGIN/SIGNUP ================= */
.form-popup .signup,
.form-popup.show-signup .login {
    display: none;
}

.form-popup.show-signup .signup {
    display: flex;
}

/* ================= SIGNUP POLICY ================= */
.signup .policy-text {
    display: flex;
    margin-top: 14px;
    align-items: center;
}

.signup .policy-text input {
    width: 14px;
    height: 14px;
    margin-right: 7px;
}


@media (max-width: 950px) {
    .navbar :is(.hamburger-btn, .close-btn) {
        display: block;
    }

    .navbar {
        padding: 15px 0;
    }

    .navbar .logo img {
        display: none;
    }

    .navbar .logo h2 {
        font-size: 1.4rem;
    }

    .navbar .links {
        position: fixed;
        top: 0;
        z-index: 10;
        left: -100%;
        display: block;
        height: 100vh;
        width: 100%;
        padding-top: 60px;
        text-align: center;
        background: #fff;
        transition: 0.2s ease;
    }

    .navbar .links.show-menu {
        left: 0;
    }

    .navbar .links a {
        display: inline-flex;
        margin: 20px 0;
        font-size: 1.2rem;
        color: #000;
    }

    .navbar .links a:hover {
        color: #00BCD4;
    }

    .login-btn {
        font-size: 0.9rem;
        padding: 7px 10px;
    }
}

@media (max-width: 760px) {
    .form-popup {
        width: 95%;
    }

    .form-box .form-details {
        display: none;
    }

    .form-box .form-content {
        padding: 30px 20px;
    }
}
  </style>
  <body>
    <!--[if lte IE 9]>
      <p class="browserupgrade">
        You are using an <strong>outdated</strong> browser. Please
        <a href="https://browsehappy.com/">upgrade your browser</a> to improve
        your experience and security.
      </p>
    <![endif]-->

    <!-- ========================= preloader start ========================= -->
    <div class="preloader">
      <div class="loader">
        <div class="spinner">
          <div class="spinner-container">
            <div class="spinner-rotator">
              <div class="spinner-left">
                <div class="spinner-circle"></div>
              </div>
              <div class="spinner-right">
                <div class="spinner-circle"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ========================= preloader end ========================= -->

    <!-- ========================= hero-section-wrapper-2 start ========================= -->
    <section id="home" class="hero-section-wrapper-2">

      <!-- ========================= header-2 start ========================= -->
      <header class="header header-2">
        <div class="navbar-area">
          <div class="container">
            <div class="row align-items-center">
              <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg">
                  <a class="navbar-brand" href="index.html">
                    <img src="assets/img/logo/logo.svg" alt="Logo" />
                  </a>
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent2" aria-controls="navbarSupportedContent2" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                    <span class="toggler-icon"></span>
                  </button>

                  <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent2">
                    <ul id="nav2" class="navbar-nav ml-auto">
                      <li class="nav-item">
                        <a class="page-scroll active" href="#home">Home</a>
                      </li>
                      <li class="nav-item">
                        <a class="page-scroll" href="#services">Services</a>
                      </li>
                      <li class="nav-item">
                        <a class="page-scroll" href="#about">About</a>
                      </li>
                      <li class="nav-item">
                        <a class="page-scroll" href="#pricing">Pricing</a>
                      </li>
                      <li class="nav-item">
                        <a class="page-scroll" href="#contact">Contact</a>
                      </li>
                    </ul>
        <button class="login-btn" onclick="window.location.href='Super admin dashboard/login.php'">
    LOG IN
</button>

                  </div>
                  <!-- navbar collapse -->
                   
                </nav>
                <!-- navbar -->
              </div>
            </div>
            <!-- row -->
          </div>
          <!-- container -->
        </div>
        <!-- navbar area -->
      </header>
      
      <!-- ========================= header-2 end ========================= -->

      <!-- ========================= hero-2 start ========================= -->
      <div class="hero-section hero-style-2">
        <div class="container">
          <div class="row align-items-end">
            <div class="col-lg-6">
              <div class="hero-content-wrapper">
                <h4 class="wow fadeInUp" data-wow-delay=".2s">You're Using</h4>
                <h2 class="mb-30 wow fadeInUp" data-wow-delay=".4s">Free Lite Version of Template</h2>
                <p class="mb-50 wow fadeInUp" data-wow-delay=".6s">Please, purchase full version of the template to get all sections, features and permission to remove footer credit</p>
                <div class="buttons">
                  <a href="https://rebrand.ly/flat-ud/" rel="nofollow" target="blank" class="button button-lg radius-10 wow fadeInUp" data-wow-delay=".7s">Purchase Now</a>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="hero-image">
                <img src="assets/img/hero/hero-2/hero-img.svg" alt="" class="wow fadeInRight" data-wow-delay=".2s">
                <img src="assets/img/hero/hero-2/paattern.svg" alt="" class="shape shape-1">
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- ========================= hero-2 end ========================= -->

    </section>
    <!-- ========================= hero-section-wrapper-2 end ========================= -->

    <!-- ========================= feature style-2 start ========================= -->
    <section id="services" class="feature-section feature-style-2">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div class="row">
              <div class="col-xl-7 col-lg-10 col-md-9">
                <div class="section-title mb-60">
                  <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">The future of designing starts here</h3>
                  <p class="wow fadeInUp" data-wow-delay=".4s">Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed!</p>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="single-feature wow fadeInUp" data-wow-delay=".2s">
                  <div class="icon">
                    <i class="lni lni-vector"></i>
                  </div>
                  <div class="content">
                    <h5 class="mb-25">Graphics Design</h5>
                    <p>Short description for the ones who look for something new.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="single-feature wow fadeInUp" data-wow-delay=".4s">
                  <div class="icon">
                    <i class="lni lni-layers"></i>
                  </div>
                  <div class="content">
                    <h5 class="mb-25">UI/UX Design</h5>
                    <p>Short description for the ones who look for something new.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="single-feature wow fadeInUp" data-wow-delay=".6s">
                  <div class="icon">
                    <i class="lni lni-layout"></i>
                  </div>
                  <div class="content">
                    <h5 class="mb-25">Web Design</h5>
                    <p>Short description for the ones who look for something new.</p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="single-feature wow fadeInUp" data-wow-delay=".8s">
                  <div class="icon">
                    <i class="lni lni-display"></i>
                  </div>
                  <div class="content">
                    <h5 class="mb-25">Web Development</h5>
                    <p>Short description for the ones who look for something new.</p>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
      <div class="feature-img wow fadeInLeft" data-wow-delay=".2s">
        <img src="assets/img/feature/feature-2-1.svg" alt="">
      </div>
    </section>
		<!-- ========================= feature style-2 end ========================= -->

    <!-- ========================= about style-3 start ========================= -->
    <section id="about" class="about-section about-style-3">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="about-image wow fadeInLeft" data-wow-delay=".2s">
              <img src="assets/img/about/about-3/about-img.jpg" alt="">
            </div>
          </div>
          <div class="col-lg-6">
            <div class="about-content-wrapper">
              <div class="section-title mb-40">
                <h3 class="mb-25 wow fadeInUp" data-wow-delay=".2s">The future of designing starts here</h3>
                <p class="wow fadeInUp" data-wow-delay=".4s">Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed, Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed,</p>
              </div>
              <div class="counter-up-wrapper mb-40 wow fadeInUp" data-wow-delay=".6s">
                <div class="single-counter">
                  <h4 class="countup" id="secondo1" cup-end="123" cup-append="M">123 M</h4>
                  <h6>Happy Client</h6>
                </div>
                <div class="single-counter">
                  <h4 class="countup" id="secondo2" cup-end="1434" cup-append="K">1434 K</h4>
                  <h6>Project Done</h6>
                </div>
                <div class="single-counter">
                  <h4 class="countup" id="secondo3" cup-end="134" cup-append="K">134 K</h4>
                  <h6>Award Win</h6>
                </div>
              </div>
              <a href="#0" class="button button-lg radius-3 wow fadeInUp" data-wow-delay=".7s">Learn More</a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= about style-3 end ========================= -->


              <div class="container pt-60 pb-60">
                <h4 class="wow fadeInUp" data-wow-delay=".2s">You're Using</h4>
                <h2 class="mb-30 wow fadeInUp" data-wow-delay=".4s">Free Lite Version of Template</h2>
                <p class="mb-50 wow fadeInUp" data-wow-delay=".6s">Please, purchase full version of the template to get all sections, features and permission to remove footer credit</p>
                <div class="buttons">
                  <a href="https://rebrand.ly/flat-ud/" rel="nofollow" target="blank" class="button button-lg radius-10 wow fadeInUp" data-wow-delay=".7s">Purchase Now</a>
                </div>
              </div>

		<!-- ========================= pricing style-1 start ========================= -->
		<section id="pricing" class="pricing-section pricing-style-1 bg-white">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xxl-5 col-xl-5 col-lg-7 col-md-10">
            <div class="section-title text-center mb-60">
              <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Pricing Plan</h3>
              <p class="wow fadeInUp" data-wow-delay=".4s">Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed!</p>
            </div>
          </div>
        </div>

        <div class="row justify-content-center">
          <div class="col-lg-4 col-md-8 col-sm-10">
            <div class="single-pricing wow fadeInUp" data-wow-delay=".2s">
              <div class="image">
                <img src="assets/img/pricing/pricing-1/pricing-1.svg" alt="">
              </div>
              <h6>Basic Design</h6>
              <h4>Web Design</h4>
              <h3>$ 29.00</h3>
              <ul>
                <li> <i class="lni lni-checkmark-circle"></i> Carefully crafted components</li>
                <li> <i class="lni lni-checkmark-circle"></i> Amazing page examples</li>
                <li> <i class="lni lni-checkmark-circle"></i> Super friendly support team</li>
                <li> <i class="lni lni-checkmark-circle"></i> Awesome Support</li>
              </ul>
              <a href="#0" class="button radius-30">Get Started</a>
            </div>
          </div>
          <div class="col-lg-4 col-md-8 col-sm-10">
            <div class="single-pricing active wow fadeInUp" data-wow-delay=".4s">
              <span class="button button-sm radius-30 popular-badge">Popular</span>
              <div class="image">
                <img src="assets/img/pricing/pricing-1/pricing-2.svg" alt="">
              </div>
              <h6>Standard Design</h6>
              <h4>Web Development</h4>
              <h3>$ 89.00</h3>
              <ul>
                <li> <i class="lni lni-checkmark-circle"></i> Carefully crafted components</li>
                <li> <i class="lni lni-checkmark-circle"></i> Amazing page examples</li>
                <li> <i class="lni lni-checkmark-circle"></i> Super friendly support team</li>
                <li> <i class="lni lni-checkmark-circle"></i> Awesome Support</li>
              </ul>
              <a href="#0" class="button radius-30">Get Started</a>
            </div>
          </div>
          <div class="col-lg-4 col-md-8 col-sm-10">
            <div class="single-pricing wow fadeInUp" data-wow-delay=".6s">
              <div class="image">
                <img src="assets/img/pricing/pricing-1/pricing-3.svg" alt="">
              </div>
              <h6>Pro Design</h6>
              <h4>Design & Develop</h4>
              <h3>$ 199.00</h3>
              <ul>
                <li> <i class="lni lni-checkmark-circle"></i> Carefully crafted components</li>
                <li> <i class="lni lni-checkmark-circle"></i> Amazing page examples</li>
                <li> <i class="lni lni-checkmark-circle"></i> Super friendly support team</li>
                <li> <i class="lni lni-checkmark-circle"></i> Awesome Support</li>
              </ul>
              <a href="#0" class="button radius-30">Get Started</a>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- ========================= pricing style-1 end ========================= -->

		<!-- ========================= team style-1 start ========================= -->
		<section id="team" class="team-section team-style-1">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-xxl-5 col-xl-5 col-lg-7 col-md-10">
            <div class="section-title text-center mb-60">
              <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Our Team</h3>
              <p class="wow fadeInUp" data-wow-delay=".4s">Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed!</p>
            </div>
          </div>
        </div>
        
        <div class="row justify-content-center">
          <div class="col-xl-3 col-md-6 col-sm-10">
            <div class="single-team wow fadeInUp" data-wow-delay=".2s">
              <div class="image">
                <img src="assets/img/team/team-1/team-1.png" alt="">
              </div>
              <div class="info">
                <h6>John Doe</h6>
                <p>Product Designer</p>
                <ul class="socials">
                  <li>
                    <a href="#0"> <i class="lni lni-facebook-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-twitter-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-instagram-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-linkedin-original"></i> </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 col-sm-10">
            <div class="single-team wow fadeInUp" data-wow-delay=".4s">
              <div class="image">
                <img src="assets/img/team/team-1/team-2.png" alt="">
              </div>
              <div class="info">
                <h6>David Endow</h6>
                <p>Creative Designer</p>
                <ul class="socials">
                  <li>
                    <a href="#0"> <i class="lni lni-facebook-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-twitter-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-instagram-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-linkedin-original"></i> </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 col-sm-10">
            <div class="single-team wow fadeInUp" data-wow-delay=".6s">
              <div class="image">
                <img src="assets/img/team/team-1/team-3.png" alt="">
              </div>
              <div class="info">
                <h6>Jonathon Smith</h6>
                <p>Brand Designer</p>
                <ul class="socials">
                  <li>
                    <a href="#0"> <i class="lni lni-facebook-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-twitter-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-instagram-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-linkedin-original"></i> </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 col-sm-10">
            <div class="single-team wow fadeInUp" data-wow-delay=".8s">
              <div class="image">
                <img src="assets/img/team/team-1/team-4.png" alt="">
              </div>
              <div class="info">
                <h6>Gray Simon</h6>
                <p>Frontend Developer</p>
                <ul class="socials">
                  <li>
                    <a href="#0"> <i class="lni lni-facebook-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-twitter-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-instagram-filled"></i> </a>
                  </li>
                  <li>
                    <a href="#0"> <i class="lni lni-linkedin-original"></i> </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- ========================= team style-1 end ========================= -->

              <div class="container pt-60 pb-60">
                <h4 class="wow fadeInUp" data-wow-delay=".2s">You're Using</h4>
                <h2 class="mb-30 wow fadeInUp" data-wow-delay=".4s">Free Lite Version of Template</h2>
                <p class="mb-50 wow fadeInUp" data-wow-delay=".6s">Please, purchase full version of the template to get all sections, features and permission to remove footer credit</p>
                <div class="buttons">
                  <a href="https://rebrand.ly/flat-ud/" rel="nofollow" target="blank" class="button button-lg radius-10 wow fadeInUp" data-wow-delay=".7s">Purchase Now</a>
                </div>
              </div>

    <!-- ========================= contact style-6 start ========================= -->
    <section id="contact" class="contact-section contact-style-6">
      <div class="container">
        <div class="row">
          <div class="col-lg-7">
            <div class="contact-form-wrapper">
              <form action="assets/php/contact.php" method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <div class="single-input">
                      <label for="name">Name</label>
                      <input type="text" id="name" name="name" class="form-input" placeholder="Name">
                      <i class="lni lni-user"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="single-input">
                      <label for="email">Email</label>
                      <input type="email" id="email" name="email" class="form-input" placeholder="Email">
                      <i class="lni lni-envelope"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="single-input">
                      <label for="number">Number</label>
                      <input type="text" id="number" name="number" class="form-input" placeholder="Number">
                      <i class="lni lni-phone"></i>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="single-input">
                      <label for="subject">Subject</label>
                      <input type="text" id="subject" name="subject" class="form-input" placeholder="Subject">
                      <i class="lni lni-text-format"></i>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="single-input">
                      <label for="message">Message</label>
                      <textarea name="message" id="message" class="form-input" placeholder="Message" rows="6"></textarea>
                      <i class="lni lni-comments-alt"></i>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-button">
                      <button type="submit" class="button radius-10">Submit <i class="lni lni-telegram-original"></i> </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>

          </div>

          <div class="col-lg-5 order-first order-lg-last">
            <div class="left-wrapper">
              <div class="section-title mb-40">
                <h3 class="mb-15">Get in touch</h3>
                <p>Stop wasting time and money designing and managing a website that doesn’t get results. Happiness guaranteed!</p>
              </div>
              <div class="row">
                <div class="col-lg-12 col-md-6">
                  <div class="single-item">
                    <div class="icon">
                      <i class="lni lni-phone"></i>
                    </div>
                    <div class="text">
                      <p>0045939863784</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 col-md-6">
                  <div class="single-item">
                    <div class="icon">
                      <i class="lni lni-envelope"></i>
                    </div>
                    <div class="text">
                      <p>yourmail@gmail.com</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 col-md-6">
                  <div class="single-item">
                    <div class="icon">
                      <i class="lni lni-map-marker"></i>
                    </div>
                    <div class="text">
                      <p>John's House, 13/5 Road, Sidny United State Of America</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= contact style-6 end ========================= -->

    <!-- ========================= clients-logo start ========================= -->
    <section class="clients-logo-section pt-100 pb-100">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="client-logo wow fadeInUp" data-wow-delay=".2s">
              <img src="assets/img/clients/brands.svg" alt="" class="w-100">
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========================= clients-logo end ========================= -->

		<!-- ========================= footer style-1 start ========================= -->
    <footer class="footer footer-style-1">
      <div class="container">
        <div class="widget-wrapper">
          <div class="row">
            <div class="col-xl-3 col-lg-4 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".2s">
                <div class="logo">
                  <a href="#0"> <img src="assets/img/logo/logo.svg" alt=""> </a>
                </div>
                <p class="desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Facilisis nulla placerat amet amet congue.</p>
                <ul class="socials">
                  <li> <a href="#0"> <i class="lni lni-facebook-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-twitter-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-instagram-filled"></i> </a> </li>
                  <li> <a href="#0"> <i class="lni lni-linkedin-original"></i> </a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-2 offset-xl-1 col-lg-2 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".3s">
                <h6>Quick Link</h6>
                <ul class="links">
                  <li> <a href="#0">Home</a> </li>
                  <li> <a href="#0">About</a> </li>
                  <li> <a href="#0">Service</a> </li>
                  <li> <a href="#0">Contact</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".4s">
                <h6>Services</h6>
                <ul class="links">
                  <li> <a href="#0">Web Design</a> </li>
                  <li> <a href="#0">Web Development</a> </li>
                  <li> <a href="#0">Seo Optimization</a> </li>
                  <li> <a href="#0">Blog Writing</a> </li>
                </ul>
              </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
              <div class="footer-widget wow fadeInUp" data-wow-delay=".5s">
                <h6>Help & Support</h6>
                <ul class="links">
                  <li> <a href="#0">Support Center</a> </li>
                  <li> <a href="#0">Live Chat</a> </li>
                  <li> <a href="#0">FAQ</a> </li>
                  <li> <a href="#0">Terms & Conditions</a> </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="copyright-wrapper wow fadeInUp" data-wow-delay=".2s">
          <p>Design and Developed by <a href="https://uideck.com" rel="nofollow" target="_blank">UIdeck</a> Built-with <a href="#">Lindy UI Kit</a>. Distibuted by <a href="https://themewagon.com" target="_blank">ThemeWagon</a></p>
        </div>
      </div>
    </footer>
    <!-- ========================= footer style-1 end ========================= -->

    <!-- ========================= scroll-top start ========================= -->
    <a href="#" class="scroll-top"> <i class="lni lni-chevron-up"></i> </a>
    <!-- ========================= scroll-top end ========================= -->
		

    <!-- ========================= JS here ========================= -->
    <script src="assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
    <script src="assets/js/count-up.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    const showPopupBtn = document.querySelector(".login-btn");
const formPopup = document.querySelector(".form-popup");
const hidePopupBtn = formPopup.querySelector(".close-btn");
const signupLoginLink = formPopup.querySelectorAll(".bottom-link a");

showPopupBtn.addEventListener("click", () => {
    document.body.classList.toggle("show-popup");
});

hidePopupBtn.addEventListener("click", () => {
    document.body.classList.remove("show-popup");
});

signupLoginLink.forEach(link => {
    link.addEventListener("click", (e) => {
        e.preventDefault();
        formPopup.classList[
          link.id === 'signup-link' ? 'add' : 'remove'
        ]("show-signup");
    });
});

    </script>
  </body>
</html>
