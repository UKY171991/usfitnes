<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.', 's_no' => null, 'report_id' => null];
$branch_id = $_SESSION['branch_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null; // For logging who created/updated

if (!$branch_id) {
    $response['message'] = 'Branch information is missing. Please log in again.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->beginTransaction();
    try {
        // Patient Details (some might be new if patient not selected from list)
        $patient_id = filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT);
        // If patient_id is not provided or is 0, it implies a new patient or manual entry
        // For simplicity, this backend currently assumes an existing patient_id is chosen.
        // A more robust solution would handle creating/updating patient details here if necessary.
        if (empty($patient_id)) {
            throw new Exception("Patient selection is mandatory.");
        }

        // Report Details
        $report_id = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
        $s_no = filter_input(INPUT_POST, 's_no', FILTER_SANITIZE_STRING);
        $fn_hn = filter_input(INPUT_POST, 'fn_hn', FILTER_SANITIZE_STRING);
        $referred_by = filter_input(INPUT_POST, 'ref_by', FILTER_SANITIZE_STRING);
        $report_heading = filter_input(INPUT_POST, 'report_heading', FILTER_SANITIZE_STRING);
        $report_method = filter_input(INPUT_POST, 'report_method', FILTER_SANITIZE_STRING);
        $report_comments = filter_input(INPUT_POST, 'report_comments', FILTER_SANITIZE_STRING);
        $reporting_date_str = filter_input(INPUT_POST, 'reporting_date_display', FILTER_SANITIZE_STRING);
        $reporting_date = date('Y-m-d H:i:s', strtotime($reporting_date_str));

        // Billing Details
        $subtotal = filter_input(INPUT_POST, 'subtotal', FILTER_VALIDATE_FLOAT);
        $discount_percentage = filter_input(INPUT_POST, 'discount_percentage', FILTER_VALIDATE_FLOAT);
        $discount_amount = filter_input(INPUT_POST, 'discount_amount', FILTER_VALIDATE_FLOAT);
        $total_amount = filter_input(INPUT_POST, 'total_amount', FILTER_VALIDATE_FLOAT);
        $paid_amount = filter_input(INPUT_POST, 'paid_amount', FILTER_VALIDATE_FLOAT);
        $balance_amount = filter_input(INPUT_POST, 'balance_amount', FILTER_VALIDATE_FLOAT);
        $payment_status = filter_input(INPUT_POST, 'status_payment', FILTER_SANITIZE_STRING);
        $billing_note = filter_input(INPUT_POST, 'note_billing', FILTER_SANITIZE_STRING);

        // Report Items (JSON string from FormData)
        $report_items_json = $_POST['report_items'] ?? '[]';
        $report_items = json_decode($report_items_json, true);

        // Selected tests (for linking report to tests)
        $selected_tests_json = $_POST['selected_tests'] ?? '[]';
        $selected_tests = json_decode($selected_tests_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid report items data format: " . json_last_error_msg());
        }

        $current_timestamp = date('Y-m-d H:i:s');

        if (empty($s_no)) { // Generate S.No if it's a new report
            // Basic S.No generation - enhance as needed (e.g., sequential per branch)
            $stmt_count = $conn->prepare("SELECT COUNT(*) as count FROM reports WHERE branch_id = :branch_id AND DATE(created_at) = CURDATE()");
            $stmt_count->bindParam(':branch_id', $branch_id, PDO::PARAM_INT);
            $stmt_count->execute();
            $today_reports_count = $stmt_count->fetchColumn();
            $s_no = "B" . $branch_id . "D" . date('ymd') . "N" . ($today_reports_count + 1);
        }

        if ($report_id) { // Update existing report
            $sql_report = "UPDATE reports SET patient_id = :patient_id, s_no = :s_no, fn_hn = :fn_hn, referred_by_doctor_name = :referred_by, report_heading = :report_heading, method = :report_method, comments = :report_comments, reporting_date = :reporting_date, sub_total = :sub_total, discount_percentage = :discount_percentage, discount_amount = :discount_amount, total_amount = :total_amount, paid_amount = :paid_amount, balance_amount = :balance_amount, payment_status = :payment_status, billing_note = :billing_note, updated_at = :updated_at, updated_by = :user_id WHERE id = :report_id AND branch_id = :branch_id";
        } else { // Insert new report
            $sql_report = "INSERT INTO reports (branch_id, patient_id, s_no, fn_hn, referred_by_doctor_name, report_heading, method, comments, reporting_date, sub_total, discount_percentage, discount_amount, total_amount, paid_amount, balance_amount, payment_status, billing_note, created_at, updated_at, created_by, updated_by) VALUES (:branch_id, :patient_id, :s_no, :fn_hn, :referred_by, :report_heading, :report_method, :report_comments, :reporting_date, :sub_total, :discount_percentage, :discount_amount, :total_amount, :paid_amount, :balance_amount, :payment_status, :billing_note, :created_at, :updated_at, :user_id, :user_id)";
        }

        $stmt_report = $conn->prepare($sql_report);
        if (!$report_id) {
            $stmt_report->bindParam(':branch_id', $branch_id, PDO::PARAM_INT);
            $stmt_report->bindParam(':created_at', $current_timestamp);
        }
        $stmt_report->bindParam(':patient_id', $patient_id, PDO::PARAM_INT);
        $stmt_report->bindParam(':s_no', $s_no);
        $stmt_report->bindParam(':fn_hn', $fn_hn);
        $stmt_report->bindParam(':referred_by', $referred_by);
        $stmt_report->bindParam(':report_heading', $report_heading);
        $stmt_report->bindParam(':report_method', $report_method);
        $stmt_report->bindParam(':report_comments', $report_comments);
        $stmt_report->bindParam(':reporting_date', $reporting_date);
        $stmt_report->bindParam(':sub_total', $subtotal);
        $stmt_report->bindParam(':discount_percentage', $discount_percentage);
        $stmt_report->bindParam(':discount_amount', $discount_amount);
        $stmt_report->bindParam(':total_amount', $total_amount);
        $stmt_report->bindParam(':paid_amount', $paid_amount);
        $stmt_report->bindParam(':balance_amount', $balance_amount);
        $stmt_report->bindParam(':payment_status', $payment_status);
        $stmt_report->bindParam(':billing_note', $billing_note);
        $stmt_report->bindParam(':updated_at', $current_timestamp);
        $stmt_report->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        if ($report_id) {
            $stmt_report->bindParam(':report_id', $report_id, PDO::PARAM_INT);
            $stmt_report->bindParam(':branch_id', $branch_id, PDO::PARAM_INT); // For WHERE clause on update
        }
        $stmt_report->execute();

        if (!$report_id) {
            $report_id = $conn->lastInsertId();
        } else {
            // For updates, clear existing report items and test links before re-inserting
            $stmt_delete_items = $conn->prepare("DELETE FROM report_items WHERE report_id = :report_id");
            $stmt_delete_items->bindParam(':report_id', $report_id, PDO::PARAM_INT);
            $stmt_delete_items->execute();

            $stmt_delete_tests_link = $conn->prepare("DELETE FROM report_tests_link WHERE report_id = :report_id");
            $stmt_delete_tests_link->bindParam(':report_id', $report_id, PDO::PARAM_INT);
            $stmt_delete_tests_link->execute();
        }

        // Insert Report Items (Parameters)
        $sql_item = "INSERT INTO report_items (report_id, test_id, test_parameter_id, parameter_name, result_value, unit, reference_range, is_bold, specimen, min_value, max_value, sort_order) VALUES (:report_id, :test_id, :test_parameter_id, :parameter_name, :result_value, :unit, :reference_range, :is_bold, :specimen, :min_value, :max_value, :sort_order)";
        $stmt_item = $conn->prepare($sql_item);
        $sort_order = 0;
        foreach ($report_items as $item) {
            $sort_order++;
            $stmt_item->bindParam(':report_id', $report_id, PDO::PARAM_INT);
            $stmt_item->bindValue(':test_id', $item['test_id'] ? (int)$item['test_id'] : null, PDO::PARAM_INT);
            $stmt_item->bindValue(':test_parameter_id', $item['test_parameter_id'] ? (int)$item['test_parameter_id'] : null, PDO::PARAM_INT);
            $stmt_item->bindParam(':parameter_name', $item['parameter_name']);
            $stmt_item->bindParam(':result_value', $item['result']);
            $stmt_item->bindParam(':unit', $item['unit']);
            $stmt_item->bindParam(':reference_range', $item['ref_range']);
            $stmt_item->bindValue(':is_bold', $item['is_bold'] ? 1 : 0, PDO::PARAM_INT);
            $stmt_item->bindParam(':specimen', $item['specimen']);
            $stmt_item->bindParam(':min_value', $item['min_value']);
            $stmt_item->bindParam(':max_value', $item['max_value']);
            $stmt_item->bindParam(':sort_order', $sort_order, PDO::PARAM_INT);
            $stmt_item->execute();
        }

        // Link report to selected tests (report_tests_link table)
        if (!empty($selected_tests)) {
            $sql_test_link = "INSERT INTO report_tests_link (report_id, test_id, price_at_billing) VALUES (:report_id, :test_id, :price_at_billing)";
            $stmt_test_link = $conn->prepare($sql_test_link);
            foreach ($selected_tests as $selected_test) {
                $stmt_test_link->bindParam(':report_id', $report_id, PDO::PARAM_INT);
                $stmt_test_link->bindParam(':test_id', $selected_test['id'], PDO::PARAM_INT);
                $stmt_test_link->bindParam(':price_at_billing', $selected_test['price']);
                $stmt_test_link->execute();
            }
        }

        // TODO: Payment recording logic if status is Paid or Partial
        // This might involve inserting into a 'payments' table and linking to the report_id

        $conn->commit();
        $response['success'] = true;
        $response['message'] = $report_id && !isset($_POST['report_id']) ? 'Report created successfully.' : 'Report updated successfully.';
        $response['s_no'] = $s_no;
        $response['report_id'] = $report_id;

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Add/Update Report AJAX Error (PDO): " . $e->getMessage() . " Data: " . json_encode($_POST));
        $response['message'] = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Add/Update Report AJAX Error: " . $e->getMessage() . " Data: " . json_encode($_POST));
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>