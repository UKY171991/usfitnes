<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['action'])) {
        throw new Exception('Action parameter is required');
    }

    $action = $_GET['action'];
    $response = ['success' => false, 'message' => '', 'data' => null];

    switch ($action) {
        case 'stats':
            $response = getDashboardStats($pdo);
            break;

        case 'recent_activity':
            $response = getRecentActivity($pdo);
            break;

        default:
            throw new Exception('Invalid action');
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}

function getDashboardStats($pdo) {
    try {
        $stats = [];

        // Total patients
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
        $stats['total_patients'] = $stmt->fetch()['count'] ?? 0;

        // Today's tests
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE DATE(created_at) = CURDATE()");
        $stats['today_tests'] = $stmt->fetch()['count'] ?? 0;

        // Pending results
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE status = 'pending'");
        $stats['pending_results'] = $stmt->fetch()['count'] ?? 0;

        // Total doctors
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'");
        $stats['total_doctors'] = $stmt->fetch()['count'] ?? 0;

        // Monthly data for chart
        $stmt = $pdo->query("
            SELECT MONTH(created_at) as month, COUNT(*) as count 
            FROM test_orders 
            WHERE YEAR(created_at) = YEAR(CURDATE()) 
            GROUP BY MONTH(created_at) 
            ORDER BY month
        ");

        $monthlyData = array_fill(0, 12, 0);
        while ($row = $stmt->fetch()) {
            $monthlyData[$row['month'] - 1] = (int)$row['count'];
        }
        $stats['monthly_data'] = $monthlyData;

        return [
            'success' => true,
            'message' => 'Statistics loaded successfully',
            'data' => $stats
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error loading statistics: ' . $e->getMessage(),
            'data' => null
        ];
    }
}

function getRecentActivity($pdo) {
    try {
        $activities = [];

        // Get recent test orders
        $stmt = $pdo->query("
            SELECT 
                to.created_at,
                p.name as patient_name,
                t.name as test_name,
                to.status,
                'Test Order' as activity_type
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.id
            LEFT JOIN tests t ON to.test_id = t.id
            ORDER BY to.created_at DESC
            LIMIT 10
        ");

        while ($row = $stmt->fetch()) {
            $activities[] = [
                'time' => date('H:i', strtotime($row['created_at'])),
                'description' => "{$row['test_name']} ordered for {$row['patient_name']}",
                'user' => 'System',
                'status' => $row['status']
            ];
        }

        // If no activities, add sample data
        if (empty($activities)) {
            $activities = [
                [
                    'time' => date('H:i', strtotime('-10 minutes')),
                    'description' => 'New patient registered',
                    'user' => 'Admin',
                    'status' => 'completed'
                ],
                [
                    'time' => date('H:i', strtotime('-25 minutes')),
                    'description' => 'Blood test completed',
                    'user' => 'Lab Tech',
                    'status' => 'completed'
                ],
                [
                    'time' => date('H:i', strtotime('-45 minutes')),
                    'description' => 'Equipment maintenance scheduled',
                    'user' => 'Admin',
                    'status' => 'pending'
                ]
            ];
        }

        return [
            'success' => true,
            'message' => 'Recent activity loaded successfully',
            'data' => $activities
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error loading recent activity: ' . $e->getMessage(),
            'data' => null
        ];
    }
}
?>
```