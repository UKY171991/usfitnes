<?php
/**
 * Database Connection Class
 * Handles PDO database connections with error handling
 */

class Database {
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct() {
        $this->config = require CONFIG_PATH . 'db.php';
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
        } catch (PDOException $e) {
            Logger::log("Database connection failed: " . $e->getMessage(), LOG_LEVEL_ERROR);
            throw new Exception("Database connection failed");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    private function __wakeup() {}
}
?>
