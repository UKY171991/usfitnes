<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Include init for logo functions
require_once 'includes/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro - Laboratory Management System</title>
  <meta name="description" content="PathLab Pro - Advanced Laboratory Management System for modern healthcare facilities. Streamline operations, manage patients, and generate reports efficiently.">
  <meta name="keywords" content="laboratory management, pathlab, medical lab, healthcare, laboratory software, lab automation">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/custom.css">
  
  <style>
    :root {
      --primary-color: #2c5aa0;
      --primary-dark: #1e3c72;
      --primary-light: #4b6cb7;
      --secondary-color: #6c757d;
      --success-color: #28a745;
      --info-color: #17a2b8;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
    }

    body {
      font-family: 'Source Sans Pro', sans-serif;
      background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    }

    /* Hero Section */
    .hero-section {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 100px 0;
      position: relative;
      overflow: hidden;
    }

    .hero-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>') no-repeat;
      background-size: cover;
    }

    .hero-content {
      position: relative;
      z-index: 2;
    }

    .hero-title {
      font-size: 3.5rem;
      font-weight: 300;
      margin-bottom: 1rem;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
      font-size: 1.4rem;
      margin-bottom: 2rem;
      opacity: 0.9;
    }

    .hero-logo {
      max-width: 120px;
      height: auto;
      margin-bottom: 2rem;
      border-radius: 50%;
      box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }

    /* Features Section */
    .features-section {
      padding: 80px 0;
      background: white;
    }

    .feature-card {
      text-align: center;
      padding: 2rem;
      border-radius: 15px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      height: 100%;
      background: white;
      border: 1px solid #e3f2fd;
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .feature-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem auto;
      color: white;
      font-size: 2rem;
    }

    .feature-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: var(--primary-dark);
    }

    .feature-description {
      color: #666;
      line-height: 1.6;
    }

    /* Stats Section */
    .stats-section {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 60px 0;
    }

    .stat-card {
      text-align: center;
      padding: 1.5rem;
    }

    .stat-number {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stat-label {
      font-size: 1.1rem;
      opacity: 0.9;
    }

    /* CTA Section */
    .cta-section {
      padding: 80px 0;
      background: #f8f9fa;
      text-align: center;
    }

    .btn-primary-custom {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      border: none;
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      border-radius: 50px;
      color: white;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
    }

    .btn-primary-custom:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(44, 90, 160, 0.4);
      color: white;
      text-decoration: none;
    }

    .btn-outline-custom {
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      border-radius: 50px;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
      margin-left: 1rem;
    }

    .btn-outline-custom:hover {
      background: var(--primary-color);
      color: white;
      text-decoration: none;
    }

    /* Header */
    .navbar-custom {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0,0,0,0.1);
      padding: 1rem 0;
    }

    .navbar-brand {
      font-weight: 600;
      font-size: 1.5rem;
      color: var(--primary-dark) !important;
    }

    .navbar-nav .nav-link {
      color: var(--primary-dark) !important;
      font-weight: 500;
      margin: 0 0.5rem;
      transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
      color: var(--primary-color) !important;
    }

    /* Footer */
    .footer {
      background: var(--primary-dark);
      color: white;
      padding: 40px 0 20px 0;
    }

    .footer h5 {
      color: white;
      margin-bottom: 1rem;
    }

    .footer a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer a:hover {
      color: white;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.5rem;
      }
      
      .hero-subtitle {
        font-size: 1.2rem;
      }
      
      .btn-outline-custom {
        margin-left: 0;
        margin-top: 1rem;
        display: block;
        width: fit-content;
        margin-left: auto;
        margin-right: auto;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light fixed-top navbar-custom">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#home">
        <?php if (hasLogo()): ?>
          <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" height="40" class="me-2">
        <?php endif; ?>
        PathLab Pro
      </a>
      
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="#home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#features">Features</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">Contact</a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn btn-outline-primary ml-2 px-3" href="login.php">Login</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="home" class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <div class="hero-content">
            <?php if (hasLogo()): ?>
              <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" class="hero-logo">
            <?php endif; ?>
            <h1 class="hero-title">PathLab Pro</h1>
            <p class="hero-subtitle">Advanced Laboratory Management System for Modern Healthcare</p>
            <p class="mb-4">Streamline your laboratory operations with our comprehensive management solution. From patient registration to report generation, manage everything efficiently in one platform.</p>
            <a href="login.php" class="btn-primary-custom">Access Dashboard</a>
            <a href="#features" class="btn-outline-custom">Learn More</a>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="text-center">
            <i class="fas fa-microscope" style="font-size: 15rem; opacity: 0.1;"></i>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="features-section">
    <div class="container">
      <div class="row mb-5">
        <div class="col-12 text-center">
          <h2 class="mb-3" style="color: var(--primary-dark); font-weight: 300; font-size: 2.5rem;">Powerful Features</h2>
          <p class="lead text-muted">Everything you need to manage your laboratory efficiently</p>
        </div>
      </div>
      
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-users"></i>
            </div>
            <h4 class="feature-title">Patient Management</h4>
            <p class="feature-description">Comprehensive patient database with medical history, contact information, and appointment scheduling.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-flask"></i>
            </div>
            <h4 class="feature-title">Test Management</h4>
            <p class="feature-description">Complete test catalog with automated workflows, quality control, and result validation.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h4 class="feature-title">Advanced Reports</h4>
            <p class="feature-description">Generate detailed reports with charts, graphs, and analytics for better decision making.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-cogs"></i>
            </div>
            <h4 class="feature-title">Equipment Tracking</h4>
            <p class="feature-description">Monitor laboratory equipment, maintenance schedules, and calibration records.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h4 class="feature-title">Secure & Compliant</h4>
            <p class="feature-description">HIPAA compliant with role-based access control and audit trails for data security.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-mobile-alt"></i>
            </div>
            <h4 class="feature-title">Mobile Ready</h4>
            <p class="feature-description">Responsive design that works perfectly on all devices - desktop, tablet, and mobile.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-number">500+</div>
            <div class="stat-label">Laboratories</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-number">1M+</div>
            <div class="stat-label">Tests Processed</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-number">99.9%</div>
            <div class="stat-label">Uptime</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-number">24/7</div>
            <div class="stat-label">Support</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section id="about" class="cta-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto text-center">
          <h2 class="mb-4" style="color: var(--primary-dark); font-weight: 300;">Ready to Get Started?</h2>
          <p class="lead mb-4">Join hundreds of laboratories worldwide who trust PathLab Pro for their daily operations. Experience the future of laboratory management today.</p>
          <a href="login.php" class="btn-primary-custom">Start Now</a>
          <a href="#contact" class="btn-outline-custom">Contact Sales</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer id="contact" class="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <h5>PathLab Pro</h5>
          <p>Advanced Laboratory Management System designed to streamline operations and improve efficiency in modern healthcare facilities.</p>
        </div>
        <div class="col-lg-2 mb-4">
          <h5>Quick Links</h5>
          <ul class="list-unstyled">
            <li><a href="#home">Home</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
          </ul>
        </div>
        <div class="col-lg-3 mb-4">
          <h5>Support</h5>
          <ul class="list-unstyled">
            <li><a href="#">Documentation</a></li>
            <li><a href="#">Help Center</a></li>
            <li><a href="#">Contact Support</a></li>
            <li><a href="#">System Status</a></li>
          </ul>
        </div>
        <div class="col-lg-3 mb-4">
          <h5>Contact Info</h5>
          <p><i class="fas fa-envelope mr-2"></i> support@pathlab.com</p>
          <p><i class="fas fa-phone mr-2"></i> +1 (555) 123-4567</p>
          <p><i class="fas fa-map-marker-alt mr-2"></i> 123 Medical Center Dr<br>Healthcare City, HC 12345</p>
        </div>
      </div>
      <hr style="border-color: rgba(255,255,255,0.2);">
      <div class="row">
        <div class="col-12 text-center">
          <p class="mb-0">&copy; <?php echo date('Y'); ?> PathLab Pro. All rights reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- jQuery -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

  <script>
    // Smooth scrolling for anchor links
    $('a[href*="#"]').on('click', function (e) {
      e.preventDefault();
      $('html, body').animate({
        scrollTop: $($(this).attr('href')).offset().top - 80
      }, 500, 'linear');
    });

    // Navbar background on scroll
    $(window).scroll(function() {
      if ($(document).scrollTop() > 50) {
        $('.navbar-custom').addClass('bg-white shadow-sm');
      } else {
        $('.navbar-custom').removeClass('bg-white shadow-sm');
      }
    });

    // Counter animation
    function animateCounters() {
      $('.stat-number').each(function() {
        const $this = $(this);
        const countTo = $this.text();
        
        $({ countNum: 0 }).animate({
          countNum: countTo
        }, {
          duration: 2000,
          easing: 'linear',
          step: function() {
            $this.text(Math.floor(this.countNum));
          },
          complete: function() {
            $this.text(this.countNum);
          }
        });
      });
    }

    // Trigger counter animation when stats section is in view
    $(window).scroll(function() {
      const statsSection = $('.stats-section');
      const scrollTop = $(window).scrollTop();
      const windowHeight = $(window).height();
      const sectionTop = statsSection.offset().top;
      
      if (scrollTop + windowHeight > sectionTop && !statsSection.hasClass('animated')) {
        statsSection.addClass('animated');
        animateCounters();
      }
    });
  </script>
</body>
</html>
