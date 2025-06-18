<?php
// Load configuration constants
require_once __DIR__ . '/../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'US Fitness Lab - Pathology Test Center'; ?></title>
    
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    
    <!-- AJAX Configuration -->
    <script>
        const AJAX_URL = '<?php echo AJAX_URL; ?>';
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const CSRF_TOKEN = '<?php echo isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ''; ?>';
    </script>
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>    <!-- Navigation -->
    <?php include __DIR__ . '/header.php'; ?>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])): ?>
        <div class="container mt-3">
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?php echo $type === 'error' ? 'danger' : $type; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
            <?php unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>
      <!-- Main Content -->
    <main class="<?php echo $containerClass ?? 'container'; ?> <?php echo $mainClass ?? 'my-4'; ?>">
        <?php echo $content ?? ''; ?>
    </main>
      <!-- Footer -->
    <?php include __DIR__ . '/footer.php'; ?>
    
    <!-- Loading Spinner -->
    <div id="loading-spinner" class="loading-spinner" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <!-- Toast Container for AJAX notifications -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
    
    <!-- Bootstrap JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- jQuery via CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Scripts -->
    <?php if (isset($inlineScripts)): ?>
        <script>
            <?php echo $inlineScripts; ?>
        </script>
    <?php endif; ?>
</body>
</html>
