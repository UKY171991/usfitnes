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

// Delete patient (Admin-only) with CSRF protection
if (isset($_GET['delete']) && $_SESSION['role'] === 'Admin') {
    // Verify CSRF token
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid request');
    }

    try {
        $patient_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
        if ($patient_id === false) {
            throw new Exception('Invalid patient ID');
        }

        $stmt = $pdo->prepare("DELETE FROM Patients WHERE patient_id = :patient_id");
        $stmt->execute(['patient_id' => $patient_id]);
        
        // Log the deletion
        error_log("Patient deleted by user {$_SESSION['user_id']}: Patient ID {$patient_id}");
        
        header("Location: patients.php");
        exit();
    } catch (Exception $e) {
        error_log("Delete patient error: " . $e->getMessage());
        die('Error deleting patient');
    }
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
    <title>Pathology | Patient Management</title>
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
                            <h3>Patient Management</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <a href="add_patient.php" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Add New Patient
                            </a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search by name or email">
                                <button class="btn btn-primary" onclick="loadPatients(1)">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <button class="btn btn-secondary" onclick="document.getElementById('searchInput').value=''; loadPatients(1);">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="card-title mb-0">Patient List</h3>
                                </div>
                                <div class="card-body">
                                    <table id="patientTable" class="table table-bordered table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>DOB</th>
                                                <th>Gender</th>
                                                <th>Contact</th>
                                                <th>Email</th>
                                                <th>Created By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="patientTableBody">
                                            <tr>
                                                <td colspan="8" class="text-center">Loading...</td>
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
            </section> 
            
        </main>
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Anything you want</div>
            <strong>Copyright © 2025 <a href="#" class="text-decoration-none">Pathology System</a>.</strong> All rights reserved.
        </footer>
    </div>
    <?php include('inc/js.php'); ?>
    <script>
        function loadPatients(page = 1) {
            const searchQuery = document.getElementById('searchInput').value.trim();
            const url = `includes/fetch_patients.php?page=${page}${searchQuery ? `&search=${encodeURIComponent(searchQuery)}` : ''}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const tbody = document.getElementById('patientTableBody');
                    tbody.innerHTML = '';

                    if (data.patients.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No patients found</td></tr>';
                    } else {
                        data.patients.forEach(patient => {
                            const row = `
                                <tr>
                                    <td>${escapeHtml(patient.patient_id)}</td>
                                    <td>${escapeHtml(patient.first_name)} ${escapeHtml(patient.last_name)}</td>
                                    <td>${escapeHtml(patient.date_of_birth)}</td>
                                    <td>${escapeHtml(patient.gender)}</td>
                                    <td>${escapeHtml(patient.phone || '-')}</td>
                                    <td>${escapeHtml(patient.email || '-')}</td>
                                    <td>${escapeHtml(patient.created_by_name || 'Unknown')}</td>
                                    <td>
                                        <a href="add_patient.php?edit=${escapeHtml(patient.patient_id)}" class="btn btn-sm btn-warning me-2" data-bs-toggle="tooltip" title="Edit Patient">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                                            <a href="patients.php?delete=${escapeHtml(patient.patient_id)}&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this patient?');" data-bs-toggle="tooltip" title="Delete Patient">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
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
                            <a class="page-link" href="#" onclick="loadPatients(${data.current_page - 1}); return false;">«</a>
                        </li>`;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                                <a class="page-link" href="#" onclick="loadPatients(${i}); return false;">${i}</a>
                            </li>`;
                        }
                        pagination.innerHTML += `<li class="page-item ${data.current_page === data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" onclick="loadPatients(${data.current_page + 1}); return false;">»</a>
                        </li>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching patients:', error);
                    document.getElementById('patientTableBody').innerHTML = '<tr><td colspan="8" class="text-center">Error loading patients</td></tr>';
                });
        }

        document.addEventListener('DOMContentLoaded', () => loadPatients(1));

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                loadPatients(1);
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

        // Helper function to escape HTML
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>
</html>