<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

header('Content-Type: application/json');

checkBranchAdminAccess(); // Ensure user is a branch admin

$response = ['success' => false, 'patients' => [], 'message' => 'An error occurred.'];
$branch_id = $_SESSION['branch_id'];

try {
    // Get all patients for this branch, ensure gender is lowercase for consistency
    $patients_stmt = $conn->prepare("
        SELECT id, name, age, LOWER(TRIM(gender)) as gender, phone, email, address 
        FROM patients 
        WHERE branch_id = ? 
        ORDER BY name
    ");
    $patients_stmt->execute([$branch_id]);
    $patients_list = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['patients'] = $patients_list;
    $response['message'] = 'Patients fetched successfully.'; // Optional message

} catch (PDOException $e) {
    error_log("Get Patients AJAX Error: " . $e->getMessage());
    $response['message'] = 'Database error occurred while fetching patients.';
}

echo json_encode($response);
?> 