<?php
/**
 * Branch Model Class
 * Handles branch management operations
 */

class Branch {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new branch
     */
    public function create($branchData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO branches (branch_name, branch_code, address, city, state, 
                                    pincode, phone, email, manager_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $branchData['branch_name'],
                $branchData['branch_code'] ?? $this->generateBranchCode($branchData['branch_name']),
                $branchData['address'],
                $branchData['city'],
                $branchData['state'],
                $branchData['pincode'],
                $branchData['phone'],
                $branchData['email'],
                $branchData['manager_id'] ?? null,
                $branchData['status'] ?? 1
            ]);

            if ($result) {
                $branchId = $this->db->lastInsertId();
                Logger::activity(null, 'Branch Created', ['branch_id' => $branchId]);
                return $branchId;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::error("Failed to create branch: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get branch by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.*, u.name as manager_name, u.email as manager_email
                FROM branches b
                LEFT JOIN users u ON b.manager_id = u.id
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get branch by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update branch
     */
    public function update($id, $branchData) {
        try {
            $setParts = [];
            $params = [];

            foreach ($branchData as $field => $value) {
                if ($field !== 'id') {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                }
            }

            $params[] = $id;
            
            $sql = "UPDATE branches SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                Logger::activity(null, 'Branch Updated', ['branch_id' => $id, 'data' => $branchData]);
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update branch: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete branch
     */
    public function delete($id) {
        try {
            // Check if branch has bookings or users
            $stmt = $this->db->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM bookings WHERE branch_id = ?) as booking_count,
                    (SELECT COUNT(*) FROM users WHERE branch_id = ?) as user_count
            ");
            $stmt->execute([$id, $id]);
            $counts = $stmt->fetch();
            
            if ($counts['booking_count'] > 0 || $counts['user_count'] > 0) {
                return [
                    'success' => false, 
                    'message' => 'Cannot delete branch with existing bookings or users'
                ];
            }
            
            $stmt = $this->db->prepare("DELETE FROM branches WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                Logger::activity(null, 'Branch Deleted', ['branch_id' => $id]);
            }
            return ['success' => $result];
        } catch (Exception $e) {
            Logger::error("Failed to delete branch: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get all branches with pagination and filters
     */
    public function getBranches($filters = [], $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $where = ['1=1'];
            $params = [];

            // Add filters
            if (!empty($filters['status'])) {
                $where[] = 'b.status = ?';
                $params[] = $filters['status'];
            }

            if (!empty($filters['city'])) {
                $where[] = 'b.city = ?';
                $params[] = $filters['city'];
            }

            if (!empty($filters['state'])) {
                $where[] = 'b.state = ?';
                $params[] = $filters['state'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(b.branch_name LIKE ? OR b.branch_code LIKE ? OR b.city LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $whereClause = implode(' AND ', $where);

            // Get total count
            $countSql = "SELECT COUNT(*) FROM branches b WHERE $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get branches
            $sql = "
                SELECT b.*, u.name as manager_name,
                       (SELECT COUNT(*) FROM bookings WHERE branch_id = b.id) as total_bookings,
                       (SELECT COUNT(*) FROM users WHERE branch_id = b.id AND role = 'branch_admin') as staff_count
                FROM branches b
                LEFT JOIN users u ON b.manager_id = u.id
                WHERE $whereClause 
                ORDER BY b.branch_name 
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $branches = $stmt->fetchAll();

            return [
                'branches' => $branches,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            Logger::error("Failed to get branches: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active branches
     */
    public function getActiveBranches() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM branches 
                WHERE status = 1 
                ORDER BY branch_name
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get active branches: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get branch tests (tests available at a specific branch)
     */
    public function getBranchTests($branchId) {
        try {
            $stmt = $this->db->prepare("
                SELECT bt.*, t.test_name, t.description, t.sample_type,
                       tc.category_name
                FROM branch_tests bt
                JOIN tests t ON bt.test_id = t.id
                LEFT JOIN test_categories tc ON t.category_id = tc.id
                WHERE bt.branch_id = ? AND bt.status = 1 AND t.status = 1
                ORDER BY t.test_name
            ");
            $stmt->execute([$branchId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get branch tests: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add test to branch
     */
    public function addTest($branchId, $testId, $price = null) {
        try {
            // Check if test already exists for branch
            $stmt = $this->db->prepare("
                SELECT id FROM branch_tests 
                WHERE branch_id = ? AND test_id = ?
            ");
            $stmt->execute([$branchId, $testId]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Test already exists for this branch'];
            }

            // Get test price if not provided
            if ($price === null) {
                $stmt = $this->db->prepare("SELECT price FROM tests WHERE id = ?");
                $stmt->execute([$testId]);
                $test = $stmt->fetch();
                $price = $test['price'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO branch_tests (branch_id, test_id, price, status, created_at)
                VALUES (?, ?, ?, 1, NOW())
            ");
            
            $result = $stmt->execute([$branchId, $testId, $price]);
            
            if ($result) {
                Logger::activity(null, 'Test Added to Branch', [
                    'branch_id' => $branchId,
                    'test_id' => $testId,
                    'price' => $price
                ]);
            }
            
            return ['success' => $result];
        } catch (Exception $e) {
            Logger::error("Failed to add test to branch: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Update branch test price
     */
    public function updateTestPrice($branchId, $testId, $price) {
        try {
            $stmt = $this->db->prepare("
                UPDATE branch_tests 
                SET price = ?, updated_at = NOW()
                WHERE branch_id = ? AND test_id = ?
            ");
            
            $result = $stmt->execute([$price, $branchId, $testId]);
            
            if ($result) {
                Logger::activity(null, 'Branch Test Price Updated', [
                    'branch_id' => $branchId,
                    'test_id' => $testId,
                    'price' => $price
                ]);
            }
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update branch test price: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove test from branch
     */
    public function removeTest($branchId, $testId) {
        try {
            // Check if test has bookings
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE branch_id = ? AND test_id = ?
            ");
            $stmt->execute([$branchId, $testId]);
            
            if ($stmt->fetchColumn() > 0) {
                return [
                    'success' => false, 
                    'message' => 'Cannot remove test with existing bookings'
                ];
            }

            $stmt = $this->db->prepare("
                DELETE FROM branch_tests 
                WHERE branch_id = ? AND test_id = ?
            ");
            
            $result = $stmt->execute([$branchId, $testId]);
            
            if ($result) {
                Logger::activity(null, 'Test Removed from Branch', [
                    'branch_id' => $branchId,
                    'test_id' => $testId
                ]);
            }
            
            return ['success' => $result];
        } catch (Exception $e) {
            Logger::error("Failed to remove test from branch: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get branch statistics
     */
    public function getStatistics($branchId = null) {
        try {
            $where = ['1=1'];
            $params = [];

            if ($branchId) {
                $where[] = 'id = ?';
                $params[] = $branchId;
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_branches,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_branches,
                    COUNT(DISTINCT state) as states_covered,
                    COUNT(DISTINCT city) as cities_covered
                FROM branches 
                WHERE $whereClause
            ");
            
            $stmt->execute($params);
            $stats = $stmt->fetch();

            // Get additional stats if specific branch
            if ($branchId) {
                $stmt = $this->db->prepare("
                    SELECT 
                        (SELECT COUNT(*) FROM bookings WHERE branch_id = ?) as total_bookings,
                        (SELECT COUNT(*) FROM bookings WHERE branch_id = ? AND DATE(booking_date) = CURDATE()) as today_bookings,
                        (SELECT COUNT(*) FROM branch_tests WHERE branch_id = ? AND status = 1) as available_tests,
                        (SELECT COUNT(*) FROM users WHERE branch_id = ? AND role = 'branch_admin') as staff_count
                ");
                $stmt->execute([$branchId, $branchId, $branchId, $branchId]);
                $branchStats = $stmt->fetch();
                $stats = array_merge($stats, $branchStats);
            }

            return $stats;
        } catch (Exception $e) {
            Logger::error("Failed to get branch statistics: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate unique branch code
     */
    private function generateBranchCode($branchName) {
        $code = strtoupper(substr($branchName, 0, 3));
        $number = sprintf('%03d', mt_rand(1, 999));
        
        // Check if code exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM branches WHERE branch_code = ?");
        $stmt->execute([$code . $number]);
        
        if ($stmt->fetchColumn() > 0) {
            // Generate random code if exists
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 3));
            $number = sprintf('%03d', mt_rand(1, 999));
        }
        
        return $code . $number;
    }

    /**
     * Get cities where branches are located
     */
    public function getCities() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT city FROM branches 
                WHERE status = 1 
                ORDER BY city
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            Logger::error("Failed to get cities: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get states where branches are located
     */
    public function getStates() {
        try {
            $stmt = $this->db->prepare("
                SELECT DISTINCT state FROM branches 
                WHERE status = 1 
                ORDER BY state
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            Logger::error("Failed to get states: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search branches
     */
    public function search($query, $limit = 10) {
        try {
            $search = '%' . $query . '%';
            $stmt = $this->db->prepare("
                SELECT * FROM branches
                WHERE status = 1 
                  AND (branch_name LIKE ? OR branch_code LIKE ? OR city LIKE ? OR address LIKE ?)
                ORDER BY 
                    CASE 
                        WHEN branch_name LIKE ? THEN 1
                        WHEN city LIKE ? THEN 2
                        ELSE 3
                    END,
                    branch_name
                LIMIT ?
            ");
            $stmt->execute([
                $search, $search, $search, $search, 
                $search, $search, $limit
            ]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to search branches: " . $e->getMessage());
            return false;
        }
    }
}
?>
