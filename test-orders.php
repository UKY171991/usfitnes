<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Orders - US Fitness</title>
    <meta name="description" content="Test Orders for US Fitness Management System">
    <meta name="keywords" content="test orders, us fitness, fitness testing, health assessments">
    
    <?php include 'includes/public-head.php'; ?>
    
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            min-height: 100vh;
            padding-top: 100px; /* Account for fixed navbar */
        }
        .orders-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 3rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .orders-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
        }
        .orders-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f8f9fa;
        }
        .orders-title {
            font-size: 3rem;
            font-weight: 700;
            color: #2c5aa0;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #2c5aa0, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .orders-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-left: 5px solid #667eea;
            transition: all 0.3s ease;
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .test-card h4 {
            color: #2c5aa0;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .test-card .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 1rem;
        }
        .test-card .description {
            color: #6c757d;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .order-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            border: none;
            display: inline-block;
        }
        .order-btn:hover {
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #6c757d, #495057);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
            margin-top: 2rem;
        }
        .back-btn:hover {
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(108, 117, 125, 0.5);
        }
        .back-btn i {
            margin-right: 0.5rem;
        }
        @media (max-width: 768px) {
            .orders-container {
                margin: 1rem;
                padding: 2rem;
            }
            .orders-title {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Test Orders Content -->
    <div class="container" style="margin-top: 120px;">
        <div class="orders-container">
            <div class="orders-header">
                <h1 class="orders-title">Fitness Test Orders</h1>
                <p class="orders-subtitle">Professional Fitness Assessments & Health Tests</p>
                <p class="text-muted">Choose from our comprehensive range of fitness tests and health assessments to track your progress and optimize your fitness journey.</p>
            </div>

            <div class="row">
                <!-- Basic Fitness Assessment -->
                <div class="col-md-6 col-lg-4">
                    <div class="test-card">
                        <h4><i class="fas fa-heartbeat mr-2"></i>Basic Fitness Assessment</h4>
                        <div class="price">$99</div>
                        <div class="description">
                            Comprehensive basic fitness evaluation including:
                            <ul class="mt-2">
                                <li>Body composition analysis</li>
                                <li>Cardiovascular endurance test</li>
                                <li>Flexibility assessment</li>
                                <li>Basic strength measurements</li>
                            </ul>
                        </div>
                        <a href="login.php" class="order-btn">Order Test</a>
                    </div>
                </div>

                <!-- Advanced Performance Test -->
                <div class="col-md-6 col-lg-4">
                    <div class="test-card">
                        <h4><i class="fas fa-running mr-2"></i>Advanced Performance Test</h4>
                        <div class="price">$199</div>
                        <div class="description">
                            Complete performance analysis including:
                            <ul class="mt-2">
                                <li>VO2 max testing</li>
                                <li>Lactate threshold analysis</li>
                                <li>Power output assessment</li>
                                <li>Movement pattern analysis</li>
                            </ul>
                        </div>
                        <a href="login.php" class="order-btn">Order Test</a>
                    </div>
                </div>

                <!-- Nutritional Assessment -->
                <div class="col-md-6 col-lg-4">
                    <div class="test-card">
                        <h4><i class="fas fa-apple-alt mr-2"></i>Nutritional Assessment</h4>
                        <div class="price">$149</div>
                        <div class="description">
                            Comprehensive nutritional evaluation:
                            <ul class="mt-2">
                                <li>Metabolic rate testing</li>
                                <li>Vitamin & mineral analysis</li>
                                <li>Food sensitivity testing</li>
                                <li>Custom meal planning</li>
                            </ul>
                        </div>
                        <a href="login.php" class="order-btn">Order Test</a>
                    </div>
                </div>

                <!-- Body Composition Analysis -->
                <div class="col-md-6 col-lg-4">
                    <div class="test-card">
                        <h4><i class="fas fa-weight mr-2"></i>Body Composition Analysis</h4>
                        <div class="price">$79</div>
                        <div class="description">
                            Detailed body composition breakdown:
                            <ul class="mt-2">
                                <li>Body fat percentage</li>
                                <li>Muscle mass distribution</li>
                                <li>Bone density assessment</li>
                                <li>Hydration levels</li>
                            </ul>
                        </div>
                        <a href="login.php" class="order-btn">Order Test</a>
                    </div>
                </div>

                <!-- Injury Risk Assessment -->
                <div class="col-md-6 col-lg-4">
                    <div class="test-card">
                        <h4><i class="fas fa-shield-alt mr-2"></i>Injury Risk Assessment</h4>
                        <div class="price">$129</div>
                        <div class="description">
                            Prevent injuries with our assessment:
                            <ul class="mt-2">
                                <li>Movement screening</li>
                                <li>Joint stability testing</li>
                                <li>Muscle imbalance detection</li>
                                <li>Corrective exercise plan</li>
                            </ul>
                        </div>
                        <a href="login.php" class="order-btn">Order Test</a>
                    </div>
                </div>

                <!-- Complete Health Package -->
                <div class="col-md-6 col-lg-4">
                    <div class="test-card">
                        <h4><i class="fas fa-trophy mr-2"></i>Complete Health Package</h4>
                        <div class="price">$399 <small class="text-muted" style="font-size: 0.8rem; text-decoration: line-through;">$576</small></div>
                        <div class="description">
                            All-inclusive health and fitness assessment:
                            <ul class="mt-2">
                                <li>All above tests included</li>
                                <li>Personal consultation</li>
                                <li>3-month follow-up plan</li>
                                <li>Priority support</li>
                            </ul>
                        </div>
                        <a href="login.php" class="order-btn">Order Package</a>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle mr-2"></i>How it works:</h5>
                        <ol class="mb-0">
                            <li>Select your desired test or package</li>
                            <li>Login or create an account to place your order</li>
                            <li>Schedule your appointment at our facility</li>
                            <li>Complete your tests with our certified professionals</li>
                            <li>Receive detailed results and personalized recommendations</li>
                        </ol>
                    </div>
                </div>
            </div>

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
