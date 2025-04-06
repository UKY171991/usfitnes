<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict access to Admin only
if ($_SESSION['role'] !== 'Admin') {
    header("Location: index.php"); // Redirect non-Admins to dashboard
    exit();
}

// Delete user (Admin-only action)
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = :user_id AND user_id != :current_user");
    $stmt->execute(['user_id' => $user_id, 'current_user' => $_SESSION['user_id']]);
    header("Location: users.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | User Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="Pathology | User Management" />
    <meta name="author" content="Your Name" />
    <meta name="description" content="Pathology Management System - User CRUD" />
    <meta name="keywords" content="pathology, users, crud, adminlte" />
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
                            <h3>User Management</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <?php if ($_SESSION['role'] === 'Admin'): ?>
                                <a href="add_user.php" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Add New User
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search by username, name, or email">
                                <button class="btn btn-primary" onclick="loadUsers(1)">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <button class="btn btn-secondary" onclick="document.getElementById('searchInput').value=''; loadUsers(1);">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">User List</h5>
                                </div>
                                <div class="card-body">
                                    <table id="userTable" class="table table-bordered table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Username</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="userTableBody">
                                            <tr>
                                                <td colspan="7" class="text-center">Loading...</td>
                                            </tr>
                                        </tbody>
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
        function loadUsers(page = 1) {
            const searchQuery = document.getElementById('searchInput').value.trim();
            const url = `includes/fetch_users.php?page=${page}${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('userTableBody');
                    tbody.innerHTML = ''; // Clear existing rows

                    if (data.users.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
                    } else {
                        data.users.forEach(user => {
                            const row = `
                                <tr>
                                    <td>${user.user_id}</td>
                                    <td>${user.username}</td>
                                    <td>${user.first_name} ${user.last_name}</td>
                                    <td>${user.email}</td>
                                    <td>${user.role}</td>
                                    <td>${user.created_at}</td>
                                    <td>
                                        <a href="add_user.php?edit=${user.user_id}" class="btn btn-sm btn-warning me-2" data-bs-toggle="tooltip" title="Edit User">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        ${user.user_id != <?php echo $_SESSION['user_id']; ?> ? 
                                            `<a href="users.php?delete=${user.user_id}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');" data-bs-toggle="tooltip" title="Delete User">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>` 
                                            : ''}
                                    </td>
                                </tr>`;
                            tbody.innerHTML += row;
                        });
                    }

                    // Generate pagination
                    const pagination = document.getElementById('pagination');
                    pagination.innerHTML = '';
                    if (data.total_pages > 1) {
                        pagination.innerHTML += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadUsers(${data.current_page - 1}); return false;">«</a>
                        </li>`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadUsers(${i}); return false;">${i}</a>
                            </li>`;
                        }
                        pagination.innerHTML += `<li class="page-item ${data.current_page === data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadUsers(${data.current_page + 1}); return false;">»</a>
                        </li>`;
                    }
                })
                .catch(error => console.error('Error fetching users:', error));
        }

        // Load users on page load
        document.addEventListener('DOMContentLoaded', () => loadUsers(1));

        // Add Enter key support for search
        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                loadUsers(1);
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

        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>