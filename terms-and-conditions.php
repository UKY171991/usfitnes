<?php
// Include init for logo functions
require_once 'includes/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - PathLab Pro</title>
    
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
    <!-- Navbar Fix -->
    <link rel="stylesheet" href="css/navbar-fix.css">
    
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            min-height: 100vh;
            padding-top: 100px; /* Account for fixed navbar */
        }
        .terms-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 3rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .terms-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
        }
        .terms-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f8f9fa;
        }
        .terms-title {
            font-size: 3rem;
            font-weight: 700;
            color: #2c5aa0;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #2c5aa0, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .terms-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .terms-content {
            line-height: 1.8;
            color: #495057;
        }
        .terms-content h2 {
            color: #2c5aa0;
            font-weight: 700;
            margin: 2.5rem 0 1.5rem 0;
            font-size: 1.8rem;
            position: relative;
            padding-left: 1rem;
        }
        .terms-content h2::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        .terms-content h3 {
            color: #495057;
            font-weight: 600;
            margin: 2rem 0 1rem 0;
            font-size: 1.4rem;
        }
        .terms-content p {
            margin-bottom: 1.5rem;
            text-align: justify;
        }
        .terms-content ul {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }
        .terms-content li {
            margin-bottom: 0.8rem;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            margin-top: 2rem;
        }
        .back-btn:hover {
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
        }
        .back-btn i {
            margin-right: 0.5rem;
        }
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 60px 0 30px 0;
            position: relative;
            margin-top: 4rem;
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
        }
        .footer a:hover {
            color: white;
            transform: translateX(5px);
        }
        @media (max-width: 768px) {
            .terms-container {
                margin: 1rem;
                padding: 2rem;
            }
            .terms-title {
                font-size: 2.5rem;
            }
            .terms-content h2 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
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
                        <a class="nav-link" href="index.php#home">
                            <i class="fas fa-home mr-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#features">
                            <i class="fas fa-star mr-1"></i>Features
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">
                            <i class="fas fa-info-circle mr-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">
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

    <!-- Terms and Conditions Content -->
    <div class="container" style="margin-top: 120px;">
        <div class="terms-container">
            <div class="terms-header">
                <h1 class="terms-title">Terms and Conditions</h1>
                <p class="terms-subtitle">Last updated: <?php echo date('F j, Y'); ?></p>
                <p class="text-muted">Please read these terms and conditions carefully before using PathLab Pro services.</p>
            </div>

            <div class="terms-content">
                <h2>1. Agreement to Terms</h2>
                <p>By accessing and using PathLab Pro ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>

                <h2>2. Description of Service</h2>
                <p>PathLab Pro is a comprehensive laboratory management system designed to streamline laboratory operations, manage patient data, handle test results, and facilitate communication between healthcare providers and laboratories.</p>

                <h3>2.1 Service Features</h3>
                <ul>
                    <li>Patient management and registration</li>
                    <li>Test ordering and result management</li>
                    <li>Equipment tracking and maintenance</li>
                    <li>Report generation and analytics</li>
                    <li>User management and access control</li>
                    <li>Data backup and security features</li>
                </ul>

                <h2>3. User Accounts and Registration</h2>
                <p>To access certain features of the Service, you may be required to create an account. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>

                <h3>3.1 Account Security</h3>
                <ul>
                    <li>You are responsible for maintaining the confidentiality of your account credentials</li>
                    <li>You must notify us immediately of any unauthorized use of your account</li>
                    <li>We reserve the right to suspend or terminate accounts that violate these terms</li>
                </ul>

                <h2>4. Privacy and Data Protection</h2>
                <p>Your privacy is important to us. We are committed to protecting your personal information and health data in accordance with applicable privacy laws and regulations, including HIPAA compliance where applicable.</p>

                <h3>4.1 Data Collection</h3>
                <ul>
                    <li>We collect only the information necessary to provide our services</li>
                    <li>Patient health information is handled with the highest level of security</li>
                    <li>Access to sensitive data is restricted to authorized personnel only</li>
                </ul>

                <h3>4.2 Data Security</h3>
                <ul>
                    <li>We implement industry-standard security measures to protect your data</li>
                    <li>Data is encrypted both in transit and at rest</li>
                    <li>Regular security audits and updates are performed</li>
                </ul>

                <h2>5. Acceptable Use</h2>
                <p>You agree to use the Service only for lawful purposes and in accordance with these Terms. You agree not to use the Service:</p>
                <ul>
                    <li>In any way that violates applicable laws or regulations</li>
                    <li>To transmit or procure the sending of any advertising or promotional material</li>
                    <li>To impersonate or attempt to impersonate the Company, employees, or other users</li>
                    <li>To engage in any other conduct that restricts or inhibits anyone's use of the Service</li>
                </ul>

                <h2>6. Intellectual Property Rights</h2>
                <p>The Service and its original content, features, and functionality are and will remain the exclusive property of PathLab Pro and its licensors. The Service is protected by copyright, trademark, and other laws.</p>

                <h2>7. Limitation of Liability</h2>
                <p>In no event shall PathLab Pro, its directors, employees, partners, agents, suppliers, or affiliates be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the Service.</p>

                <h2>8. Medical Disclaimer</h2>
                <p>PathLab Pro is a laboratory management tool and does not provide medical advice, diagnosis, or treatment. The information provided through the Service is for informational purposes only and should not be used as a substitute for professional medical advice.</p>

                <h2>9. Service Availability</h2>
                <p>We strive to maintain the Service availability but do not guarantee uninterrupted access. We may temporarily suspend the Service for maintenance, updates, or other operational reasons.</p>

                <h2>10. Termination</h2>
                <p>We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever, including but not limited to a breach of the Terms.</p>

                <h2>11. Changes to Terms</h2>
                <p>We reserve the right to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days notice prior to any new terms taking effect.</p>

                <h2>12. Governing Law</h2>
                <p>These Terms shall be interpreted and governed by the laws of the jurisdiction in which our company is registered, without regard to its conflict of law provisions.</p>

                <h2>13. Contact Information</h2>
                <p>If you have any questions about these Terms and Conditions, please contact us at:</p>
                <ul>
                    <li>Email: support@pathlabpro.com</li>
                    <li>Phone: +1 (555) 123-4567</li>
                    <li>Address: 123 Healthcare Drive, Medical City, HC 12345</li>
                </ul>

                <div class="text-center mt-5">
                    <a href="index.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>PathLab Pro</h5>
                    <p>Advanced Laboratory Management System designed to streamline operations and improve efficiency in modern healthcare facilities.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php#home">Home</a></li>
                        <li><a href="index.php#features">Features</a></li>
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
                        <li><a href="terms-and-conditions.php">Terms & Conditions</a></li>
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
                    <p class="mb-2">
                        <a href="terms-and-conditions.php" class="text-white-50 mr-3" style="text-decoration: none;">Terms & Conditions</a>
                        <a href="#" class="text-white-50" style="text-decoration: none;">Privacy Policy</a>
                    </p>
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> PathLab Pro. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="js/home.js"></script>
</body>
</html>
