<?php
require_once '../db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $parameter_name = trim($_POST['parameter_name']);
    if (empty($parameter_name)) {
        echo json_encode(['success' => false, 'message' => 'Parameter name is required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Test_Parameters (parameter_name) VALUES (:parameter_name)");
        $stmt->execute(['parameter_name' => $parameter_name]);
        echo json_encode(['success' => true, 'message' => 'Parameter added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding parameter: ' . $e->getMessage()]);
    }
} elseif ($action === 'edit') {
    $parameter_id = trim($_POST['parameter_id']);
    $parameter_name = trim($_POST['parameter_name']);
    if (empty($parameter_id) || empty($parameter_name)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE Test_Parameters SET parameter_name = :parameter_name WHERE parameter_id = :parameter_id");
        $stmt->execute(['parameter_name' => $parameter_name, 'parameter_id' => $parameter_id]);
        echo json_encode(['success' => true, 'message' => 'Parameter updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating parameter: ' . $e->getMessage()]);
    }
} elseif ($action === 'delete') {
    $parameter_id = trim($_POST['parameter_id']);
    if (empty($parameter_id)) {
        echo json_encode(['success' => false, 'message' => 'Parameter ID is required']);
        exit();
    }

    try {
        // Check if the parameter is in use
        $stmt = $pdo->prepare("SELECT test_id, parameters FROM Tests_Catalog");
        $stmt->execute();
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $parameter_stmt = $pdo->prepare("SELECT parameter_name FROM Test_Parameters WHERE parameter_id = :parameter_id");
        $parameter_stmt->execute(['parameter_id' => $parameter_id]);
        $parameter_name = $parameter_stmt->fetchColumn();

        $in_use = false;
        foreach ($tests as $test) {
            $parameters = explode(',', $test['parameters']);
            if (in_array($parameter_name, $parameters)) {
                $in_use = true;
                break;
            }
        }

        if ($in_use) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete parameter: It is used in one or more tests']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM Test_Parameters WHERE parameter_id = :parameter_id");
        $stmt->execute(['parameter_id' => $parameter_id]);
        echo json_encode(['success' => true, 'message' => 'Parameter deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting parameter: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}