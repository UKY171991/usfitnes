<?php
/**
 * Dashboard API - AdminLTE3 AJAX Handler
 */

// Get the correct path to config.php
$config_path = dirname(__DIR__) . '/config.php';
require_once $config_path;

header('Content-Type: application/json');

try {
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'stats':
            echo json_encode(getDashboardStats());
            break;
            
        case 'monthly_stats':
            echo json_encode(getMonthlyStats());
            break;
            
        case 'test_types':
            echo json_encode(getTestTypesStats());
            break;
            
        case 'recent_orders':
            echo json_encode(getRecentOrders());
            break;
            
        case 'recent_activities':
            echo json_encode(getRecentActivities());
            break;
            
        default:
            echo json_encode(getDashboardStats());
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getDashboardStats() {
    try {
        $conn = getDatabaseConnection();
        
        // Get patients count
        $stmt = $conn->query("SELECT COUNT(*) FROM patients");
        $patients = $stmt->fetchColumn();
        
        // Get doctors count
        $stmt = $conn->query("SELECT COUNT(*) FROM doctors");
        $doctors = $stmt->fetchColumn();
        
        // Get test orders count
        $stmt = $conn->query("SELECT COUNT(*) FROM test_orders");
        $testOrders = $stmt->fetchColumn();
        
        // Get equipment count
        $stmt = $conn->query("SELECT COUNT(*) FROM equipment");
        $equipment = $stmt->fetchColumn();
        
        return [
            'success' => true,
            'data' => [
                'patients' => $patients,
                'doctors' => $doctors,
                'test_orders' => $testOrders,
                'equipment' => $equipment
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getMonthlyStats() {
    try {
        $conn = getDatabaseConnection();
        
        $months = [];
        $orders = [];
        $patients = [];
        
        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-{$i} months"));
            $monthName = date('M Y', strtotime("-{$i} months"));
            $months[] = $monthName;
            
            // Get orders count for this month
            $stmt = $conn->prepare("SELECT COUNT(*) FROM test_orders WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
            $stmt->execute([$date]);
            $orders[] = (int)$stmt->fetchColumn();
            
            // Get patients count for this month
            $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
            $stmt->execute([$date]);
            $patients[] = (int)$stmt->fetchColumn();
        }
        
        return [
            'success' => true,
            'data' => [
                'months' => $months,
                'orders' => $orders,
                'patients' => $patients
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getTestTypesStats() {
    try {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->query("
            SELECT test_type, COUNT(*) as count 
            FROM test_orders 
            GROUP BY test_type 
            ORDER BY count DESC 
            LIMIT 6
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $labels = [];
        $values = [];
        
        foreach ($results as $row) {
            $labels[] = ucwords(str_replace('_', ' ', $row['test_type']));
            $values[] = (int)$row['count'];
        }
        
        return [
            'success' => true,
            'data' => [
                'labels' => $labels,
                'values' => $values
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getRecentOrders() {
    try {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->query("
            SELECT 
                to.id,
                to.test_type,
                to.status,
                to.created_at,
                p.name as patient_name,
                d.name as doctor_name
            FROM test_orders to
            JOIN patients p ON to.patient_id = p.id
            JOIN doctors d ON to.doctor_id = d.id
            ORDER BY to.created_at DESC
            LIMIT 10
        ");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $orders
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getRecentActivities() {
    try {
        $conn = getDatabaseConnection();
        
        $activities = [];
        
        // Recent patient additions
        $stmt = $conn->query("
            SELECT 'patient_added' as type, 'New Patient Added' as title, 
                   CONCAT('Patient ', name, ' was added to the system') as description,
                   created_at
            FROM patients 
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Recent order creations
        $stmt = $conn->query("
            SELECT 'order_created' as type, 'Test Order Created' as title,
                   CONCAT('Test order #', to.id, ' created for patient ', p.name) as description,
                   to.created_at
            FROM test_orders to
            JOIN patients p ON to.patient_id = p.id
            ORDER BY to.created_at DESC 
            LIMIT 5
        ");
        $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Get only latest 10
        $activities = array_slice($activities, 0, 10);
        
        return [
            'success' => true,
            'data' => $activities
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>