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

// Restrict to Admin, Doctor, Technician with proper role check
$allowed_roles = ['Admin', 'Doctor', 'Technician'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: index3.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid request');
    }

    try {
        // Validate and sanitize input
        $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
        $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
        $date_of_birth = filter_input(INPUT_POST, 'date_of_birth', FILTER_SANITIZE_STRING);
        $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
        $user_id = $_SESSION['user_id'];

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($date_of_birth) || empty($gender)) {
            throw new Exception('Required fields are missing');
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_of_birth)) {
            throw new Exception('Invalid date format');
        }

        // Validate gender
        if (!in_array($gender, ['Male', 'Female', 'Other'])) {
            throw new Exception('Invalid gender');
        }

        if (isset($_POST['patient_id']) && !empty($_POST['patient_id'])) {
            // Update patient
            $patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);
            if ($patient_id === false) {
                throw new Exception('Invalid patient ID');
            }

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
            
            // Log the update
            error_log("Patient updated by user {$_SESSION['user_id']}: Patient ID {$patient_id}");
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
            
            // Log the creation
            error_log("New patient created by user {$_SESSION['user_id']}");
        }
        
        header("Location: patients.php");
        exit();
    } catch (Exception $e) {
        error_log("Patient form error: " . $e->getMessage());
        $error = $e->getMessage();
    }
}

// Fetch patient data for editing
$edit_patient = null;
if (isset($_GET['edit'])) {
    try {
        $patient_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
        if ($patient_id === false) {
            throw new Exception('Invalid patient ID');
        }

        $stmt = $pdo->prepare("SELECT * FROM Patients WHERE patient_id = :patient_id");
        $stmt->execute(['patient_id' => $patient_id]);
        $edit_patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$edit_patient) {
            throw new Exception('Patient not found');
        }
    } catch (Exception $e) {
        error_log("Error fetching patient: " . $e->getMessage());
        $error = $e->getMessage();
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
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><?php echo $edit_patient ? 'Edit Patient' : 'Add New Patient'; ?></h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" novalidate>
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <?php if ($edit_patient): ?>
                                            <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($edit_patient['patient_id']); ?>">
                                        <?php endif; ?>
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="w-auto px-2">Personal Information</legend>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="first_name" class="form-label">First Name</label>
                                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['first_name']) : ''; ?>" required>
                                                        <div class="invalid-feedback">Please enter the first name.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="last_name" class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['last_name']) : ''; ?>" required>
                                                        <div class="invalid-feedback">Please enter the last name.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['date_of_birth']) : ''; ?>" required>
                                                        <div class="invalid-feedback">Please select a valid date of birth.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="gender" class="form-label">Gender</label>
                                                        <select class="form-control" id="gender" name="gender" required>
                                                            <option value="Male" <?php echo $edit_patient && $edit_patient['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                                            <option value="Female" <?php echo $edit_patient && $edit_patient['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                                            <option value="Other" <?php echo $edit_patient && $edit_patient['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                                        </select>
                                                        <div class="invalid-feedback">Please select a gender.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-3 mb-4">
                                            <legend class="w-auto px-2">Contact Information</legend>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="phone" class="form-label">Contact Number</label>
                                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['phone']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="email" class="form-label">Email</label>
                                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $edit_patient ? htmlspecialchars($edit_patient['email']) : ''; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label for="address" class="form-label">Address</label>
                                                        <textarea class="form-control" id="address" name="address"><?php echo $edit_patient ? htmlspecialchars($edit_patient['address']) : ''; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bi bi-save"></i> Save
                                            </button>
                                            <a href="patients.php" class="btn btn-secondary">
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