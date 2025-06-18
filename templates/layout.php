<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Pathology Lab Management System'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <?php include 'header.php'; ?>
    
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
    <?php include 'footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
