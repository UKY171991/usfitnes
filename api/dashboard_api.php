<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

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
    $action = $_GET['action'] ?? 'stats';
    
    switch ($action) {
        case 'stats':
            getStats($pdo);
            break;
        case 'recent_activities':
            getRecentActivities($pdo);
            break;
        case 'charts':
            getChartData($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function getStats($pdo) {
    try {
        $stats = [];
        
        // Total patients
        $stmt = $pdo->query("SELECT COUNT(*) FROM patients");
        $stats['total_patients'] = $stmt->fetchColumn() ?: 0;
        
        // Today's tests
        $stmt = $pdo->query("SELECT COUNT(*) FROM test_orders WHERE DATE(order_date) = CURDATE()");
        $stats['today_tests'] = $stmt->fetchColumn() ?: 0;
        
        // Pending results
        $stmt = $pdo->query("SELECT COUNT(*) FROM test_orders WHERE status = 'pending'");
        $stats['pending_results'] = $stmt->fetchColumn() ?: 0;
        
        // Total doctors
        $stmt = $pdo->query("SELECT COUNT(*) FROM doctors");
        $stats['total_doctors'] = $stmt->fetchColumn() ?: 0;
        
        // Monthly revenue
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM test_orders WHERE YEAR(order_date) = YEAR(CURDATE()) AND MONTH(order_date) = MONTH(CURDATE())");
        $stats['monthly_revenue'] = $stmt->fetchColumn() ?: 0;
        
        // Tests completed today
        $stmt = $pdo->query("SELECT COUNT(*) FROM test_orders WHERE DATE(order_date) = CURDATE() AND status = 'completed'");
        $stats['tests_completed_today'] = $stmt->fetchColumn() ?: 0;
        
        // New patients this month
        $stmt = $pdo->query("SELECT COUNT(*) FROM patients WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");
        $stats['new_patients_month'] = $stmt->fetchColumn() ?: 0;
        
        // Average test cost
        $stmt = $pdo->query("SELECT COALESCE(AVG(total_amount), 0) FROM test_orders WHERE total_amount > 0");
        $stats['avg_test_cost'] = round($stmt->fetchColumn() ?: 0, 2);
        
        echo json_encode(['success' => true, 'data' => $stats]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getRecentActivities($pdo) {
    try {
        $activities = [];
        
        // Recent test orders
        $stmt = $pdo->query("
            SELECT 
                'test_order' as type,
                CONCAT('Test order #', order_id, ' for ', p.first_name, ' ', p.last_name) as description,
                order_date as created_at
            FROM test_orders to
            JOIN patients p ON to.patient_id = p.id
            ORDER BY order_date DESC
            LIMIT 5
        ");
        $testOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent patient registrations
        $stmt = $pdo->query("
            SELECT 
                'patient_registration' as type,
                CONCAT('New patient: ', first_name, ' ', last_name) as description,
                created_at
            FROM patients
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $newPatients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge and sort activities
        $activities = array_merge($testOrders, $newPatients);
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Take only top 10
        $activities = array_slice($activities, 0, 10);
        
        echo json_encode(['success' => true, 'data' => $activities]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getChartData($pdo) {
    try {
        $chartData = [];
        
        // Monthly test orders for the last 6 months
        $stmt = $pdo->query("
            SELECT 
                DATE_FORMAT(order_date, '%Y-%m') as month,
                COUNT(*) as count
            FROM test_orders
            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(order_date, '%Y-%m')
            ORDER BY month
        ");
        $monthlyTests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Test status distribution
        $stmt = $pdo->query("
            SELECT 
                status,
                COUNT(*) as count
            FROM test_orders
            GROUP BY status
        ");
        $statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top 5 tests by frequency (using test order items and tests table)
        $stmt = $pdo->query("
            SELECT 
                t.test_name,
                COUNT(*) as count
            FROM test_order_items toi
            JOIN tests t ON toi.test_id = t.id
            GROUP BY t.test_name
            ORDER BY count DESC
            LIMIT 5
        ");
        $topTests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $chartData = [
            'monthly_tests' => $monthlyTests,
            'status_distribution' => $statusDistribution,
            'top_tests' => $topTests
        ];
        
        echo json_encode(['success' => true, 'data' => $chartData]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
