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

// Restrict to Admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | Branch Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include('inc/head.php'); ?>
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-secondary {
            margin-left: 5px;
        }
    </style>
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
                            <h3>Branch Management</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <button class="btn btn-primary" id="addBranchBtn">
                                <i class="bi bi-plus-circle"></i> Add New Branch
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="searchBranchInput" class="form-control" placeholder="Search by branch name or location">
                                <button class="btn btn-primary" id="searchBranchBtn">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <button class="btn btn-secondary" id="clearSearchBranchBtn">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="card-title mb-0">Branch List</h3>
                                </div>
                                <div class="card-body">
                                    <table id="branchTable" class="table table-bordered table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Branch Name</th>
                                                <th>Location</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="branchTableBody">
                                            <tr>
                                                <td colspan="4" class="text-center">Loading...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Anything you want</div>
            <strong>Copyright © 2025 <a href="#" class="text-decoration-none">Pathology System</a>.</strong> All rights reserved.
        </footer>
    </div>
    <?php include('inc/js.php'); ?>

    <!-- Add Branch Modal -->
    <div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBranchModalLabel">Add Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBranchForm">
                        <div class="mb-3">
                            <label for="addBranchName" class="form-label">Branch Name</label>
                            <input type="text" class="form-control" id="addBranchName" name="branch_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="addBranchLocation" class="form-label">Branch Location</label>
                            <input type="text" class="form-control" id="addBranchLocation" name="branch_location" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Branch Modal -->
    <div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editBranchForm">
                        <input type="hidden" id="editBranchId" name="branch_id">
                        <div class="mb-3">
                            <label for="editBranchName" class="form-label">Branch Name</label>
                            <input type="text" class="form-control" id="editBranchName" name="branch_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editBranchLocation" class="form-label">Branch Location</label>
                            <input type="text" class="form-control" id="editBranchLocation" name="branch_location" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            loadBranches();

            // Open Add Branch Modal
            document.getElementById('addBranchBtn').addEventListener('click', function () {
                const addBranchModal = new bootstrap.Modal(document.getElementById('addBranchModal'));
                addBranchModal.show();
            });

            // Handle Add Branch Form Submission
            document.getElementById('addBranchForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const branchName = document.getElementById('addBranchName').value;
                const branchLocation = document.getElementById('addBranchLocation').value;

                fetch('includes/manage_branches.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'add', branch_name: branchName, branch_location: branchLocation })
                }).then(() => {
                    loadBranches();
                    bootstrap.Modal.getInstance(document.getElementById('addBranchModal')).hide();
                });
            });

            // Open Edit Branch Modal
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('editBranchBtn')) {
                    const branchId = e.target.getAttribute('data-id');
                    const branchName = e.target.getAttribute('data-name');
                    const branchLocation = e.target.getAttribute('data-location');

                    document.getElementById('editBranchId').value = branchId;
                    document.getElementById('editBranchName').value = branchName;
                    document.getElementById('editBranchLocation').value = branchLocation;

                    const editBranchModal = new bootstrap.Modal(document.getElementById('editBranchModal'));
                    editBranchModal.show();
                }
            });

            // Handle Edit Branch Form Submission
            document.getElementById('editBranchForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const branchId = document.getElementById('editBranchId').value;
                const branchName = document.getElementById('editBranchName').value;
                const branchLocation = document.getElementById('editBranchLocation').value;

                fetch('includes/manage_branches.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'edit', branch_id: branchId, branch_name: branchName, branch_location: branchLocation })
                }).then(() => {
                    loadBranches();
                    bootstrap.Modal.getInstance(document.getElementById('editBranchModal')).hide();
                });
            });

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('deleteBranchBtn')) {
                    const branchId = e.target.getAttribute('data-id');
                    if (confirm('Are you sure you want to delete this branch?')) {
                        fetch('includes/manage_branches.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'delete', branch_id: branchId })
                        }).then(() => loadBranches());
                    }
                }
            });

            // Search Branches
            document.getElementById('searchBranchBtn').addEventListener('click', function () {
                const searchQuery = document.getElementById('searchBranchInput').value.trim();
                loadBranches(searchQuery);
            });

            // Clear Search
            document.getElementById('clearSearchBranchBtn').addEventListener('click', function () {
                document.getElementById('searchBranchInput').value = '';
                loadBranches();
            });
        });

        // Modify loadBranches to accept search query
        function loadBranches(searchQuery = '') {
            const url = `includes/fetch_branches.php${searchQuery ? `?search=${encodeURIComponent(searchQuery)}` : ''}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('branchTableBody');
                    tbody.innerHTML = '';

                    if (data.branches.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center">No branches found</td></tr>';
                    } else {
                        data.branches.forEach(branch => {
                            const row = `
                                <tr>
                                    <td>${escapeHtml(branch.branch_id)}</td>
                                    <td>${escapeHtml(branch.branch_name)}</td>
                                    <td>${escapeHtml(branch.branch_location)}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning me-2 editBranchBtn" data-id="${escapeHtml(branch.branch_id)}" data-name="${escapeHtml(branch.branch_name)}" data-location="${escapeHtml(branch.branch_location)}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger deleteBranchBtn" data-id="${escapeHtml(branch.branch_id)}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>`;
                            tbody.innerHTML += row;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching branches:', error);
                    document.getElementById('branchTableBody').innerHTML = '<tr><td colspan="4" class="text-center">Error loading branches</td></tr>';
                });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>
</html>