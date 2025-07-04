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
  <!-- Bootstrap -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/custom.css">
  <!-- Home Page Styles -->
  <link rel="stylesheet" href="css/home.css">
  
</head>
<body>
  <!-- Animated Background -->
  <div class="animated-bg"></div>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#home">
        <?php if (hasLogo()): ?>
          <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" height="35" class="me-2 mr-2">
        <?php else: ?>
          <i class="fas fa-microscope mr-2" style="font-size: 1.8rem; color: var(--primary-color);"></i>
        <?php endif; ?>
        <span style="font-weight: 700; font-size: 1.8rem;">PathLab Pro</span>
      </a>
      
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto align-items-lg-center">
          <li class="nav-item">
            <a class="nav-link" href="#home">
              <i class="fas fa-home mr-1"></i>Home
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#features">
              <i class="fas fa-star mr-1"></i>Features
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">
              <i class="fas fa-info-circle mr-1"></i>About
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#contact">
              <i class="fas fa-envelope mr-1"></i>Contact
            </a>
          </li>
          <li class="nav-item ml-lg-3">
            <a class="btn btn-primary rounded-pill px-4 py-2 font-weight-bold" href="login.php" style="border: none; background: linear-gradient(45deg, #667eea, #764ba2); color: white; text-decoration: none;">
              <i class="fas fa-sign-in-alt mr-2"></i>LOGIN
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="home" class="hero-section">
    <!-- Floating Particles -->
    <div class="particles">
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
    </div>
    
    <div class="container">
      <div class="row align-items-center min-vh-100">
        <div class="col-lg-6">
          <div class="hero-content">
            <h1 class="hero-title">PathLab Pro</h1>
            <p class="hero-subtitle">Advanced Laboratory Management System</p>
            <p class="hero-description">Transform your laboratory operations with our cutting-edge management solution. Streamline workflows, enhance patient care, and boost efficiency with intelligent automation and comprehensive reporting.</p>
            <div class="hero-buttons">
              <a href="login.php" class="btn-primary-custom">
                <i class="fas fa-rocket mr-2"></i>Get Started
              </a>
              <a href="#features" class="btn-outline-custom">
                <i class="fas fa-play mr-2"></i>Learn More
              </a>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="hero-illustration text-center">
            <i class="fas fa-microscope" style="font-size: 20rem; opacity: 0.15; color: white;"></i>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="features-section">
    <div class="container">
      <div class="row mb-5">
        <div class="col-12">
          <h2 class="section-title fade-in-up">Powerful Features</h2>
          <p class="section-subtitle fade-in-up">Discover the comprehensive tools that make PathLab Pro the ultimate laboratory management solution</p>
        </div>
      </div>
      
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card fade-in-up" style="animation-delay: 0.1s;">
            <div class="feature-icon">
              <i class="fas fa-users-medical"></i>
            </div>
            <h4 class="feature-title">Smart Patient Management</h4>
            <p class="feature-description">Advanced patient database with AI-powered insights, medical history tracking, appointment scheduling, and automated notifications for better patient care.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="feature-icon">
              <i class="fas fa-flask"></i>
            </div>
            <h4 class="feature-title">Intelligent Test Management</h4>
            <p class="feature-description">Complete test catalog with automated workflows, real-time quality control, AI-powered result validation, and seamless integration with lab equipment.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card fade-in-up" style="animation-delay: 0.3s;">
            <div class="feature-icon">
              <i class="fas fa-chart-area"></i>
            </div>
            <h4 class="feature-title">Advanced Analytics</h4>
            <p class="feature-description">Comprehensive reporting with interactive dashboards, predictive analytics, performance metrics, and data-driven insights for strategic decision making.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card fade-in-up" style="animation-delay: 0.4s;">
            <div class="feature-icon">
              <i class="fas fa-robot"></i>
            </div>
            <h4 class="feature-title">AI-Powered Automation</h4>
            <p class="feature-description">Intelligent workflow automation, predictive maintenance for equipment, automated quality control checks, and smart resource optimization.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card fade-in-up" style="animation-delay: 0.5s;">
            <div class="feature-icon">
              <i class="fas fa-shield-virus"></i>
            </div>
            <h4 class="feature-title">Enterprise Security</h4>
            <p class="feature-description">Military-grade security with HIPAA compliance, end-to-end encryption, role-based access control, and comprehensive audit trails for complete data protection.</p>
          </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="feature-card fade-in-up" style="animation-delay: 0.6s;">
            <div class="feature-icon">
              <i class="fas fa-cloud"></i>
            </div>
            <h4 class="feature-title">Cloud-Native Platform</h4>
            <p class="feature-description">Scalable cloud infrastructure with 99.9% uptime, automatic backups, multi-device synchronization, and seamless remote access capabilities.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="stat-card">
            <div class="stat-number counter" data-target="1000">0</div>
            <div class="stat-label">Laboratories Worldwide</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="stat-card">
            <div class="stat-number counter" data-target="10">0</div>
            <div class="stat-label">Million Tests Processed</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="stat-card">
            <div class="stat-number">99.9<span style="font-size: 2rem;">%</span></div>
            <div class="stat-label">System Uptime</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="stat-card">
            <div class="stat-number">24<span style="font-size: 2rem;">/7</span></div>
            <div class="stat-label">Expert Support</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section id="about" class="cta-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-10 mx-auto">
          <div class="cta-content fade-in-up">
            <h2 class="mb-4" style="color: var(--primary-dark); font-weight: 700; font-size: 3rem;">Ready to Transform Your Laboratory?</h2>
            <p class="lead mb-5" style="font-size: 1.3rem; color: #666;">Join thousands of laboratories worldwide who have revolutionized their operations with PathLab Pro. Experience the future of laboratory management with our cutting-edge platform.</p>
            <div class="mb-4">
              <div class="row text-center">
                <div class="col-md-4 mb-3">
                  <i class="fas fa-rocket" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                  <h5>Quick Setup</h5>
                  <p>Get started in minutes with our intuitive setup process</p>
                </div>
                <div class="col-md-4 mb-3">
                  <i class="fas fa-graduation-cap" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                  <h5>Free Training</h5>
                  <p>Comprehensive training and onboarding support included</p>
                </div>
                <div class="col-md-4 mb-3">
                  <i class="fas fa-headset" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                  <h5>24/7 Support</h5>
                  <p>Expert support team available around the clock</p>
                </div>
              </div>
            </div>
            <a href="login.php" class="btn-primary-custom mr-3">
              <i class="fas fa-play mr-2"></i>Start Free Trial
            </a>
            <a href="#contact" class="btn-outline-custom">
              <i class="fas fa-phone mr-2"></i>Schedule Demo
            </a>
          </div>
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
  <!-- Home Page JavaScript -->
  <script src="js/home.js"></script>
</body>
</html>
