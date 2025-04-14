<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db_connect.php';

class Auth {
    private static $instance = null;
    private $db;

    private function __construct() {
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Auth Database Error: " . $e->getMessage());
            throw new Exception("Failed to initialize authentication system.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT user_id AS id, password, role, branch_id FROM Users WHERE email = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $this->startSession($user);
                    return true;
                } else {
                    error_log("Login failed: Incorrect password for email $email");
                }
            } else {
                error_log("Login failed: No active user found with email $email");
            }

            return false;
        } catch (PDOException $e) {
            error_log("Database Error during login: " . $e->getMessage());
            throw new Exception("Login failed due to a database error.");
        }
    }

    private function startSession($user) {
        // Only start session if it's not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['branch_id'] = $user['branch_id'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();
    }

    public function isLoggedIn() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }

        if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
            return false;
        }

        // Check branch context
        if (!isset($_SESSION['branch_id']) || empty($_SESSION['branch_id'])) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function logout() {
        // Only start session if it's not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
        
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }

    public function requireRole($requiredRole) {
        $this->requireLogin();
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            header("Location: dashboard.php");
            exit();
        }
    }
}