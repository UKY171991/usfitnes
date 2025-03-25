<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


require_once 'db_connect.php';
require_once 'vendor/tcpdf/tcpdf.php'; // Adjust path as needed
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
if (isset($_GET['generate_pdf']) && !empty($_GET['result_id'])) {
    $result_id = $_GET['result_id'];
    
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
        WHERE trs.result_id = :result_id
    ");
    $stmt->execute(['result_id' => $result_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Pathology System');
        $pdf->SetTitle('Test Result Report');
        $pdf->SetSubject('Test Result');
        $pdf->SetHeaderData('', 0, 'Pathology System Report', 'Generated on ' . date('Y-m-d H:i:s'));
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setFont('helvetica', '', 12);

        // Add a page
        $pdf->AddPage();

        // HTML content for PDF
        $html = '
        <h1>Test Result Report</h1>
        <table border="1" cellpadding="4">
            <tr><td><strong>Result ID:</strong></td><td>' . htmlspecialchars($result['result_id']) . '</td></tr>
            <tr><td><strong>Patient Name:</strong></td><td>' . htmlspecialchars($result['patient_name']) . '</td></tr>
            <tr><td><strong>Date of Birth:</strong></td><td>' . htmlspecialchars($result['date_of_birth']) . '</td></tr>
            <tr><td><strong>Gender:</strong></td><td>' . htmlspecialchars($result['gender']) . '</td></tr>
            <tr><td><strong>Test Name:</strong></td><td>' . htmlspecialchars($result['test_name']) . '</td></tr>
            <tr><td><strong>Test Code:</strong></td><td>' . htmlspecialchars($result['test_code']) . '</td></tr>
            <tr><td><strong>Result Value:</strong></td><td>' . htmlspecialchars($result['result_value']) . '</td></tr>
            <tr><td><strong>Normal Range:</strong></td><td>' . htmlspecialchars($result['normal_range']) . '</td></tr>
            <tr><td><strong>Unit:</strong></td><td>' . htmlspecialchars($result['unit']) . '</td></tr>
            <tr><td><strong>Comments:</strong></td><td>' . htmlspecialchars($result['comments']) . '</td></tr>
            <tr><td><strong>Recorded By:</strong></td><td>' . htmlspecialchars($result['recorded_by_name']) . ' (' . htmlspecialchars($result['recorded_by_role']) . ')</td></tr>
            <tr><td><strong>Recorded At:</strong></td><td>' . htmlspecialchars($result['recorded_at']) . '</td></tr>
            <tr><td><strong>Request Date:</strong></td><td>' . htmlspecialchars($result['request_date']) . '</td></tr>
            <tr><td><strong>Ordered By:</strong></td><td>' . htmlspecialchars($result['ordered_by']) . '</td></tr>
        </table>';

        // Output HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdf->Output('test_result_' . $result_id . '.pdf', 'D'); // 'D' forces download
        exit();
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