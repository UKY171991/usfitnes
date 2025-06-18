<?php
/**
 * Report Model Class
 * Handles test report operations
 */

class Report {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new report
     */
    public function create($reportData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO reports (booking_id, patient_id, test_id, report_number,
                                   report_status, technician_id, doctor_id, 
                                   report_date, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $reportData['booking_id'],
                $reportData['patient_id'],
                $reportData['test_id'],
                $reportData['report_number'] ?? $this->generateReportNumber(),
                $reportData['report_status'] ?? REPORT_PENDING,
                $reportData['technician_id'] ?? null,
                $reportData['doctor_id'] ?? null,
                $reportData['report_date'] ?? null,
                $reportData['notes'] ?? null
            ]);

            if ($result) {
                $reportId = $this->db->lastInsertId();
                Logger::activity($reportData['patient_id'], 'Report Created', ['report_id' => $reportId]);
                return $reportId;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::error("Failed to create report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get report by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, 
                       u.name as patient_name, u.email as patient_email, 
                       u.phone as patient_phone, u.date_of_birth, u.gender,
                       t.test_name, t.description as test_description,
                       b.booking_date, b.appointment_date,
                       br.branch_name, br.address as branch_address,
                       tech.name as technician_name,
                       doc.name as doctor_name
                FROM reports r
                JOIN users u ON r.patient_id = u.id
                JOIN tests t ON r.test_id = t.id
                JOIN bookings b ON r.booking_id = b.id
                JOIN branches br ON b.branch_id = br.id
                LEFT JOIN users tech ON r.technician_id = tech.id
                LEFT JOIN users doc ON r.doctor_id = doc.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $report = $stmt->fetch();
            
            if ($report) {
                // Get test results
                $report['results'] = $this->getReportResults($id);
            }
            
            return $report;
        } catch (Exception $e) {
            Logger::error("Failed to get report by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update report
     */
    public function update($id, $reportData) {
        try {
            $setParts = [];
            $params = [];

            foreach ($reportData as $field => $value) {
                if ($field !== 'id' && $field !== 'results') {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                }
            }

            $params[] = $id;
            
            $sql = "UPDATE reports SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                Logger::activity(null, 'Report Updated', ['report_id' => $id, 'data' => $reportData]);
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add report results
     */
    public function addResults($reportId, $results) {
        try {
            $this->db->beginTransaction();

            // Delete existing results
            $stmt = $this->db->prepare("DELETE FROM report_results WHERE report_id = ?");
            $stmt->execute([$reportId]);

            // Add new results
            $stmt = $this->db->prepare("
                INSERT INTO report_results (report_id, parameter_id, parameter_name,
                                          value, normal_range, unit, status, remarks, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($results as $result) {
                $stmt->execute([
                    $reportId,
                    $result['parameter_id'] ?? null,
                    $result['parameter_name'],
                    $result['value'],
                    $result['normal_range'] ?? null,
                    $result['unit'] ?? null,
                    $result['status'] ?? 'normal',
                    $result['remarks'] ?? null
                ]);
            }

            $this->db->commit();
            Logger::activity(null, 'Report Results Added', ['report_id' => $reportId]);
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error("Failed to add report results: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get report results
     */
    public function getReportResults($reportId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM report_results 
                WHERE report_id = ? 
                ORDER BY id
            ");
            $stmt->execute([$reportId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get report results: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate PDF report
     */
    public function generatePDF($reportId) {
        try {
            $report = $this->getById($reportId);
            if (!$report) {
                return false;
            }

            require_once __DIR__ . '/../lib/mpdf/vendor/autoload.php';
            
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 16,
                'margin_bottom' => 16,
                'margin_header' => 9,
                'margin_footer' => 9
            ]);

            // Generate HTML content
            $html = $this->generateReportHTML($report);
            
            $mpdf->WriteHTML($html);
            
            // Create reports directory if it doesn't exist
            $reportsDir = __DIR__ . '/../../reports/generated';
            if (!file_exists($reportsDir)) {
                mkdir($reportsDir, 0755, true);
            }
            
            $filename = 'report_' . $report['report_number'] . '_' . date('Y-m-d') . '.pdf';
            $filepath = $reportsDir . '/' . $filename;
            
            $mpdf->Output($filepath, 'F');
            
            // Update report with PDF path
            $this->update($reportId, [
                'pdf_path' => 'reports/generated/' . $filename,
                'report_status' => REPORT_COMPLETED,
                'report_date' => date('Y-m-d H:i:s')
            ]);
            
            Logger::activity(null, 'Report PDF Generated', [
                'report_id' => $reportId,
                'file' => $filename
            ]);
            
            return $filepath;
        } catch (Exception $e) {
            Logger::error("Failed to generate PDF report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate report HTML
     */
    private function generateReportHTML($report) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Lab Report - ' . htmlspecialchars($report['report_number']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .lab-name { font-size: 24px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
                .lab-details { font-size: 10px; color: #666; }
                .patient-info { margin: 20px 0; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .info-table td { padding: 5px; border: 1px solid #ddd; }
                .info-table .label { background: #f8f9fa; font-weight: bold; width: 150px; }
                .test-results { margin: 20px 0; }
                .results-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .results-table th, .results-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
                .results-table th { background: #f8f9fa; font-weight: bold; }
                .abnormal { color: #e74c3c; font-weight: bold; }
                .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
                .signatures { margin-top: 40px; }
                .signature { display: inline-block; width: 45%; text-align: center; }
                .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="lab-name">US FITNESS LAB</div>
                <div class="lab-details">
                    ' . htmlspecialchars($report['branch_address']) . '<br>
                    Email: info@usfitnesslab.com | Phone: +1-XXX-XXX-XXXX
                </div>
            </div>

            <div class="patient-info">
                <h3>Patient Information</h3>
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
                    <tr>
                        <td class="label">Phone:</td>
                        <td>' . htmlspecialchars($report['patient_phone']) . '</td>
                        <td class="label">Sample Date:</td>
                        <td>' . date('d-m-Y', strtotime($report['booking_date'])) . '</td>
                    </tr>
                </table>
            </div>

            <div class="test-results">
                <h3>Test: ' . htmlspecialchars($report['test_name']) . '</h3>
                ' . (!empty($report['test_description']) ? '<p><strong>Description:</strong> ' . htmlspecialchars($report['test_description']) . '</p>' : '') . '
                
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
                if (!empty($result['remarks'])) {
                    $html .= '
                        <tr>
                            <td colspan="5"><em>Remarks: ' . htmlspecialchars($result['remarks']) . '</em></td>
                        </tr>';
                }
            }
        }

        $html .= '
                    </tbody>
                </table>
            </div>

            ' . (!empty($report['notes']) ? '<div class="notes"><h4>Notes:</h4><p>' . nl2br(htmlspecialchars($report['notes'])) . '</p></div>' : '') . '

            <div class="signatures">
                <div class="signature">
                    <div class="signature-line">
                        Lab Technician<br>
                        ' . htmlspecialchars($report['technician_name'] ?? '') . '
                    </div>
                </div>
                <div class="signature" style="float: right;">
                    <div class="signature-line">
                        Verified By<br>
                        ' . htmlspecialchars($report['doctor_name'] ?? 'Dr. [Name]') . '
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>This is a computer generated report and does not require signature.</p>
                <p>For any queries, please contact: info@usfitnesslab.com</p>
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

    /**
     * Get reports with pagination and filters
     */
    public function getReports($filters = [], $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $where = ['1=1'];
            $params = [];

            // Add filters
            if (!empty($filters['patient_id'])) {
                $where[] = 'r.patient_id = ?';
                $params[] = $filters['patient_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = 'r.report_status = ?';
                $params[] = $filters['status'];
            }

            if (!empty($filters['date_from'])) {
                $where[] = 'DATE(r.created_at) >= ?';
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where[] = 'DATE(r.created_at) <= ?';
                $params[] = $filters['date_to'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(r.report_number LIKE ? OR u.name LIKE ? OR t.test_name LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $whereClause = implode(' AND ', $where);

            // Get total count
            $countSql = "
                SELECT COUNT(*) 
                FROM reports r
                JOIN users u ON r.patient_id = u.id
                JOIN tests t ON r.test_id = t.id
                WHERE $whereClause
            ";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get reports
            $sql = "
                SELECT r.*, 
                       u.name as patient_name, u.phone as patient_phone,
                       t.test_name,
                       b.booking_date
                FROM reports r
                JOIN users u ON r.patient_id = u.id
                JOIN tests t ON r.test_id = t.id
                JOIN bookings b ON r.booking_id = b.id
                WHERE $whereClause 
                ORDER BY r.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reports = $stmt->fetchAll();

            return [
                'reports' => $reports,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            Logger::error("Failed to get reports: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get patient reports
     */
    public function getPatientReports($patientId, $page = 1, $limit = 10) {
        return $this->getReports(['patient_id' => $patientId], $page, $limit);
    }

    /**
     * Generate unique report number
     */
    private function generateReportNumber() {
        $prefix = 'RPT';
        $date = date('Ymd');
        $random = sprintf('%04d', mt_rand(1, 9999));
        return $prefix . $date . $random;
    }

    /**
     * Get report statistics
     */
    public function getStatistics($dateFrom = null, $dateTo = null) {
        try {
            $where = ['1=1'];
            $params = [];

            if ($dateFrom) {
                $where[] = 'DATE(created_at) >= ?';
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $where[] = 'DATE(created_at) <= ?';
                $params[] = $dateTo;
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_reports,
                    SUM(CASE WHEN report_status = ? THEN 1 ELSE 0 END) as pending_reports,
                    SUM(CASE WHEN report_status = ? THEN 1 ELSE 0 END) as processing_reports,
                    SUM(CASE WHEN report_status = ? THEN 1 ELSE 0 END) as completed_reports,
                    SUM(CASE WHEN pdf_path IS NOT NULL THEN 1 ELSE 0 END) as reports_with_pdf
                FROM reports 
                WHERE $whereClause
            ");
            
            $queryParams = array_merge([
                REPORT_PENDING,
                REPORT_PROCESSING,
                REPORT_COMPLETED
            ], $params);
            
            $stmt->execute($queryParams);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get report statistics: " . $e->getMessage());
            return false;
        }
    }
}
?>
