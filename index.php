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
      --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Source Sans Pro', sans-serif;
      line-height: 1.6;
      overflow-x: hidden;
    }

    /* Animated background */
    .animated-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Floating particles */
    .particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 1;
    }

    .particle {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .particle:nth-child(1) { left: 20%; animation-delay: 0s; width: 10px; height: 10px; }
    .particle:nth-child(2) { left: 40%; animation-delay: 2s; width: 15px; height: 15px; }
    .particle:nth-child(3) { left: 60%; animation-delay: 4s; width: 8px; height: 8px; }
    .particle:nth-child(4) { left: 80%; animation-delay: 1s; width: 12px; height: 12px; }
    .particle:nth-child(5) { left: 30%; animation-delay: 3s; width: 6px; height: 6px; }

    @keyframes float {
      0%, 100% { transform: translateY(100vh) rotate(0deg); }
      50% { transform: translateY(-10px) rotate(180deg); }
    }

    /* Hero Section */
    .hero-section {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      color: white;
      overflow: hidden;
      background: transparent;
    }

    .hero-content {
      position: relative;
      z-index: 3;
      animation: slideInUp 1s ease-out;
    }

    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .hero-title {
      font-size: 4rem;
      font-weight: 700;
      margin-bottom: 1rem;
      text-shadow: 0 4px 20px rgba(0,0,0,0.3);
      background: linear-gradient(45deg, #fff, #f0f8ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: glow 2s ease-in-out infinite alternate;
    }

    @keyframes glow {
      from { text-shadow: 0 0 20px rgba(255,255,255,0.5); }
      to { text-shadow: 0 0 30px rgba(255,255,255,0.8), 0 0 40px rgba(255,255,255,0.3); }
    }

    .hero-subtitle {
      font-size: 1.6rem;
      margin-bottom: 2rem;
      opacity: 0.95;
      font-weight: 300;
      animation: slideInUp 1s ease-out 0.2s both;
    }

    .hero-description {
      font-size: 1.2rem;
      margin-bottom: 3rem;
      opacity: 0.9;
      animation: slideInUp 1s ease-out 0.4s both;
    }

    .hero-logo {
      max-width: 150px;
      height: auto;
      margin-bottom: 2rem;
      border-radius: 50%;
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
      animation: logoFloat 3s ease-in-out infinite;
    }

    @keyframes logoFloat {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    .hero-illustration {
      animation: slideInRight 1s ease-out 0.6s both;
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(50px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    /* Modern Buttons */
    .btn-primary-custom {
      background: linear-gradient(45deg, #667eea, #764ba2);
      border: none;
      padding: 1.2rem 3rem;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      color: white;
      text-decoration: none;
      display: inline-block;
      transition: all 0.4s ease;
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
      position: relative;
      overflow: hidden;
      animation: slideInUp 1s ease-out 0.8s both;
    }

    .btn-primary-custom::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
      transition: left 0.5s;
    }

    .btn-primary-custom:hover::before {
      left: 100%;
    }

    .btn-primary-custom:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6);
      color: white;
      text-decoration: none;
    }

    .btn-outline-custom {
      border: 2px solid rgba(255,255,255,0.8);
      color: white;
      padding: 1.2rem 3rem;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      text-decoration: none;
      display: inline-block;
      transition: all 0.4s ease;
      margin-left: 1rem;
      backdrop-filter: blur(10px);
      background: rgba(255,255,255,0.1);
      animation: slideInUp 1s ease-out 1s both;
    }

    .btn-outline-custom:hover {
      background: rgba(255,255,255,0.2);
      color: white;
      text-decoration: none;
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    /* Features Section */
    .features-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
      position: relative;
    }

    .section-title {
      color: var(--primary-dark);
      font-weight: 700;
      font-size: 3rem;
      margin-bottom: 1rem;
      text-align: center;
      position: relative;
    }

    .section-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(45deg, #667eea, #764ba2);
      border-radius: 2px;
    }

    .section-subtitle {
      text-align: center;
      font-size: 1.3rem;
      color: #666;
      margin-bottom: 4rem;
    }

    .feature-card {
      text-align: center;
      padding: 3rem 2rem;
      border-radius: 20px;
      transition: all 0.4s ease;
      height: 100%;
      background: white;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(45deg, #667eea, #764ba2);
      opacity: 0;
      transition: opacity 0.4s ease;
      z-index: 1;
    }

    .feature-card:hover::before {
      opacity: 0.05;
    }

    .feature-card:hover {
      transform: translateY(-15px) scale(1.02);
      box-shadow: 0 25px 50px rgba(0,0,0,0.15);
    }

    .feature-card * {
      position: relative;
      z-index: 2;
    }

    .feature-icon {
      width: 100px;
      height: 100px;
      background: linear-gradient(45deg, #667eea, #764ba2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 2rem auto;
      color: white;
      font-size: 2.5rem;
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
      transition: all 0.4s ease;
    }

    .feature-card:hover .feature-icon {
      transform: scale(1.1) rotate(5deg);
      box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
    }

    .feature-title {
      font-size: 1.6rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: var(--primary-dark);
    }

    .feature-description {
      color: #666;
      line-height: 1.7;
      font-size: 1.1rem;
    }

    /* Stats Section */
    .stats-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 80px 0;
      position: relative;
      overflow: hidden;
    }

    .stats-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="rgba(255,255,255,0.1)"><circle cx="20" cy="20" r="2"/><circle cx="80" cy="20" r="3"/><circle cx="40" cy="40" r="1"/><circle cx="90" cy="60" r="2"/><circle cx="10" cy="80" r="1"/></svg>');
      animation: moveStars 20s linear infinite;
    }

    @keyframes moveStars {
      from { transform: translateX(0) translateY(0); }
      to { transform: translateX(-10px) translateY(-10px); }
    }

    .stat-card {
      text-align: center;
      padding: 2rem 1.5rem;
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: scale(1.1);
    }

    .stat-number {
      font-size: 4rem;
      font-weight: 900;
      margin-bottom: 0.5rem;
      text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .stat-label {
      font-size: 1.2rem;
      opacity: 0.9;
      font-weight: 500;
    }

    /* CTA Section */
    .cta-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
      text-align: center;
      position: relative;
    }

    .cta-content {
      background: white;
      padding: 4rem 3rem;
      border-radius: 25px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.1);
      position: relative;
      overflow: hidden;
    }

    .cta-content::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(45deg, transparent, rgba(102, 126, 234, 0.1), transparent);
      animation: shimmer 3s ease-in-out infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%) translateY(-100%) rotate(30deg); }
      100% { transform: translateX(100%) translateY(100%) rotate(30deg); }
    }

    /* Header */
    .navbar-custom {
      background: rgba(255, 255, 255, 0.98);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(0,0,0,0.08);
      padding: 1rem 0;
      transition: all 0.4s ease;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 20px rgba(0,0,0,0.05);
    }

    .navbar-custom.scrolled {
      background: rgba(255, 255, 255, 0.99);
      box-shadow: 0 2px 25px rgba(0,0,0,0.1);
      padding: 0.8rem 0;
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.8rem;
      color: var(--primary-dark) !important;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .navbar-brand:hover {
      transform: scale(1.02);
      color: var(--primary-color) !important;
      text-decoration: none;
    }

    .navbar-nav .nav-link {
      color: var(--primary-dark) !important;
      font-weight: 600;
      margin: 0 0.8rem;
      padding: 0.8rem 1rem !important;
      transition: all 0.3s ease;
      position: relative;
      border-radius: 8px;
    }

    .navbar-nav .nav-link::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 3px;
      background: linear-gradient(45deg, #667eea, #764ba2);
      transition: all 0.3s ease;
      transform: translateX(-50%);
      border-radius: 2px;
    }

    .navbar-nav .nav-link:hover {
      color: var(--primary-color) !important;
      background: rgba(102, 126, 234, 0.05);
      transform: translateY(-1px);
    }

    .navbar-nav .nav-link:hover::after {
      width: 80%;
    }

    .navbar-nav .btn {
      margin-left: 1rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }

    .navbar-nav .btn:hover {
      background: linear-gradient(45deg, #667eea, #764ba2) !important;
      color: white !important;
      border-color: transparent !important;
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .navbar-toggler {
      border: none;
      padding: 0.5rem;
    }

    .navbar-toggler:focus {
      box-shadow: none;
    }

    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2844, 90, 160, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    /* Footer */
    .footer {
      background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
      color: white;
      padding: 60px 0 30px 0;
      position: relative;
    }

    .footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
    }

    .footer h5 {
      color: white;
      margin-bottom: 1.5rem;
      font-weight: 700;
    }

    .footer a {
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      transition: all 0.3s ease;
      position: relative;
    }

    .footer a:hover {
      color: white;
      transform: translateX(5px);
    }

    /* Scroll animations */
    .fade-in-up {
      opacity: 0;
      transform: translateY(50px);
      transition: all 0.6s ease;
    }

    .fade-in-up.animated {
      opacity: 1;
      transform: translateY(0);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.8rem;
      }
      
      .hero-subtitle {
        font-size: 1.3rem;
      }

      .hero-description {
        font-size: 1.1rem;
      }
      
      .btn-outline-custom {
        margin-left: 0;
        margin-top: 1rem;
        display: block;
        width: fit-content;
        margin-left: auto;
        margin-right: auto;
      }

      .section-title {
        font-size: 2.5rem;
      }

      .cta-content {
        padding: 3rem 2rem;
      }

      .navbar-nav .nav-link {
        margin: 0.2rem 0;
        text-align: center;
      }

      .navbar-nav .btn {
        margin: 1rem auto;
        display: block;
        width: fit-content;
      }

      .navbar-collapse {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 15px;
        margin-top: 1rem;
        padding: 1rem;
        box-shadow: 0 5px 25px rgba(0,0,0,0.1);
      }
    }

    @media (max-width: 576px) {
      .hero-title {
        font-size: 2.2rem;
      }

      .feature-card {
        padding: 2rem 1.5rem;
      }

      .navbar-brand {
        font-size: 1.5rem;
      }

      .navbar-brand span {
        font-size: 1.5rem !important;
      }
    }
  </style>
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
        <ul class="navbar-nav ml-auto align-items-center">
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
          <li class="nav-item ml-2">
            <a class="btn btn-outline-primary px-4 py-2 rounded-pill font-weight-bold" href="login.php" style="border-width: 2px; transition: all 0.3s ease;">
              <i class="fas fa-sign-in-alt mr-2"></i>Login
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
  <!-- AdminLTE App -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

  <script>
    $(document).ready(function() {
      // Initialize animations
      initScrollAnimations();
      initCounterAnimations();
      initParticleAnimations();
      
      // Smooth scrolling for anchor links
      $('a[href*="#"]').on('click', function (e) {
        e.preventDefault();
        const target = $($(this).attr('href'));
        if (target.length) {
          $('html, body').animate({
            scrollTop: target.offset().top - 80
          }, 800, 'easeInOutQuart');
        }
      });

      // Enhanced navbar on scroll
      $(window).scroll(function() {
        const scrollTop = $(this).scrollTop();
        const navbar = $('.navbar-custom');
        
        if (scrollTop > 50) {
          navbar.addClass('scrolled');
        } else {
          navbar.removeClass('scrolled');
        }
        
        // Parallax effect for hero section
        const heroSection = $('.hero-section');
        const parallaxSpeed = 0.3;
        heroSection.css('transform', `translateY(${scrollTop * parallaxSpeed}px)`);
      });

      // Auto-collapse navbar on mobile after clicking a link
      $('.navbar-nav .nav-link').on('click', function() {
        if ($(window).width() < 992) {
          $('.navbar-collapse').collapse('hide');
        }
      });

      // Add easing function
      $.easing.easeInOutQuart = function (x, t, b, c, d) {
        if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
        return -c/2 * ((t-=2)*t*t*t - 2) + b;
      };
    });

    // Scroll animations
    function initScrollAnimations() {
      const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animated');
          }
        });
      }, observerOptions);

      document.querySelectorAll('.fade-in-up').forEach(el => {
        observer.observe(el);
      });
    }

    // Counter animations
    function initCounterAnimations() {
      const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
            entry.target.classList.add('counted');
            animateCounter(entry.target);
          }
        });
      }, { threshold: 0.5 });

      document.querySelectorAll('.counter').forEach(counter => {
        counterObserver.observe(counter);
      });
    }

    function animateCounter(element) {
      const target = parseInt(element.dataset.target);
      const duration = 2000;
      const step = target / (duration / 16);
      let current = 0;

      const timer = setInterval(() => {
        current += step;
        if (current >= target) {
          current = target;
          clearInterval(timer);
        }
        element.textContent = Math.floor(current) + '+';
      }, 16);
    }

    // Particle animations
    function initParticleAnimations() {
      const particles = document.querySelectorAll('.particle');
      particles.forEach((particle, index) => {
        // Random size and animation duration
        const size = Math.random() * 10 + 5;
        const duration = Math.random() * 3 + 3;
        const delay = Math.random() * 2;
        
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.animationDuration = duration + 's';
        particle.style.animationDelay = delay + 's';
        
        // Random horizontal position
        particle.style.left = Math.random() * 100 + '%';
      });
    }

    // Feature card hover effects
    $('.feature-card').hover(
      function() {
        $(this).find('.feature-icon').addClass('animate__animated animate__pulse');
      },
      function() {
        $(this).find('.feature-icon').removeClass('animate__animated animate__pulse');
      }
    );

    // Add dynamic particles on mouse move
    document.addEventListener('mousemove', (e) => {
      if (Math.random() > 0.98) { // Only create particles occasionally
        createMouseParticle(e.clientX, e.clientY);
      }
    });

    function createMouseParticle(x, y) {
      const particle = document.createElement('div');
      particle.style.position = 'fixed';
      particle.style.left = x + 'px';
      particle.style.top = y + 'px';
      particle.style.width = '4px';
      particle.style.height = '4px';
      particle.style.background = 'rgba(102, 126, 234, 0.6)';
      particle.style.borderRadius = '50%';
      particle.style.pointerEvents = 'none';
      particle.style.zIndex = '9999';
      particle.style.animation = 'particleFade 1s ease-out forwards';
      
      document.body.appendChild(particle);
      
      setTimeout(() => {
        particle.remove();
      }, 1000);
    }

    // Add CSS for particle fade animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes particleFade {
        0% {
          opacity: 1;
          transform: scale(1) translateY(0);
        }
        100% {
          opacity: 0;
          transform: scale(0.5) translateY(-20px);
        }
      }
    `;
    document.head.appendChild(style);

    // Enhanced button interactions
    $('.btn-primary-custom, .btn-outline-custom').hover(
      function() {
        $(this).addClass('animate__animated animate__pulse');
      },
      function() {
        $(this).removeClass('animate__animated animate__pulse');
      }
    );

    // Typing effect for hero title (optional)
    function typeWriter(element, text, speed = 100) {
      let i = 0;
      element.innerHTML = '';
      
      function type() {
        if (i < text.length) {
          element.innerHTML += text.charAt(i);
          i++;
          setTimeout(type, speed);
        }
      }
      
      setTimeout(type, 1000); // Start after 1 second
    }

    // Uncomment to enable typing effect
    // const heroTitle = document.querySelector('.hero-title');
    // if (heroTitle) typeWriter(heroTitle, 'PathLab Pro');
  </script>
</body>
</html>
