<?php
session_start();

function checkUserAccess() {
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }
}

function checkAdminAccess() {
    checkUserAccess();
    if($_SESSION['role'] != 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

function checkBranchAdminAccess() {
    checkUserAccess();
    if($_SESSION['role'] != 'branch_admin') {
        header("Location: ../index.php");
        exit();
    }
}

function checkTechnicianAccess() {
    checkUserAccess();
    if($_SESSION['role'] != 'technician') {
        header("Location: ../index.php");
        exit();
    }
}

function checkReceptionistAccess() {
    checkUserAccess();
    if($_SESSION['role'] != 'receptionist') {
        header("Location: ../index.php");
        exit();
    }
}
?> 