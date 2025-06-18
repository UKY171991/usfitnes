<?php
/**
 * Main Entry Point and Router
 * Handles all incoming requests and routes them to appropriate controllers
 */

// Include configuration and constants
require_once 'config/constants.php';
require_once 'config/db.php';

// Include core classes
require_once 'src/models/Database.php';
require_once 'src/helpers/logger.php';
require_once 'src/helpers/auth.php';
require_once 'src/helpers/sanitize.php';
require_once 'src/helpers/csrf.php';
require_once 'src/models/User.php';
require_once 'src/models/Booking.php';
require_once 'src/models/Test.php';
require_once 'src/models/Report.php';
require_once 'src/models/Payment.php';
require_once 'src/models/Branch.php';

// Include controllers
require_once 'src/controllers/BaseController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/BookingController.php';
require_once 'src/controllers/PaymentController.php';
require_once 'src/controllers/ReportController.php';
require_once 'src/controllers/TestController.php';

// Initialize logger
Logger::init();

// Initialize database connection
try {
    Database::getInstance();
} catch (Exception $e) {
    Logger::error("Database initialization failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Initialize authentication
Auth::init();

// Get the requested URL
$url = $_GET['url'] ?? '';
$url = trim($url, '/');
$urlParts = explode('/', $url);

// Define routes
$routes = [
    '' => 'HomeController@index',
    'home' => 'HomeController@index',
    'login' => 'UserController@login',
    'logout' => 'UserController@logout',
    'register' => 'UserController@register',
    'profile' => 'UserController@profile',
    'dashboard' => 'UserController@dashboard',
    'change-password' => 'UserController@changePassword',
    
    // Patient auth routes
    'patient/register' => 'UserController@patientRegister',
    'patient/login' => 'UserController@patientLogin',
    'patient/authenticate' => 'UserController@patientLogin',
    
    // Patient dashboard routes
    'patient/dashboard' => 'UserController@patientDashboard',
    'patient/profile' => 'UserController@patientProfile',
    'patient/profile/update' => 'UserController@patientProfile',
    'patient/bookings' => 'UserController@patientBookings',
    'patient/reports' => 'UserController@patientReports',
    
    // Patient booking routes
    'patient/book-test' => 'BookingController@create',
    'booking/store' => 'BookingController@store',
    'patient/booking/{id}' => 'BookingController@show',
    'patient/booking/{id}/cancel' => 'BookingController@cancel',
    
    // Patient payment routes
    'patient/payment/{id}' => 'PaymentController@show',
    'payment/process' => 'PaymentController@process',
    'payment/callback' => 'PaymentController@callback',
    'payment/success' => 'PaymentController@success',
    'payment/failure' => 'PaymentController@failure',
    
    // Patient report routes
    'patient/report/{id}/view' => 'ReportController@view',
    'patient/report/{id}/download' => 'ReportController@download',
    'patient/report/{id}/share' => 'ReportController@share',
    
    // Test routes
    'tests' => 'TestController@index',
    'tests/category/{id}' => 'TestController@category',
    'ajax/tests-by-category' => 'TestController@getByCategory',
    
    // Branch routes
    'branches' => 'BranchController@index',
    'branches/{id}' => 'BranchController@show',
    
    // Admin routes
    'admin/dashboard' => 'AdminController@dashboard',
    'admin/users' => 'AdminController@users',
    'admin/branches' => 'AdminController@branches',
    'admin/tests' => 'AdminController@tests',
    'admin/reports' => 'AdminController@reports',
    
    // Branch Admin routes
    'branch-admin/dashboard' => 'BranchAdminController@dashboard',
    'branch-admin/patients' => 'BranchAdminController@patients',
    'branch-admin/bookings' => 'BranchAdminController@bookings',
    'branch-admin/reports' => 'BranchAdminController@reports',
    
    // API routes
    'api/tests' => 'ApiController@tests',
    'api/branches' => 'ApiController@branches',
    'api/bookings' => 'ApiController@bookings',
    
    // Payment routes
    'payment/initiate' => 'PaymentController@initiate',
    'payment/success' => 'PaymentController@success',
    'payment/webhook' => 'PaymentController@webhook',
    
    // Report download
    'download-report' => 'ReportController@download',
    
    // Static pages
    'about' => 'HomeController@about',
    'contact' => 'HomeController@contact',
    'privacy' => 'HomeController@privacy',
    'terms' => 'HomeController@terms',
];

// Handle routing
try {
    $route = $url;
    
    // Check if route exists
    if (!isset($routes[$route])) {
        // Try to find a dynamic route
        $found = false;
        
        // Handle admin existing routes (backward compatibility)
        if (strpos($url, 'admin/') === 0 && file_exists($url . '.php')) {
            include $url . '.php';
            exit;
        }
        
        // Handle branch-admin existing routes (backward compatibility)
        if (strpos($url, 'branch-admin/') === 0 && file_exists($url . '.php')) {
            include $url . '.php';
            exit;
        }
        
        if (!$found) {
            // Show 404 page
            http_response_code(404);
            include 'templates/errors/404.php';
            exit;
        }
    }
    
    // Parse controller and method
    list($controller, $method) = explode('@', $routes[$route]);
    
    // Include controller file
    $controllerFile = "src/controllers/{$controller}.php";
    if (!file_exists($controllerFile)) {
        throw new Exception("Controller not found: $controller");
    }
    
    require_once $controllerFile;
    
    // Check if controller class exists
    if (!class_exists($controller)) {
        throw new Exception("Controller class not found: $controller");
    }
    
    // Instantiate controller and call method
    $controllerInstance = new $controller();
    
    if (!method_exists($controllerInstance, $method)) {
        throw new Exception("Method not found: $controller@$method");
    }
    
    // Call the controller method
    $controllerInstance->$method();
    
} catch (Exception $e) {
    Logger::error("Routing error: " . $e->getMessage(), [
        'url' => $url,
        'route' => $route ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'ip' => Security::getClientIP()
    ]);
    
    if (DEBUG_MODE) {
        die("Error: " . $e->getMessage());
    } else {
        http_response_code(500);
        include 'templates/errors/500.php';
    }
}

// Clean up and log request
Logger::info("Request processed", [
    'url' => $url,
    'method' => $_SERVER['REQUEST_METHOD'],
    'user_id' => Auth::getCurrentUser()['id'] ?? null,
    'ip' => Security::getClientIP(),
    'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
]);
?>
