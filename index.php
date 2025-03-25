<?php
session_start();
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Shiva Pathology Centre</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include('inc/head.php'); ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <main class="app-main">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 mt-2">
                        <div class="col-sm-6">
                            <h3>Welcome to Shiva Pathology Centre</h3>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Pathology Services</h3></div>
                                <div class="card-body">
                                    <p>We provide comprehensive pathology services, including blood tests like Complete Blood Count (CBC).</p>
                                    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary">Login</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Anything you want</div>
            <strong>Copyright © 2025 <a href="#" class="text-decoration-none">Shiva Pathology Centre</a>.</strong> All rights reserved.
        </footer>
    </div>
    <?php include('inc/js.php'); ?>
</body>
</html>