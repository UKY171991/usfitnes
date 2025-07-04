<?php
session_start();

// Simple login for testing
if (isset($_POST['username']) && $_POST['username'] === 'admin' && $_POST['password'] === 'admin') {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['user_type'] = 'admin';
    header('Location: dashboard.php');
    exit();
}

// Auto-login for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['user_type'] = 'admin';
}

require_once 'config.php';

$status = [
    'database' => false,
    'tables' => [],
    'apis' => []
];

try {
    // Test database connection
    $pdo->query("SELECT 1");
    $status['database'] = true;
    
    // Check tables
    $tables = ['users', 'patients', 'tests', 'doctors', 'test_orders'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            $status['tables'][$table] = $count;
        } catch (Exception $e) {
            $status['tables'][$table] = 'Error: ' . $e->getMessage();
        }
    }
    
    // Test APIs
    $apis = ['tests_api.php', 'doctors_api.php', 'test_orders_api.php', 'patients_api.php'];
    foreach ($apis as $api) {
        $status['apis'][$api] = file_exists("api/$api");
    }
    
} catch (Exception $e) {
    $status['error'] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PathLab Pro - System Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
        <div class="container">
            <a href="#" class="navbar-brand">
                <span class="brand-text font-weight-light">PathLab Pro - System Status</span>
            </a>
        </div>
    </nav>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">System Status Check</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Database Status</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-<?php echo $status['database'] ? 'success' : 'danger'; ?>">
                                                <i class="fas fa-<?php echo $status['database'] ? 'check' : 'times'; ?>"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Database Connection</span>
                                                <span class="info-box-number"><?php echo $status['database'] ? 'Connected' : 'Failed'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (isset($status['error'])): ?>
                                <div class="alert alert-danger">
                                    <strong>Error:</strong> <?php echo htmlspecialchars($status['error']); ?>
                                </div>
                                <?php endif; ?>
                                
                                <h5>Tables Status:</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Table</th>
                                                <th>Records</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($status['tables'] as $table => $count): ?>
                                            <tr>
                                                <td><?php echo $table; ?></td>
                                                <td><?php echo is_numeric($count) ? $count : 'N/A'; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo is_numeric($count) ? 'success' : 'danger'; ?>">
                                                        <?php echo is_numeric($count) ? 'OK' : 'Error'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <h5>API Files Status:</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>API File</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($status['apis'] as $api => $exists): ?>
                                            <tr>
                                                <td><?php echo $api; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $exists ? 'success' : 'danger'; ?>">
                                                        <?php echo $exists ? 'Exists' : 'Missing'; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Quick Actions</h3>
                            </div>
                            <div class="card-body">
                                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                                <a href="tests.php" class="btn btn-info">Tests Page</a>
                                <a href="test-orders.php" class="btn btn-success">Test Orders</a>
                                <a href="doctors.php" class="btn btn-warning">Doctors</a>
                                <button onclick="location.reload()" class="btn btn-secondary">Refresh Status</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
