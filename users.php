<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Delete user
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
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" />
    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css" integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg=" crossorigin="anonymous" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous" />
    <!-- AdminLTE CSS (adjusted path) -->
    <link rel="stylesheet" href="../dist/css/adminlte.css" />
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Top Navbar -->
        <?php include('inc/top.php'); ?>
        <!-- Sidebar -->
        <?php include('inc/sidebar.php'); ?>
        <main class="app-main">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 mt-2">
                        <div class="col-sm-6">
                            <h3>User Management</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <a href="add_user.php" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Add New User
                            </a>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">User List</h3></div>
                                <div class="card-body">
                                    <table id="userTable" class="table table-bordered table-striped">
                                        <thead>
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
                                        <tbody id="userTableBody"></tbody>
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
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="../dist/js/adminlte.js"></script>
    <script>
        function loadUsers(page = 1) {
            fetch(`includes/fetch_users.php?page=${page}`) // Adjusted path
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('userTableBody');
                    tbody.innerHTML = ''; // Clear existing rows

                    // Populate table
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
                                    <a href="add_user.php?edit=${user.user_id}" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                                    ${user.user_id != <?php echo $_SESSION['user_id']; ?> ? 
                                        `<a href="users.php?delete=${user.user_id}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="bi bi-trash"></i> Delete</a>` 
                                        : ''}
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });

                    // Generate pagination
                    const pagination = document.getElementById('pagination');
                    pagination.innerHTML = '';
                    if (data.total_pages > 1) {
                        // Previous button
                        pagination.innerHTML += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadUsers(${data.current_page - 1}); return false;">«</a>
                        </li>`;

                        // Page numbers
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadUsers(${i}); return false;">${i}</a>
                            </li>`;
                        }

                        // Next button
                        pagination.innerHTML += `<li class="page-item ${data.current_page === data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadUsers(${data.current_page + 1}); return false;">»</a>
                        </li>`;
                    }
                })
                .catch(error => console.error('Error fetching users:', error));
        }

        // Load users on page load
        document.addEventListener('DOMContentLoaded', () => loadUsers(1));

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