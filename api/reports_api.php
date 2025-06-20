<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleGet($pdo) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'generate':
            generateReport($pdo);
            break;
        case 'types':
            getReportTypes($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function generateReport($pdo) {
    try {
        $reportType = $_GET['report_type'] ?? '';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        // Validate inputs
        if (empty($reportType)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Report type is required']);
            return;
        }
        
        // Generate different reports based on type
        switch ($reportType) {
            case 'test_volume':
                generateTestVolumeReport($pdo, $dateFrom, $dateTo);
                break;
            case 'revenue':
                generateRevenueReport($pdo, $dateFrom, $dateTo);
                break;
            case 'doctor_performance':
                generateDoctorReport($pdo, $dateFrom, $dateTo);
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid report type']);
                break;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error generating report: ' . $e->getMessage()]);
    }
}

function getReportTypes($pdo) {
    $types = [
        ['id' => 'test_volume', 'name' => 'Test Volume Report'],
        ['id' => 'revenue', 'name' => 'Revenue Report'],
        ['id' => 'doctor_performance', 'name' => 'Doctor Performance Report']
    ];
    
    echo json_encode(['success' => true, 'data' => $types]);
}

function generateTestVolumeReport($pdo, $dateFrom, $dateTo) {
    try {
        // Get test volume by day
        $stmt = $pdo->prepare("
            SELECT 
                DATE(order_date) as date,
                COUNT(*) as test_count
            FROM test_orders
            WHERE order_date BETWEEN :date_from AND :date_to
            GROUP BY DATE(order_date)
            ORDER BY date
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $dailyVolume = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get test volume by test type
        $stmt = $pdo->prepare("
            SELECT 
                test_name,
                COUNT(*) as test_count
            FROM test_orders
            WHERE order_date BETWEEN :date_from AND :date_to
            GROUP BY test_name
            ORDER BY test_count DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $testTypeVolume = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get test volume by status
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as test_count
            FROM test_orders
            WHERE order_date BETWEEN :date_from AND :date_to
            GROUP BY status
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $statusVolume = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate total test count
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_count
            FROM test_orders
            WHERE order_date BETWEEN :date_from AND :date_to
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $totalCount = $stmt->fetchColumn();
        
        $data = [
            'daily_volume' => $dailyVolume,
            'test_type_volume' => $testTypeVolume,
            'status_volume' => $statusVolume,
            'total_count' => $totalCount,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function generateRevenueReport($pdo, $dateFrom, $dateTo) {
    try {
        // Get revenue by day
        $stmt = $pdo->prepare("
            SELECT 
                DATE(order_date) as date,
                SUM(total_amount) as revenue
            FROM test_orders
            WHERE order_date BETWEEN :date_from AND :date_to
            GROUP BY DATE(order_date)
            ORDER BY date
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $dailyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get revenue by test type
        $stmt = $pdo->prepare("
            SELECT 
                test_name,
                COUNT(*) as test_count,
                SUM(total_amount) as revenue,
                AVG(total_amount) as avg_price
            FROM test_orders
            WHERE order_date BETWEEN :date_from AND :date_to
            GROUP BY test_name
            ORDER BY revenue DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $testTypeRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate total revenue
        $stmt = $pdo->prepare("
            SELECT 
                SUM(total_amount) as total_revenue,
                COUNT(*) as total_count,
                AVG(total_amount) as avg_revenue_per_test
            FROM test_orders
            WHERE order_date BETWEEN :date_from AND :date_to
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $data = [
            'daily_revenue' => $dailyRevenue,
            'test_type_revenue' => $testTypeRevenue,
            'total_revenue' => $totalRevenue,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function generateDoctorReport($pdo, $dateFrom, $dateTo) {
    try {
        // Get test orders by doctor
        $stmt = $pdo->prepare("
            SELECT 
                d.doctor_id,
                d.first_name,
                d.last_name,
                COUNT(o.order_id) as test_count,
                SUM(o.total_amount) as total_revenue
            FROM test_orders o
            JOIN doctors d ON o.doctor_id = d.doctor_id
            WHERE o.order_date BETWEEN :date_from AND :date_to
            GROUP BY d.doctor_id, d.first_name, d.last_name
            ORDER BY test_count DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $doctorStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get test types by doctor
        $stmt = $pdo->prepare("
            SELECT 
                d.doctor_id,
                d.first_name,
                d.last_name,
                o.test_name,
                COUNT(o.order_id) as test_count
            FROM test_orders o
            JOIN doctors d ON o.doctor_id = d.doctor_id
            WHERE o.order_date BETWEEN :date_from AND :date_to
            GROUP BY d.doctor_id, d.first_name, d.last_name, o.test_name
            ORDER BY d.doctor_id, test_count DESC
        ");
        $stmt->execute([
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
        $doctorTestTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [
            'doctor_stats' => $doctorStats,
            'doctor_test_types' => $doctorTestTypes,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
