<?php
// Include config for database connection and environment setup
require_once 'config.php';

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

  <!-- Contact Section -->
  <section id="contact" class="contact-section">
    <div class="container">
      <div class="row mb-5">
        <div class="col-12">
          <h2 class="section-title fade-in-up">Get In Touch</h2>
          <p class="section-subtitle fade-in-up">Ready to revolutionize your laboratory? Contact us today for a personalized demo and consultation.</p>
        </div>
      </div>
      
      <!-- Alert Container for Contact Form Messages -->
      <div class="row">
        <div class="col-12">
          <div id="alertContainer" class="mb-4"></div>
        </div>
      </div>
      
      <div class="row">
        <!-- Contact Form -->
        <div class="col-lg-8 mb-4">
          <div class="contact-form-card fade-in-up">
            <div class="card h-100" style="border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-radius: 20px;">
              <div class="card-body p-5">
                <h4 class="mb-4" style="color: var(--primary-dark); font-weight: 600;">Send us a Message</h4>
                <form id="contactForm" action="contact_handler.php" method="POST">
                  <!-- Honeypot field for spam protection -->
                  <div style="display: none;">
                    <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <label for="firstName" class="form-label" style="font-weight: 600; color: #333;">First Name *</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required 
                               style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; transition: all 0.3s;">
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <label for="lastName" class="form-label" style="font-weight: 600; color: #333;">Last Name *</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" required
                               style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; transition: all 0.3s;">
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <label for="email" class="form-label" style="font-weight: 600; color: #333;">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; transition: all 0.3s;">
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <div class="form-group">
                        <label for="phone" class="form-label" style="font-weight: 600; color: #333;">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; transition: all 0.3s;">
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <div class="form-group">
                      <label for="company" class="form-label" style="font-weight: 600; color: #333;">Company/Organization</label>
                      <input type="text" class="form-control" id="company" name="company"
                             style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; transition: all 0.3s;">
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <div class="form-group">
                      <label for="subject" class="form-label" style="font-weight: 600; color: #333;">Subject *</label>
                      <select class="form-control" id="subject" name="subject" required
                              style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; transition: all 0.3s;">
                        <option value="">Please select a subject</option>
                        <option value="demo">Request a Demo</option>
                        <option value="pricing">Pricing Information</option>
                        <option value="support">Technical Support</option>
                        <option value="partnership">Partnership Opportunities</option>
                        <option value="general">General Inquiry</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="mb-4">
                    <div class="form-group">
                      <label for="message" class="form-label" style="font-weight: 600; color: #333;">Message *</label>
                      <textarea class="form-control" id="message" name="message" rows="4" required placeholder="Tell us about your laboratory and how we can help you..."
                                style="border: 2px solid #e9ecef; border-radius: 10px; padding: 12px 15px; transition: all 0.3s; resize: vertical;"></textarea>
                    </div>
                  </div>
                  
                  <div class="d-flex justify-content-between align-items-center">
                    <button type="submit" class="btn-primary-custom">
                      <i class="fas fa-paper-plane mr-2"></i>Send Message
                    </button>
                    <small class="text-muted">* Required fields</small>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Contact Information -->
        <div class="col-lg-4 mb-4">
          <div class="contact-info-card fade-in-up" style="animation-delay: 0.2s;">
            <div class="card h-100" style="border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border-radius: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
              <div class="card-body p-5">
                <h4 class="mb-4" style="font-weight: 600;">Contact Information</h4>
                
                <div class="contact-info-item mb-4">
                  <div class="d-flex align-items-start">
                    <div class="contact-icon mr-3">
                      <i class="fas fa-map-marker-alt" style="font-size: 1.2rem; color: #fff; background: rgba(255,255,255,0.2); padding: 12px; border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"></i>
                    </div>
                    <div>
                      <h6 style="font-weight: 600; margin-bottom: 5px;">Our Address</h6>
                      <p style="margin-bottom: 0; opacity: 0.9; line-height: 1.5;">
                        123 Healthcare Avenue<br>
                        Medical District<br>
                        New York, NY 10001
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="contact-info-item mb-4">
                  <div class="d-flex align-items-start">
                    <div class="contact-icon mr-3">
                      <i class="fas fa-phone" style="font-size: 1.2rem; color: #fff; background: rgba(255,255,255,0.2); padding: 12px; border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"></i>
                    </div>
                    <div>
                      <h6 style="font-weight: 600; margin-bottom: 5px;">Phone Number</h6>
                      <p style="margin-bottom: 0; opacity: 0.9;">
                        <a href="tel:+1-800-PATHLAB" style="color: white; text-decoration: none;">+1 (800) PATHLAB</a><br>
                        <small style="opacity: 0.8;">Monday - Friday: 8AM - 6PM EST</small>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="contact-info-item mb-4">
                  <div class="d-flex align-items-start">
                    <div class="contact-icon mr-3">
                      <i class="fas fa-envelope" style="font-size: 1.2rem; color: #fff; background: rgba(255,255,255,0.2); padding: 12px; border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"></i>
                    </div>
                    <div>
                      <h6 style="font-weight: 600; margin-bottom: 5px;">Email Address</h6>
                      <p style="margin-bottom: 0; opacity: 0.9;">
                        <a href="mailto:info@pathlabpro.com" style="color: white; text-decoration: none;">info@pathlabpro.com</a><br>
                        <a href="mailto:support@pathlabpro.com" style="color: white; text-decoration: none;">support@pathlabpro.com</a>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="contact-info-item mb-4">
                  <div class="d-flex align-items-start">
                    <div class="contact-icon mr-3">
                      <i class="fas fa-clock" style="font-size: 1.2rem; color: #fff; background: rgba(255,255,255,0.2); padding: 12px; border-radius: 50%; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;"></i>
                    </div>
                    <div>
                      <h6 style="font-weight: 600; margin-bottom: 5px;">Business Hours</h6>
                      <p style="margin-bottom: 0; opacity: 0.9; font-size: 0.9rem;">
                        Monday - Friday: 8:00 AM - 6:00 PM<br>
                        Saturday: 9:00 AM - 2:00 PM<br>
                        Sunday: Closed<br>
                        <small style="opacity: 0.8;">(Emergency support available 24/7)</small>
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Social Media Links -->
                <div class="social-links mt-4 pt-4" style="border-top: 1px solid rgba(255,255,255,0.2);">
                  <h6 style="font-weight: 600; margin-bottom: 15px;">Follow Us</h6>
                  <div class="d-flex">
                    <a href="#" class="social-link mr-3" style="color: white; font-size: 1.2rem; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;">
                      <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="social-link mr-3" style="color: white; font-size: 1.2rem; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;">
                      <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link mr-3" style="color: white; font-size: 1.2rem; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;">
                      <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" style="color: white; font-size: 1.2rem; background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.3s;">
                      <i class="fab fa-youtube"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Quick Contact Options -->
      <div class="row mt-5">
        <div class="col-12">
          <div class="quick-contact-section text-center fade-in-up" style="animation-delay: 0.4s;">
            <h4 class="mb-4" style="color: var(--primary-dark); font-weight: 600;">Need Immediate Assistance?</h4>
            <div class="row justify-content-center">
              <div class="col-md-4 mb-3">
                <a href="tel:+1-800-PATHLAB" class="quick-contact-btn" style="display: block; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-decoration: none; color: var(--primary-dark); transition: all 0.3s; border: 2px solid transparent;">
                  <i class="fas fa-phone-alt" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 10px;"></i>
                  <h6 style="font-weight: 600; margin-bottom: 5px;">Call Us Now</h6>
                  <p style="margin-bottom: 0; color: #666;">Speak with our experts</p>
                </a>
              </div>
              <div class="col-md-4 mb-3">
                <a href="mailto:info@pathlabpro.com" class="quick-contact-btn" style="display: block; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-decoration: none; color: var(--primary-dark); transition: all 0.3s; border: 2px solid transparent;">
                  <i class="fas fa-envelope" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 10px;"></i>
                  <h6 style="font-weight: 600; margin-bottom: 5px;">Email Support</h6>
                  <p style="margin-bottom: 0; color: #666;">Get detailed assistance</p>
                </a>
              </div>
              <div class="col-md-4 mb-3">
                <a href="login.php" class="quick-contact-btn" style="display: block; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-decoration: none; color: var(--primary-dark); transition: all 0.3s; border: 2px solid transparent;">
                  <i class="fas fa-play-circle" style="font-size: 2rem; color: var(--primary-color); margin-bottom: 10px;"></i>
                  <h6 style="font-weight: 600; margin-bottom: 5px;">Start Free Trial</h6>
                  <p style="margin-bottom: 0; color: #666;">No commitment required</p>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'includes/public-footer.php'; ?>

  <?php include 'includes/public-scripts.php'; ?>
</body>
</html>
