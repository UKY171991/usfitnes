<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logo Display Test - PathLab Pro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
    <style>
        .test-section {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            background: #f8f9fa;
        }
        .logo-container {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #dee2e6;
        }
        .navbar-test {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%);
            padding: 10px 15px;
            border-radius: 5px;
        }
        .sidebar-test {
            background: #343a40;
            padding: 15px;
            border-radius: 5px;
            color: white;
        }
    </style>
</head>
<body>

<?php
require_once 'includes/init.php';
?>

<div class="container mt-4">
    <h1 class="text-center mb-4">PathLab Pro - Logo Display Test</h1>
    
    <!-- Logo Status -->
    <div class="test-section">
        <h3>Logo Status</h3>
        <div class="logo-container">
            <p><strong>Logo Available:</strong> <?php echo hasLogo() ? '<span class="badge badge-success">YES</span>' : '<span class="badge badge-danger">NO</span>'; ?></p>
            <?php if (hasLogo()): ?>
                <p><strong>Logo Path:</strong> <?php echo getLogoPath(); ?></p>
                <p><strong>Logo Preview:</strong></p>
                <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" style="max-height: 60px; border: 1px solid #ccc; padding: 5px;">
            <?php else: ?>
                <p><strong>Fallback Display:</strong> <i class="fas fa-microscope" style="color: #2c5aa0; font-size: 1.5rem;"></i> <strong style="color: #2c5aa0;">PathLab Pro</strong></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navbar Test -->
    <div class="test-section">
        <h3>Navbar Display Test</h3>
        <div class="navbar-test">
            <div class="d-flex align-items-center">
                <?php if (hasLogo()): ?>
                    <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" height="35" class="mr-2">
                    <span style="font-weight: 700; font-size: 1.8rem; color: white;">PathLab Pro</span>
                <?php else: ?>
                    <i class="fas fa-microscope mr-2" style="font-size: 1.8rem; color: white;"></i>
                    <span style="font-weight: 700; font-size: 1.8rem; color: white;">PathLab Pro</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar Test -->
    <div class="test-section">
        <h3>Sidebar Display Test</h3>
        <div class="sidebar-test">
            <div class="d-flex align-items-center">
                <?php if (hasLogo()): ?>
                    <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" class="img-circle" style="width: 33px; height: 33px; opacity: 0.8; margin-right: 10px;">
                    <span style="font-weight: 300;">PathLab Pro</span>
                <?php else: ?>
                    <i class="fas fa-microscope" style="font-size: 2rem; color: #007bff; margin-right: 10px;"></i>
                    <span style="font-weight: bold; font-size: 1.2rem;">PathLab Pro</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Preloader Test -->
    <div class="test-section">
        <h3>Preloader Display Test</h3>
        <div class="logo-container text-center">
            <?php if (hasLogo()): ?>
                <img src="<?php echo getLogoPath(); ?>" alt="PathLab Pro Logo" height="60" width="60" style="animation: shake 1s ease-in-out infinite;">
            <?php else: ?>
                <h2 style="color: #2c5aa0; font-weight: bold; animation: shake 1s ease-in-out infinite;">PathLab Pro</h2>
            <?php endif; ?>
        </div>
    </div>

    <!-- Debug Information -->
    <div class="test-section">
        <h3>Debug Information</h3>
        <div class="logo-container">
            <pre><?php
            echo "Current Directory: " . getcwd() . "\n";
            echo "Script Directory: " . __DIR__ . "\n";
            echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
            echo "\nFile Existence Tests:\n";
            $paths = ['img/logo.svg', 'img/logo.png', '../img/logo.svg', '../img/logo.png'];
            foreach ($paths as $path) {
                echo "$path: " . (file_exists($path) ? 'EXISTS' : 'NOT FOUND') . "\n";
            }
            ?></pre>
        </div>
    </div>
</div>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>

</body>
</html>
