<?php
/**
 * Test Model Class
 * Handles test management operations
 */

class Test {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new test
     */
    public function create($testData) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO tests (test_name, test_code, category_id, description, 
                                 price, sample_type, instructions, normal_values, 
                                 status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $testData['test_name'],
                $testData['test_code'],
                $testData['category_id'],
                $testData['description'] ?? null,
                $testData['price'],
                $testData['sample_type'] ?? null,
                $testData['instructions'] ?? null,
                $testData['normal_values'] ?? null,
                $testData['status'] ?? 1
            ]);

            if ($result) {
                $testId = $this->db->lastInsertId();
                
                // Add test parameters if provided
                if (!empty($testData['parameters'])) {
                    $this->addTestParameters($testId, $testData['parameters']);
                }
                
                $this->db->commit();
                Logger::activity(null, 'Test Created', ['test_id' => $testId]);
                return $testId;
            }
            
            $this->db->rollback();
            return false;
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error("Failed to create test: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get test by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, tc.category_name
                FROM tests t
                LEFT JOIN test_categories tc ON t.category_id = tc.id
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);
            $test = $stmt->fetch();
            
            if ($test) {
                // Get test parameters
                $test['parameters'] = $this->getTestParameters($id);
            }
            
            return $test;
        } catch (Exception $e) {
            Logger::error("Failed to get test by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update test
     */
    public function update($id, $testData) {
        try {
            $this->db->beginTransaction();
            
            $setParts = [];
            $params = [];

            foreach ($testData as $field => $value) {
                if ($field !== 'id' && $field !== 'parameters') {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                }
            }

            $params[] = $id;
            
            $sql = "UPDATE tests SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result && isset($testData['parameters'])) {
                // Update test parameters
                $this->updateTestParameters($id, $testData['parameters']);
            }

            $this->db->commit();
            
            if ($result) {
                Logger::activity(null, 'Test Updated', ['test_id' => $id, 'data' => $testData]);
            }
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error("Failed to update test: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete test
     */
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            // Check if test has bookings
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE test_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Cannot delete test with existing bookings'];
            }
            
            // Delete test parameters
            $stmt = $this->db->prepare("DELETE FROM test_parameters WHERE test_id = ?");
            $stmt->execute([$id]);
            
            // Delete test
            $stmt = $this->db->prepare("DELETE FROM tests WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            
            if ($result) {
                Logger::activity(null, 'Test Deleted', ['test_id' => $id]);
            }
            return ['success' => $result];
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error("Failed to delete test: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get tests with pagination and filters
     */
    public function getTests($filters = [], $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $where = ['1=1'];
            $params = [];

            // Add filters
            if (!empty($filters['category_id'])) {
                $where[] = 't.category_id = ?';
                $params[] = $filters['category_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = 't.status = ?';
                $params[] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(t.test_name LIKE ? OR t.test_code LIKE ? OR t.description LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            if (isset($filters['price_min'])) {
                $where[] = 't.price >= ?';
                $params[] = $filters['price_min'];
            }

            if (isset($filters['price_max'])) {
                $where[] = 't.price <= ?';
                $params[] = $filters['price_max'];
            }

            $whereClause = implode(' AND ', $where);

            // Get total count
            $countSql = "
                SELECT COUNT(*) 
                FROM tests t
                LEFT JOIN test_categories tc ON t.category_id = tc.id
                WHERE $whereClause
            ";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get tests
            $sql = "
                SELECT t.*, tc.category_name,
                       (SELECT COUNT(*) FROM test_parameters WHERE test_id = t.id) as parameter_count,
                       (SELECT COUNT(*) FROM bookings WHERE test_id = t.id) as booking_count
                FROM tests t
                LEFT JOIN test_categories tc ON t.category_id = tc.id
                WHERE $whereClause 
                ORDER BY t.test_name 
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $tests = $stmt->fetchAll();

            return [
                'tests' => $tests,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            Logger::error("Failed to get tests: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active tests
     */
    public function getActiveTests($categoryId = null) {
        try {
            $where = ['t.status = 1'];
            $params = [];

            if ($categoryId) {
                $where[] = 't.category_id = ?';
                $params[] = $categoryId;
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT t.*, tc.category_name
                FROM tests t
                LEFT JOIN test_categories tc ON t.category_id = tc.id
                WHERE $whereClause
                ORDER BY t.test_name
            ");
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get active tests: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get test categories
     */
    public function getCategories() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM test_categories 
                WHERE status = 1 
                ORDER BY category_name
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get test categories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get tests by category
     */
    public function getTestsByCategory($categoryId) {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, tc.category_name
                FROM tests t
                LEFT JOIN test_categories tc ON t.category_id = tc.id
                WHERE t.category_id = ? AND t.status = 1
                ORDER BY t.test_name
            ");
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get tests by category: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add test parameters
     */
    private function addTestParameters($testId, $parameters) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO test_parameters (test_id, parameter_name, normal_range, 
                                           unit, sequence, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($parameters as $index => $param) {
                $stmt->execute([
                    $testId,
                    $param['parameter_name'],
                    $param['normal_range'] ?? null,
                    $param['unit'] ?? null,
                    $param['sequence'] ?? $index + 1,
                    $param['status'] ?? 1
                ]);
            }
            return true;
        } catch (Exception $e) {
            Logger::error("Failed to add test parameters: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get test parameters
     */
    public function getTestParameters($testId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM test_parameters 
                WHERE test_id = ? AND status = 1
                ORDER BY sequence, parameter_name
            ");
            $stmt->execute([$testId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get test parameters: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update test parameters
     */
    private function updateTestParameters($testId, $parameters) {
        try {
            // Delete existing parameters
            $stmt = $this->db->prepare("DELETE FROM test_parameters WHERE test_id = ?");
            $stmt->execute([$testId]);
            
            // Add new parameters
            if (!empty($parameters)) {
                $this->addTestParameters($testId, $parameters);
            }
            return true;
        } catch (Exception $e) {
            Logger::error("Failed to update test parameters: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get test statistics
     */
    public function getStatistics() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_tests,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_tests,
                    COUNT(DISTINCT category_id) as total_categories,
                    AVG(price) as average_price,
                    MIN(price) as min_price,
                    MAX(price) as max_price
                FROM tests
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get test statistics: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search tests
     */
    public function search($query, $limit = 10) {
        try {
            $search = '%' . $query . '%';
            $stmt = $this->db->prepare("
                SELECT t.*, tc.category_name
                FROM tests t
                LEFT JOIN test_categories tc ON t.category_id = tc.id
                WHERE t.status = 1 
                  AND (t.test_name LIKE ? OR t.test_code LIKE ? OR t.description LIKE ?)
                ORDER BY 
                    CASE 
                        WHEN t.test_name LIKE ? THEN 1
                        WHEN t.test_code LIKE ? THEN 2
                        ELSE 3
                    END,
                    t.test_name
                LIMIT ?
            ");
            $stmt->execute([$search, $search, $search, $search, $search, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to search tests: " . $e->getMessage());
            return false;
        }
    }
}
?>
