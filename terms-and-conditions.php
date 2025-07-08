<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - PathLab Pro</title>
    <meta name="description" content="Terms and Conditions for PathLab Pro Laboratory Management System">
    <meta name="keywords" content="terms, conditions, privacy, pathlab pro, laboratory management">
    
    <?php include 'includes/public-head.php'; ?>
    
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
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Terms and Conditions Content -->
    <div class="container" style="margin-top: 120px;">
        <div class="terms-container">
            <div class="terms-header">
                <h1 class="terms-title">Terms and Conditions</h1>
                <p class="terms-subtitle">Last updated: <?php echo date('F j, Y'); ?></p>
                <p class="text-muted">Please read these terms and conditions carefully before using US Fitness services.</p>
            </div>

            <div class="terms-content">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using the US Fitness website and services ("the Service"), you agree to be bound by these Terms and Conditions. If you disagree with any part of the terms, you may not access the Service.</p>

                <h2>2. Description of Service</h2>
                <p>US Fitness provides online and in-person fitness services, including but not limited to workout plans, nutritional guidance, fitness tracking, and educational content related to health and wellness.</p>

                <h2>3. Health and Medical Disclaimer</h2>
                <p>The Service offers health, fitness, and nutritional information that is designed for educational purposes only. You should not rely on this information as a substitute for, nor does it replace, professional medical advice, diagnosis, or treatment. If you have any concerns or questions about your health, you should always consult with a physician or other health-care professional.</p>
                <p>You should be in good physical condition and be able to participate in the exercise. US Fitness is not a licensed medical care provider and represents that it has no expertise in diagnosing, examining, or treating medical conditions of any kind, or in determining the effect of any specific exercise on a medical condition.</p>

                <h2>4. User Accounts</h2>
                <p>To access some features of the Service, you may need to create an account. You are responsible for safeguarding your account credentials and for any activities or actions under your account. You agree to provide accurate and complete information and to keep this information up to date.</p>

                <h2>5. User Conduct</h2>
                <p>You agree not to use the Service for any unlawful purpose or in any way that could damage, disable, overburden, or impair the Service. You agree not to harass, abuse, or harm another person or group.</p>

                <h2>6. Intellectual Property</h2>
                <p>All content included on the Service, such as text, graphics, logos, images, as well as the compilation thereof, and any software used on the site, is the property of US Fitness or its suppliers and protected by copyright and other laws.</p>

                <h2>7. Limitation of Liability</h2>
                <p>In no event shall US Fitness, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of or inability to access or use the Service.</p>

                <h2>8. Termination</h2>
                <p>We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>

                <h2>9. Changes to Terms</h2>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. We will try to provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>

                <h2>10. Governing Law</h2>
                <p>These Terms shall be governed and construed in accordance with the laws of our jurisdiction, without regard to its conflict of law provisions.</p>

                <h2>11. Contact Us</h2>
                <p>If you have any questions about these Terms, please contact us:</p>
                <ul>
                    <li>Email: support@usfitnes.com</li>
                    <li>Phone: (123) 456-7890</li>
                    <li>Address: 123 Fitness Ave, Suite 100, Wellness City, USA</li>
                </ul>

                <div class="text-center mt-5">
                    <a href="index.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
    </div>

    <?php include 'includes/public-footer.php'; ?>

    <?php include 'includes/public-scripts.php'; ?>
</body>
</html>
