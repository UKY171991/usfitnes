<?php
// Prevent direct access
if (!defined('PATHLAB_PRO')) {
    define('PATHLAB_PRO', true);
}

// Start session if not already started (and not in CLI mode)
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('America/New_York');


// Local development configuration (comment out for production)
$host = 'localhost';
$dbname = 'u902379465_fitness';
$username = 'u902379465_fitness';
$password = '57x6pJ7!r#yW';

try {
    // First create database if it doesn't exist
    $pdo_temp = new PDO("mysql:host=$host", $username, $password);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo_temp = null;
    
    // Connect to the database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Also create MySQLi connection for backward compatibility
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("MySQLi Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    
} catch(PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    
    // For development, create mock connections to prevent fatal errors
    if (php_sapi_name() !== 'cli' && isset($_GET['demo'])) {
        // Create a mock PDO object that doesn't actually connect
        $pdo = null;
        $conn = null;
        echo "<div class='alert alert-warning'>Demo mode: Database connection not available</div>";
    } else {
        die("Connection failed. Please check your database configuration.");
    }
}

// Security functions
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
        return $data;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

// Authentication functions
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        if (isAjaxRequest()) {
            errorResponse('Please log in to continue');
        } else {
            header('Location: login.php');
            exit;
        }
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching current user: " . $e->getMessage());
        return null;
    }
}

function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

function isAdmin() {
    return hasRole('admin');
}

// Utility functions
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function redirectTo($url) {
    header("Location: $url");
    exit;
}

function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . mt_rand(1000, 9999);
}

// Pagination helper
function paginate($query, $params = [], $page = 1, $perPage = 10) {
    global $pdo;
    
    $page = max(1, intval($page));
    $perPage = max(1, min(100, intval($perPage)));
    $offset = ($page - 1) * $perPage;
    
    // Count total records
    $countQuery = "SELECT COUNT(*) as total FROM (" . $query . ") as countable";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();
    
    // Get paginated records
    $paginatedQuery = $query . " LIMIT $perPage OFFSET $offset";
    $stmt = $pdo->prepare($paginatedQuery);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
    
    return [
        'records' => $records,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'hasNext' => $page < ceil($total / $perPage),
            'hasPrev' => $page > 1
        ]
    ];
}

// Application constants
define('APP_NAME', 'PathLab Pro');
define('APP_VERSION', '2.0');
define('BASE_URL', 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']));

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

?>
