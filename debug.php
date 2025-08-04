<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<h2>Debug Information</h2>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>User Information:</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Full Name: " . ($_SESSION['full_name'] ?? $_SESSION['name'] ?? 'Not set') . "<br>";
echo "User Type: " . ($_SESSION['user_type'] ?? $_SESSION['role'] ?? 'Not set') . "<br>";

echo "<h3>File Checks:</h3>";
echo "includes/init.php exists: " . (file_exists('includes/init.php') ? 'Yes' : 'No') . "<br>";
echo "includes/header.php exists: " . (file_exists('includes/header.php') ? 'Yes' : 'No') . "<br>";
echo "includes/sidebar.php exists: " . (file_exists('includes/sidebar.php') ? 'Yes' : 'No') . "<br>";
echo "includes/footer.php exists: " . (file_exists('includes/footer.php') ? 'Yes' : 'No') . "<br>";

echo "<h3>Try loading dashboard:</h3>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";
?>
