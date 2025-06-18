<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */

abstract class BaseController {
    protected $db;
    protected $request;
    protected $session;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->request = $_REQUEST;
        $this->session = $_SESSION;
        
        // Initialize authentication
        Auth::init();
    }

    /**
     * Render template
     */
    protected function render($template, $data = []) {
        extract($data);
        
        $templatePath = TEMPLATE_PATH . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template not found: $template");
        }
        
        include $templatePath;
    }

    /**
     * Render JSON response
     */
    protected function json($data, $httpCode = 200) {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url, $httpCode = 302) {
        http_response_code($httpCode);
        header("Location: $url");
        exit;
    }

    /**
     * Get request method
     */
    protected function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request is POST
     */
    protected function isPost() {
        return $this->getMethod() === 'POST';
    }

    /**
     * Check if request is GET
     */
    protected function isGet() {
        return $this->getMethod() === 'GET';
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get POST data
     */
    protected function getPost($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function getGet($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Get sanitized input
     */
    protected function input($key, $default = null, $type = 'string') {
        $value = $this->request[$key] ?? $default;
        
        if ($value === null) {
            return $default;
        }
        
        switch ($type) {
            case 'email':
                return Sanitizer::email($value);
            case 'phone':
                return Sanitizer::phone($value);
            case 'number':
                return Sanitizer::number($value);
            case 'int':
                return Sanitizer::number($value, 'int');
            case 'float':
                return Sanitizer::number($value, 'float');
            case 'url':
                return Sanitizer::url($value);
            default:
                return Sanitizer::string($value);
        }
    }

    /**
     * Validate CSRF token
     */
    protected function validateCSRF() {
        $token = $this->input('csrf_token');
        if (!Auth::verifyCSRFToken($token)) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
    }

    /**
     * Require authentication
     */
    protected function requireAuth() {
        if (!Auth::isLoggedIn()) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Authentication required'], 401);
            } else {
                $this->redirect(BASE_URL . 'login.php');
            }
        }
    }

    /**
     * Require specific role
     */
    protected function requireRole($role) {
        $this->requireAuth();
        
        if (!Auth::hasRole($role)) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Insufficient permissions'], 403);
            } else {
                $this->redirect(BASE_URL . 'unauthorized.php');
            }
        }
    }

    /**
     * Require any of the specified roles
     */
    protected function requireAnyRole($roles) {
        $this->requireAuth();
        
        if (!Auth::hasAnyRole($roles)) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Insufficient permissions'], 403);
            } else {
                $this->redirect(BASE_URL . 'unauthorized.php');
            }
        }
    }

    /**
     * Get current user
     */
    protected function getCurrentUser() {
        return Auth::getCurrentUser();
    }

    /**
     * Flash message to session
     */
    protected function flash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash messages
     */
    protected function getFlash($type = null) {
        if ($type) {
            $message = $_SESSION['flash'][$type] ?? null;
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    /**
     * Handle file upload
     */
    protected function handleFileUpload($fileKey, $uploadPath = null, $allowedTypes = null) {
        if (!isset($_FILES[$fileKey])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }

        $file = $_FILES[$fileKey];
        $uploadPath = $uploadPath ?? UPLOAD_PATH;
        
        // Validate file
        $validation = Security::validateFileUpload($file, $allowedTypes);
        if (!$validation['success']) {
            return $validation;
        }

        // Generate unique filename
        $fileInfo = pathinfo($file['name']);
        $filename = Security::cleanFilename($fileInfo['filename']) . '_' . time() . '.' . $fileInfo['extension'];
        $filepath = $uploadPath . $filename;

        // Create directory if it doesn't exist
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => $file['size']
            ];
        }

        return ['success' => false, 'message' => 'Failed to upload file'];
    }

    /**
     * Paginate results
     */
    protected function paginate($total, $page = 1, $limit = 10) {
        $pages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        
        return [
            'total' => $total,
            'pages' => $pages,
            'current_page' => $page,
            'limit' => $limit,
            'offset' => $offset,
            'has_prev' => $page > 1,
            'has_next' => $page < $pages,
            'prev_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $pages ? $page + 1 : null
        ];
    }
}
?>
