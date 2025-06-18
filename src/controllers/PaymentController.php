<?php
/**
 * Payment Controller
 * Handles payment operations and Instamojo integration
 */

class PaymentController extends BaseController {

    /**
     * Display payment history for user
     */
    public function index() {
        $this->requireAuth();
        
        $page = Sanitizer::integer($_GET['page'] ?? 1);
        $search = Sanitizer::string($_GET['search'] ?? '');
        $status = Sanitizer::string($_GET['status'] ?? '');
        
        $filters = [];
        
        if ($_SESSION['role'] === 'patient') {
            $filters['patient_id'] = $_SESSION['user_id'];
        }
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        if ($status) {
            $filters['status'] = $status;
        }

        $paymentModel = new Payment();
        $result = $paymentModel->getPayments($filters, $page, 10);
        
        $data = [
            'payments' => $result['payments'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total' => $result['total']
            ],
            'filters' => [
                'search' => $search,
                'status' => $status
            ]
        ];
        
        $this->render('patient/payments', $data);
    }

    /**
     * Display payment details
     */
    public function show($paymentId) {
        $this->requireAuth();
        
        $paymentModel = new Payment();
        $payment = $paymentModel->getById($paymentId);
        
        if (!$payment) {
            $this->show404();
            return;
        }

        // Check if user can view this payment
        if ($_SESSION['role'] === 'patient' && $payment['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        $this->render('patient/payment_details', ['payment' => $payment]);
    }

    /**
     * Process payment via Instamojo
     */
    public function processInstamojo() {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $bookingId = Sanitizer::integer($_POST['booking_id']);
            
            if (!$bookingId) {
                $this->jsonError('Booking ID is required');
                return;
            }

            $bookingModel = new Booking();
            $booking = $bookingModel->getById($bookingId);
            
            if (!$booking) {
                $this->jsonError('Booking not found');
                return;
            }

            // Check if user can pay for this booking
            if ($_SESSION['role'] === 'patient' && $booking['patient_id'] != $_SESSION['user_id']) {
                $this->jsonError('Unauthorized');
                return;
            }

            if ($booking['payment_status'] === PAYMENT_PAID) {
                $this->jsonError('Payment already completed');
                return;
            }

            $paymentModel = new Payment();
            $result = $paymentModel->initiateInstamojo(
                $bookingId,
                $booking['final_amount'],
                'Lab Test - ' . $booking['test_name'] . ' (Booking #' . $bookingId . ')',
                $booking['patient_name'],
                $booking['patient_email'],
                $booking['patient_phone']
            );

            if ($result['success']) {
                Logger::activity($_SESSION['user_id'], 'Instamojo Payment Initiated', [
                    'booking_id' => $bookingId,
                    'amount' => $booking['final_amount'],
                    'payment_id' => $result['payment_id'],
                    'request_id' => $result['request_id']
                ]);

                $this->jsonSuccess([
                    'payment_url' => $result['payment_url'],
                    'payment_id' => $result['payment_id'],
                    'message' => 'Redirecting to payment gateway...'
                ]);
            } else {
                $this->jsonError($result['message'] ?? 'Payment initiation failed');
            }
        } catch (Exception $e) {
            Logger::error("Instamojo payment initiation failed: " . $e->getMessage());
            $this->jsonError('An error occurred while initiating payment');
        }
    }

    /**
     * Handle Instamojo payment success callback
     */
    public function instamojoSuccess() {
        $paymentId = Sanitizer::string($_GET['payment_id'] ?? '');
        $paymentRequestId = Sanitizer::string($_GET['payment_request_id'] ?? '');
        
        if (!$paymentId || !$paymentRequestId) {
            $this->redirect('/payment/failure?error=invalid_parameters');
            return;
        }

        $paymentModel = new Payment();
        $payment = $paymentModel->getByTransactionId($paymentRequestId);
        
        if (!$payment) {
            $this->redirect('/payment/failure?error=payment_not_found');
            return;
        }

        // Verify payment with Instamojo
        $verificationResult = $paymentModel->verifyInstamojo($payment['id'], $paymentRequestId);
        
        if ($verificationResult['success'] && $verificationResult['status'] === 'paid') {
            Logger::activity($payment['patient_id'], 'Payment Completed Successfully', [
                'payment_id' => $payment['id'],
                'booking_id' => $payment['booking_id'],
                'instamojo_payment_id' => $paymentId
            ]);

            $data = [
                'payment' => $payment,
                'booking_id' => $payment['booking_id'],
                'success' => true,
                'message' => 'Payment completed successfully!'
            ];
            
            $this->render('patient/payment_result', $data);
        } else {
            $this->redirect('/payment/failure?payment_id=' . $payment['id']);
        }
    }

    /**
     * Handle Instamojo payment failure callback
     */
    public function instamojoFailure() {
        $paymentRequestId = Sanitizer::string($_GET['payment_request_id'] ?? '');
        $paymentId = Sanitizer::integer($_GET['payment_id'] ?? 0);
        $error = Sanitizer::string($_GET['error'] ?? '');
        
        $data = [
            'payment_request_id' => $paymentRequestId,
            'payment_id' => $paymentId,
            'error' => $error,
            'success' => false,
            'message' => 'Payment was not completed. Please try again.'
        ];
        
        Logger::payment('Payment Failed or Cancelled', [
            'payment_request_id' => $paymentRequestId,
            'payment_id' => $paymentId,
            'error' => $error
        ]);
        
        $this->render('patient/payment_result', $data);
    }

    /**
     * Handle Instamojo webhook
     */
    public function webhook() {
        // Get raw POST data
        $payload = file_get_contents('php://input');
        
        if (empty($payload)) {
            http_response_code(400);
            echo 'Invalid payload';
            return;
        }

        // Verify webhook signature if configured
        if (defined('INSTAMOJO_WEBHOOK_SECRET')) {
            $signature = $_SERVER['HTTP_X_INSTAMOJO_SIGNATURE'] ?? '';
            $computedSignature = hash_hmac('sha1', $payload, INSTAMOJO_WEBHOOK_SECRET);
            
            if (!hash_equals($computedSignature, $signature)) {
                Logger::error('Instamojo webhook signature verification failed');
                http_response_code(401);
                echo 'Unauthorized';
                return;
            }
        }

        $data = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error('Instamojo webhook invalid JSON: ' . json_last_error_msg());
            http_response_code(400);
            echo 'Invalid JSON';
            return;
        }

        $paymentModel = new Payment();
        $result = $paymentModel->handleInstamojokWebhook($data);
        
        if ($result) {
            Logger::payment('Webhook processed successfully', ['data' => $data]);
            http_response_code(200);
            echo 'OK';
        } else {
            Logger::error('Webhook processing failed', ['data' => $data]);
            http_response_code(500);
            echo 'Processing failed';
        }
    }

    /**
     * Verify payment status (AJAX)
     */
    public function verifyStatus() {
        $this->requireAuth();
        
        try {
            $paymentId = Sanitizer::integer($_POST['payment_id']);
            $paymentRequestId = Sanitizer::string($_POST['payment_request_id']);
            
            if (!$paymentId || !$paymentRequestId) {
                $this->jsonError('Payment ID and request ID are required');
                return;
            }

            $paymentModel = new Payment();
            $payment = $paymentModel->getById($paymentId);
            
            if (!$payment) {
                $this->jsonError('Payment not found');
                return;
            }

            // Check if user can verify this payment
            if ($_SESSION['role'] === 'patient' && $payment['patient_id'] != $_SESSION['user_id']) {
                $this->jsonError('Unauthorized');
                return;
            }

            $verificationResult = $paymentModel->verifyInstamojo($paymentId, $paymentRequestId);
            
            $this->jsonSuccess([
                'status' => $verificationResult['status'] ?? 'pending',
                'verified' => $verificationResult['success'] ?? false,
                'payment_status' => $payment['payment_status']
            ]);
        } catch (Exception $e) {
            Logger::error("Payment verification failed: " . $e->getMessage());
            $this->jsonError('An error occurred while verifying payment');
        }
    }

    /**
     * Admin: View all payments
     */
    public function adminIndex() {
        $this->requireRole(['admin', 'branch_admin']);
        
        $page = Sanitizer::integer($_GET['page'] ?? 1);
        $search = Sanitizer::string($_GET['search'] ?? '');
        $status = Sanitizer::string($_GET['status'] ?? '');
        $method = Sanitizer::string($_GET['method'] ?? '');
        $dateFrom = Sanitizer::string($_GET['date_from'] ?? '');
        $dateTo = Sanitizer::string($_GET['date_to'] ?? '');
        
        $filters = [];
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        if ($status) {
            $filters['status'] = $status;
        }

        if ($method) {
            $filters['method'] = $method;
        }

        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }

        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        $paymentModel = new Payment();
        $result = $paymentModel->getPayments($filters, $page, 20);
        $statistics = $paymentModel->getStatistics($dateFrom, $dateTo);
        
        $data = [
            'payments' => $result['payments'],
            'statistics' => $statistics,
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total' => $result['total']
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'method' => $method,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ];
        
        $template = ($_SESSION['role'] === 'admin') ? 'admin/payments' : 'branch_admin/payments';
        $this->render($template, $data);
    }

    /**
     * Admin: Process refund
     */
    public function refund($paymentId) {
        $this->requireRole(['admin']);
        
        try {
            $this->validateCsrf();
            
            $reason = Sanitizer::string($_POST['reason'] ?? '');
            
            if (empty($reason)) {
                $this->jsonError('Refund reason is required');
                return;
            }

            $paymentModel = new Payment();
            $result = $paymentModel->refund($paymentId, $reason);
            
            if ($result['success']) {
                Logger::activity($_SESSION['user_id'], 'Payment Refunded', [
                    'payment_id' => $paymentId,
                    'reason' => $reason
                ]);

                $this->jsonSuccess(['message' => $result['message']]);
            } else {
                $this->jsonError($result['message']);
            }
        } catch (Exception $e) {
            Logger::error("Payment refund failed: " . $e->getMessage());
            $this->jsonError('An error occurred while processing refund');
        }
    }

    /**
     * Generate payment receipt
     */
    public function receipt($paymentId) {
        $this->requireAuth();
        
        $paymentModel = new Payment();
        $payment = $paymentModel->getById($paymentId);
        
        if (!$payment) {
            $this->show404();
            return;
        }

        // Check if user can view this payment receipt
        if ($_SESSION['role'] === 'patient' && $payment['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        if ($payment['payment_status'] !== PAYMENT_PAID) {
            $this->redirect('/payment/' . $paymentId . '?error=payment_not_completed');
            return;
        }

        // Generate receipt HTML
        $html = $this->generateReceiptHTML($payment);
        
        // If PDF is requested
        if (isset($_GET['pdf']) && $_GET['pdf'] === '1') {
            try {
                require_once __DIR__ . '/../lib/mpdf/vendor/autoload.php';
                
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'orientation' => 'P'
                ]);

                $mpdf->WriteHTML($html);
                $mpdf->Output('receipt_' . $payment['transaction_id'] . '.pdf', 'D');
                return;
            } catch (Exception $e) {
                Logger::error("Receipt PDF generation failed: " . $e->getMessage());
            }
        }

        // Display HTML receipt
        echo $html;
    }

    /**
     * Generate receipt HTML
     */
    private function generateReceiptHTML($payment) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Payment Receipt - ' . htmlspecialchars($payment['transaction_id']) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .company-name { font-size: 24px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
                .receipt-title { font-size: 18px; color: #e74c3c; margin-top: 10px; }
                .receipt-info { margin: 20px 0; }
                .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .info-table td { padding: 10px; border: 1px solid #ddd; }
                .info-table .label { background: #f8f9fa; font-weight: bold; width: 200px; }
                .amount-section { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .amount { font-size: 18px; font-weight: bold; color: #27ae60; }
                .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
                .status-paid { color: #27ae60; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company-name">US FITNESS LAB</div>
                <div class="receipt-title">PAYMENT RECEIPT</div>
            </div>

            <div class="receipt-info">
                <table class="info-table">
                    <tr>
                        <td class="label">Receipt No:</td>
                        <td>' . htmlspecialchars($payment['transaction_id']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Payment Date:</td>
                        <td>' . date('d-m-Y H:i:s', strtotime($payment['payment_date'])) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Patient Name:</td>
                        <td>' . htmlspecialchars($payment['patient_name']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td>' . htmlspecialchars($payment['patient_email']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Test:</td>
                        <td>' . htmlspecialchars($payment['test_name']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Booking Date:</td>
                        <td>' . date('d-m-Y', strtotime($payment['booking_date'])) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Payment Method:</td>
                        <td>' . strtoupper($payment['payment_method']) . '</td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td class="status-paid">' . strtoupper($payment['payment_status']) . '</td>
                    </tr>
                </table>

                <div class="amount-section">
                    <div>Amount Paid: <span class="amount">₹' . number_format($payment['amount'], 2) . '</span></div>
                </div>
            </div>

            <div class="footer">
                <p>This is a computer generated receipt and does not require signature.</p>
                <p>For any queries, please contact: info@usfitnesslab.com</p>
                <p>Thank you for choosing US Fitness Lab!</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
?>
