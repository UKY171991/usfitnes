<?php
require_once '../config.php';
header('Content-Type: application/json');

try {
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'get_counts':
            echo json_encode(getDashboardCounts());
            break;
            
        case 'get_recent_activities':
            $limit = (int)($_REQUEST['limit'] ?? 10);
            echo json_encode(getRecentActivities($limit));
            break;
            
        case 'get_recent_orders':
            $limit = (int)($_REQUEST['limit'] ?? 10);
            echo json_encode(getRecentOrders($limit));
            break;
            
        case 'get_monthly_stats':
            echo json_encode(getMonthlyStats());
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get dashboard counts
 */
function getDashboardCounts() {
    try {
        $conn = getDatabaseConnection();
        
        // Get total patients
        $stmt = $conn->query("SELECT COUNT(*) FROM patients");
        $total_patients = $stmt->fetchColumn();
        
        // Get today's orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM test_orders WHERE DATE(order_date) = CURDATE()");
        $stmt->execute();
        $todays_orders = $stmt->fetchColumn();
        
        // Get pending results
        $stmt = $conn->prepare("SELECT COUNT(*) FROM test_orders WHERE status IN ('pending', 'processing')");
        $stmt->execute();
        $pending_results = $stmt->fetchColumn();
        
        // Get total doctors
        $stmt = $conn->query("SELECT COUNT(*) FROM doctors");
        $total_doctors = $stmt->fetchColumn();
        
        return [
            'success' => true,
            'data' => [
                'total_patients' => (int)$total_patients,
                'todays_orders' => (int)$todays_orders,
                'pending_results' => (int)$pending_results,
                'total_doctors' => (int)$total_doctors
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get recent activities
 */
function getRecentActivities($limit = 10) {
    try {
        $conn = getDatabaseConnection();
        
        // Check if activity_logs table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'activity_logs'");
        if (!$stmt->fetch()) {
            // Create sample activities if no activity table exists
            $activities = [
                [
                    'action' => 'patient_created',
                    'details' => 'New patient John Doe registered',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'time_ago' => '2 hours ago'
                ],
                [
                    'action' => 'order_created',
                    'details' => 'Blood test order #12345 created',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                    'time_ago' => '4 hours ago'
                ],
                [
                    'action' => 'login',
                    'details' => 'User logged in from dashboard',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
                    'time_ago' => '6 hours ago'
                ]
            ];
        } else {
            // Fetch from activity logs table
            $stmt = $conn->prepare("
                SELECT 
                    action,
                    details,
                    created_at,
                    CASE 
                        WHEN created_at >= NOW() - INTERVAL 1 HOUR THEN CONCAT(TIMESTAMPDIFF(MINUTE, created_at, NOW()), ' minutes ago')
                        WHEN created_at >= NOW() - INTERVAL 1 DAY THEN CONCAT(TIMESTAMPDIFF(HOUR, created_at, NOW()), ' hours ago')
                        ELSE CONCAT(TIMESTAMPDIFF(DAY, created_at, NOW()), ' days ago')
                    END as time_ago
                FROM activity_logs 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [
            'success' => true,
            'data' => [
                'activities' => $activities
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get recent test orders
 */
function getRecentOrders($limit = 10) {
    try {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                o.id,
                o.order_number,
                o.order_date,
                o.status,
                p.name as patient_name,
                p.id as patient_id,
                d.name as doctor_name
            FROM test_orders o
            LEFT JOIN patients p ON o.patient_id = p.id
            LEFT JOIN doctors d ON o.doctor_id = d.id
            ORDER BY o.order_date DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => [
                'orders' => $orders
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Get monthly statistics for charts
 */
function getMonthlyStats() {
    try {
        $conn = getDatabaseConnection();
        
        // Get last 6 months data
        $months = [];
        $orders = [];
        $results = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-{$i} months"));
            $monthName = date('M Y', strtotime("-{$i} months"));
            $months[] = $monthName;
            
            // Get orders count for this month
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM test_orders 
                WHERE DATE_FORMAT(order_date, '%Y-%m') = ?
            ");
            $stmt->execute([$date]);
            $orders[] = (int)$stmt->fetchColumn();
            
            // Get completed results count for this month
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM test_orders 
                WHERE DATE_FORMAT(order_date, '%Y-%m') = ? 
                AND status = 'completed'
            ");
            $stmt->execute([$date]);
            $results[] = (int)$stmt->fetchColumn();
        }
        
        return [
            'success' => true,
            'data' => [
                'months' => $months,
                'orders' => $orders,
                'results' => $results
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>