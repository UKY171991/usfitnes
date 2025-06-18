<?php
/**
 * Test Controller
 * Handles test management operations
 */

class TestController extends BaseController {

    /**
     * Display all tests for patients
     */
    public function index() {
        $page = Sanitizer::integer($_GET['page'] ?? 1);
        $search = Sanitizer::string($_GET['search'] ?? '');
        $categoryId = Sanitizer::integer($_GET['category'] ?? 0);
        $priceMin = Sanitizer::float($_GET['price_min'] ?? 0);
        $priceMax = Sanitizer::float($_GET['price_max'] ?? 0);
        
        $filters = ['status' => 1]; // Only active tests
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        if ($categoryId) {
            $filters['category_id'] = $categoryId;
        }

        if ($priceMin > 0) {
            $filters['price_min'] = $priceMin;
        }

        if ($priceMax > 0) {
            $filters['price_max'] = $priceMax;
        }

        $testModel = new Test();
        $result = $testModel->getTests($filters, $page, 12);
        $categories = $testModel->getCategories();
        
        $data = [
            'tests' => $result['tests'],
            'categories' => $categories,
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total' => $result['total']
            ],
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
                'price_min' => $priceMin,
                'price_max' => $priceMax
            ]
        ];
        
        $this->render('patient/tests', $data);
    }

    /**
     * Display test details
     */
    public function show($testId) {
        $testModel = new Test();
        $test = $testModel->getById($testId);
        
        if (!$test || $test['status'] != 1) {
            $this->show404();
            return;
        }

        // Get available branches for this test
        $branchModel = new Branch();
        $branches = $branchModel->getActiveBranches();
        
        // Check which branches offer this test
        $availableBranches = [];
        foreach ($branches as $branch) {
            $branchTests = $branchModel->getBranchTests($branch['id']);
            foreach ($branchTests as $branchTest) {
                if ($branchTest['test_id'] == $testId) {
                    $branch['test_price'] = $branchTest['price'];
                    $availableBranches[] = $branch;
                    break;
                }
            }
        }

        $data = [
            'test' => $test,
            'branches' => $availableBranches
        ];
        
        $this->render('patient/test_details', $data);
    }

    /**
     * Search tests (AJAX)
     */
    public function search() {
        $query = Sanitizer::string($_GET['q'] ?? '');
        $limit = Sanitizer::integer($_GET['limit'] ?? 10);
        
        if (strlen($query) < 2) {
            $this->jsonSuccess(['tests' => []]);
            return;
        }

        $testModel = new Test();
        $tests = $testModel->search($query, $limit);
        
        $this->jsonSuccess(['tests' => $tests]);
    }

    /**
     * Get tests by category (AJAX)
     */
    public function getByCategory() {
        $categoryId = Sanitizer::integer($_GET['category_id'] ?? 0);
        
        if (!$categoryId) {
            $this->jsonError('Category ID is required');
            return;
        }

        $testModel = new Test();
        $tests = $testModel->getTestsByCategory($categoryId);
        
        $this->jsonSuccess(['tests' => $tests]);
    }

    /**
     * Admin: Display all tests
     */
    public function adminIndex() {
        $this->requireRole(['admin', 'branch_admin']);
        
        $page = Sanitizer::integer($_GET['page'] ?? 1);
        $search = Sanitizer::string($_GET['search'] ?? '');
        $categoryId = Sanitizer::integer($_GET['category'] ?? 0);
        $status = Sanitizer::integer($_GET['status'] ?? -1);
        
        $filters = [];
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        if ($categoryId) {
            $filters['category_id'] = $categoryId;
        }

        if ($status >= 0) {
            $filters['status'] = $status;
        }

        $testModel = new Test();
        $result = $testModel->getTests($filters, $page, 20);
        $categories = $testModel->getCategories();
        $statistics = $testModel->getStatistics();
        
        $data = [
            'tests' => $result['tests'],
            'categories' => $categories,
            'statistics' => $statistics,
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total' => $result['total']
            ],
            'filters' => [
                'search' => $search,
                'category' => $categoryId,
                'status' => $status
            ]
        ];
        
        $this->render('admin/tests', $data);
    }

    /**
     * Admin: Display create test form
     */
    public function create() {
        $this->requireRole(['admin']);
        
        $testModel = new Test();
        $categories = $testModel->getCategories();
        
        $data = [
            'categories' => $categories
        ];
        
        $this->render('admin/test_create', $data);
    }

    /**
     * Admin: Store new test
     */
    public function store() {
        $this->requireRole(['admin']);
        
        try {
            $this->validateCsrf();
            
            $testData = [
                'test_name' => Sanitizer::string($_POST['test_name']),
                'test_code' => Sanitizer::string($_POST['test_code']),
                'category_id' => Sanitizer::integer($_POST['category_id']),
                'description' => Sanitizer::string($_POST['description'] ?? ''),
                'price' => Sanitizer::float($_POST['price']),
                'sample_type' => Sanitizer::string($_POST['sample_type'] ?? ''),
                'instructions' => Sanitizer::string($_POST['instructions'] ?? ''),
                'normal_values' => Sanitizer::string($_POST['normal_values'] ?? ''),
                'status' => Sanitizer::integer($_POST['status'] ?? 1)
            ];

            // Validate required fields
            if (!$testData['test_name'] || !$testData['price'] || !$testData['category_id']) {
                $this->jsonError('Test name, price, and category are required');
                return;
            }

            // Check if test code already exists
            if ($testData['test_code']) {
                $existingTest = Database::getInstance()->prepare("SELECT id FROM tests WHERE test_code = ?");
                $existingTest->execute([$testData['test_code']]);
                if ($existingTest->fetch()) {
                    $this->jsonError('Test code already exists');
                    return;
                }
            }

            // Handle parameters
            $parameters = [];
            if (isset($_POST['parameters']) && is_array($_POST['parameters'])) {
                foreach ($_POST['parameters'] as $param) {
                    if (!empty($param['parameter_name'])) {
                        $parameters[] = [
                            'parameter_name' => Sanitizer::string($param['parameter_name']),
                            'normal_range' => Sanitizer::string($param['normal_range'] ?? ''),
                            'unit' => Sanitizer::string($param['unit'] ?? ''),
                            'sequence' => Sanitizer::integer($param['sequence'] ?? 0)
                        ];
                    }
                }
            }

            $testData['parameters'] = $parameters;

            $testModel = new Test();
            $testId = $testModel->create($testData);

            if ($testId) {
                Logger::activity($_SESSION['user_id'], 'Test Created', [
                    'test_id' => $testId,
                    'test_name' => $testData['test_name']
                ]);

                $this->jsonSuccess([
                    'test_id' => $testId,
                    'message' => 'Test created successfully'
                ]);
            } else {
                $this->jsonError('Failed to create test');
            }
        } catch (Exception $e) {
            Logger::error("Test creation failed: " . $e->getMessage());
            $this->jsonError('An error occurred while creating test');
        }
    }

    /**
     * Admin: Display edit test form
     */
    public function edit($testId) {
        $this->requireRole(['admin']);
        
        $testModel = new Test();
        $test = $testModel->getById($testId);
        
        if (!$test) {
            $this->show404();
            return;
        }

        $categories = $testModel->getCategories();
        
        $data = [
            'test' => $test,
            'categories' => $categories
        ];
        
        $this->render('admin/test_edit', $data);
    }

    /**
     * Admin: Update test
     */
    public function update($testId) {
        $this->requireRole(['admin']);
        
        try {
            $this->validateCsrf();
            
            $testModel = new Test();
            $existingTest = $testModel->getById($testId);
            
            if (!$existingTest) {
                $this->jsonError('Test not found');
                return;
            }

            $testData = [
                'test_name' => Sanitizer::string($_POST['test_name']),
                'test_code' => Sanitizer::string($_POST['test_code']),
                'category_id' => Sanitizer::integer($_POST['category_id']),
                'description' => Sanitizer::string($_POST['description'] ?? ''),
                'price' => Sanitizer::float($_POST['price']),
                'sample_type' => Sanitizer::string($_POST['sample_type'] ?? ''),
                'instructions' => Sanitizer::string($_POST['instructions'] ?? ''),
                'normal_values' => Sanitizer::string($_POST['normal_values'] ?? ''),
                'status' => Sanitizer::integer($_POST['status'] ?? 1)
            ];

            // Validate required fields
            if (!$testData['test_name'] || !$testData['price'] || !$testData['category_id']) {
                $this->jsonError('Test name, price, and category are required');
                return;
            }

            // Check if test code already exists for other tests
            if ($testData['test_code'] && $testData['test_code'] !== $existingTest['test_code']) {
                $stmt = Database::getInstance()->prepare("SELECT id FROM tests WHERE test_code = ? AND id != ?");
                $stmt->execute([$testData['test_code'], $testId]);
                if ($stmt->fetch()) {
                    $this->jsonError('Test code already exists');
                    return;
                }
            }

            // Handle parameters
            $parameters = [];
            if (isset($_POST['parameters']) && is_array($_POST['parameters'])) {
                foreach ($_POST['parameters'] as $param) {
                    if (!empty($param['parameter_name'])) {
                        $parameters[] = [
                            'parameter_name' => Sanitizer::string($param['parameter_name']),
                            'normal_range' => Sanitizer::string($param['normal_range'] ?? ''),
                            'unit' => Sanitizer::string($param['unit'] ?? ''),
                            'sequence' => Sanitizer::integer($param['sequence'] ?? 0)
                        ];
                    }
                }
            }

            $testData['parameters'] = $parameters;

            $result = $testModel->update($testId, $testData);

            if ($result) {
                Logger::activity($_SESSION['user_id'], 'Test Updated', [
                    'test_id' => $testId,
                    'test_name' => $testData['test_name']
                ]);

                $this->jsonSuccess(['message' => 'Test updated successfully']);
            } else {
                $this->jsonError('Failed to update test');
            }
        } catch (Exception $e) {
            Logger::error("Test update failed: " . $e->getMessage());
            $this->jsonError('An error occurred while updating test');
        }
    }

    /**
     * Admin: Delete test
     */
    public function delete($testId) {
        $this->requireRole(['admin']);
        
        try {
            $this->validateCsrf();
            
            $testModel = new Test();
            $test = $testModel->getById($testId);
            
            if (!$test) {
                $this->jsonError('Test not found');
                return;
            }

            $result = $testModel->delete($testId);

            if ($result['success']) {
                Logger::activity($_SESSION['user_id'], 'Test Deleted', [
                    'test_id' => $testId,
                    'test_name' => $test['test_name']
                ]);

                $this->jsonSuccess(['message' => 'Test deleted successfully']);
            } else {
                $this->jsonError($result['message'] ?? 'Failed to delete test');
            }
        } catch (Exception $e) {
            Logger::error("Test deletion failed: " . $e->getMessage());
            $this->jsonError('An error occurred while deleting test');
        }
    }

    /**
     * Admin: Get test parameters (AJAX)
     */
    public function getParameters($testId) {
        $this->requireRole(['admin', 'branch_admin', 'lab_technician']);
        
        $testModel = new Test();
        $parameters = $testModel->getTestParameters($testId);
        
        $this->jsonSuccess(['parameters' => $parameters]);
    }

    /**
     * Admin: Toggle test status
     */
    public function toggleStatus($testId) {
        $this->requireRole(['admin']);
        
        try {
            $this->validateCsrf();
            
            $testModel = new Test();
            $test = $testModel->getById($testId);
            
            if (!$test) {
                $this->jsonError('Test not found');
                return;
            }

            $newStatus = $test['status'] == 1 ? 0 : 1;
            $result = $testModel->update($testId, ['status' => $newStatus]);

            if ($result) {
                Logger::activity($_SESSION['user_id'], 'Test Status Changed', [
                    'test_id' => $testId,
                    'old_status' => $test['status'],
                    'new_status' => $newStatus
                ]);

                $this->jsonSuccess([
                    'status' => $newStatus,
                    'message' => 'Test status updated successfully'
                ]);
            } else {
                $this->jsonError('Failed to update test status');
            }
        } catch (Exception $e) {
            Logger::error("Test status toggle failed: " . $e->getMessage());
            $this->jsonError('An error occurred while updating test status');
        }
    }

    /**
     * Admin: Bulk operations
     */
    public function bulkAction() {
        $this->requireRole(['admin']);
        
        try {
            $this->validateCsrf();
            
            $action = Sanitizer::string($_POST['action']);
            $testIds = $_POST['test_ids'] ?? [];
            
            if (!is_array($testIds) || empty($testIds)) {
                $this->jsonError('No tests selected');
                return;
            }

            $testModel = new Test();
            $processedCount = 0;
            $errors = [];

            foreach ($testIds as $testId) {
                $testId = Sanitizer::integer($testId);
                if (!$testId) continue;

                $success = false;
                switch ($action) {
                    case 'activate':
                        $success = $testModel->update($testId, ['status' => 1]);
                        break;
                    case 'deactivate':
                        $success = $testModel->update($testId, ['status' => 0]);
                        break;
                    case 'delete':
                        $result = $testModel->delete($testId);
                        $success = $result['success'];
                        if (!$success) {
                            $errors[] = "Test ID {$testId}: " . ($result['message'] ?? 'Unknown error');
                        }
                        break;
                }

                if ($success) {
                    $processedCount++;
                }
            }

            Logger::activity($_SESSION['user_id'], 'Bulk Test Action', [
                'action' => $action,
                'processed_count' => $processedCount,
                'total_selected' => count($testIds)
            ]);

            $message = "Processed {$processedCount} out of " . count($testIds) . " tests";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', $errors);
            }

            $this->jsonSuccess(['message' => $message]);
        } catch (Exception $e) {
            Logger::error("Bulk test action failed: " . $e->getMessage());
            $this->jsonError('An error occurred while processing bulk action');
        }
    }

    /**
     * Export tests to CSV
     */
    public function export() {
        $this->requireRole(['admin', 'branch_admin']);
        
        try {
            $testModel = new Test();
            $tests = $testModel->getTests([], 1, 10000); // Get all tests
            
            $filename = 'tests_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($output, [
                'ID', 'Test Name', 'Test Code', 'Category', 'Price', 
                'Sample Type', 'Status', 'Parameters Count', 'Bookings Count', 
                'Created Date'
            ]);
            
            // CSV data
            foreach ($tests['tests'] as $test) {
                fputcsv($output, [
                    $test['id'],
                    $test['test_name'],
                    $test['test_code'],
                    $test['category_name'],
                    $test['price'],
                    $test['sample_type'],
                    $test['status'] ? 'Active' : 'Inactive',
                    $test['parameter_count'],
                    $test['booking_count'],
                    date('Y-m-d H:i:s', strtotime($test['created_at']))
                ]);
            }
            
            fclose($output);
            
            Logger::activity($_SESSION['user_id'], 'Tests Exported', [
                'count' => count($tests['tests'])
            ]);
            
        } catch (Exception $e) {
            Logger::error("Test export failed: " . $e->getMessage());
            $this->show500();
        }
    }
}
?>
