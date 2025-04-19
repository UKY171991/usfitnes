<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Redirect based on user role
switch($_SESSION['role']) {
    case 'admin':
        header("Location: admin/dashboard.php");
        break;
    case 'branch_admin':
        header("Location: branch-admin/dashboard.php");
        break;
    case 'technician':
    case 'receptionist':
        header("Location: users/view-patients.php");
        break;
    default:
        // If role is not recognized, log out the user
        session_destroy();
        header("Location: auth/login.php?error=invalid_role");
        break;
}
exit();
?> 