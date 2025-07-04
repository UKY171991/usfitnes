<?php
/**
 * Fix showAlert function parameter inconsistencies across all files
 */

echo "PathLab Pro - JavaScript Function Consistency Fix\n";
echo "==============================================\n\n";

// Files to fix
$files = [
    'doctors.php',
    'equipment.php',
    'settings.php',
    'results.php',
    'reports.php',
    'register.php'
];

// Pattern to find showAlert calls with message, type order
$pattern = "/showAlert\s*\(\s*'([^']+)'\s*,\s*'(success|danger|warning|info|error)'\s*\)/";

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "Processing {$file}...\n";
        
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Fix showAlert calls with wrong parameter order
        $content = preg_replace_callback($pattern, function($matches) {
            $message = $matches[1];
            $type = $matches[2];
            // Convert 'error' to 'danger' for consistency
            if ($type === 'error') $type = 'danger';
            return "showAlert('{$type}', '{$message}')";
        }, $content);
        
        // Also fix function declaration if needed
        $content = preg_replace('/function showAlert\s*\(\s*message\s*,\s*type\s*\)/', 'function showAlert(type, message)', $content);
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "  - Fixed showAlert calls in {$file}\n";
        } else {
            echo "  - No fixes needed in {$file}\n";
        }
    } else {
        echo "  - File {$file} not found\n";
    }
}

echo "\nChecking for any remaining inconsistencies...\n";

// Check for remaining issues
foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for showAlert calls with quoted strings
        if (preg_match_all("/showAlert\s*\(\s*'([^']+)'\s*,\s*'([^']+)'\s*\)/", $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $first = $match[1];
                $second = $match[2];
                
                // Check if first parameter looks like a message (contains spaces or is long)
                if (strlen($first) > 20 || strpos($first, ' ') !== false) {
                    echo "  - Potential issue in {$file}: showAlert('{$first}', '{$second}')\n";
                }
            }
        }
    }
}

echo "\nFix completed!\n";
?>
