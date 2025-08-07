<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Patients Management';
$page_description = 'Manage patient records and information';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?> - PathLab Pro</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="css/global.css">
    
    <style>
        .patient-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        .action-buttons .btn {
            margin: 0 2px;
            padding: 0.375rem 0.5rem;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Header -->
        <?php include 'includes/header_clean.php'; ?>
        
        <!-- Sidebar -->
        <?php include 'includes/sidebar_clean.php'; ?>
        
        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><i class="fas fa-users mr-2"></i><?= $page_title ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Patients</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="total-patients-count">0</h3>
                                    <p>Total Patients</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="active-patients-count">0</h3>
                                    <p>Active Patients</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="today-visits-count">0</h3>
                                    <p>Today's Visits</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="pending-tests-count">0</h3>
                                    <p>Pending Tests</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-flask"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                Patient Records
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-light btn-sm" onclick="refreshTable()" data-toggle="tooltip" title="Refresh Table">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-light btn-sm" onclick="openAddModal()" data-toggle="tooltip" title="Add New Patient">
                                    <i class="fas fa-plus"></i> Add Patient
                                </button>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <table class="table table-bordered table-striped data-table" id="patientsTable">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="15%">Name</th>
                                        <th width="10%">Phone</th>
                                        <th width="15%">Email</th>
                                        <th width="8%">Age</th>
                                        <th width="10%">Gender</th>
                                        <th width="12%">Registration</th>
                                        <th width="8%">Status</th>
                                        <th width="17%">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <?php include 'includes/footer_clean.php'; ?>
    </div>

    <!-- Add Patient Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Patient</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form class="ajax-form" action="api/patients_api.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" name="email">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth *</label>
                                    <input type="date" class="form-control" name="date_of_birth" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender">Gender *</label>
                                    <select class="form-control" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="blood_group">Blood Group</label>
                                    <select class="form-control" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="emergency_contact">Emergency Contact</label>
                                    <input type="text" class="form-control" name="emergency_contact">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="emergency_phone">Emergency Phone</label>
                                    <input type="tel" class="form-control" name="emergency_phone">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="medical_history">Medical History</label>
                            <textarea class="form-control" name="medical_history" rows="3" placeholder="Any relevant medical history, allergies, or conditions..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save Patient
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Patient</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form class="ajax-form" action="api/patients_api.php" method="POST">
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_first_name">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_last_name">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_phone">Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_email">Email Address</label>
                                    <input type="email" class="form-control" name="email">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_date_of_birth">Date of Birth *</label>
                                    <input type="date" class="form-control" name="date_of_birth" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_gender">Gender *</label>
                                    <select class="form-control" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_blood_group">Blood Group</label>
                                    <select class="form-control" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_address">Address</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_emergency_contact">Emergency Contact</label>
                                    <input type="text" class="form-control" name="emergency_contact">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_emergency_phone">Emergency Phone</label>
                                    <input type="tel" class="form-control" name="emergency_phone">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_medical_history">Medical History</label>
                            <textarea class="form-control" name="medical_history" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select class="form-control" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Update Patient
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Patient Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Name:</th>
                                    <td id="view-full-name"></td>
                                </tr>
                                <tr>
                                    <th>Phone:</th>
                                    <td id="view-phone"></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td id="view-email"></td>
                                </tr>
                                <tr>
                                    <th>Date of Birth:</th>
                                    <td id="view-date_of_birth"></td>
                                </tr>
                                <tr>
                                    <th>Gender:</th>
                                    <td id="view-gender"></td>
                                </tr>
                                <tr>
                                    <th>Blood Group:</th>
                                    <td id="view-blood_group"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Emergency Contact:</th>
                                    <td id="view-emergency_contact"></td>
                                </tr>
                                <tr>
                                    <th>Emergency Phone:</th>
                                    <td id="view-emergency_phone"></td>
                                </tr>
                                <tr>
                                    <th>Registration Date:</th>
                                    <td id="view-created_at"></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="view-status"></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td id="view-updated_at"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6><strong>Address:</strong></h6>
                            <p id="view-address" class="text-muted"></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6><strong>Medical History:</strong></h6>
                            <p id="view-medical_history" class="text-muted"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const table = App.initDataTable('#patientsTable', 'ajax/patients_datatable.php', [
                { data: 'id', name: 'id' },
                { 
                    data: 'full_name', 
                    name: 'full_name',
                    render: function(data, type, row) {
                        return `<div class="d-flex align-items-center">
                                    <div class="patient-avatar bg-primary text-white d-flex align-items-center justify-content-center mr-2">
                                        ${row.first_name.charAt(0)}${row.last_name.charAt(0)}
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">${data}</div>
                                        <small class="text-muted">ID: ${row.id}</small>
                                    </div>
                                </div>`;
                    }
                },
                { data: 'phone', name: 'phone' },
                { 
                    data: 'email', 
                    name: 'email',
                    render: function(data) {
                        return data || '<span class="text-muted">N/A</span>';
                    }
                },
                { 
                    data: 'age', 
                    name: 'age',
                    render: function(data) {
                        return data + ' years';
                    }
                },
                { 
                    data: 'gender', 
                    name: 'gender',
                    render: function(data) {
                        return data.charAt(0).toUpperCase() + data.slice(1);
                    }
                },
                { 
                    data: 'created_at', 
                    name: 'created_at',
                    render: function(data) {
                        return App.formatDate(data);
                    }
                },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        const badgeClass = data === 'active' ? 'success' : 'secondary';
                        return `<span class="badge badge-${badgeClass} status-badge">${data.toUpperCase()}</span>`;
                    }
                },
                { 
                    data: 'actions', 
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<div class="action-buttons">
                                    <button class="btn btn-sm btn-info view-btn" 
                                            data-id="${row.id}" 
                                            data-url="api/patients_api.php" 
                                            data-modal="#viewModal"
                                            data-toggle="tooltip" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-id="${row.id}" 
                                            data-url="api/patients_api.php" 
                                            data-modal="#editModal"
                                            data-toggle="tooltip" title="Edit Patient">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" 
                                            data-id="${row.id}" 
                                            data-url="api/patients_api.php" 
                                            data-name="patient"
                                            data-toggle="tooltip" title="Delete Patient">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>`;
                    }
                }
            ]);

            // Load patient stats
            loadPatientStats();
        });

        // Load patient statistics
        function loadPatientStats() {
            $.get('api/patients_stats.php')
                .done(function(response) {
                    if (response.success) {
                        $('#total-patients-count').text(response.data.total || 0);
                        $('#active-patients-count').text(response.data.active || 0);
                        $('#today-visits-count').text(response.data.today_visits || 0);
                        $('#pending-tests-count').text(response.data.pending_tests || 0);
                    }
                })
                .fail(function() {
                    console.log('Failed to load patient statistics');
                });
        }

        // Custom view modal population
        $(document).on('click', '.view-btn', function() {
            const button = $(this);
            const id = button.data('id');
            
            $.ajax({
                url: 'api/patients_api.php',
                type: 'POST',
                data: { action: 'get', id: id },
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        
                        // Populate view modal
                        $('#view-full-name').text((data.first_name || '') + ' ' + (data.last_name || ''));
                        $('#view-phone').text(data.phone || 'N/A');
                        $('#view-email').text(data.email || 'N/A');
                        $('#view-date_of_birth').text(App.formatDate(data.date_of_birth));
                        $('#view-gender').text(data.gender ? data.gender.charAt(0).toUpperCase() + data.gender.slice(1) : 'N/A');
                        $('#view-blood_group').text(data.blood_group || 'N/A');
                        $('#view-emergency_contact').text(data.emergency_contact || 'N/A');
                        $('#view-emergency_phone').text(data.emergency_phone || 'N/A');
                        $('#view-created_at').text(App.formatDateTime(data.created_at));
                        $('#view-updated_at').text(App.formatDateTime(data.updated_at));
                        $('#view-address').text(data.address || 'No address provided');
                        $('#view-medical_history').text(data.medical_history || 'No medical history recorded');
                        
                        const badgeClass = data.status === 'active' ? 'success' : 'secondary';
                        $('#view-status').html(`<span class="badge badge-${badgeClass}">${data.status.toUpperCase()}</span>`);
                        
                        $('#viewModal').modal('show');
                    } else {
                        toastr.error('Failed to load patient details');
                    }
                },
                error: function() {
                    toastr.error('Failed to load patient details');
                }
            });
        });
    </script>
</body>
</html>
