<?php
/**
 * CSRF Helper Functions
 */

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF hidden input field
 */
function csrf_field() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Get CSRF token for AJAX requests
 */
function csrf_token() {
    return generateCsrfToken();
}

/**
 * Generate CSRF meta tag for HTML head
 */
function csrf_meta() {
    $token = generateCsrfToken();
    return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
}
?>
