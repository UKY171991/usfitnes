<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require_once 'db_connect.php';

// Check if TCPDF is available
if (!file_exists('vendor/tcpdf/tcpdf.php')) {
    die("TCPDF library not found at 'vendor/tcpdf/tcpdf.php'. Please install it.");
}
require_once 'vendor/tcpdf/tcpdf.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true
]);

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

// Only allow Admin, Doctor, and Technician roles
$allowed_roles = ['Admin', 'Doctor', 'Technician'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generate PDF if requested
if (isset($_GET['generate_pdf']) && isset($_GET['result_id'])) {
    try {
        // Validate CSRF token
        if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        // Validate result_id
        $result_id = filter_var($_GET['result_id'], FILTER_VALIDATE_INT);
        if ($result_id === false) {
            throw new Exception('Invalid result ID');
        }

        // Rate limiting check
        $rate_key = "pdf_gen_" . $_SESSION['user_id'];
        if (apcu_exists($rate_key)) {
            $count = apcu_fetch($rate_key);
            if ($count > 10) { // Max 10 PDFs per minute
                throw new Exception('Rate limit exceeded. Please try again later.');
            }
            apcu_inc($rate_key);
        } else {
            apcu_store($rate_key, 1, 60); // 1 minute expiry
        }

        // Fetch test result with prepared statement
        $stmt = $conn->prepare("
            SELECT r.*, t.test_name, t.normal_range, t.unit, 
                   p.patient_name, p.age, p.gender, p.contact,
                   u.name as recorded_by_name
            FROM test_results r
            JOIN tests t ON r.test_id = t.test_id
            JOIN patients p ON r.patient_id = p.patient_id
            JOIN users u ON r.recorded_by = u.user_id
            WHERE r.result_id = ?
        ");
        $stmt->bind_param("i", $result_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            throw new Exception('Test result not found');
        }

        // Create PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information and security
        $pdf->SetCreator('Pathology System');
        $pdf->SetAuthor('Pathology Lab');
        $pdf->SetTitle('Test Report - ' . $result['patient_name']);
        $pdf->SetProtection(['print', 'copy'], '', 'userpass', 128);

        // Set header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterData(array(0,64,0), array(0,64,128));

        // Add a page
        $pdf->AddPage();

        // Set content security headers
        header('Content-Security-Policy: default-src \'self\'');
        header('X-Content-Type-Options: nosniff');

        // Generate PDF content
        $html = '
        <h1 style="text-align: center;">Pathology Test Report</h1>
        <hr>
        <h3>Patient Information</h3>
        <table border="1" cellpadding="5">
            <tr>
                <th>Name</th>
                <td>' . htmlspecialchars($result['patient_name']) . '</td>
                <th>Age</th>
                <td>' . htmlspecialchars($result['age']) . '</td>
            </tr>
            <tr>
                <th>Gender</th>
                <td>' . htmlspecialchars($result['gender']) . '</td>
                <th>Contact</th>
                <td>' . htmlspecialchars($result['contact']) . '</td>
            </tr>
        </table>
        
        <h3>Test Details</h3>
        <table border="1" cellpadding="5">
            <tr>
                <th>Test Name</th>
                <td>' . htmlspecialchars($result['test_name']) . '</td>
            </tr>
            <tr>
                <th>Result Value</th>
                <td>' . htmlspecialchars($result['result_value']) . ' ' . htmlspecialchars($result['unit']) . '</td>
            </tr>
            <tr>
                <th>Normal Range</th>
                <td>' . htmlspecialchars($result['normal_range']) . '</td>
            </tr>
            <tr>
                <th>Comments</th>
                <td>' . htmlspecialchars($result['comments']) . '</td>
            </tr>
            <tr>
                <th>Recorded By</th>
                <td>' . htmlspecialchars($result['recorded_by_name']) . '</td>
            </tr>
            <tr>
                <th>Recorded At</th>
                <td>' . htmlspecialchars($result['recorded_at']) . '</td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Output PDF
        $pdf->Output('test_report_' . $result_id . '.pdf', 'D');
        exit;

    } catch (Exception $e) {
        // Log error
        error_log("PDF Generation Error: " . $e->getMessage());
        
        // Return user-friendly error
        header('HTTP/1.1 500 Internal Server Error');
        echo "An error occurred while generating the report. Please try again later.";
        exit;
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
                                <input type="text" class="form-control" id="searchInput" placeholder="Search reports..." 
                                       maxlength="50" pattern="[A-Za-z0-9\s-]+" 
                                       title="Only letters, numbers, spaces and hyphens allowed">
                                <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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
            const csrfToken = document.getElementById('csrf_token').value;
            
            // Validate search input
            if (searchQuery && !/^[A-Za-z0-9\s-]+$/.test(searchQuery)) {
                showAlert('Invalid search query', 'error');
                return;
            }
            
            const url = `includes/fetch_reports.php?page=${page}&csrf_token=${encodeURIComponent(csrfToken)}${
                searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''
            }`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('reportTableBody');
                    tbody.innerHTML = '';

                    if (data.reports.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No reports found</td></tr>';
                        return;
                    }

                    data.reports.forEach(report => {
                        const row = `
                            <tr>
                                <td>${escapeHtml(report.result_id)}</td>
                                <td>${escapeHtml(report.patient_name)}</td>
                                <td>${escapeHtml(report.test_name)}</td>
                                <td>${escapeHtml(report.result_value)}</td>
                                <td>${escapeHtml(report.comments || '-')}</td>
                                <td>${escapeHtml(report.recorded_by_name)}</td>
                                <td>${escapeHtml(report.recorded_at)}</td>
                                <td>
                                    <a href="reports.php?generate_pdf=true&result_id=${encodeURIComponent(report.result_id)}&csrf_token=${encodeURIComponent(csrfToken)}" 
                                       class="btn btn-sm btn-success">
                                        <i class="bi bi-file-pdf"></i> Download PDF
                                    </a>
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });

                    updatePagination(data.current_page, data.total_pages);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading reports: ' + error.message, 'error');
                    document.getElementById('reportTableBody').innerHTML = 
                        '<tr><td colspan="8" class="text-center">Error loading reports</td></tr>';
                });
        }

        // Helper function to escape HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Helper function to show alerts
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
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