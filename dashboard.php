<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Shiva Pathology Centre | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include('inc/head.php'); ?>
    <style>
        .card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
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
                    <div class="row mb-4">
                        <div class="col-sm-6">
                            <h3>Dashboard</h3>
                            <p class="text-muted">Welcome to the Shiva Pathology Centre dashboard. Manage your operations efficiently.</p>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-primary text-white d-flex align-items-center">
                                    <i class="bi bi-clipboard-data me-2"></i>
                                    <h5 class="mb-0">Manage Tests</h5>
                                </div>
                                <div class="card-body">
                                    <p>Add or edit pathology tests like CBC.</p>
                                    <a href="test.php" class="btn btn-primary">
                                        <i class="bi bi-arrow-right-circle"></i> Go to Tests
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-success text-white d-flex align-items-center">
                                    <i class="bi bi-person-lines-fill me-2"></i>
                                    <h5 class="mb-0">Manage Patients</h5>
                                </div>
                                <div class="card-body">
                                    <p>View and manage patient records.</p>
                                    <a href="patients.php" class="btn btn-success">
                                        <i class="bi bi-arrow-right-circle"></i> Go to Patients
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-warning text-white d-flex align-items-center">
                                    <i class="bi bi-file-earmark-bar-graph me-2"></i>
                                    <h5 class="mb-0">Generate Reports</h5>
                                </div>
                                <div class="card-body">
                                    <p>Generate pathology reports for patients.</p>
                                    <a href="reports.php" class="btn btn-warning text-white">
                                        <i class="bi bi-arrow-right-circle"></i> Go to Reports
                                    </a>
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
    <script>
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
    </script>
</body>
</html>