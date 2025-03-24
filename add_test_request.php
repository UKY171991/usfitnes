<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict to Admin, Doctor, Technician
if (!in_array($_SESSION['role'], ['Admin', 'Doctor', 'Technician'])) {
    header("Location: index3.php");
    exit();
}

// Fetch patients and tests for dropdowns
$patients_stmt = $pdo->query("SELECT patient_id, CONCAT(first_name, ' ', last_name) AS patient_name FROM Patients ORDER BY patient_name");
$patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

$tests_stmt = $pdo->query("SELECT test_id, test_name FROM Tests_Catalog ORDER BY test_name");
$tests = $tests_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $test_id = $_POST['test_id'];
    $user_id = $_SESSION['user_id'];
    $ordered_by = $_POST['ordered_by'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];

    try {
        if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
            // Update request
            $request_id = $_POST['request_id'];
            $stmt = $pdo->prepare("UPDATE Test_Requests SET patient_id = :patient_id, test_id = :test_id, ordered_by = :ordered_by, status = :status, priority = :priority WHERE request_id = :request_id");
            $stmt->execute([
                'patient_id' => $patient_id,
                'test_id' => $test_id,
                'ordered_by' => $ordered_by,
                'status' => $status,
                'priority' => $priority,
                'request_id' => $request_id
            ]);
        } else {
            // Insert new request
            $stmt = $pdo->prepare("INSERT INTO Test_Requests (patient_id, test_id, user_id, ordered_by, status, priority) VALUES (:patient_id, :test_id, :user_id, :ordered_by, :status, :priority)");
            $stmt->execute([
                'patient_id' => $patient_id,
                'test_id' => $test_id,
                'user_id' => $user_id,
                'ordered_by' => $ordered_by,
                'status' => $status,
                'priority' => $priority
            ]);
        }
        header("Location: test_requests.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
}

// Fetch request data for editing
$edit_request = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Test_Requests WHERE request_id = :request_id");
        $stmt->execute(['request_id' => $_GET['edit']]);
        $edit_request = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching request: " . $e->getMessage();
        exit();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | <?php echo $edit_request ? 'Edit Test Request' : 'Add Test Request'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include('inc/head.php'); ?>
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
                            <h3><?php echo $edit_request ? 'Edit Test Request' : 'Add New Test Request'; ?></h3>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form method="post">
                                        <?php if ($edit_request): ?>
                                            <input type="hidden" name="request_id" value="<?php echo $edit_request['request_id']; ?>">
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="patient_id" class="form-label">Patient</label>
                                            <select class="form-control" id="patient_id" name="patient_id" required>
                                                <option value="">Select Patient</option>
                                                <?php foreach ($patients as $patient): ?>
                                                    <option value="<?php echo $patient['patient_id']; ?>" <?php echo $edit_request && $edit_request['patient_id'] == $patient['patient_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($patient['patient_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="test_id" class="form-label">Test</label>
                                            <select class="form-control" id="test_id" name="test_id" required>
                                                <option value="">Select Test</option>
                                                <?php foreach ($tests as $test): ?>
                                                    <option value="<?php echo $test['test_id']; ?>" <?php echo $edit_request && $edit_request['test_id'] == $test['test_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($test['test_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ordered_by" class="form-label">Ordered By</label>
                                            <input type="text" class="form-control" id="ordered_by" name="ordered_by" value="<?php echo $edit_request ? htmlspecialchars($edit_request['ordered_by']) : ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="Pending" <?php echo $edit_request && $edit_request['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="In Progress" <?php echo $edit_request && $edit_request['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="Completed" <?php echo $edit_request && $edit_request['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="priority" class="form-label">Priority</label>
                                            <select class="form-control" id="priority" name="priority" required>
                                                <option value="Normal" <?php echo $edit_request && $edit_request['priority'] === 'Normal' ? 'selected' : ''; ?>>Normal</option>
                                                <option value="Urgent" <?php echo $edit_request && $edit_request['priority'] === 'Urgent' ? 'selected' : ''; ?>>Urgent</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="test_requests.php" class="btn btn-secondary">Cancel</a>
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