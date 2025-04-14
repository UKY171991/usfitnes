<?php
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict to Admin, Doctor, Technician with proper role check
$allowed_roles = ['Admin', 'Doctor', 'Technician'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: index3.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Delete result (Admin-only) with CSRF protection
if (isset($_GET['delete']) && $_SESSION['role'] === 'Admin') {
    // Verify CSRF token
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid request');
    }

    try {
        $result_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
        if ($result_id === false) {
            throw new Exception('Invalid result ID');
        }

        $stmt = $pdo->prepare("DELETE FROM Test_Results WHERE result_id = :result_id");
        $stmt->execute(['result_id' => $result_id]);
        
        // Log the deletion
        error_log("Test result deleted by user {$_SESSION['user_id']}: Result ID {$result_id}");
        
        header("Location: test_results.php");
        exit();
    } catch (Exception $e) {
        error_log("Delete result error: " . $e->getMessage());
        die('Error deleting result');
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | Test Results</title>
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
                            <h3>Test Results</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <a href="add_test_result.php" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Add New Result
                            </a>
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
                                <button class="btn btn-primary" onclick="loadResults(1)">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Result List</h3></div>
                                <div class="card-body">
                                    <table id="resultTable" class="table table-bordered table-striped">
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
                                        <tbody id="resultTableBody"></tbody>
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
        function loadResults(page = 1) {
            const searchQuery = document.getElementById('searchInput').value.trim();
            const url = `includes/fetch_test_results.php?page=${page}${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('resultTableBody');
                    tbody.innerHTML = '';

                    if (data.results.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No results found</td></tr>';
                    } else {
                        data.results.forEach(result => {
                            const row = `
                                <tr>
                                    <td>${escapeHtml(result.result_id)}</td>
                                    <td>${escapeHtml(result.patient_name)}</td>
                                    <td>${escapeHtml(result.test_name)}</td>
                                    <td>${escapeHtml(result.result_value)}</td>
                                    <td>${escapeHtml(result.comments || '-')}</td>
                                    <td>${escapeHtml(result.recorded_by_name)}</td>
                                    <td>${escapeHtml(result.recorded_at)}</td>
                                    <td>
                                        <a href="add_test_result.php?edit=${escapeHtml(result.result_id)}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                                            <a href="test_results.php?delete=${escapeHtml(result.result_id)}&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>`;
                            tbody.innerHTML += row;
                        });
                    }

                    const pagination = document.getElementById('pagination');
                    pagination.innerHTML = '';
                    if (data.total_pages > 1) {
                        pagination.innerHTML += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadResults(${data.current_page - 1}); return false;">«</a>
                        </li>`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadResults(${i}); return false;">${i}</a>
                            </li>`;
                        }
                        pagination.innerHTML += `<li class="page-item ${data.current_page === data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadResults(${data.current_page + 1}); return false;">»</a>
                        </li>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching results:', error);
                    document.getElementById('resultTableBody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading results</td></tr>';
                });
        }

        document.addEventListener('DOMContentLoaded', () => loadResults(1));

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                loadResults(1);
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

        // Helper function to escape HTML
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>
</html>