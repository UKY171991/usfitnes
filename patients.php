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
    <?php include 'inc/head.php'; ?>
    <title>Patient Management | Pathology Lab</title>
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }
        .search-box {
            position: relative;
            max-width: 300px;
        }
        .search-box .form-control {
            padding-left: 40px;
            border-radius: 20px;
        }
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .btn-add-patient {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
        }
        .table th {
            font-weight: 600;
            color: #495057;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
            color: #6c757d;
        }
        .patient-name {
            font-weight: 500;
            color: #2c3e50;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .status-inactive {
            background-color: #fbe9e7;
            color: #c62828;
        }
        .pagination {
            margin-bottom: 0;
        }
        .pagination .page-link {
            border-radius: 5px;
            margin: 0 3px;
            color: #3498db;
        }
        .pagination .page-item.active .page-link {
            background-color: #3498db;
            border-color: #3498db;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 14px;
            margin: 0 2px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: stretch !important;
            }
            .search-box {
                margin-bottom: 15px;
                max-width: 100%;
            }
            .table-responsive {
                border: none;
            }
        }
    </style>
</head>
<body class="layout-fixed">
    <div class="wrapper">
        <?php include 'inc/sidebar.php'; ?>
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Patient Management</h1>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email, or phone">
                            </div>
                            <button type="button" class="btn btn-primary btn-add-patient" onclick="location.href='add_patient.php'">
                                <i class="bi bi-plus-lg me-1"></i>Add New Patient
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
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
                                    <tbody id="patientsTableBody">
                                        <tr>
                                            <td colspan="9">
                                                <div class="loading-spinner">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                </div>
                                            </td>
                                            </tr>
                                        </tbody>
                                    </table>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div class="text-muted" id="totalRecords">Total: 0 patients</div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0" id="pagination"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let searchTimer = null;

        function loadPatients(page = 1, search = '') {
            const tbody = document.getElementById('patientsTableBody');
            const spinner = document.querySelector('.loading-spinner');
            spinner.style.display = 'block';
            
            const csrf_token = '<?php echo $_SESSION["csrf_token"]; ?>';
            
            fetch(`includes/fetch_patients.php?page=${page}&search=${encodeURIComponent(search)}&csrf_token=${csrf_token}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateTable(data);
                        updatePagination(data);
                        updateTotalRecords(data.total_records);
                    } else {
                        showError(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Failed to load patients');
                })
                .finally(() => {
                    spinner.style.display = 'none';
                });
        }

        function updateTable(data) {
            const tbody = document.getElementById('patientsTableBody');
            if (data.patients.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-person-x"></i>
                                <p>No patients found</p>
                            </div>
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = data.patients.map(patient => `
                <tr>
                    <td>${patient.patient_id}</td>
                    <td class="patient-name">${patient.first_name} ${patient.last_name}</td>
                    <td>${patient.date_of_birth}</td>
                    <td>${patient.gender}</td>
                    <td>${patient.phone}</td>
                    <td>${patient.email}</td>
                    <td>
                        <span class="status-badge ${patient.status === 'active' ? 'status-active' : 'status-inactive'}">
                            ${patient.status}
                        </span>
                    </td>
                    <td>${patient.created_by_name}</td>
                    <td class="action-buttons">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewPatient(${patient.patient_id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="editPatient(${patient.patient_id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deletePatient(${patient.patient_id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function updatePagination(data) {
            const pagination = document.getElementById('pagination');
            const totalPages = data.total_pages;
            const currentPage = data.current_page;

            let paginationHTML = '';

            // Previous button
            paginationHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1})" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>`;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    paginationHTML += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                        </li>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    paginationHTML += `
                        <li class="page-item disabled">
                            <a class="page-link" href="#">...</a>
                        </li>`;
                }
            }

            // Next button
            paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1})" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>`;

            pagination.innerHTML = paginationHTML;
        }

        function updateTotalRecords(total) {
            document.getElementById('totalRecords').textContent = `Total: ${total} patient${total !== 1 ? 's' : ''}`;
        }

        function changePage(page) {
            if (page < 1) return;
            currentPage = page;
            const searchInput = document.getElementById('searchInput');
            loadPatients(page, searchInput.value);
        }

        function showError(message) {
            const tbody = document.getElementById('patientsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9">
                        <div class="alert alert-danger m-3" role="alert">
                            ${message}
                        </div>
                    </td>
                </tr>`;
        }

        // Initialize search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                currentPage = 1;
                loadPatients(1, e.target.value);
            }, 300);
        });

        // Initial load
        document.addEventListener('DOMContentLoaded', () => {
            loadPatients();
        });

        // Patient actions
        function viewPatient(id) {
            window.location.href = `view_patient.php?id=${id}`;
        }

        function editPatient(id) {
            window.location.href = `edit_patient.php?id=${id}`;
        }

        function deletePatient(id) {
            if (confirm('Are you sure you want to delete this patient?')) {
                // Implement delete functionality
                console.log('Delete patient:', id);
            }
        }
    </script>
</body>
</html>