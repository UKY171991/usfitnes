<?php
// db_connect.php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;
    private $connectionPool = [];
    private $maxPoolSize = 10;
    private $activeConnections = 0;

    private function __construct() {
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT => true
            ];
            
            $this->connection = new PDO(DSN, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log the error with detailed information
            error_log(sprintf(
                "Database Connection Error: %s\nDSN: %s\nUser: %s\n",
                $e->getMessage(),
                DSN,
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
        if ($this->activeConnections < $this->maxPoolSize) {
            $this->activeConnections++;
            return $this->connection;
        }
        
        // If pool is full, wait for a connection to become available
        while ($this->activeConnections >= $this->maxPoolSize) {
            usleep(100000); // Wait 100ms
        }
        
        $this->activeConnections++;
        return $this->connection;
    }

    public function releaseConnection() {
        if ($this->activeConnections > 0) {
            $this->activeConnections--;
        }
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollBack() {
        return $this->connection->rollBack();
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            
            // Log query for debugging
            if (ENVIRONMENT !== 'production') {
                error_log("SQL Query: $sql\nParams: " . print_r($params, true));
            }
            
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . "\nSQL: $sql");
            throw $e;
        } finally {
            $this->releaseConnection();
        }
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

// Create logs directory if it doesn't exist with secure permissions
$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
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
    if (ENVIRONMENT === 'production') {
        die("A system error occurred. Please check the error logs for more information.");
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>