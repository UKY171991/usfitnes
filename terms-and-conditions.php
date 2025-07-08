<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - US Fitness</title>
    <meta name="description" content="Terms and Conditions for US Fitness Management System">
    <meta name="keywords" content="terms, conditions, privacy, us fitness, fitness management">
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    
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
    <!-- Simple Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-dumbbell mr-2"></i>US Fitness
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
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
                <p class="terms-subtitle">Last updated: July 9, 2025</p>
                <p class="text-muted">Please read these terms and conditions carefully before using US Fitness services.</p>
            </div>

            <div class="terms-content">
                <h2>1. Agreement to Terms</h2>
                <p>By accessing and using US Fitness ("the Service"), you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>

                <h2>2. Description of Service</h2>
                <p>US Fitness is a comprehensive fitness management system designed to help users track their fitness journey, manage workout routines, monitor progress, and connect with fitness professionals.</p>

                <h3>2.1 Service Features</h3>
                <ul>
                    <li>Member registration and profile management</li>
                    <li>Workout tracking and scheduling</li>
                    <li>Equipment usage monitoring</li>
                    <li>Progress reports and analytics</li>
                    <li>Trainer-client communication tools</li>
                    <li>Health and safety guidelines</li>
                </ul>

                <h2>3. User Accounts and Registration</h2>
                <p>To access certain features of the Service, you may be required to create an account. You agree to provide accurate, current, and complete information during the registration process.</p>

                <h2>4. Health and Safety Disclaimer</h2>
                <p>Before beginning any fitness program, please consult with your healthcare provider. US Fitness is not responsible for any injuries that may occur while using our services or facilities.</p>

                <h2>5. Privacy and Data Protection</h2>
                <p>Your privacy is important to us. We are committed to protecting your personal information in accordance with applicable privacy laws and regulations.</p>

                <h2>6. Acceptable Use</h2>
                <p>You agree to use the Service only for lawful purposes and in accordance with these Terms. Harassment, inappropriate behavior, or misuse of equipment is strictly prohibited.</p>

                <h2>7. Limitation of Liability</h2>
                <p>US Fitness shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the Service.</p>

                <h2>8. Termination</h2>
                <p>We may terminate or suspend your account for violations of these terms or for any reason deemed necessary for the safety and security of our members.</p>

                <h2>9. Changes to Terms</h2>
                <p>We reserve the right to modify these Terms at any time. Material changes will be communicated with at least 30 days notice.</p>

                <h2>10. Contact Information</h2>
                <p>If you have any questions about these Terms and Conditions, please contact us:</p>
                <ul>
                    <li>Email: support@usfitness.com</li>
                    <li>Phone: +1 (555) 123-4567</li>
                    <li>Address: 123 Fitness Street, Health City, HC 12345</li>
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
                <div class="col-md-6">
                    <h5><i class="fas fa-dumbbell mr-2"></i>US Fitness</h5>
                    <p class="mb-0">Your premier fitness management platform.</p>
                </div>
                <div class="col-md-6">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="terms-and-conditions.php">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2025 US Fitness. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
