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
$allowed_roles = ['Admin'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $profile_image = null;

    // Handle image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/img/';
        $file_name = basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image = $target_file;
        }
    }

    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        // Update user
        $user_id = $_POST['user_id'];
        $stmt = $pdo->prepare("UPDATE Users SET username = :username, first_name = :first_name, last_name = :last_name, email = :email, password = :password, role = :role, profile_image = :profile_image WHERE user_id = :user_id");
        $stmt->execute([
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'profile_image' => $profile_image,
            'user_id' => $user_id
        ]);
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO Users (username, password, first_name, last_name, email, role, profile_image) VALUES (:username, :password, :first_name, :last_name, :email, :role, :profile_image)");
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'role' => $role,
            'profile_image' => $profile_image
        ]);
    }
    header("Location: users.php");
    exit();
}

// Fetch user data for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Pathology | <?php echo $edit_user ? 'Edit User' : 'Add User'; ?></title>
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
                            <h3><?php echo $edit_user ? 'Edit User' : 'Add New User'; ?></h3>
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
                                    <form method="post" enctype="multipart/form-data">
                                        <?php if ($edit_user): ?>
                                            <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                                        <?php endif; ?>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $edit_user ? htmlspecialchars($edit_user['username']) : ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $edit_user ? htmlspecialchars($edit_user['first_name']) : ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $edit_user ? htmlspecialchars($edit_user['last_name']) : ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" <?php echo $edit_user ? '' : 'required'; ?>>
                                            <?php if ($edit_user): ?>
                                                <small class="form-text text-muted">Leave blank to keep current password.</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Role</label>
                                            <select class="form-control" id="role" name="role" required>
                                                <option value="Admin" <?php echo $edit_user && $edit_user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                                <option value="Doctor" <?php echo $edit_user && $edit_user['role'] === 'Doctor' ? 'selected' : ''; ?>>Doctor</option>
                                                <option value="Technician" <?php echo $edit_user && $edit_user['role'] === 'Technician' ? 'selected' : ''; ?>>Technician</option>
                                                <option value="Receptionist" <?php echo $edit_user && $edit_user['role'] === 'Receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="profile_image" class="form-label">Profile Image</label>
                                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                            <?php if ($edit_user && !empty($edit_user['profile_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($edit_user['profile_image']); ?>" alt="Profile Image" class="img-thumbnail mt-2" style="width: 100px; height: 100px; object-fit: cover;">
                                            <?php endif; ?>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="users.php" class="btn btn-secondary">Cancel</a>
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