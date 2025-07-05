<?php
// Test logo functionality
require_once 'includes/init.php';

echo "<h1>Logo Test Results</h1>";
echo "<hr>";

echo "<h3>Debug Information:</h3>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Parent Directory:</strong> " . dirname(__DIR__) . "</p>";

echo "<h3>Logo Function Results:</h3>";

$logoPath = getLogoPath();
echo "<p><strong>getLogoPath():</strong> " . ($logoPath ? $logoPath : 'NULL') . "</p>";
echo "<p><strong>hasLogo():</strong> " . (hasLogo() ? 'TRUE' : 'FALSE') . "</p>";

echo "<h3>File Existence Check:</h3>";
$rootPath = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
if (empty($rootPath) || !is_dir($rootPath)) {
    $rootPath = dirname(__DIR__);
}

$svgPath = $rootPath . '/img/logo.svg';
$pngPath = $rootPath . '/img/logo.png';

echo "<p><strong>SVG Path:</strong> $svgPath</p>";
echo "<p><strong>SVG Exists:</strong> " . (file_exists($svgPath) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>PNG Path:</strong> $pngPath</p>";
echo "<p><strong>PNG Exists:</strong> " . (file_exists($pngPath) ? 'YES' : 'NO') . "</p>";

echo "<h3>Visual Test:</h3>";
if (hasLogo()) {
    echo "<p>Logo should display: <img src='" . getLogoPath() . "' alt='Logo' height='50' style='border: 1px solid #ccc; padding: 5px;'></p>";
} else {
    echo "<p>Logo not available - fallback text: <strong style='color: #2c5aa0; font-size: 1.5rem;'><i class='fas fa-microscope'></i> PathLab Pro</strong></p>";
}

// Check current working directory relative paths
echo "<h3>Relative Path Check:</h3>";
echo "<p><strong>img/logo.svg exists:</strong> " . (file_exists('img/logo.svg') ? 'YES' : 'NO') . "</p>";
echo "<p><strong>img/logo.png exists:</strong> " . (file_exists('img/logo.png') ? 'YES' : 'NO') . "</p>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logo Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #2c5aa0; }
        h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        p { margin: 5px 0; }
    </style>
</head>
<body>
</body>
</html>
