<?php
/**
 * Secure Report Download Handler
 * Handles secure PDF report downloads with access control
 */

session_start();

// Load configuration and helpers
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/src/helpers/auth.php';
require_once __DIR__ . '/src/helpers/logger.php';
require_once __DIR__ . '/src/models/Database.php';
require_once __DIR__ . '/src/models/Report.php';
require_once __DIR__ . '/src/models/User.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    die('Unauthorized access');
}

// Get report ID and token from URL
$reportId = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;

// Validate input
if (!$reportId || !$token) {
    http_response_code(400);
    die('Invalid parameters');
}

try {
    $db = new Database();
    $reportModel = new Report($db);
    $userModel = new User($db);
    
    // Get report details
    $report = $reportModel->getById($reportId);
    
    if (!$report) {
        http_response_code(404);
        die('Report not found');
    }
    
    // Verify access permissions
    $currentUserId = $_SESSION['user_id'];
    $currentUserRole = $_SESSION['user_role'];
    
    $hasAccess = false;
    
    switch ($currentUserRole) {
        case USER_ROLE_PATIENT:
            // Patient can only access their own reports
            $hasAccess = ($report['patient_id'] == $currentUserId);
            break;
            
        case USER_ROLE_BRANCH_ADMIN:
            // Branch admin can access reports from their branch
            $hasAccess = ($report['branch_id'] == $_SESSION['branch_id']);
            break;
            
        case USER_ROLE_MASTER_ADMIN:
            // Master admin can access all reports
            $hasAccess = true;
            break;
    }
    
    if (!$hasAccess) {
        Logger::warning('Unauthorized report access attempt', [
            'user_id' => $currentUserId,
            'report_id' => $reportId,
            'user_role' => $currentUserRole
        ]);
        http_response_code(403);
        die('Access denied');
    }
    
    // Verify token (simple hash-based verification)
    $expectedToken = hash('sha256', $reportId . $report['created_at'] . 'USFitnessLabSecretKey');
    
    if (!hash_equals($expectedToken, $token)) {
        Logger::warning('Invalid report download token', [
            'user_id' => $currentUserId,
            'report_id' => $reportId,
            'provided_token' => $token
        ]);
        http_response_code(403);
        die('Invalid access token');
    }
    
    // Check if report status allows download
    if ($report['status'] !== REPORT_STATUS_READY && $report['status'] !== 'completed') {
        http_response_code(400);
        die('Report is not ready for download');
    }
    
    // Get file path
    $filePath = REPORT_PATH . $report['pdf_path'];
    
    // Check if file exists
    if (!file_exists($filePath)) {
        Logger::error('Report file not found', [
            'report_id' => $reportId,
            'file_path' => $filePath
        ]);
        http_response_code(404);
        die('Report file not found');
    }
    
    // Log successful access
    Logger::info('Report downloaded', [
        'user_id' => $currentUserId,
        'report_id' => $reportId,
        'file_path' => $report['pdf_path']
    ]);
    
    // Update report status to delivered if it's a patient downloading
    if ($currentUserRole === USER_ROLE_PATIENT) {
        $reportModel->updateStatus($reportId, REPORT_STATUS_DELIVERED);
    }
    
    // Set headers for file download
    $fileName = 'Report_' . $reportId . '_' . date('Y-m-d', strtotime($report['created_at'])) . '.pdf';
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Clear output buffer
    ob_clean();
    flush();
    
    // Output file
    readfile($filePath);
    exit;
    
} catch (Exception $e) {
    Logger::error('Report download error', [
        'error' => $e->getMessage(),
        'report_id' => $reportId,
        'user_id' => $_SESSION['user_id'] ?? null
    ]);
    
    http_response_code(500);
    die('Internal server error');
}
?>
