<?php
/**
 * Report Controller
 * Handles test report operations and PDF generation
 */

class ReportController extends BaseController {

    /**
     * Display user's reports
     */
    public function index() {
        $this->requireAuth();
        
        $page = Sanitizer::integer($_GET['page'] ?? 1);
        $search = Sanitizer::string($_GET['search'] ?? '');
        $status = Sanitizer::string($_GET['status'] ?? '');
        
        $filters = [];
        
        if ($_SESSION['role'] === 'patient') {
            $filters['patient_id'] = $_SESSION['user_id'];
        }
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        if ($status) {
            $filters['status'] = $status;
        }

        $reportModel = new Report();
        $result = $reportModel->getReports($filters, $page, 10);
        
        $data = [
            'reports' => $result['reports'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total' => $result['total']
            ],
            'filters' => [
                'search' => $search,
                'status' => $status
            ]
        ];
        
        $this->render('patient/reports', $data);
    }

    /**
     * Display report details
     */
    public function show($reportId) {
        $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->getById($reportId);
        
        if (!$report) {
            $this->show404();
            return;
        }

        // Check if user can view this report
        if ($_SESSION['role'] === 'patient' && $report['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        $this->render('patient/report_details', ['report' => $report]);
    }

    /**
     * Download report PDF
     */
    public function download($reportId) {
        $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->getById($reportId);
        
        if (!$report) {
            $this->show404();
            return;
        }

        // Check if user can download this report
        if ($_SESSION['role'] === 'patient' && $report['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        if ($report['report_status'] !== REPORT_COMPLETED || empty($report['pdf_path'])) {
            $this->redirect('/report/' . $reportId . '?error=report_not_ready');
            return;
        }

        $filePath = __DIR__ . '/../../' . $report['pdf_path'];
        
        if (!file_exists($filePath)) {
            // Try to regenerate PDF
            $newFilePath = $reportModel->generatePDF($reportId);
            if ($newFilePath) {
                $filePath = $newFilePath;
            } else {
                $this->show404();
                return;
            }
        }

        // Log download
        Logger::activity($_SESSION['user_id'], 'Report Downloaded', [
            'report_id' => $reportId,
            'report_number' => $report['report_number']
        ]);

        // Send file
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="report_' . $report['report_number'] . '.pdf"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    }

    /**
     * View report PDF in browser
     */
    public function view($reportId) {
        $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->getById($reportId);
        
        if (!$report) {
            $this->show404();
            return;
        }

        // Check if user can view this report
        if ($_SESSION['role'] === 'patient' && $report['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        if ($report['report_status'] !== REPORT_COMPLETED || empty($report['pdf_path'])) {
            $this->redirect('/report/' . $reportId . '?error=report_not_ready');
            return;
        }

        $filePath = __DIR__ . '/../../' . $report['pdf_path'];
        
        if (!file_exists($filePath)) {
            // Try to regenerate PDF
            $newFilePath = $reportModel->generatePDF($reportId);
            if ($newFilePath) {
                $filePath = $newFilePath;
            } else {
                $this->show404();
                return;
            }
        }

        // Log view
        Logger::activity($_SESSION['user_id'], 'Report Viewed', [
            'report_id' => $reportId,
            'report_number' => $report['report_number']
        ]);

        // Send file for viewing
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="report_' . $report['report_number'] . '.pdf"');
        readfile($filePath);
    }

    /**
     * Admin: View all reports
     */
    public function adminIndex() {
        $this->requireRole(['admin', 'branch_admin', 'lab_technician']);
        
        $page = Sanitizer::integer($_GET['page'] ?? 1);
        $search = Sanitizer::string($_GET['search'] ?? '');
        $status = Sanitizer::string($_GET['status'] ?? '');
        $dateFrom = Sanitizer::string($_GET['date_from'] ?? '');
        $dateTo = Sanitizer::string($_GET['date_to'] ?? '');
        
        $filters = [];
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        if ($status) {
            $filters['status'] = $status;
        }

        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        $reportModel = new Report();
        $result = $reportModel->getReports($filters, $page, 20);
        $statistics = $reportModel->getStatistics($dateFrom, $dateTo);
        
        $data = [
            'reports' => $result['reports'],
            'statistics' => $statistics,
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total' => $result['total']
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ];
        
        $template = match($_SESSION['role']) {
            'admin' => 'admin/reports',
            'branch_admin' => 'branch_admin/reports',
            'lab_technician' => 'technician/reports',
            default => 'admin/reports'
        };
        
        $this->render($template, $data);
    }

    /**
     * Admin: Create/Edit report
     */
    public function edit($reportId) {
        $this->requireRole(['admin', 'branch_admin', 'lab_technician']);
        
        $reportModel = new Report();
        $report = $reportModel->getById($reportId);
        
        if (!$report) {
            $this->show404();
            return;
        }

        // Get test parameters for this report
        $testModel = new Test();
        $testParameters = $testModel->getTestParameters($report['test_id']);
        
        $data = [
            'report' => $report,
            'test_parameters' => $testParameters
        ];
        
        $template = match($_SESSION['role']) {
            'admin' => 'admin/report_edit',
            'branch_admin' => 'branch_admin/report_edit',
            'lab_technician' => 'technician/report_edit',
            default => 'admin/report_edit'
        };
        
        $this->render($template, $data);
    }

    /**
     * Admin: Update report
     */
    public function update($reportId) {
        $this->requireRole(['admin', 'branch_admin', 'lab_technician']);
        
        try {
            $this->validateCsrf();
            
            $reportModel = new Report();
            $report = $reportModel->getById($reportId);
            
            if (!$report) {
                $this->jsonError('Report not found');
                return;
            }

            $updateData = [
                'technician_id' => $_SESSION['user_id'],
                'notes' => Sanitizer::string($_POST['notes'] ?? '')
            ];

            // Get report results
            $results = [];
            if (isset($_POST['parameters']) && is_array($_POST['parameters'])) {
                foreach ($_POST['parameters'] as $paramId => $paramData) {
                    $results[] = [
                        'parameter_id' => $paramId,
                        'parameter_name' => Sanitizer::string($paramData['name']),
                        'value' => Sanitizer::string($paramData['value']),
                        'normal_range' => Sanitizer::string($paramData['normal_range'] ?? ''),
                        'unit' => Sanitizer::string($paramData['unit'] ?? ''),
                        'status' => Sanitizer::string($paramData['status'] ?? 'normal'),
                        'remarks' => Sanitizer::string($paramData['remarks'] ?? '')
                    ];
                }
            }

            // Update report
            $reportModel->update($reportId, $updateData);
            
            // Add results
            if (!empty($results)) {
                $reportModel->addResults($reportId, $results);
                $updateData['report_status'] = REPORT_PROCESSING;
            }

            $reportModel->update($reportId, $updateData);

            Logger::activity($_SESSION['user_id'], 'Report Updated', [
                'report_id' => $reportId,
                'results_count' => count($results)
            ]);

            $this->jsonSuccess(['message' => 'Report updated successfully']);
        } catch (Exception $e) {
            Logger::error("Report update failed: " . $e->getMessage());
            $this->jsonError('An error occurred while updating report');
        }
    }

    /**
     * Admin: Complete report and generate PDF
     */
    public function complete($reportId) {
        $this->requireRole(['admin', 'branch_admin', 'doctor']);
        
        try {
            $this->validateCsrf();
            
            $reportModel = new Report();
            $report = $reportModel->getById($reportId);
            
            if (!$report) {
                $this->jsonError('Report not found');
                return;
            }

            if ($report['report_status'] === REPORT_COMPLETED) {
                $this->jsonError('Report already completed');
                return;
            }

            $doctorId = $_SESSION['user_id'];
            $remarks = Sanitizer::string($_POST['remarks'] ?? '');

            // Update report as completed
            $updateData = [
                'doctor_id' => $doctorId,
                'report_status' => REPORT_COMPLETED,
                'notes' => $report['notes'] . "\n" . date('Y-m-d H:i:s') . " - Completed by Dr. " . $_SESSION['name']
            ];

            if ($remarks) {
                $updateData['notes'] .= "\nRemarks: " . $remarks;
            }

            $result = $reportModel->update($reportId, $updateData);

            if ($result) {
                // Generate PDF
                $pdfPath = $reportModel->generatePDF($reportId);
                
                if ($pdfPath) {
                    Logger::activity($_SESSION['user_id'], 'Report Completed', [
                        'report_id' => $reportId,
                        'pdf_generated' => true
                    ]);

                    $this->jsonSuccess([
                        'message' => 'Report completed and PDF generated successfully',
                        'pdf_available' => true
                    ]);
                } else {
                    Logger::error("PDF generation failed for report: " . $reportId);
                    $this->jsonSuccess([
                        'message' => 'Report completed but PDF generation failed',
                        'pdf_available' => false
                    ]);
                }
            } else {
                $this->jsonError('Failed to complete report');
            }
        } catch (Exception $e) {
            Logger::error("Report completion failed: " . $e->getMessage());
            $this->jsonError('An error occurred while completing report');
        }
    }

    /**
     * Admin: Regenerate PDF
     */
    public function regeneratePdf($reportId) {
        $this->requireRole(['admin', 'branch_admin']);
        
        try {
            $this->validateCsrf();
            
            $reportModel = new Report();
            $report = $reportModel->getById($reportId);
            
            if (!$report) {
                $this->jsonError('Report not found');
                return;
            }

            if ($report['report_status'] !== REPORT_COMPLETED) {
                $this->jsonError('Report must be completed before generating PDF');
                return;
            }

            $pdfPath = $reportModel->generatePDF($reportId);
            
            if ($pdfPath) {
                Logger::activity($_SESSION['user_id'], 'Report PDF Regenerated', [
                    'report_id' => $reportId
                ]);

                $this->jsonSuccess([
                    'message' => 'PDF regenerated successfully',
                    'pdf_path' => $pdfPath
                ]);
            } else {
                $this->jsonError('Failed to regenerate PDF');
            }
        } catch (Exception $e) {
            Logger::error("PDF regeneration failed: " . $e->getMessage());
            $this->jsonError('An error occurred while regenerating PDF');
        }
    }

    /**
     * Get report parameters (AJAX)
     */
    public function getParameters($reportId) {
        $this->requireRole(['admin', 'branch_admin', 'lab_technician']);
        
        $reportModel = new Report();
        $report = $reportModel->getById($reportId);
        
        if (!$report) {
            $this->jsonError('Report not found');
            return;
        }

        $testModel = new Test();
        $testParameters = $testModel->getTestParameters($report['test_id']);
        $reportResults = $reportModel->getReportResults($reportId);
        
        // Merge existing results with parameters
        $parameters = [];
        foreach ($testParameters as $param) {
            $existingResult = null;
            foreach ($reportResults as $result) {
                if ($result['parameter_id'] == $param['id']) {
                    $existingResult = $result;
                    break;
                }
            }
            
            $parameters[] = [
                'id' => $param['id'],
                'name' => $param['parameter_name'],
                'normal_range' => $param['normal_range'],
                'unit' => $param['unit'],
                'value' => $existingResult['value'] ?? '',
                'status' => $existingResult['status'] ?? 'normal',
                'remarks' => $existingResult['remarks'] ?? ''
            ];
        }
        
        $this->jsonSuccess([
            'parameters' => $parameters,
            'report' => $report
        ]);
    }

    /**
     * Print report (simple HTML view)
     */
    public function print($reportId) {
        $this->requireAuth();
        
        $reportModel = new Report();
        $report = $reportModel->getById($reportId);
        
        if (!$report) {
            $this->show404();
            return;
        }

        // Check if user can view this report
        if ($_SESSION['role'] === 'patient' && $report['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        if ($report['report_status'] !== REPORT_COMPLETED) {
            echo '<p>Report is not yet completed.</p>';
            return;
        }

        // Generate print-friendly HTML
        $html = $this->generatePrintHTML($report);
        echo $html;
    }

    /**
     * Generate print-friendly HTML
     */
    private function generatePrintHTML($report) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Lab Report - ' . htmlspecialchars($report['report_number']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; margin: 20px; }
                @media print { body { margin: 0; } }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .lab-name { font-size: 20px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
                .patient-info { margin: 20px 0; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
                .info-table td { padding: 5px; border: 1px solid #ddd; }
                .info-table .label { background: #f8f9fa; font-weight: bold; width: 150px; }
                .results-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
                .results-table th, .results-table td { padding: 6px; border: 1px solid #ddd; text-align: left; }
                .results-table th { background: #f8f9fa; font-weight: bold; }
                .abnormal { color: #e74c3c; font-weight: bold; }
                .signatures { margin-top: 40px; font-size: 10px; }
                .signature { display: inline-block; width: 45%; text-align: center; }
                .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; }
                @media print { 
                    .no-print { display: none; }
                    .page-break { page-break-before: always; }
                }
            </style>
            <script>
                window.onload = function() { window.print(); }
            </script>
        </head>
        <body>
            <div class="header">
                <div class="lab-name">US FITNESS LAB</div>
                <div>Laboratory Report</div>
            </div>

            <div class="patient-info">
                <table class="info-table">
                    <tr>
                        <td class="label">Patient Name:</td>
                        <td>' . htmlspecialchars($report['patient_name']) . '</td>
                        <td class="label">Report No:</td>
                        <td>' . htmlspecialchars($report['report_number']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Age/Gender:</td>
                        <td>' . $this->calculateAge($report['date_of_birth']) . ' / ' . htmlspecialchars($report['gender']) . '</td>
                        <td class="label">Report Date:</td>
                        <td>' . date('d-m-Y H:i', strtotime($report['report_date'])) . '</td>
                    </tr>
                </table>
            </div>

            <div class="test-results">
                <h4>Test: ' . htmlspecialchars($report['test_name']) . '</h4>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Value</th>
                            <th>Normal Range</th>
                            <th>Unit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';

        if (!empty($report['results'])) {
            foreach ($report['results'] as $result) {
                $statusClass = ($result['status'] === 'abnormal') ? 'abnormal' : '';
                $html .= '
                        <tr>
                            <td>' . htmlspecialchars($result['parameter_name']) . '</td>
                            <td class="' . $statusClass . '">' . htmlspecialchars($result['value']) . '</td>
                            <td>' . htmlspecialchars($result['normal_range'] ?? '-') . '</td>
                            <td>' . htmlspecialchars($result['unit'] ?? '-') . '</td>
                            <td class="' . $statusClass . '">' . strtoupper($result['status']) . '</td>
                        </tr>';
            }
        }

        $html .= '
                    </tbody>
                </table>
            </div>

            <div class="signatures">
                <div class="signature">
                    <div class="signature-line">Lab Technician</div>
                </div>
                <div class="signature" style="float: right;">
                    <div class="signature-line">Verified By</div>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Calculate age from date of birth
     */
    private function calculateAge($dateOfBirth) {
        if (!$dateOfBirth) return 'N/A';
        
        $today = new DateTime();
        $dob = new DateTime($dateOfBirth);
        $age = $today->diff($dob);
        
        return $age->y . ' years';
    }
}
?>
