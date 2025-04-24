<?php
// Suppress output of errors and log them instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

require_once '../../inc/db.php';

header('Content-Type: application/json');

try {
    // Fetch test names
    $stmt = $conn->prepare("SELECT id, test_name FROM tests ORDER BY test_name");
    $stmt->execute();
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch parameters grouped by test_id
    $stmt = $conn->prepare("SELECT test_id, parameter_name, default_unit, normal_value FROM test_parameters ORDER BY test_id, parameter_name");
    $stmt->execute();
    $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group parameters by test_id
    $groupedParameters = [];
    foreach ($parameters as $parameter) {
        $groupedParameters[$parameter['test_id']][] = [
            'parameter_name' => $parameter['parameter_name'],
            'default_unit' => $parameter['default_unit'],
            'normal_value' => $parameter['normal_value']
        ];
    }

    echo json_encode([
        'success' => true,
        'tests' => $tests,
        'parameters' => $groupedParameters
    ]);
} catch (Exception $e) {
    error_log('Error fetching test parameters: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data. Please check the logs.'
    ]);
}