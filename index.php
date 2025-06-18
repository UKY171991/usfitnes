<?php
/**
 * Main Router - US Fitness Lab
 * Clean URL routing for patient interface and API endpoints
 * Follows the new project structure requirements
 */

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';

// Load models and helpers
require_once __DIR__ . '/src/models/Database.php';
require_once __DIR__ . '/src/models/User.php';
require_once __DIR__ . '/src/models/Branch.php';
require_once __DIR__ . '/src/models/Test.php';
require_once __DIR__ . '/src/models/Booking.php';
require_once __DIR__ . '/src/models/Report.php';
require_once __DIR__ . '/src/models/Payment.php';

// Load helpers
require_once __DIR__ . '/src/helpers/auth.php';
require_once __DIR__ . '/src/helpers/sanitize.php';
require_once __DIR__ . '/src/helpers/csrf.php';
require_once __DIR__ . '/src/helpers/logger.php';

// Load controllers
require_once __DIR__ . '/src/controllers/BaseController.php';
require_once __DIR__ . '/src/controllers/UserController.php';
require_once __DIR__ . '/src/controllers/TestController.php';
require_once __DIR__ . '/src/controllers/BookingController.php';
require_once __DIR__ . '/src/controllers/ReportController.php';
require_once __DIR__ . '/src/controllers/PaymentController.php';

// Initialize CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Parse the URL
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);
$path = str_replace($base_path, '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Route the request
router($path);

/**
 * Main router function
 */
function router($path) {
    // Split path into segments
    $segments = explode('/', $path);
    $route = $segments[0] ?? '';
    
    // Handle default route
    if (empty($route)) {
        // Check if user is logged in
        if (Auth::isLoggedIn()) {
            $role = $_SESSION['user_role'] ?? '';
            switch ($role) {
                case USER_ROLE_MASTER_ADMIN:
                    header('Location: ' . BASE_URL . 'admin/dashboard.php');
                    break;
                case USER_ROLE_BRANCH_ADMIN:
                    header('Location: ' . BASE_URL . 'branch-admin/dashboard.php');
                    break;
                case USER_ROLE_PATIENT:
                    header('Location: ' . BASE_URL . 'patient/dashboard');
                    break;
                default:
                    header('Location: ' . BASE_URL . 'patient/login');
                    break;
            }
        } else {
            header('Location: ' . BASE_URL . 'patient/login');
        }
        exit;
    }
    
    // Define routes
    switch ($route) {
        case 'patient':
            handlePatientRoutes($segments);
            break;
            
        case 'api':
            // API endpoints handled by ajax.php
            header('Location: ' . BASE_URL . 'ajax.php?' . http_build_query($_GET));
            break;
            
        case 'download-report':
            handleReportDownload($segments);
            break;
            
        default:
            // Legacy redirect or 404
            handleLegacyRedirect($route);
            break;
    }
}

/**
 * Handle patient interface routes
 */
function handlePatientRoutes($segments) {
    $action = $segments[1] ?? 'dashboard';
    $id = $segments[2] ?? null;
    
    $controller = new UserController();
    
    switch ($action) {
        case 'register':
            $controller->register();
            break;
            
        case 'login':
            $controller->login();
            break;
            
        case 'logout':
            $controller->logout();
            break;
            
        case 'dashboard':
            requirePatientAuth();
            $controller->dashboard();
            break;
            
        case 'book-test':
            requirePatientAuth();
            $controller->bookTest();
            break;
            
        case 'bookings':
            requirePatientAuth();
            $controller->bookings();
            break;
            
        case 'reports':
            requirePatientAuth();
            $controller->reports();
            break;
            
        case 'profile':
            requirePatientAuth();
            $controller->profile();
            break;
            
        case 'payment':
            requirePatientAuth();
            $paymentController = new PaymentController();
            if ($id) {
                $paymentController->processPayment($id);
            } else {
                $paymentController->paymentPage();
            }
            break;
            
        default:
            show404();
            break;
    }
}

/**
 * Handle secure report downloads
 */
function handleReportDownload($segments) {
    $reportId = $segments[1] ?? null;
    $token = $_GET['token'] ?? null;
    
    if (!$reportId || !$token) {
        http_response_code(400);
        die('Invalid request');
    }
    
    requirePatientAuth();
    
    $reportController = new ReportController();
    $reportController->downloadReport($reportId, $token);
}

/**
 * Handle legacy redirects
 */
function handleLegacyRedirect($route) {
    $redirects = [
        'login.php' => 'patient/login',
        'dashboard.php' => 'patient/dashboard',
        'patients.php' => 'patient/dashboard',
        'profile.php' => 'patient/profile',
    ];
    
    if (isset($redirects[$route])) {
        header('Location: ' . BASE_URL . $redirects[$route]);
        exit;
    }
    
    show404();
}

/**
 * Require patient authentication
 */
function requirePatientAuth() {
    if (!isLoggedIn() || $_SESSION['user_role'] !== USER_ROLE_PATIENT) {
        header('Location: ' . BASE_URL . 'patient/login');
        exit;
    }
}

/**
 * Show 404 page
 */
function show404() {
    http_response_code(404);
    $title = '404 - Page Not Found';
    $content = '
    <div class="container text-center mt-5">
        <h1 class="display-1">404</h1>
        <p class="fs-3"><span class="text-danger">Oops!</span> Page not found.</p>
        <p class="lead">The page you\'re looking for doesn\'t exist.</p>
        <a href="' . BASE_URL . '" class="btn btn-primary">Go Home</a>
    </div>';
    
    include __DIR__ . '/templates/layout.php';
}

/**
 * Render template
 */
function render($template, $data = []) {
    extract($data);
    
    $templatePath = __DIR__ . '/templates/' . $template . '.php';
    if (!file_exists($templatePath)) {
        show404();
        return;
    }
    
    ob_start();
    include $templatePath;
    $content = ob_get_clean();
    
    include __DIR__ . '/templates/layout.php';
}
?>