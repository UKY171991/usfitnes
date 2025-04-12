<?php
// db_connect.php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log the error with detailed information
            error_log(sprintf(
                "Database Connection Error: %s\nDSN: %s\nUser: %s\n",
                $e->getMessage(),
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER
            ));
            throw new Exception("Database connection failed. Please check your database settings.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Test the connection
    public static function testConnection() {
        try {
            $db = self::getInstance();
            $conn = $db->getConnection();
            $stmt = $conn->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            error_log("Connection test failed: " . $e->getMessage());
            return false;
        }
    }
}

// Enable error display temporarily for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create logs directory if it doesn't exist
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

// Test the connection and provide detailed error information
try {
    if (!Database::testConnection()) {
        throw new Exception("Database connection test failed");
    }
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    die("A system error occurred. Please check the error logs for more information.");
}
?>