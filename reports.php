<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

// Check if TCPDF is available
if (!file_exists('vendor/tcpdf/tcpdf.php')) {
    die("TCPDF library not found at 'vendor/tcpdf/tcpdf.php'. Please install it.");
}
require_once 'vendor/tcpdf/tcpdf.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict to Admin, Doctor, Technician
if (!in_array($_SESSION['role'], ['Admin', 'Doctor', 'Technician'])) {
    header("Location: index3.php");
    exit();
}

// Generate PDF if requested
// Generate PDF if requested
if (isset($_GET['generate_pdf']) && !empty($_GET['result_id'])) {
    $result_id = $_GET['result_id'];

    try {
        // Fetch result details
        $stmt = $pdo->prepare("
            SELECT 
                trs.result_id, trs.result_value, trs.comments, trs.recorded_at,
                CONCAT(p.first_name, ' ', p.last_name) AS patient_name, p.date_of_birth, p.gender,
                t.test_name, t.test_code, t.normal_range, t.unit,
                CONCAT(s.first_name, ' ', s.last_name) AS recorded_by_name, s.role AS recorded_by_role,
                tr.request_date, tr.ordered_by
            FROM Test_Results trs
            JOIN Test_Requests tr ON trs.request_id = tr.request_id
            JOIN Patients p ON tr.patient_id = p.patient_id
            JOIN Tests_Catalog t ON tr.test_id = t.test_id
            JOIN Staff s ON trs.recorded_by = s.staff_id
            WHERE trs.result_id = 1
        ");
        $stmt->execute(['result_id' => $result_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            echo "<script>alert('No result found for result_id: $result_id');</script>";
            exit();
        }

        // Fetch sub-test details for CBC
        $sub_stmt = $pdo->prepare("
            SELECT sub_test_name, result_value, unit, normal_range
            FROM Test_Result_Details
            WHERE result_id = :result_id
        ");
        $sub_stmt->execute(['result_id' => $result_id]);
        $sub_tests = $sub_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Shiva Pathology Centre');
        $pdf->SetTitle('Pathological Report');
        $pdf->SetSubject('Test Result');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setFont('helvetica', '', 10);

        // Disable default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Add a page
        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'SHIVA PATHOLOGY CENTRE', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Sambhugan Jaipur', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Pathological Report', 0, 1, 'C');
        $pdf->Ln(2);

        // Patient Details (Left and Right Columns)
        $pdf->SetFont('helvetica', '', 10);
        $html = '
        <table cellpadding="2">
            <tr>
                <td width="50%">
                    <strong>Patient Name:</strong> ' . htmlspecialchars($result['patient_name']) . '<br>
                    <strong>Age:</strong> ' . (date('Y') - date('Y', strtotime($result['date_of_birth']))) . '<br>
                    <strong>Sex:</strong> ' . htmlspecialchars($result['gender']) . '<br>
                    <strong>Ref. By:</strong> ' . htmlspecialchars($result['ordered_by']) . '<br>
                    <strong>Address:</strong> -<br>
                </td>
                <td width="50%">
                    <strong>First Name:</strong> -<br>
                    <strong>Last Name:</strong> -<br>
                    <strong>Patient ID:</strong> -<br>
                    <strong>Sample ID:</strong> -<br>
                    <strong>Mode:</strong> -<br>
                    <strong>Time of Analysis:</strong> ' . htmlspecialchars($result['recorded_at']) . '<br>
                </td>
            </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Test Results Section
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'TEST', 0, 1, 'C');
        $pdf->Cell(0, 5, 'COMPLETE BLOOD COUNT', 0, 1, 'C', false, '', 0, false, 'T', 'T');
        $pdf->Ln(2);

        // CBC Table (Dynamic)
        $pdf->SetFont('helvetica', '', 9);
        $html = '
        <table border="1" cellpadding="3">
            <tr>
                <th width="40%"><strong>TEST</strong></th>
                <th width="20%"><strong>RESULT</strong></th>
                <th width="20%"><strong>UNIT</strong></th>
                <th width="20%"><strong>NORMAL VALUE</strong></th>
            </tr>';

        foreach ($sub_tests as $sub_test) {
            $html .= '
            <tr>
                <td>' . htmlspecialchars($sub_test['sub_test_name']) . '</td>
                <td>' . htmlspecialchars($sub_test['result_value']) . '</td>
                <td>' . htmlspecialchars($sub_test['unit']) . '</td>
                <td>' . htmlspecialchars($sub_test['normal_range']) . '</td>
            </tr>';
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Widal Test Section (Hardcoded for now)
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 5, 'WIDAL TEST', 0, 1, 'C', false, '', 0, false, 'T', 'T');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, 'Finding POSITIVE', 0, 1, 'L');
        $pdf->Ln(2);

        // Widal Test Table
        $html = '
        <table border="1" cellpadding="3">
            <tr>
                <th width="20%"><strong>Dilution</strong></th>
                <th width="16%"><strong>1:40</strong></th>
                <th width="16%"><strong>1:80</strong></th>
                <th width="16%"><strong>1:160</strong></th>
                <th width="16%"><strong>1:320</strong></th>
                <th width="16%"><strong>1:640</strong></th>
            </tr>
            <tr>
                <td>S. Typhi "O"</td>
                <td>+</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>S. Typhi "H"</td>
                <td>+</td>
                <td>+</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>S. Paratyphi "AH"</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
            <tr>
                <td>S. Paratyphi "BH"</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>
        </table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, '2024/08/11 11:05', 0, 1, 'L');
        $pdf->Cell(0, 5, 'Lab Incharge', 0, 1, 'R');

        // Output PDF
        ob_start();
        $pdf->Output('pathology_report_' . $result_id . '.pdf', 'D');
        ob_end_clean();
        exit();
    } catch (Exception $e) {
        die("PDF Generation Error: " . $e->getMessage());
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include('inc/head.php'); ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <?php include('inc/sidebar.php'); ?>
        <main class="app-main">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 mt-2">
                        <div class="col-sm-6">
                            <h3>Reports</h3>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search by patient or test">
                                <button class="btn btn-primary" onclick="loadReports(1)">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Report List</h3></div>
                                <div class="card-body">
                                    <table id="reportTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Patient</th>
                                                <th>Test</th>
                                                <th>Result Value</th>
                                                <th>Comments</th>
                                                <th>Recorded By</th>
                                                <th>Recorded At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reportTableBody"></tbody>
                                    </table>
                                </div>
                                <div class="card-footer clearfix">
                                    <ul class="pagination pagination-sm m-0 float-end" id="pagination"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Anything you want</div>
            <strong>Copyright © 2025 <a href="#" class="text-decoration-none">Pathology System</a>.</strong> All rights reserved.
        </footer>
    </div>
    <?php include('inc/js.php'); ?>
    <script>
        function loadReports(page = 1) {
            const searchQuery = document.getElementById('searchInput').value.trim();
            const url = `includes/fetch_reports.php?page=${page}${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('reportTableBody');
                    tbody.innerHTML = '';

                    if (data.reports.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No reports found</td></tr>';
                    } else {
                        data.reports.forEach(report => {
                            const row = `
                                <tr>
                                    <td>${report.result_id}</td>
                                    <td>${report.patient_name}</td>
                                    <td>${report.test_name}</td>
                                    <td>${report.result_value}</td>
                                    <td>${report.comments || '-'}</td>
                                    <td>${report.recorded_by_name}</td>
                                    <td>${report.recorded_at}</td>
                                    <td>
                                        <a href="reports.php?generate_pdf=true&result_id=${report.result_id}" class="btn btn-sm btn-success"><i class="bi bi-file-pdf"></i> Download PDF</a>
                                    </td>
                                </tr>`;
                            tbody.innerHTML += row;
                        });
                    }

                    const pagination = document.getElementById('pagination');
                    pagination.innerHTML = '';
                    if (data.total_pages > 1) {
                        pagination.innerHTML += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadReports(${data.current_page - 1}); return false;">«</a>
                        </li>`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadReports(${i}); return false;">${i}</a>
                            </li>`;
                        }
                        pagination.innerHTML += `<li class="page-item ${data.current_page === data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadReports(${data.current_page + 1}); return false;">»</a>
                        </li>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching reports:', error);
                    document.getElementById('reportTableBody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading reports</td></tr>';
                });
        }

        document.addEventListener('DOMContentLoaded', () => loadReports(1));

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                loadReports(1);
            }
        });

        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
</body>
</html>