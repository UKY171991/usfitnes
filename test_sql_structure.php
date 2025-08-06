<?php
// Test SQL syntax and execution without actually running it
$config_content = file_get_contents('config.php');

// Extract each CREATE TABLE statement
preg_match_all('/CREATE TABLE IF NOT EXISTS `[^`]+`.*?ENGINE[^;]*;/s', $config_content, $create_tables);

echo "Found " . count($create_tables[0]) . " CREATE TABLE statements\n\n";

foreach ($create_tables[0] as $i => $table_sql) {
    // Extract table name
    preg_match('/CREATE TABLE IF NOT EXISTS `([^`]+)`/', $table_sql, $table_match);
    $table_name = $table_match[1] ?? 'unknown';
    
    echo "Table " . ($i + 1) . ": $table_name\n";
    
    // Check for CONSTRAINT in this table
    if (preg_match('/CONSTRAINT/i', $table_sql)) {
        echo "❌ FOUND CONSTRAINT in CREATE TABLE\n";
        preg_match_all('/CONSTRAINT[^,\)]+/i', $table_sql, $constraints);
        foreach ($constraints[0] as $constraint) {
            echo "  - " . trim($constraint) . "\n";
        }
    } else {
        echo "✅ No constraints in CREATE TABLE\n";
    }
    echo "---\n";
}

// Check if all ALTER TABLE statements are present
$alter_fks = preg_match_all('/ALTER TABLE.*?ADD CONSTRAINT.*?FOREIGN KEY/s', $config_content, $alter_matches);
echo "\nFound $alter_fks ALTER TABLE FOREIGN KEY statements\n";

echo "\nSQL structure analysis completed.\n";
?>
