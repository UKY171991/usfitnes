<?php
require_once 'api/safe_config.php';
list($db_available, $conn) = tryDatabaseConnection();
echo 'Database available: ' . ($db_available ? 'YES' : 'NO') . PHP_EOL;
if ($db_available) {
    $result = mysqli_query($conn, 'SHOW TABLES LIKE \'patients\'');
    echo 'Patients table exists: ' . (mysqli_num_rows($result) > 0 ? 'YES' : 'NO') . PHP_EOL;
    if (mysqli_num_rows($result) > 0) {
        $count = mysqli_query($conn, 'SELECT COUNT(*) as count FROM patients WHERE status != \'deleted\'');
        $row = mysqli_fetch_assoc($count);
        echo 'Patient count: ' . $row['count'] . PHP_EOL;
    }
} else {
    echo 'Error: Cannot connect to database' . PHP_EOL;
}
?>
