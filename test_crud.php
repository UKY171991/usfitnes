<?php
// Simple CRUD test script
require_once 'config.php';

echo "<h2>PathLab Pro - CRUD Operations Test</h2>\n";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test</h3>\n";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<span style='color: green;'>✅ Database connection: SUCCESS</span><br>\n";
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Database connection: FAILED - " . $e->getMessage() . "</span><br>\n";
}

// Test 2: Check if required tables exist
echo "<h3>2. Tables Existence Test</h3>\n";
$required_tables = ['users', 'patients', 'test_categories', 'tests', 'test_orders', 'test_results', 'doctors', 'equipment'];

foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("DESCRIBE `$table`");
        echo "<span style='color: green;'>✅ Table '$table': EXISTS</span><br>\n";
    } catch (Exception $e) {
        echo "<span style='color: red;'>❌ Table '$table': MISSING</span><br>\n";
    }
}

// Test 3: Test API endpoints
echo "<h3>3. API Endpoints Test</h3>\n";
$api_files = [
    'api/dashboard_api.php',
    'api/patients_api.php', 
    'api/tests_api.php',
    'api/test_orders_api.php',
    'api/results_api.php',
    'api/doctors_api.php',
    'api/equipment_api.php',
    'api/users_api.php'
];

foreach ($api_files as $api_file) {
    if (file_exists($api_file)) {
        echo "<span style='color: green;'>✅ API '$api_file': EXISTS</span><br>\n";
    } else {
        echo "<span style='color: red;'>❌ API '$api_file': MISSING</span><br>\n";
    }
}

// Test 4: Test sample data operations
echo "<h3>4. Sample CRUD Operations Test</h3>\n";

// Test creating a test category
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO test_categories (category_name, description) VALUES (?, ?)");
    $stmt->execute(['Blood Test', 'Blood analysis tests']);
    echo "<span style='color: green;'>✅ Test category creation: SUCCESS</span><br>\n";
} catch (Exception $e) {
    echo "<span style='color: orange;'>⚠️ Test category creation: " . $e->getMessage() . "</span><br>\n";
}

// Test reading test categories
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_categories");
    $result = $stmt->fetch();
    echo "<span style='color: green;'>✅ Test category read: SUCCESS (Found {$result['count']} categories)</span><br>\n";
} catch (Exception $e) {
    echo "<span style='color: red;'>❌ Test category read: FAILED - " . $e->getMessage() . "</span><br>\n";
}

echo "<h3>5. Frontend Pages Test</h3>\n";
$pages = [
    'dashboard.php',
    'patients.php',
    'tests.php', 
    'test-orders.php',
    'results.php',
    'doctors.php',
    'equipment.php',
    'users.php'
];

foreach ($pages as $page) {
    if (file_exists($page)) {
        echo "<span style='color: green;'>✅ Page '$page': EXISTS</span><br>\n";
    } else {
        echo "<span style='color: red;'>❌ Page '$page': MISSING</span><br>\n";
    }
}

echo "<br><h3>Summary</h3>\n";
echo "✅ = Working correctly<br>\n";
echo "⚠️ = Working with warnings<br>\n";
echo "❌ = Not working<br>\n";

echo "<br><a href='dashboard.php'>← Back to Dashboard</a>\n";
?>
