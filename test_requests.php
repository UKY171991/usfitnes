<?php
require_once 'db_connect.php';

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

// Delete request (Admin-only)
if (isset($_GET['delete']) && $_SESSION['role'] === 'Admin') {
    $request_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM Test_Requests WHERE request_id = :request_id");
    $stmt->execute(['request_id' => $request_id]);
    header("Location: test_requests.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | Test Requests</title>
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
                            <h3>Test Requests</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <a href="add_test_request.php" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Add New Request
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
                                <button class="btn btn-primary" onclick="loadRequests(1)">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Request List</h3></div>
                                <div class="card-body">
                                    <table id="requestTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Patient</th>
                                                <th>Test</th>
                                                <th>Ordered By</th>
                                                <th>Request Date</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="requestTableBody"></tbody>
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
        function loadRequests(page = 1) {
            const searchQuery = document.getElementById('searchInput').value.trim();
            const url = `includes/fetch_test_requests.php?page=${page}${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('requestTableBody');
                    tbody.innerHTML = '';

                    if (data.requests.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No requests found</td></tr>';
                    } else {
                        data.requests.forEach(request => {
                            const row = `
                                <tr>
                                    <td>${request.request_id}</td>
                                    <td>${request.patient_name}</td>
                                    <td>${request.test_name}</td>
                                    <td>${request.ordered_by}</td>
                                    <td>${request.request_date}</td>
                                    <td>${request.status}</td>
                                    <td>${request.priority}</td>
                                    <td>
                                        <a href="add_test_request.php?edit=${request.request_id}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                                            <a href="test_requests.php?delete=${request.request_id}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
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
                            <a class="page-link" href="#" onclick="loadRequests(${data.current_page - 1}); return false;">«</a>
                        </li>`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadRequests(${i}); return false;">${i}</a>
                            </li>`;
                        }
                        pagination.innerHTML += `<li class="page-item ${data.current_page === data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadRequests(${data.current_page + 1}); return false;">»</a>
                        </li>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching requests:', error);
                    document.getElementById('requestTableBody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading requests</td></tr>';
                });
        }

        document.addEventListener('DOMContentLoaded', () => loadRequests(1));

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                loadRequests(1);
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