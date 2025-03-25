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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $user_id = $_SESSION['user_id'];

    try {
        if (isset($_POST['patient_id']) && !empty($_POST['patient_id'])) {
            // Update patient
            $patient_id = $_POST['patient_id'];
            $stmt = $pdo->prepare("UPDATE Patients SET first_name = :first_name, last_name = :last_name, date_of_birth = :date_of_birth, gender = :gender, phone = :phone, email = :email, address = :address WHERE patient_id = :patient_id");
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'patient_id' => $patient_id
            ]);
        } else {
            // Insert new patient
            $stmt = $pdo->prepare("INSERT INTO Patients (first_name, last_name, date_of_birth, gender, phone, email, address, user_id) VALUES (:first_name, :last_name, :date_of_birth, :gender, :phone, :email, :address, :user_id)");
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'user_id' => $user_id
            ]);
        }
        header("Location: patients.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Debugging output
        exit();
    }
}

// Fetch patient data for editing
$edit_patient = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Patients WHERE patient_id = :patient_id");
        $stmt->execute(['patient_id' => $_GET['edit']]);
        $edit_patient = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching patient: " . $e->getMessage(); // Debugging output
        exit();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | <?php echo $edit_patient ? 'Edit Patient' : 'Add Patient'; ?></title>
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
                            <h3><?php echo $edit_patient ? 'Edit Patient' : 'Add New Patient'; ?></h3>
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
                                        <?php if ($edit_patient): ?>
                                            <input type="hidden" name="patient_id" value="<?php echo $edit_patient['patient_id']; ?>">
                                        <?php endif; ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="first_name" class="form-label">First Name</label>
                                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['first_name']) : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="last_name" class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['last_name']) : ''; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6"></div>
                                            <div class="col-md-6"></div>
                                            <div class="col-md-6"></div>
                                            <div class="col-md-6"></div>
                                            <div class="col-md-6"></div>
                                        </div>
                                        
                                        
                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['date_of_birth']) : ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="gender" class="form-label">Gender</label>
                                            <select class="form-control" id="gender" name="gender" required>
                                                <option value="Male" <?php echo $edit_patient && $edit_patient['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo $edit_patient && $edit_patient['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo $edit_patient && $edit_patient['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['phone']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['email']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <textarea class="form-control" id="address" name="address"><?php echo $edit_patient ? htmlspecialchars($edit_patient['address']) : ''; ?></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="patients.php" class="btn btn-secondary">Cancel</a>
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