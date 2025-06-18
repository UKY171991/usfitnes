<?php
/**
 * Input Sanitization and Validation Helper
 * Provides security functions for input handling
 */

class Sanitizer {
    
    /**
     * Sanitize string input
     */
    public static function string($input, $allowHtml = false) {
        if ($allowHtml) {
            return htmlspecialchars_decode(filter_var($input, FILTER_SANITIZE_STRING));
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     */
    public static function email($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize phone number
     */
    public static function phone($phone) {
        return preg_replace('/[^0-9+\-\s\(\)]/', '', trim($phone));
    }

    /**
     * Sanitize numeric input
     */
    public static function number($input, $type = 'int') {
        switch ($type) {
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'int':
            default:
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        }
    }

    /**
     * Sanitize URL
     */
    public static function url($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    /**
     * Clean array of inputs
     */
    public static function array($array, $type = 'string') {
        $cleaned = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = self::array($value, $type);
            } else {
                switch ($type) {
                    case 'email':
                        $cleaned[$key] = self::email($value);
                        break;
                    case 'phone':
                        $cleaned[$key] = self::phone($value);
                        break;
                    case 'number':
                        $cleaned[$key] = self::number($value);
                        break;
                    case 'url':
                        $cleaned[$key] = self::url($value);
                        break;
                    default:
                        $cleaned[$key] = self::string($value);
                }
            }
        }
        return $cleaned;
    }
}

/**
 * Input Validation Class
 */
class Validator {
    
    private $errors = [];
    private $data = [];

    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Validate required field
     */
    public function required($field, $message = null) {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message ?? "Field '$field' is required";
        }
        return $this;
    }

    /**
     * Validate email format
     */
    public function email($field, $message = null) {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "Field '$field' must be a valid email";
        }
        return $this;
    }

    /**
     * Validate phone number
     */
    public function phone($field, $message = null) {
        if (!empty($this->data[$field])) {
            $phone = preg_replace('/[^0-9]/', '', $this->data[$field]);
            if (strlen($phone) < 10 || strlen($phone) > 15) {
                $this->errors[$field] = $message ?? "Field '$field' must be a valid phone number";
            }
        }
        return $this;
    }

    /**
     * Validate minimum length
     */
    public function minLength($field, $length, $message = null) {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?? "Field '$field' must be at least $length characters";
        }
        return $this;
    }

    /**
     * Validate maximum length
     */
    public function maxLength($field, $length, $message = null) {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?? "Field '$field' must not exceed $length characters";
        }
        return $this;
    }

    /**
     * Validate numeric field
     */
    public function numeric($field, $message = null) {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? "Field '$field' must be numeric";
        }
        return $this;
    }

    /**
     * Validate field matches another field
     */
    public function matches($field, $matchField, $message = null) {
        if ($this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? "Field '$field' must match '$matchField'";
        }
        return $this;
    }

    /**
     * Validate field is in array of values
     */
    public function in($field, $values, $message = null) {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message ?? "Field '$field' must be one of: " . implode(', ', $values);
        }
        return $this;
    }

    /**
     * Validate date format
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (!empty($this->data[$field])) {
            $date = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? "Field '$field' must be a valid date in format $format";
            }
        }
        return $this;
    }

    /**
     * Custom validation rule
     */
    public function custom($field, $callback, $message = null) {
        if (!empty($this->data[$field]) && !$callback($this->data[$field])) {
            $this->errors[$field] = $message ?? "Field '$field' is invalid";
        }
        return $this;
    }

    /**
     * Check if validation passes
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation fails
     */
    public function fails() {
        return !empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Get errors for specific field
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
}

/**
 * Security Helper Functions
 */
class Security {
    
    /**
     * Generate secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Escape output for HTML
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean filename for upload
     */
    public static function cleanFilename($filename) {
        // Remove special characters and spaces
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        // Remove leading/trailing underscores
        return trim($filename, '_');
    }

    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
        $allowedTypes = $allowedTypes ?? ALLOWED_FILE_TYPES;
        $maxSize = $maxSize ?? MAX_FILE_SIZE;

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error'];
        }

        // Check file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size too large'];
        }

        // Check file type
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (!isset($allowedMimes[$extension]) || $mimeType !== $allowedMimes[$extension]) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }

        return ['success' => true];
    }

    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
?>
