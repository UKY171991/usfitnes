<?php
/**
 * Reports API - AdminLTE3 AJAX Handler
 */

// Get the correct path to config.php
$config_path = dirname(__DIR__) . '/config.php';
require_once $config_path;

header('Content-Type: application/json');

try {
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'stats':
            echo json_encode(getReportsStats());
            break;
            
        case 'list':
            echo json_encode(getReportsList());
            break;
            
        case 'generate':
            echo json_encode(generateReport());
            break;
            
        case 'charts':
            echo json_encode(getChartsData());
            break;
            
        case 'export':
            exportReports();
            break;
            
        default:
            echo json_encode(getReportsStats());
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getReportsStats() {
    try {
        $conn = getDatabaseConnection();
        
        // Get test statistics
        $totalTests = $conn->query("SELECT COUNT(*) FROM test_orders")->fetchColumn();
        $completedTests = $conn->query("SELECT COUNT(*) FROM test_orders WHERE status = 'completed'")->fetchColumn();
        $pendingTests = $conn->query("SELECT COUNT(*) FROM test_orders WHERE status = 'pending'")->fetchColumn();
        $urgentTests = $conn->query("SELECT COUNT(*) FROM test_orders WHERE status = 'in_progress'")->fetchColumn();
        
        return [
            'success' => true,
            'data' => [
                'totalTests' => $totalTests,
                'completedTests' => $completedTests,
                'pendingTests' => $pendingTests,
                'urgentTests' => $urgentTests
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getReportsList() {
    try {
        $conn = getDatabaseConnection();
        
        $draw = (int)($_POST['draw'] ?? 1);
        $start = (int)($_POST['start'] ?? 0);
        $length = (int)($_POST['length'] ?? 25);
        $search = $_POST['search']['value'] ?? '';
        
        $dateRange = $_POST['date_range'] ?? '';
        $reportType = $_POST['report_type'] ?? '';
        
        $where = "1=1";
        $params = [];
        
        // Apply filters
        if (!empty($search)) {
            $where .= " AND (to.order_number LIKE ? OR p.name LIKE ? OR to.test_type LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($dateRange) && $dateRange !== 'all') {
            switch ($dateRange) {
                case 'today':
                    $where .= " AND DATE(to.created_at) = CURDATE()";
                    break;
                case 'this_week':
                    $where .= " AND WEEK(to.created_at) = WEEK(CURDATE())";
                    break;
                case 'this_month':
                    $where .= " AND MONTH(to.created_at) = MONTH(CURDATE()) AND YEAR(to.created_at) = YEAR(CURDATE())";
                    break;
            }
        }
        
        if (!empty($reportType) && $reportType !== 'all') {
            if ($reportType === 'tests') {
                $where .= " AND to.test_type IS NOT NULL";
            }
        }
        
        // Get total count
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM test_orders to 
            LEFT JOIN patients p ON to.patient_id = p.id 
            LEFT JOIN doctors d ON to.doctor_id = d.id 
            WHERE {$where}
        ");
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        // Get data
        $sql = "
            SELECT to.id, to.order_number as report_id, to.created_at as date, 
                   'Test Report' as type, p.name as patient_name, to.test_type as test,
                   to.status, d.name as doctor_name
            FROM test_orders to
            LEFT JOIN patients p ON to.patient_id = p.id
            LEFT JOIN doctors d ON to.doctor_id = d.id
            WHERE {$where}
            ORDER BY to.created_at DESC 
            LIMIT {$start}, {$length}
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];
        
    } catch (Exception $e) {
        return [
            'draw' => 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => []
        ];
    }
}

function getChartsData() {
    try {
        $conn = getDatabaseConnection();
        
        // Test distribution chart
        $stmt = $conn->query("
            SELECT test_type, COUNT(*) as count 
            FROM test_orders 
            WHERE test_type IS NOT NULL 
            GROUP BY test_type 
            ORDER BY count DESC 
            LIMIT 10
        ");
        $testDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Monthly trends chart
        $stmt = $conn->query("
            SELECT 
                MONTH(created_at) as month,
                MONTHNAME(created_at) as month_name,
                COUNT(*) as count
            FROM test_orders 
            WHERE YEAR(created_at) = YEAR(CURDATE())
            GROUP BY MONTH(created_at), MONTHNAME(created_at)
            ORDER BY MONTH(created_at)
        ");
        $monthlyTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => [
                'testDistribution' => [
                    'labels' => array_column($testDistribution, 'test_type'),
                    'data' => array_column($testDistribution, 'count')
                ],
                'monthlyTrends' => [
                    'labels' => array_column($monthlyTrends, 'month_name'),
                    'data' => array_column($monthlyTrends, 'count')
                ]
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function generateReport() {
    try {
        $dateRange = $_POST['date_range'] ?? 'this_month';
        $fromDate = $_POST['from_date'] ?? '';
        $toDate = $_POST['to_date'] ?? '';
        $reportType = $_POST['report_type'] ?? 'all';
        
        // Generate report based on parameters
        $reportData = getReportData($dateRange, $fromDate, $toDate, $reportType);
        
        return [
            'success' => true,
            'message' => 'Report generated successfully',
            'data' => $reportData
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

function getReportData($dateRange, $fromDate, $toDate, $reportType) {
    $conn = getDatabaseConnection();
    
    $where = "1=1";
    $params = [];
    
    // Date filtering
    if ($dateRange === 'custom' && !empty($fromDate) && !empty($toDate)) {
        $where .= " AND DATE(to.created_at) BETWEEN ? AND ?";
        $params[] = $fromDate;
        $params[] = $toDate;
    } elseif ($dateRange !== 'all') {
        switch ($dateRange) {
            case 'today':
                $where .= " AND DATE(to.created_at) = CURDATE()";
                break;
            case 'this_week':
                $where .= " AND WEEK(to.created_at) = WEEK(CURDATE())";
                break;
            case 'this_month':
                $where .= " AND MONTH(to.created_at) = MONTH(CURDATE()) AND YEAR(to.created_at) = YEAR(CURDATE())";
                break;
        }
    }
    
    $sql = "
        SELECT to.*, p.name as patient_name, p.phone as patient_phone,
               d.name as doctor_name, d.specialization
        FROM test_orders to
        LEFT JOIN patients p ON to.patient_id = p.id
        LEFT JOIN doctors d ON to.doctor_id = d.id
        WHERE {$where}
        ORDER BY to.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function exportReports() {
    $format = $_GET['format'] ?? 'excel';
    $dateRange = $_GET['date_range'] ?? 'this_month';
    $reportType = $_GET['report_type'] ?? 'all';
    
    $data = getReportData($dateRange, '', '', $reportType);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reports_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order Number', 'Patient', 'Doctor', 'Test Type', 'Status', 'Date']);
        
        foreach ($data as $row) {
            fputcsv($output, [
                $row['order_number'],
                $row['patient_name'] ?: 'N/A',
                $row['doctor_name'] ?: 'N/A',
                $row['test_type'],
                $row['status'],
                date('Y-m-d H:i', strtotime($row['created_at']))
            ]);
        }
        
        fclose($output);
    } else {
        // For Excel/PDF export, you would typically use a library like PhpSpreadsheet
        echo json_encode([
            'success' => true,
            'message' => 'Export feature coming soon',
            'data' => $data
        ]);
    }
}
?>
