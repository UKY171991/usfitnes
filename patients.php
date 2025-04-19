<?php
require_once 'config.php';
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
    'gc_maxlifetime' => SESSION_LIFETIME
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

try {
    $db = Database::getInstance();

    // Delete patient (Admin-only) with CSRF protection
    if (isset($_GET['delete']) && $_SESSION['role'] === 'Admin') {
        // Verify CSRF token
        if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }

        $patient_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
        if ($patient_id === false) {
            throw new Exception('Invalid patient ID');
        }

        // Check if patient belongs to the current branch
        $stmt = $db->query(
            "SELECT patient_id FROM patients WHERE patient_id = :patient_id AND branch_id = :branch_id",
            ['patient_id' => $patient_id, 'branch_id' => $_SESSION['branch_id']]
        );
        if (!$stmt->fetch()) {
            throw new Exception('Patient not found or access denied');
        }

        // Start transaction
        $db->beginTransaction();

        try {
            // Delete related records first (if any)
            $db->query(
                "DELETE FROM test_requests WHERE patient_id = :patient_id",
                ['patient_id' => $patient_id]
            );
            
            $db->query(
                "DELETE FROM test_results WHERE patient_id = :patient_id",
                ['patient_id' => $patient_id]
            );

            // Delete the patient
            $db->query(
                "DELETE FROM patients WHERE patient_id = :patient_id AND branch_id = :branch_id",
                ['patient_id' => $patient_id, 'branch_id' => $_SESSION['branch_id']]
            );
            
            // Log the deletion
            error_log("Patient {$patient_id} deleted by user {$_SESSION['user_id']} in branch {$_SESSION['branch_id']}");
            
            $db->commit();
            $_SESSION['success_message'] = "Patient deleted successfully";
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        header("Location: patients.php");
        exit;
    }
} catch (Exception $e) {
    error_log("Patient management error: " . $e->getMessage());
    $_SESSION['error_message'] = "Operation failed: " . $e->getMessage();
    header("Location: patients.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pathology | Patient Management</title>
    <?php include('inc/head.php'); ?>
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        .alert {
            margin-bottom: 1rem;
        }
        .search-box {
            position: relative;
        }
        .search-box .clear-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .search-box .clear-search:hover {
            color: #343a40;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .card {
            position: relative;
        }
        .patient-actions {
            white-space: nowrap;
        }
        .patient-actions .btn {
            margin: 0 2px;
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        .status-active {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #842029;
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <?php include('inc/sidebar.php'); ?>
        
        <main class="app-main">
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Patient Management</h1>
                        </div>
                        <div class="col-sm-6 text-end">
                            <a href="add_patient.php" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Add New Patient
                            </a>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                                echo htmlspecialchars($_SESSION['success_message']);
                                unset($_SESSION['success_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                                echo htmlspecialchars($_SESSION['error_message']);
                                unset($_SESSION['error_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h3 class="card-title">Patient List</h3>
                                </div>
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <input type="text" id="searchInput" class="form-control" 
                                               placeholder="Search by name, email, or phone"
                                               maxlength="50" pattern="[A-Za-z0-9\s@.-]+"
                                               title="Only letters, numbers, spaces, @, ., and - allowed">
                                        <span class="clear-search" onclick="clearSearch()"><i class="bi bi-x-circle"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="loading-overlay">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>DOB</th>
                                            <th>Gender</th>
                                            <th>Contact</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="patientTableBody">
                                        <tr>
                                            <td colspan="9" class="text-center">Loading patients...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav aria-label="Patient navigation">
                                <ul class="pagination justify-content-end mb-0" id="pagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include('inc/js.php'); ?>
    <script>
        let currentPage = 1;
        let isLoading = false;
        let searchTimeout = null;

        function showLoading() {
            document.querySelector('.loading-overlay').style.display = 'flex';
            isLoading = true;
        }

        function hideLoading() {
            document.querySelector('.loading-overlay').style.display = 'none';
            isLoading = false;
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.card'));
            setTimeout(() => alertDiv.remove(), 5000);
        }

        function loadPatients(page = 1) {
            if (isLoading) return;
            
            showLoading();
            currentPage = page;
            
            const searchQuery = document.getElementById('searchInput').value.trim();
            const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
            
            // Validate search input
            if (searchQuery && !/^[A-Za-z0-9\s@.-]+$/.test(searchQuery)) {
                showAlert('Invalid search query', 'error');
                hideLoading();
                return;
            }
            
            const url = `includes/fetch_patients.php?page=${page}&csrf_token=${encodeURIComponent(csrfToken)}${
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
                    const tbody = document.getElementById('patientTableBody');
                    tbody.innerHTML = '';

                    if (data.patients.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="9" class="text-center">No patients found</td></tr>';
                        return;
                    }

                    data.patients.forEach(patient => {
                        const row = `
                            <tr>
                                <td>${escapeHtml(patient.patient_id)}</td>
                                <td>${escapeHtml(patient.first_name + ' ' + patient.last_name)}</td>
                                <td>${escapeHtml(patient.date_of_birth)}</td>
                                <td>${escapeHtml(patient.gender)}</td>
                                <td>${escapeHtml(patient.phone)}</td>
                                <td>${escapeHtml(patient.email || '-')}</td>
                                <td>
                                    <span class="status-badge status-${patient.status === 'active' ? 'active' : 'inactive'}">
                                        ${escapeHtml(patient.status)}
                                    </span>
                                </td>
                                <td>${escapeHtml(patient.created_by_name)}</td>
                                <td class="patient-actions">
                                    <a href="view_patient.php?id=${encodeURIComponent(patient.patient_id)}" 
                                       class="btn btn-sm btn-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit_patient.php?id=${encodeURIComponent(patient.patient_id)}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                                    <a href="javascript:void(0)" 
                                       onclick="deletePatient(${escapeHtml(patient.patient_id)})" 
                                       class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });

                    updatePagination(data.current_page, data.total_pages);
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading patients: ' + error.message, 'error');
                    document.getElementById('patientTableBody').innerHTML = 
                        '<tr><td colspan="9" class="text-center">Error loading patients</td></tr>';
                })
                .finally(() => {
                    hideLoading();
                });
        }

        function updatePagination(currentPage, totalPages) {
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';
            
            if (totalPages > 1) {
                pagination.innerHTML = `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="return loadPatients(${currentPage - 1});">«</a>
                    </li>`;
                    
                for (let i = 1; i <= totalPages; i++) {
                    pagination.innerHTML += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="return loadPatients(${i});">${i}</a>
                        </li>`;
                }
                
                pagination.innerHTML += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="return loadPatients(${currentPage + 1});">»</a>
                    </li>`;
            }
        }

        function clearSearch() {
            document.getElementById('searchInput').value = '';
            loadPatients(1);
        }

        function deletePatient(patientId) {
            if (!confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
                return;
            }

            const csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
            window.location.href = `patients.php?delete=${patientId}&csrf_token=${encodeURIComponent(csrfToken)}`;
        }

        // Initialize search with debounce
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadPatients(1);
            }, 500);
        });

        // Load patients on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadPatients(1);
        });
    </script>
</body>
</html>