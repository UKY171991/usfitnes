<?php
// Header component for PathLab Pro
// This file contains the navigation bar that can be used across multiple pages

// Include init for logo functions if not already included
if (!function_exists('hasLogo')) {
    require_once __DIR__ . '/init.php';
}
?>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <?php if (hasLogo()): ?>
                <img src="<?php echo getLogoPath(); ?>" alt="US Fitness Logo" height="35" class="me-2 mr-2">
                <span style="font-weight: 700; font-size: 1.8rem;">US Fitness</span>
            <?php else: ?>
                <i class="fas fa-dumbbell mr-2" style="font-size: 1.8rem; color: var(--primary-color);"></i>
                <span style="font-weight: 700; font-size: 1.8rem; color: var(--primary-color);">US Fitness</span>
            <?php endif; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="fas fa-home mr-1"></i>Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-star mr-1"></i>Classes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-info-circle mr-1"></i>About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-envelope mr-1"></i>Contact</a>
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
