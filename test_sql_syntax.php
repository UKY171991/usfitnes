<?php
// Test the SQL syntax in config.php for duplicate keys

// Read the config.php file
$config_content = file_get_contents('config.php');

// Extract the SQL part
preg_match_all('/CREATE TABLE IF NOT EXISTS.*?;/s', $config_content, $matches);

echo "Found " . count($matches[0]) . " CREATE TABLE statements\n\n";

foreach ($matches[0] as $i => $sql) {
    echo "Table " . ($i + 1) . ":\n";
    
    // Check for duplicate unique constraints
    if (preg_match('/(\w+)\s+.*?UNIQUE.*?UNIQUE KEY\s+`?\1`?/s', $sql, $duplicate_matches)) {
        echo "❌ DUPLICATE UNIQUE CONSTRAINT FOUND: " . $duplicate_matches[1] . "\n";
    } else {
        echo "✅ No duplicate unique constraints\n";
    }
    
    // Extract table name
    preg_match('/CREATE TABLE IF NOT EXISTS `?(\w+)`?/', $sql, $table_match);
    $table_name = $table_match[1] ?? 'unknown';
    echo "Table: " . $table_name . "\n";
    
    echo "---\n\n";
}

echo "SQL syntax check completed.\n";
?>
