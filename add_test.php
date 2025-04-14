<?php
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict access based on roles
$allowed_roles = ['Admin', 'Doctor'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_name = $_POST['test_name'];
    $test_code = $_POST['test_code'];
    $description = $_POST['description'];
    $normal_range = $_POST['normal_range'];
    $unit = $_POST['unit'];
    $price = $_POST['price'];

    try {
        if (isset($_POST['test_id']) && !empty($_POST['test_id'])) {
            // Update test
            $test_id = $_POST['test_id'];
            $stmt = $pdo->prepare("UPDATE Tests_Catalog SET test_name = :test_name, test_code = :test_code, description = :description, normal_range = :normal_range, unit = :unit, price = :price WHERE test_id = :test_id");
            $stmt->execute([
                'test_name' => $test_name,
                'test_code' => $test_code,
                'description' => $description,
                'normal_range' => $normal_range,
                'unit' => $unit,
                'price' => $price,
                'test_id' => $test_id
            ]);
        } else {
            // Insert new test
            $stmt = $pdo->prepare("INSERT INTO Tests_Catalog (test_name, test_code, description, normal_range, unit, price) VALUES (:test_name, :test_code, :description, :normal_range, :unit, :price)");
            $stmt->execute([
                'test_name' => $test_name,
                'test_code' => $test_code,
                'description' => $description,
                'normal_range' => $normal_range,
                'unit' => $unit,
                'price' => $price
            ]);
        }
        header("Location: tests.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
}

// Fetch test data for editing
$edit_test = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Tests_Catalog WHERE test_id = :test_id");
        $stmt->execute(['test_id' => $_GET['edit']]);
        $edit_test = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching test: " . $e->getMessage();
        exit();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | <?php echo $edit_test ? 'Edit Test' : 'Add Test'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include('inc/head.php'); ?>
    <style>
        .card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        .btn-secondary {
            margin-left: 5px;
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
                    <div class="row mb-2 mt-2">
                        <div class="col-sm-6">
                            <h3><?php echo $edit_test ? 'Edit Test' : 'Add New Test'; ?></h3>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><?php echo $edit_test ? 'Edit Test' : 'Add New Test'; ?></h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <?php if ($edit_test): ?>
                                            <input type="hidden" name="test_id" value="<?php echo $edit_test['test_id']; ?>">
                                        <?php endif; ?>
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="w-auto px-2">Test Details</legend>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="test_name" class="form-label">Test Name</label>
                                                        <input type="text" class="form-control" id="test_name" name="test_name" value="<?php echo $edit_test ? htmlspecialchars($edit_test['test_name']) : ''; ?>" required>
                                                        <div class="invalid-feedback">Please enter the test name.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="test_code" class="form-label">Test Code</label>
                                                        <input type="text" class="form-control" id="test_code" name="test_code" value="<?php echo $edit_test ? htmlspecialchars($edit_test['test_code']) : ''; ?>" required>
                                                        <div class="invalid-feedback">Please enter the test code.</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control" id="description" name="description"><?php echo $edit_test ? htmlspecialchars($edit_test['description']) : ''; ?></textarea>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="w-auto px-2">Test Specifications</legend>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="normal_range" class="form-label">Normal Range</label>
                                                        <input type="text" class="form-control" id="normal_range" name="normal_range" value="<?php echo $edit_test ? htmlspecialchars($edit_test['normal_range']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="unit" class="form-label">Unit</label>
                                                        <input type="text" class="form-control" id="unit" name="unit" value="<?php echo $edit_test ? htmlspecialchars($edit_test['unit']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label for="price" class="form-label">Price ($)</label>
                                                        <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $edit_test ? htmlspecialchars($edit_test['price']) : ''; ?>" required>
                                                        <div class="invalid-feedback">Please enter the price.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bi bi-save"></i> Save
                                            </button>
                                            <a href="tests.php" class="btn btn-secondary">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Anything you want</div>
            <strong>Copyright © 2025 <a href="#" class="text-decoration-none">Pathology System</a>.</strong> All rights reserved.
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