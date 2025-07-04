<?php
// Set page title
$page_title = 'Dashboard';

// Include header
include 'includes/header.php';
// Include sidebar with user info
include 'includes/sidebar.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Laboratory Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1">
                            <i class="fas fa-user-injured"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Patients</span>
                            <span class="info-box-number" id="totalPatients">
                                0
                                <small>%</small>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1">
                            <i class="fas fa-clipboard-list"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Orders</span>
                            <span class="info-box-number" id="pendingOrders">0</span>
                        </div>
                    </div>
                </div>
                <div class="clearfix hidden-md-up"></div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1">
                            <i class="fas fa-file-medical"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Completed Tests</span>
                            <span class="info-box-number" id="completedTests">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Reports Generated</span>
                            <span class="info-box-number" id="reportsGenerated">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main row -->
            <div class="row">
                <!-- Left col -->
                <section class="col-lg-7 connectedSortable">
                    <!-- Custom tabs (Charts with tabs)-->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Laboratory Analytics
                            </h3>
                            <div class="card-tools">
                                <ul class="nav nav-pills ml-auto">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Monthly</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#sales-chart" data-toggle="tab">Weekly</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content p-0">
                                <!-- Morris chart - Sales -->
                                <div class="chart tab-pane active" id="revenue-chart"
                                     style="position: relative; height: 300px;">
                                    <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                                </div>
                                <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                                    <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DIRECT CHAT -->
                    <div class="card direct-chat direct-chat-primary">
                        <div class="card-header">
                            <h3 class="card-title">Recent Lab Activities</h3>
                            <div class="card-tools">
                                <span title="3 New Messages" class="badge badge-primary">3</span>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" title="Contacts"
                                        data-widget="chat-pane-toggle">
                                    <i class="fas fa-comments"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Conversations are loaded here -->
                            <div class="direct-chat-messages">
                                <!-- Message -->
                                <div class="direct-chat-msg">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-left">Dr. Anderson</span>
                                        <span class="direct-chat-timestamp float-right">23 Jan 2:00 pm</span>
                                    </div>
                                    <img class="direct-chat-img" src="https://via.placeholder.com/40x40/007bff/ffffff?text=DR" alt="message user image">
                                    <div class="direct-chat-text">
                                        Patient CBC results are ready for review.
                                    </div>
                                </div>

                                <!-- Message -->
                                <div class="direct-chat-msg right">
                                    <div class="direct-chat-infos clearfix">
                                        <span class="direct-chat-name float-right">Lab Tech</span>
                                        <span class="direct-chat-timestamp float-left">23 Jan 2:05 pm</span>
                                    </div>
                                    <img class="direct-chat-img" src="https://via.placeholder.com/40x40/28a745/ffffff?text=LT" alt="message user image">
                                    <div class="direct-chat-text">
                                        Results verified and sent to doctor.
                                    </div>
                                </div>
                            </div>

                            <!-- Contacts are loaded here -->
                            <div class="direct-chat-contacts">
                                <ul class="contacts-list">
                                    <li>
                                        <a href="#">
                                            <img class="contacts-list-img" src="https://via.placeholder.com/40x40/007bff/ffffff?text=DR" alt="User Avatar">
                                            <div class="contacts-list-info">
                                                <span class="contacts-list-name">
                                                    Dr. Anderson
                                                    <small class="contacts-list-date float-right">2/28/2015</small>
                                                </span>
                                                <span class="contacts-list-msg">How have you been? I was...</span>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-footer">
                            <form action="#" method="post">
                                <div class="input-group">
                                    <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                                    <span class="input-group-append">
                                        <button type="button" class="btn btn-primary">Send</button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Right col -->
                <section class="col-lg-5 connectedSortable">
                    <!-- Calendar -->
                    <div class="card bg-gradient-success">
                        <div class="card-header border-0">
                            <h3 class="card-title">
                                <i class="far fa-calendar-alt"></i>
                                Calendar
                            </h3>
                            <div class="card-tools">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" data-offset="-52">
                                        <i class="fas fa-bars"></i>
                                    </button>
                                    <div class="dropdown-menu" role="menu">
                                        <a href="#" class="dropdown-item">Add new event</a>
                                        <a href="#" class="dropdown-item">Clear events</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item">View calendar</a>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div id="calendar" style="width: 100%"></div>
                        </div>
                    </div>

                    <!-- PRODUCT LIST -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Test Orders</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2" id="recentOrders">
                                <!-- Orders will be loaded here -->
                            </ul>
                        </div>
                        <div class="card-footer text-center">
                            <a href="test-orders.php" class="uppercase">View All Orders</a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Load dashboard data
    loadDashboardStats();
    loadRecentOrders();
    initializeCharts();
    
    // Refresh data every 30 seconds
    setInterval(function() {
        loadDashboardStats();
        loadRecentOrders();
    }, 30000);
});

function loadDashboardStats() {
    $.ajax({
        url: 'api/dashboard_api.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#totalPatients').text(response.data.totalPatients || 0);
                $('#pendingOrders').text(response.data.pendingOrders || 0);
                $('#completedTests').text(response.data.completedTests || 0);
                $('#reportsGenerated').text(response.data.reportsGenerated || 0);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading dashboard stats:', error);
        }
    });
}

function loadRecentOrders() {
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { action: 'getRecent', limit: 5 },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                let ordersHtml = '';
                response.data.forEach(function(order) {
                    const statusClass = order.status === 'Completed' ? 'success' : 
                                      order.status === 'Pending' ? 'warning' : 'info';
                    
                    ordersHtml += `
                        <li class="item">
                            <div class="product-img">
                                <img src="https://via.placeholder.com/50x50/007bff/ffffff?text=${order.patient_name.charAt(0)}" alt="Order Image" class="img-size-50">
                            </div>
                            <div class="product-info">
                                <a href="test-orders.php?id=${order.id}" class="product-title">
                                    ${order.patient_name}
                                    <span class="badge badge-${statusClass} float-right">${order.status}</span>
                                </a>
                                <span class="product-description">
                                    Order ID: ${order.order_id} - ${order.test_name || 'Multiple Tests'}
                                </span>
                            </div>
                        </li>
                    `;
                });
                $('#recentOrders').html(ordersHtml);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading recent orders:', error);
        }
    });
}

function initializeCharts() {
    // Revenue Chart
    var ctx = document.getElementById('revenue-chart-canvas').getContext('2d');
    var revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Tests Completed',
                data: [65, 59, 80, 81, 56, 55, 40],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Sales Chart
    var ctx2 = document.getElementById('sales-chart-canvas').getContext('2d');
    var salesChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Daily Orders',
                data: [12, 19, 3, 5, 2, 3, 9],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
</script>
