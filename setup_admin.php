<?php
$password = 'Uma@171991';
$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = file_get_contents('create_admin.sql');
$sql = str_replace('$2y$10$YourNewPasswordHashHere', $hash, $sql);

file_put_contents('final_admin.sql', $sql);

echo "Password hash generated: " . $hash . "\n";
echo "SQL file updated. Now executing...\n";

// Execute the SQL
$output = shell_exec('C:\xampp\mysql\bin\mysql -u root < final_admin.sql');
echo $output ? $output : "SQL executed successfully\n";

// Verify the user
$conn = new PDO("mysql:host=localhost;dbname=fitness", "root", "");
$stmt = $conn->prepare("SELECT username, role FROM users WHERE username = ?");
$stmt->execute(['uky171991@gmail.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\nUser verification:\n";
echo "Username: " . ($user['username'] ?? 'not found') . "\n";
echo "Role: " . ($user['role'] ?? 'not found') . "\n";
?> 