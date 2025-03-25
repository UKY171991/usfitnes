<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM Patients ORDER BY first_name");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Shiva Pathology Centre | Patients</title>
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
                            <h3>Patient Management</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPatientModal">
                                <i class="fas fa-user-plus"></i> New Patient
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Patient List</h3></div>
                                <div class="card-body">
                                    <?php
                                    if (isset($_SESSION['success'])) {
                                        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>"
                                            . $_SESSION['success'] .
                                            "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        </div>";
                                        unset($_SESSION['success']);
                                    }

                                    if (isset($_SESSION['error'])) {
                                        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>"
                                            . $_SESSION['error'] .
                                            "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        </div>";
                                        unset($_SESSION['error']);
                                    }
                                    ?>

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Date of Birth</th>
                                                <th>Gender</th>
                                                <th>Address</th>
                                                <th>Patient ID</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($patients as $patient): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['date_of_birth']); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['address'] ?: '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['patient_unique_id']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPatientModal<?php echo $patient['patient_id']; ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Edit Patient Modal -->
                                                <div class="modal fade" id="editPatientModal<?php echo $patient['patient_id']; ?>" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editPatientModalLabel">Edit Patient</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="includes/update-patient.php" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label>First Name</label>
                                                                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Last Name</label>
                                                                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($patient['last_name']); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Date of Birth</label>
                                                                        <input type="date" class="form-control" name="date_of_birth" value="<?php echo htmlspecialchars($patient['date_of_birth']); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Gender</label>
                                                                        <select class="form-control" name="gender" required>
                                                                            <option value="Male" <?php echo $patient['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                                                            <option value="Female" <?php echo $patient['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                                                            <option value="Other" <?php echo $patient['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Address</label>
                                                                        <textarea class="form-control" name="address"><?php echo htmlspecialchars($patient['address']); ?></textarea>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Patient ID</label>
                                                                        <input type="text" class="form-control" name="patient_unique_id" value="<?php echo htmlspecialchars($patient['patient_unique_id']); ?>" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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

    <!-- New Patient Modal -->
    <div class="modal fade" id="newPatientModal" tabindex="-1" aria-labelledby="newPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPatientModalLabel">Add New Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="includes/insert-patient.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" required>
                        </div>
                        <div class="mb-3">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" required>
                        </div>
                        <div class="mb-3">
                            <label>Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" required>
                        </div>
                        <div class="mb-3">
                            <label>Gender</label>
                            <select class="form-control" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>
                            <textarea class="form-control" name="address" placeholder="Enter Address"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Patient ID</label>
                            <input type="text" class="form-control" name="patient_unique_id" placeholder="Enter Patient ID" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Patient</button>
                    </div>
                </form>
            </div>
        </div>
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