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

  <?php include 'includes/public-head.php'; ?>
  
</head>
<body>
  <!-- Animated Background -->
  <div class="animated-bg"></div>

  <?php include 'includes/navbar.php'; ?>

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

  <?php include 'includes/public-footer.php'; ?>

  <?php include 'includes/public-scripts.php'; ?>
</body>
</html>
