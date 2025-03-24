<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict to Admin, Doctor, Technician (view-only for non-Admins)
if (!in_array($_SESSION['role'], ['Admin', 'Doctor', 'Technician'])) {
    header("Location: index3.php");
    exit();
}

// Delete test (Admin-only)
if (isset($_GET['delete']) && $_SESSION['role'] === 'Admin') {
    $test_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM Tests_Catalog WHERE test_id = :test_id");
    $stmt->execute(['test_id' => $test_id]);
    header("Location: tests.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | Test Catalog</title>
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
                            <h3>Test Catalog</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                <a href="add_test.php" class="btn btn-primary">
                                    <i class="bi bi-plus"></i> Add New Test
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search by test name or code">
                                <button class="btn btn-primary" onclick="loadTests(1)">Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Test List</h3></div>
                                <div class="card-body">
                                    <table id="testTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Test Name</th>
                                                <th>Test Code</th>
                                                <th>Description</th>
                                                <th>Normal Range</th>
                                                <th>Unit</th>
                                                <th>Price</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="testTableBody"></tbody>
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
        function loadTests(page = 1) {
            const searchQuery = document.getElementById('searchInput').value.trim();
            const url = `includes/fetch_tests.php?page=${page}${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('testTableBody');
                    tbody.innerHTML = '';

                    if (data.tests.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No tests found</td></tr>';
                    } else {
                        data.tests.forEach(test => {
                            const row = `
                                <tr>
                                    <td>${test.test_id}</td>
                                    <td>${test.test_name}</td>
                                    <td>${test.test_code}</td>
                                    <td>${test.description || '-'}</td>
                                    <td>${test.normal_range || '-'}</td>
                                    <td>${test.unit || '-'}</td>
                                    <td>$${test.price}</td>
                                    <td>
                                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                                            <a href="add_test.php?edit=${test.test_id}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                                            <a href="tests.php?delete=${test.test_id}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>
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
                            <a class="page-link" href="#" onclick="loadTests(${data.current_page - 1}); return false;">«</a>
                        </li>`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadTests(${i}); return false;">${i}</a>
                            </li>`;
                        }
                        pagination.innerHTML += `<li class="page-item ${data.current_page === data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadTests(${data.current_page + 1}); return false;">»</a>
                        </li>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching tests:', error);
                    document.getElementById('testTableBody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading tests</td></tr>';
                });
        }

        document.addEventListener('DOMContentLoaded', () => loadTests(1));

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                loadTests(1);
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