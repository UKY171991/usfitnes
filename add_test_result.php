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

// Fetch test requests and staff for dropdowns
$requests_stmt = $pdo->query("SELECT tr.request_id, CONCAT(p.first_name, ' ', p.last_name, ' - ', t.test_name) AS request_info 
                              FROM Test_Requests tr 
                              JOIN Patients p ON tr.patient_id = p.patient_id 
                              JOIN Tests_Catalog t ON tr.test_id = t.test_id 
                              ORDER BY tr.request_date DESC");
$requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);

$staff_stmt = $pdo->query("SELECT staff_id, CONCAT(first_name, ' ', last_name, ' (', role, ')') AS staff_name FROM Staff ORDER BY staff_name");
$staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $result_value = $_POST['result_value'];
    $comments = $_POST['comments'];
    $recorded_by = $_POST['recorded_by'];

    try {
        if (isset($_POST['result_id']) && !empty($_POST['result_id'])) {
            // Update result
            $result_id = $_POST['result_id'];
            $stmt = $pdo->prepare("UPDATE Test_Results SET request_id = :request_id, result_value = :result_value, comments = :comments, recorded_by = :recorded_by WHERE result_id = :result_id");
            $stmt->execute([
                'request_id' => $request_id,
                'result_value' => $result_value,
                'comments' => $comments,
                'recorded_by' => $recorded_by,
                'result_id' => $result_id
            ]);
        } else {
            // Insert new result
            $stmt = $pdo->prepare("INSERT INTO Test_Results (request_id, result_value, comments, recorded_by) VALUES (:request_id, :result_value, :comments, :recorded_by)");
            $stmt->execute([
                'request_id' => $request_id,
                'result_value' => $result_value,
                'comments' => $comments,
                'recorded_by' => $recorded_by
            ]);
        }
        header("Location: test_results.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
}

// Fetch result data for editing
$edit_result = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Test_Results WHERE result_id = :result_id");
        $stmt->execute(['result_id' => $_GET['edit']]);
        $edit_result = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching result: " . $e->getMessage();
        exit();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | <?php echo $edit_result ? 'Edit Test Result' : 'Add Test Result'; ?></title>
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
                            <h3><?php echo $edit_result ? 'Edit Test Result' : 'Add New Test Result'; ?></h3>
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
                                        <?php if ($edit_result): ?>
                                            <input type="hidden" name="result_id" value="<?php echo $edit_result['result_id']; ?>">
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="request_id" class="form-label">Test Request</label>
                                            <select class="form-control" id="request_id" name="request_id" required>
                                                <option value="">Select Request</option>
                                                <?php foreach ($requests as $request): ?>
                                                    <option value="<?php echo $request['request_id']; ?>" <?php echo $edit_result && $edit_result['request_id'] == $request['request_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($request['request_info']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="result_value" class="form-label">Result Value</label>
                                            <input type="text" class="form-control" id="result_value" name="result_value" value="<?php echo $edit_result ? htmlspecialchars($edit_result['result_value']) : ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="comments" class="form-label">Comments</label>
                                            <textarea class="form-control" id="comments" name="comments"><?php echo $edit_result ? htmlspecialchars($edit_result['comments']) : ''; ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="recorded_by" class="form-label">Recorded By</label>
                                            <select class="form-control" id="recorded_by" name="recorded_by" required>
                                                <option value="">Select Staff</option>
                                                <?php foreach ($staff as $staff_member): ?>
                                                    <option value="<?php echo $staff_member['staff_id']; ?>" <?php echo $edit_result && $edit_result['recorded_by'] == $staff_member['staff_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($staff_member['staff_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="test_results.php" class="btn btn-secondary">Cancel</a>
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