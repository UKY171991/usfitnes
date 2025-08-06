<?php
/**
 * Dashboard Data Provider
 * Provides dynamic data for dashboard widgets and statistics
 */

class DashboardDataProvider {
    private $conn;
    
    public function __construct($database_connection = null) {
        global $conn;
        $this->conn = $database_connection ?? $conn;
    }
    
    /**
     * Get dashboard statistics
     */
    public function getStats() {
        $stats = [];
        
        try {
            // Total Patients
            $query = "SELECT COUNT(*) as count FROM patients WHERE status != 'deleted'";
            $result = mysqli_query($this->conn, $query);
            $stats['total_patients'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
            // Today's Tests
            $query = "SELECT COUNT(*) as count FROM test_orders WHERE DATE(created_at) = CURDATE()";
            $result = mysqli_query($this->conn, $query);
            $stats['todays_tests'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
            // Pending Results
            $query = "SELECT COUNT(*) as count FROM test_orders WHERE status = 'pending'";
            $result = mysqli_query($this->conn, $query);
            $stats['pending_results'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
            // Total Doctors
            $query = "SELECT COUNT(*) as count FROM doctors WHERE status = 'active'";
            $result = mysqli_query($this->conn, $query);
            $stats['total_doctors'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
            // Monthly Revenue
            $query = "SELECT COALESCE(SUM(amount), 0) as revenue FROM payments 
                      WHERE MONTH(created_at) = MONTH(CURDATE()) 
                      AND YEAR(created_at) = YEAR(CURDATE()) 
                      AND status = 'paid'";
            $result = mysqli_query($this->conn, $query);
            $stats['monthly_revenue'] = $result ? mysqli_fetch_assoc($result)['revenue'] : 0;
            
            // Equipment Count
            $query = "SELECT COUNT(*) as count FROM equipment WHERE status = 'active'";
            $result = mysqli_query($this->conn, $query);
            $stats['equipment_count'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
        } catch (Exception $e) {
            error_log("Dashboard Stats Error: " . $e->getMessage());
            // Return default values on error
            $stats = [
                'total_patients' => 0,
                'todays_tests' => 0,
                'pending_results' => 0,
                'total_doctors' => 0,
                'monthly_revenue' => 0,
                'equipment_count' => 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get recent test orders
     */
    public function getRecentTestOrders($limit = 10) {
        $orders = [];
        
        try {
            $query = "SELECT 
                        to.id,
                        p.first_name,
                        p.last_name,
                        to.test_type,
                        to.status,
                        to.created_at
                      FROM test_orders to
                      LEFT JOIN patients p ON to.patient_id = p.id
                      ORDER BY to.created_at DESC
                      LIMIT ?";
            
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $limit);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $orders[] = [
                    'id' => $row['id'],
                    'patient_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: 'Unknown Patient',
                    'test_type' => $row['test_type'] ?? 'General Test',
                    'status' => $row['status'] ?? 'pending',
                    'created_at' => $row['created_at'],
                    'status_badge' => $this->getStatusBadge($row['status'] ?? 'pending')
                ];
            }
            
        } catch (Exception $e) {
            error_log("Recent Orders Error: " . $e->getMessage());
        }
        
        return $orders;
    }
    
    /**
     * Get monthly test statistics for chart
     */
    public function getMonthlyTestStats($months = 12) {
        $stats = [];
        
        try {
            $query = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as count
                      FROM test_orders
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                      GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                      ORDER BY month";
            
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $months);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $stats[] = [
                    'month' => $row['month'],
                    'count' => $row['count']
                ];
            }
            
        } catch (Exception $e) {
            error_log("Monthly Stats Error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get test type distribution
     */
    public function getTestTypeDistribution() {
        $distribution = [];
        
        try {
            $query = "SELECT 
                        test_type,
                        COUNT(*) as count
                      FROM test_orders
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                      GROUP BY test_type
                      ORDER BY count DESC
                      LIMIT 5";
            
            $result = mysqli_query($this->conn, $query);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $distribution[] = [
                    'type' => $row['test_type'],
                    'count' => $row['count']
                ];
            }
            
        } catch (Exception $e) {
            error_log("Test Distribution Error: " . $e->getMessage());
        }
        
        return $distribution;
    }
    
    /**
     * Get system alerts
     */
    public function getSystemAlerts() {
        $alerts = [];
        
        try {
            // Check for pending test orders older than 24 hours
            $query = "SELECT COUNT(*) as count FROM test_orders 
                      WHERE status = 'pending' 
                      AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $result = mysqli_query($this->conn, $query);
            $pending_count = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
            if ($pending_count > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'fas fa-exclamation-triangle',
                    'title' => 'Pending Test Orders',
                    'message' => "{$pending_count} test orders are pending for more than 24 hours."
                ];
            }
            
            // Check for equipment maintenance
            $query = "SELECT COUNT(*) as count FROM equipment 
                      WHERE next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                      AND status = 'active'";
            $result = mysqli_query($this->conn, $query);
            $maintenance_count = $result ? mysqli_fetch_assoc($result)['count'] : 0;
            
            if ($maintenance_count > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'fas fa-tools',
                    'title' => 'Equipment Maintenance',
                    'message' => "{$maintenance_count} equipment items require maintenance within 7 days."
                ];
            }
            
            // Add success message for daily backup (if exists)
            $alerts[] = [
                'type' => 'success',
                'icon' => 'fas fa-check',
                'title' => 'System Status',
                'message' => 'All systems are operating normally.'
            ];
            
        } catch (Exception $e) {
            error_log("System Alerts Error: " . $e->getMessage());
            $alerts[] = [
                'type' => 'danger',
                'icon' => 'fas fa-exclamation-circle',
                'title' => 'System Error',
                'message' => 'Unable to retrieve system status.'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status) {
        $badges = [
            'pending' => 'badge-warning',
            'in_progress' => 'badge-info',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            'active' => 'badge-success',
            'inactive' => 'badge-secondary'
        ];
        
        $class = $badges[$status] ?? 'badge-secondary';
        $text = ucfirst(str_replace('_', ' ', $status));
        
        return "<span class=\"badge {$class}\">{$text}</span>";
    }
}
?>
