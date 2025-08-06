<?php
/**
 * AdminLTE3 Layout Conversion Helper
 * This script helps convert existing pages to use AdminLTE3 layout
 */

// Define the files to convert (add more as needed)
$files_to_convert = [
    'test-orders.php',
    'results.php',
    'doctors.php',
    'equipment.php',
    'reports.php',
    'settings.php'
];

$root_dir = __DIR__;

echo "AdminLTE3 Layout Conversion Helper\n";
echo "==================================\n\n";

foreach ($files_to_convert as $file) {
    $file_path = $root_dir . '/' . $file;
    
    if (!file_exists($file_path)) {
        echo "❌ File not found: $file\n";
        continue;
    }
    
    echo "🔄 Converting: $file\n";
    
    // Read file content
    $content = file_get_contents($file_path);
    
    if ($content === false) {
        echo "❌ Could not read file: $file\n";
        continue;
    }
    
    // Track changes
    $changes_made = false;
    
    // Replace header include
    if (strpos($content, "include 'includes/header.php';") !== false) {
        $content = str_replace(
            "include 'includes/header.php';",
            "include 'includes/adminlte_header.php';",
            $content
        );
        $changes_made = true;
    }
    
    // Replace sidebar include
    if (strpos($content, "include 'includes/sidebar.php';") !== false) {
        $content = str_replace(
            "include 'includes/sidebar.php';",
            "include 'includes/adminlte_sidebar.php';",
            $content
        );
        $changes_made = true;
    }
    
    // Replace footer include
    if (strpos($content, "include 'includes/footer.php';") !== false) {
        $content = str_replace(
            "include 'includes/footer.php';",
            "include 'includes/adminlte_footer.php';",
            $content
        );
        $changes_made = true;
    }
    
    // Update page titles to include "- PathLab Pro"
    $pattern = '/\$page_title = [\'"]([^\'";]+)[\'"];/';
    if (preg_match($pattern, $content, $matches)) {
        $current_title = $matches[1];
        if (strpos($current_title, '- PathLab Pro') === false) {
            $new_title = $current_title . ' - PathLab Pro';
            $content = str_replace(
                "\$page_title = '$current_title';",
                "\$page_title = '$new_title';",
                $content
            );
            $changes_made = true;
        }
    }
    
    // Write the modified content back to the file
    if ($changes_made) {
        $backup_file = $file_path . '.backup.' . date('Ymd_His');
        
        // Create backup
        if (copy($file_path, $backup_file)) {
            echo "📁 Backup created: " . basename($backup_file) . "\n";
            
            // Write new content
            if (file_put_contents($file_path, $content) !== false) {
                echo "✅ Successfully converted: $file\n";
            } else {
                echo "❌ Failed to write converted file: $file\n";
                // Restore from backup
                copy($backup_file, $file_path);
                unlink($backup_file);
            }
        } else {
            echo "❌ Could not create backup for: $file\n";
        }
    } else {
        echo "ℹ️  No changes needed for: $file\n";
    }
    
    echo "\n";
}

echo "Conversion process completed!\n";
echo "\nNext steps:\n";
echo "1. Test each converted page to ensure it loads correctly\n";
echo "2. Update any custom CSS or JavaScript if needed\n";
echo "3. Check responsive design on mobile devices\n";
echo "4. Update navigation links if necessary\n";
?>
