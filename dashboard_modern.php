<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Dashboard';
$pageIcon = 'fas fa-tachometer-alt';
$breadcrumbs = ['Dashboard'];

include 'includes/adminlte_template_header_modern.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="<?php echo $pageIcon; ?> mr-2 text-primary"></i>
                        <?php echo $pageTitle; ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard_modern.php">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Statistics Cards Row -->
            <div class="row" id="statsCards">
                <!-- Stats will be loaded via AJAX -->
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading dashboard statistics...</p>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mt-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>Add</h3>
                            <p>New Patient</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <a href="#" class="small-box-footer" onclick="openPatientModal()">
                            Add Now <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>Create</h3>
                            <p>Test Order</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <a href="#" class="small-box-footer" onclick="openTestOrderModal()">
                            Create Now <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>Enter</h3>
                            <p>Test Results</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <a href="#" class="small-box-footer" onclick="openResultsModal()">
                            Enter Now <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>View</h3>
                            <p>Reports</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <a href="reports.php" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Charts and Tables Row -->
            <div class="row">
                <!-- Monthly Chart -->
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Monthly Statistics
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyChart" style="min-height: 300px; height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-1"></i>
                                Recent Activities
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div id="recentActivities">
                                <!-- Activities loaded via AJAX -->
                                <div class="text-center p-3">
                                    <div class="spinner-border text-info" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Test Orders Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Recent Test Orders
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-primary" onclick="openTestOrderModal()">
                                    <i class="fas fa-plus"></i> New Order
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="recentOrdersTable">
                                <!-- Table loaded via AJAX -->
                                <div class="text-center">
                                    <div class="spinner-border text-secondary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading recent orders...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div><!-- /.container-fluid -->
    </section>
</div>

<!-- Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-user-plus"></i>
                    <span id="patientModalTitle">Add New Patient</span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="patientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateOfBirth">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Order Modal -->
<div class="modal fade" id="testOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">
                    <i class="fas fa-flask"></i>
                    Create Test Order
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="testOrderForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="patientSelect">Patient <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="patientSelect" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="doctorSelect">Doctor <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="doctorSelect" name="doctor_id" required>
                                    <option value="">Select Doctor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="testType">Test Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="testType" name="test_type" required>
                                    <option value="">Select Test</option>
                                    <option value="Blood Test">Blood Test</option>
                                    <option value="Urine Test">Urine Test</option>
                                    <option value="X-Ray">X-Ray</option>
                                    <option value="CT Scan">CT Scan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="urgency">Urgency <span class="text-danger">*</span></label>
                                <select class="form-control" id="urgency" name="urgency" required>
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        loadDashboardStats();
        loadRecentActivities();
    }, 300000);
});

function initializeDashboard() {
    loadDashboardStats();
    loadRecentActivities();
    loadRecentOrders();
    initializeChart();
    loadPatientOptions();
    loadDoctorOptions();
}

function loadDashboardStats() {
    $.ajax({
        url: 'ajax/dashboard_stats.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateStatsCards(response.data);
            } else {
                showToast('error', 'Error loading statistics');
            }
        },
        error: function() {
            showToast('error', 'Failed to load dashboard statistics');
        }
    });
}

function updateStatsCards(stats) {
    const cardsHtml = `
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-user-injured"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Patients</span>
                    <span class="info-box-number">${stats.total_patients}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-flask"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Today's Tests</span>
                    <span class="info-box-number">${stats.todays_tests}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Results</span>
                    <span class="info-box-number">${stats.pending_results}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger"><i class="fas fa-user-md"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Doctors</span>
                    <span class="info-box-number">${stats.total_doctors}</span>
                </div>
            </div>
        </div>
    `;
    $('#statsCards').html(cardsHtml);
}

function loadRecentActivities() {
    $.ajax({
        url: 'ajax/recent_activities.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateRecentActivities(response.data);
            }
        },
        error: function() {
            $('#recentActivities').html('<div class="text-center p-3 text-muted">Failed to load activities</div>');
        }
    });
}

function updateRecentActivities(activities) {
    let html = '<ul class="nav nav-pills nav-stacked">';
    
    if (activities.length === 0) {
        html += '<li class="text-center p-3 text-muted">No recent activities</li>';
    } else {
        activities.forEach(activity => {
            html += `
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="${activity.icon} text-${activity.color}"></i>
                        ${activity.message}
                        <span class="float-right text-sm text-muted">${activity.time}</span>
                    </span>
                </li>
            `;
        });
    }
    
    html += '</ul>';
    $('#recentActivities').html(html);
}

function loadRecentOrders() {
    $.ajax({
        url: 'ajax/recent_orders.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateRecentOrdersTable(response.data);
            }
        },
        error: function() {
            $('#recentOrdersTable').html('<div class="text-center text-muted">Failed to load recent orders</div>');
        }
    });
}

function updateRecentOrdersTable(orders) {
    if (orders.length === 0) {
        $('#recentOrdersTable').html(`
            <div class="text-center p-4">
                <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Test Orders Yet</h5>
                <button class="btn btn-primary" onclick="openTestOrderModal()">
                    <i class="fas fa-plus"></i> Create First Order
                </button>
            </div>
        `);
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Test Type</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    orders.forEach(order => {
        html += `
            <tr>
                <td>${order.patient_name}</td>
                <td>${order.test_type}</td>
                <td>${order.doctor_name}</td>
                <td>${order.status_badge}</td>
                <td>${order.created_date}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-info" onclick="viewOrder(${order.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-warning" onclick="editOrder(${order.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="test-orders.php" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> View All Orders
            </a>
        </div>
    `;
    
    $('#recentOrdersTable').html(html);
}

function initializeChart() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    
    $.ajax({
        url: 'ajax/monthly_stats.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.data.labels,
                        datasets: [{
                            label: 'Tests Performed',
                            data: response.data.values,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },
        error: function() {
            // Show empty chart
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        label: 'Tests',
                        data: [0],
                        borderColor: '#6c757d'
                    }]
                }
            });
        }
    });
}

// Modal Functions
function openPatientModal(id = null) {
    if (id) {
        // Edit mode - load patient data
        loadPatientData(id);
        $('#patientModalTitle').text('Edit Patient');
    } else {
        // Add mode - reset form
        $('#patientForm')[0].reset();
        $('#patientModalTitle').text('Add New Patient');
    }
    $('#patientModal').modal('show');
}

function openTestOrderModal(id = null) {
    if (id) {
        loadTestOrderData(id);
    } else {
        $('#testOrderForm')[0].reset();
    }
    $('#testOrderModal').modal('show');
}

function openResultsModal(orderId = null) {
    // Implement results modal
    showToast('info', 'Results modal coming soon');
}

// Form Submissions
$('#patientForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: 'ajax/patient_save.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#patientModal').modal('hide');
                showToast('success', response.message);
                loadDashboardStats(); // Refresh stats
                loadPatientOptions(); // Refresh patient dropdown
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to save patient');
        }
    });
});

$('#testOrderForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: 'ajax/test_order_save.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#testOrderModal').modal('hide');
                showToast('success', response.message);
                loadDashboardStats();
                loadRecentOrders();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to save test order');
        }
    });
});

// Load dropdown options
function loadPatientOptions() {
    $.ajax({
        url: 'ajax/get_patients.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Patient</option>';
                response.data.forEach(patient => {
                    options += `<option value="${patient.id}">${patient.name}</option>`;
                });
                $('#patientSelect').html(options);
            }
        }
    });
}

function loadDoctorOptions() {
    $.ajax({
        url: 'ajax/get_doctors.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Doctor</option>';
                response.data.forEach(doctor => {
                    options += `<option value="${doctor.id}">${doctor.name}</option>`;
                });
                $('#doctorSelect').html(options);
            }
        }
    });
}

// Utility Functions
function showToast(type, message) {
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };
    
    switch(type) {
        case 'success':
            toastr.success(message);
            break;
        case 'error':
            toastr.error(message);
            break;
        case 'warning':
            toastr.warning(message);
            break;
        case 'info':
            toastr.info(message);
            break;
    }
}

function viewOrder(id) {
    // Implement view order functionality
    window.open('test-orders.php?action=view&id=' + id, '_blank');
}

function editOrder(id) {
    openTestOrderModal(id);
}
</script>

<?php include 'includes/adminlte_template_footer_modern.php'; ?>
