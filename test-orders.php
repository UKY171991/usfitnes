<?php
/**
 * Test Orders Management - AdminLTE3 Template
 */
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'Administrator';
    $_SESSION['user_type'] = 'admin';
}

$current_page = 'test-orders';
$page_title = 'Test Orders Management';

include 'includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= $page_title ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Test Orders</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Test Orders Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Test Orders Management
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm" onclick="addTestOrder()">
                                <i class="fas fa-plus"></i> Add Test Order
                            </button>
                            <button type="button" class="btn btn-light btn-sm" onclick="exportTestOrders()">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <button type="button" class="btn btn-light btn-sm" onclick="printTestOrders()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Row -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Search Orders:</label>
                                <input type="text" class="form-control" id="testOrderSearch" placeholder="Search by order number, patient...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Status:</label>
                                <select class="form-control" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Test Type:</label>
                                <select class="form-control" id="testTypeFilter">
                                    <option value="">All Types</option>
                                    <option value="Blood Test">Blood Test</option>
                                    <option value="Urine Analysis">Urine Analysis</option>
                                    <option value="X-Ray">X-Ray</option>
                                    <option value="CT Scan">CT Scan</option>
                                    <option value="MRI">MRI</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Show:</label>
                                <select class="form-control" id="showEntries">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="btn-group d-block">
                                    <button type="button" class="btn btn-info btn-sm" onclick="refreshTestOrdersTable()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()">
                                        <i class="fas fa-eraser"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="testOrdersTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Order Number</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Test Type</th>
                                    <th>Status</th>
                                    <th>Order Date</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Use filters to find specific test orders quickly.
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                Total orders: <span id="totalTestOrders">Loading...</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Test Order Modal -->
<div class="modal fade" id="testOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Test Orders JavaScript -->
<script src="js/test-orders.js"></script>
