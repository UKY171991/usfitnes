<?php
/**
 * Authentication Helper Class
 * Handles user authentication, sessions, and security
 */

class Auth {
    private static $db;

    public static function init() {
        self::$db = Database::getInstance();
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check session timeout
        self::checkSessionTimeout();
    }

    /**
     * Login user with email/username and password
     */
    public static function login($emailOrUsername, $password, $rememberMe = false) {
        try {
            $stmt = self::$db->prepare("
                SELECT id, name, username, email, password, role, branch_id, status, phone
                FROM users 
                WHERE (email = ? OR username = ?) AND status = 1
            ");
            $stmt->execute([$emailOrUsername, $emailOrUsername]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            if (!password_verify($password, $user['password'])) {
                // Log failed attempt
                self::logLoginAttempt($emailOrUsername, false);
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Update last login
            self::updateLastLogin($user['id']);

            // Set session data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['branch_id'] = $user['branch_id'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            // Handle remember me
            if ($rememberMe) {
                self::setRememberMeCookie($user['id']);
            }

            // Log successful attempt
            self::logLoginAttempt($emailOrUsername, true);

            return ['success' => true, 'user' => $user, 'redirect' => self::getRedirectUrl($user['role'])];

        } catch (Exception $e) {
            Logger::log("Login error: " . $e->getMessage(), LOG_LEVEL_ERROR);
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }

    /**
     * Logout current user
     */
    public static function logout() {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('remember_user', '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();
        session_start();
        
        return true;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user data
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'branch_id' => $_SESSION['branch_id']
        ];
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles);
    }

    /**
     * Check if user can access branch data
     */
    public static function canAccessBranch($branchId) {
        $user = self::getCurrentUser();
        
        if (!$user) {
            return false;
        }

        // Master admin can access all branches
        if ($user['role'] === ROLE_MASTER_ADMIN) {
            return true;
        }

        // Branch admin can only access their own branch
        return $user['branch_id'] == $branchId;
    }

    /**
     * Require login
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole($role) {
        self::requireLogin();
        
        if (!self::hasRole($role)) {
            header('Location: ' . BASE_URL . 'unauthorized.php');
            exit;
        }
    }

    /**
     * Require any of the specified roles
     */
    public static function requireAnyRole($roles) {
        self::requireLogin();
        
        if (!self::hasAnyRole($roles)) {
            header('Location: ' . BASE_URL . 'unauthorized.php');
            exit;
        }
    }

    /**
     * Register new user
     */
    public static function register($userData) {
        try {
            // Validate required fields
            $required = ['name', 'email', 'password', 'phone'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return ['success' => false, 'message' => "Field '$field' is required"];
                }
            }

            // Check if email already exists
            $stmt = self::$db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$userData['email']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            // Insert user
            $stmt = self::$db->prepare("
                INSERT INTO users (name, email, username, password, phone, role, branch_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            
            $username = $userData['username'] ?? $userData['email'];
            $role = $userData['role'] ?? ROLE_PATIENT;
            $branchId = $userData['branch_id'] ?? null;

            $stmt->execute([
                $userData['name'],
                $userData['email'],
                $username,
                $hashedPassword,
                $userData['phone'],
                $role,
                $branchId
            ]);

            $userId = self::$db->lastInsertId();

            return ['success' => true, 'user_id' => $userId, 'message' => 'Registration successful'];

        } catch (Exception $e) {
            Logger::log("Registration error: " . $e->getMessage(), LOG_LEVEL_ERROR);
            return ['success' => false, 'message' => 'Registration failed. Please try again.'];
        }
    }

    /**
     * Check session timeout
     */
    private static function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
                self::logout();
                return false;
            }
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Update last login time
     */
    private static function updateLastLogin($userId) {
        try {
            $stmt = self::$db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            Logger::log("Failed to update last login: " . $e->getMessage(), LOG_LEVEL_ERROR);
        }
    }

    /**
     * Log login attempts
     */
    private static function logLoginAttempt($identifier, $success) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt = self::$db->prepare("
                INSERT INTO audit_logs (user_id, action, ip_address, user_agent, created_at)
                VALUES (NULL, ?, ?, ?, NOW())
            ");
            $action = $success ? "Login Success: $identifier" : "Login Failed: $identifier";
            $stmt->execute([$action, $ip, $userAgent]);
        } catch (Exception $e) {
            Logger::log("Failed to log login attempt: " . $e->getMessage(), LOG_LEVEL_ERROR);
        }
    }

    /**
     * Get redirect URL based on user role
     */
    private static function getRedirectUrl($role) {
        switch ($role) {
            case ROLE_MASTER_ADMIN:
            case ROLE_ADMIN:
                return BASE_URL . 'admin/dashboard.php';
            case ROLE_BRANCH_ADMIN:
                return BASE_URL . 'branch-admin/dashboard.php';
            case ROLE_PATIENT:
                return BASE_URL . 'patient/dashboard.php';
            default:
                return BASE_URL . 'index.php';
        }
    }

    /**
     * Set remember me cookie
     */
    private static function setRememberMeCookie($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (86400 * 30); // 30 days
        
        setcookie('remember_token', $token, $expiry, '/');
        setcookie('remember_user', $userId, $expiry, '/');
        
        // Store token in database (you might want to create a remember_tokens table)
    }

    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
