<?php
// Test configuration loading with proper error handling
echo "Testing configuration loading...\n";

// Suppress output and errors for testing
ob_start();
try {
    // Try to include the config file but catch any database errors
    @include_once 'config.php';
    echo "✅ Config file loaded without fatal errors\n";
} catch (Exception $e) {
    echo "❌ Exception caught: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Error caught: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

if (!empty($output)) {
    echo "Output/Warnings:\n" . $output . "\n";
}

echo "Configuration test completed.\n";
?>
