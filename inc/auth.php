<?php
session_start();

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>