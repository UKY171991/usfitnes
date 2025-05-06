<?php
// Check if this file is included from a page in a subdirectory
$current_path = $_SERVER['PHP_SELF'];
$is_subdirectory = substr_count($current_path, '/') > 2;
$base_path = $is_subdirectory ? '../' : '';

// Ensure session and required files are included
if(!isset($config_loaded)) {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/db.php';
}

// Check if user is logged in and has branch admin access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'branch_admin') {
    header("Location: " . $base_path . "auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Admin - Pathology CRM</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: #fff !important;
        }
        .navbar {
            background-color: #2c3e50 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        .nav-link.active {
            color: #fff !important;
            background-color: rgba(255,255,255,0.2);
            border-radius: 4px;
        }
        .user-info {
            color: rgba(255,255,255,0.8);
            margin-right: 1rem;
        }
        .logout-btn {
            background-color: #e74c3c;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            color: #fff;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #c0392b;
            color: #fff;
        }
        .sidebar {
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
            padding-top: 1rem;
            border-right: 1px solid #dee2e6;
        }
        .sidebar .nav-link {
            color: #2c3e50 !important;
            padding: 0.8rem 1rem !important;
            border-radius: 4px;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
        }
        .sidebar .nav-link.active {
            background-color: #007bff;
            color: #fff !important;
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        .content {
            padding: 1.5rem;
        }
        .page-title {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/branch-admin/dashboard.php">
                <i class="fas fa-flask me-2"></i>Branch Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="d-flex align-items-center ms-auto">
                    <span class="user-info">
                        <i class="fas fa-user me-1"></i>
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    </span>
                    <a href="/auth/logout.php" class="btn logout-btn">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/branch-sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto content">
                <!-- Content will be injected here --> 