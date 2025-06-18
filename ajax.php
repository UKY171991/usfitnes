<?php
/**
 * Central AJAX Handler
 * Handles all AJAX requests for the US Fitness Lab application
 */

// Start session and load configurations
session_start();
require_once 'config/constants.php';
require_once 'config/db.php';
require_once 'src/helpers/auth.php';
require_once 'src/helpers/sanitize.php';
require_once 'src/helpers/logger.php';

// Load models
require_once 'src/models/Database.php';
require_once 'src/models/User.php';
require_once 'src/models/Test.php';
require_once 'src/models/Booking.php';
require_once 'src/models/Report.php';
require_once 'src/models/Payment.php';
require_once 'src/models/Branch.php';

// Load controllers
require_once 'src/controllers/BaseController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/TestController.php';
require_once 'src/controllers/BookingController.php';
require_once 'src/controllers/ReportController.php';
require_once 'src/controllers/PaymentController.php';

// Set content type for JSON responses
header('Content-Type: application/json');

// Enable CORS for development (remove in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests for AJAX
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // Get action from request
    $action = sanitize_input($_POST['action'] ?? '');
    
    if (empty($action)) {
        throw new Exception('Action not specified');
    }
    
    // Log AJAX request
    Logger::info("AJAX Request: $action", ['user_ip' => $_SERVER['REMOTE_ADDR']]);
    
    // Route to appropriate handler based on action
    switch ($action) {
        // Authentication actions
        case 'login':
            handleLogin();
            break;
            
        case 'register':
            handleRegister();
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        // User management
        case 'updateProfile':
            handleUpdateProfile();
            break;
            
        case 'changePassword':
            handleChangePassword();
            break;
            
        // Test management
        case 'getTests':
            handleGetTests();
            break;
            
        case 'getTestsByCategory':
            handleGetTestsByCategory();
            break;
            
        case 'addTest':
            handleAddTest();
            break;
            
        case 'updateTest':
            handleUpdateTest();
            break;
            
        case 'deleteTest':
            handleDeleteTest();
            break;
            
        // Booking management
        case 'createBooking':
            handleCreateBooking();
            break;
            
        case 'updateBooking':
            handleUpdateBooking();
            break;
            
        case 'cancelBooking':
            handleCancelBooking();
            break;
            
        case 'getBookings':
            handleGetBookings();
            break;
            
        case 'getBookingDetails':
            handleGetBookingDetails();
            break;
            
        // Payment actions
        case 'initPayment':
            handleInitPayment();
            break;
            
        case 'verifyPayment':
            handleVerifyPayment();
            break;
            
        case 'getPaymentStatus':
            handleGetPaymentStatus();
            break;
            
        // Report management
        case 'generateReport':
            handleGenerateReport();
            break;
            
        case 'updateReport':
            handleUpdateReport();
            break;
            
        case 'getReports':
            handleGetReports();
            break;
            
        case 'downloadReport':
            handleDownloadReport();
            break;
            
        // Dashboard data
        case 'getDashboardData':
            handleGetDashboardData();
            break;
            
        // Branch management
        case 'getBranches':
            handleGetBranches();
            break;
            
        case 'addBranch':
            handleAddBranch();
            break;
            
        case 'updateBranch':
            handleUpdateBranch();
            break;
            
        case 'deleteBranch':
            handleDeleteBranch();
            break;
            
        // Search functionality
        case 'searchPatients':
            handleSearchPatients();
            break;
            
        case 'searchTests':
            handleSearchTests();
            break;
            
        default:
            throw new Exception('Unknown action: ' . $action);
    }
    
} catch (Exception $e) {
    Logger::error("AJAX Error: " . $e->getMessage(), [
        'action' => $action ?? 'unknown',
        'user_ip' => $_SERVER['REMOTE_ADDR'],
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Authentication Handlers
 */
function handleLogin() {
    $controller = new UserController();
    $result = $controller->ajaxLogin();
    echo json_encode($result);
}

function handleRegister() {
    $controller = new UserController();
    $result = $controller->ajaxRegister();
    echo json_encode($result);
}

function handleLogout() {
    Auth::logout();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

/**
 * User Management Handlers
 */
function handleUpdateProfile() {
    requireAuth();
    $controller = new UserController();
    $result = $controller->ajaxUpdateProfile();
    echo json_encode($result);
}

function handleChangePassword() {
    requireAuth();
    $controller = new UserController();
    $result = $controller->ajaxChangePassword();
    echo json_encode($result);
}

/**
 * Test Management Handlers
 */
function handleGetTests() {
    $controller = new TestController();
    $result = $controller->ajaxGetTests();
    echo json_encode($result);
}

function handleGetTestsByCategory() {
    $controller = new TestController();
    $result = $controller->ajaxGetTestsByCategory();
    echo json_encode($result);
}

function handleAddTest() {
    requireAuth(['admin', 'branch_admin']);
    $controller = new TestController();
    $result = $controller->ajaxAddTest();
    echo json_encode($result);
}

function handleUpdateTest() {
    requireAuth(['admin', 'branch_admin']);
    $controller = new TestController();
    $result = $controller->ajaxUpdateTest();
    echo json_encode($result);
}

function handleDeleteTest() {
    requireAuth(['admin', 'branch_admin']);
    $controller = new TestController();
    $result = $controller->ajaxDeleteTest();
    echo json_encode($result);
}

/**
 * Booking Management Handlers
 */
function handleCreateBooking() {
    requireAuth();
    $controller = new BookingController();
    $result = $controller->ajaxCreateBooking();
    echo json_encode($result);
}

function handleUpdateBooking() {
    requireAuth(['admin', 'branch_admin']);
    $controller = new BookingController();
    $result = $controller->ajaxUpdateBooking();
    echo json_encode($result);
}

function handleCancelBooking() {
    requireAuth();
    $controller = new BookingController();
    $result = $controller->ajaxCancelBooking();
    echo json_encode($result);
}

function handleGetBookings() {
    requireAuth();
    $controller = new BookingController();
    $result = $controller->ajaxGetBookings();
    echo json_encode($result);
}

function handleGetBookingDetails() {
    requireAuth();
    $controller = new BookingController();
    $result = $controller->ajaxGetBookingDetails();
    echo json_encode($result);
}

/**
 * Payment Handlers
 */
function handleInitPayment() {
    requireAuth();
    $controller = new PaymentController();
    $result = $controller->ajaxInitPayment();
    echo json_encode($result);
}

function handleVerifyPayment() {
    requireAuth();
    $controller = new PaymentController();
    $result = $controller->ajaxVerifyPayment();
    echo json_encode($result);
}

function handleGetPaymentStatus() {
    requireAuth();
    $controller = new PaymentController();
    $result = $controller->ajaxGetPaymentStatus();
    echo json_encode($result);
}

/**
 * Report Management Handlers
 */
function handleGenerateReport() {
    requireAuth(['admin', 'branch_admin']);
    $controller = new ReportController();
    $result = $controller->ajaxGenerateReport();
    echo json_encode($result);
}

function handleUpdateReport() {
    requireAuth(['admin', 'branch_admin']);
    $controller = new ReportController();
    $result = $controller->ajaxUpdateReport();
    echo json_encode($result);
}

function handleGetReports() {
    requireAuth();
    $controller = new ReportController();
    $result = $controller->ajaxGetReports();
    echo json_encode($result);
}

function handleDownloadReport() {
    requireAuth();
    $controller = new ReportController();
    $result = $controller->ajaxDownloadReport();
    echo json_encode($result);
}

/**
 * Dashboard Handlers
 */
function handleGetDashboardData() {
    requireAuth();
    $user = Auth::getCurrentUser();
    
    switch ($user['role']) {
        case 'master_admin':
            $controller = new AdminController();
            $result = $controller->ajaxGetDashboardData();
            break;
        case 'branch_admin':
            $controller = new BranchAdminController();
            $result = $controller->ajaxGetDashboardData();
            break;
        case 'patient':
            $controller = new UserController();
            $result = $controller->ajaxGetPatientDashboardData();
            break;
        default:
            throw new Exception('Invalid user role');
    }
    
    echo json_encode($result);
}

/**
 * Branch Management Handlers
 */
function handleGetBranches() {
    $controller = new BranchController();
    $result = $controller->ajaxGetBranches();
    echo json_encode($result);
}

function handleAddBranch() {
    requireAuth(['master_admin']);
    $controller = new BranchController();
    $result = $controller->ajaxAddBranch();
    echo json_encode($result);
}

function handleUpdateBranch() {
    requireAuth(['master_admin']);
    $controller = new BranchController();
    $result = $controller->ajaxUpdateBranch();
    echo json_encode($result);
}

function handleDeleteBranch() {
    requireAuth(['master_admin']);
    $controller = new BranchController();
    $result = $controller->ajaxDeleteBranch();
    echo json_encode($result);
}

/**
 * Search Handlers
 */
function handleSearchPatients() {
    requireAuth(['admin', 'branch_admin']);
    $controller = new UserController();
    $result = $controller->ajaxSearchPatients();
    echo json_encode($result);
}

function handleSearchTests() {
    $controller = new TestController();
    $result = $controller->ajaxSearchTests();
    echo json_encode($result);
}

/**
 * Utility Functions
 */
function requireAuth($roles = null) {
    if (!Auth::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    if ($roles && !Auth::hasRole($roles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }
}

function sanitize_input($input) {
    return trim(htmlspecialchars(strip_tags($input)));
}
?>
