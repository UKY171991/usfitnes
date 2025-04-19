<?php
require_once 'inc/config.php';

// Redirect to login page if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/auth/login.php");
    exit();
}

// Redirect based on user role
if(isset($_SESSION['user_role'])) {
    switch($_SESSION['user_role']) {
        case 'admin':
            header("Location: " . SITE_URL . "/admin/dashboard.php");
            break;
        case 'branch_admin':
            header("Location: " . SITE_URL . "/branch-admin/dashboard.php");
            break;
        default:
            header("Location: " . SITE_URL . "/users/dashboard.php");
    }
    exit();
}

// If no role is set, redirect to login
header("Location: " . SITE_URL . "/auth/login.php");
exit();
?> 