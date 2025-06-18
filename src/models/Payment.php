<?php
/**
 * Payment Model Class
 * Handles payment operations and Instamojo integration
 */

class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new payment record
     */
    public function create($paymentData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO payments (booking_id, patient_id, amount, payment_method,
                                    payment_status, transaction_id, gateway_response,
                                    payment_date, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $paymentData['booking_id'],
                $paymentData['patient_id'],
                $paymentData['amount'],
                $paymentData['payment_method'],
                $paymentData['payment_status'] ?? PAYMENT_PENDING,
                $paymentData['transaction_id'] ?? null,
                $paymentData['gateway_response'] ?? null,
                $paymentData['payment_date'] ?? null
            ]);

            if ($result) {
                $paymentId = $this->db->lastInsertId();
                Logger::payment('Payment record created', [
                    'payment_id' => $paymentId,
                    'booking_id' => $paymentData['booking_id'],
                    'amount' => $paymentData['amount']
                ]);
                return $paymentId;
            }
            
            return false;
        } catch (Exception $e) {
            Logger::error("Failed to create payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       u.name as patient_name, u.email as patient_email,
                       b.booking_date, b.final_amount as booking_amount,
                       t.test_name
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN users u ON p.patient_id = u.id
                JOIN tests t ON b.test_id = t.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get payment by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment by transaction ID
     */
    public function getByTransactionId($transactionId) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, 
                       u.name as patient_name, u.email as patient_email,
                       b.booking_date, b.final_amount as booking_amount
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN users u ON p.patient_id = u.id
                WHERE p.transaction_id = ?
            ");
            $stmt->execute([$transactionId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get payment by transaction ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update payment status
     */
    public function updateStatus($id, $status, $gatewayResponse = null, $paymentDate = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE payments 
                SET payment_status = ?, gateway_response = ?, payment_date = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $status,
                $gatewayResponse ? json_encode($gatewayResponse) : null,
                $paymentDate ?? ($status === PAYMENT_PAID ? date('Y-m-d H:i:s') : null),
                $id
            ]);

            if ($result) {
                Logger::payment('Payment status updated', [
                    'payment_id' => $id,
                    'status' => $status
                ]);

                // Update booking payment status if payment is successful
                if ($status === PAYMENT_PAID) {
                    $payment = $this->getById($id);
                    if ($payment) {
                        $booking = new Booking();
                        $booking->updatePaymentStatus($payment['booking_id'], PAYMENT_PAID, $payment['transaction_id']);
                    }
                }
            }
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update payment status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Initiate Instamojo payment
     */
    public function initiateInstamojo($bookingId, $amount, $purpose, $buyerName, $buyerEmail, $buyerPhone) {
        try {
            require_once __DIR__ . '/../lib/instamojo/Instamojo.php';
            
            $api = new Instamojo(INSTAMOJO_API_KEY, INSTAMOJO_AUTH_TOKEN, INSTAMOJO_ENDPOINT);
            
            $paymentRequest = $api->createPaymentRequest([
                'purpose' => $purpose,
                'amount' => $amount,
                'buyer_name' => $buyerName,
                'email' => $buyerEmail,
                'phone' => $buyerPhone,
                'redirect_url' => INSTAMOJO_REDIRECT_URL,
                'webhook' => INSTAMOJO_WEBHOOK_URL,
                'allow_repeated_payments' => false
            ]);

            if ($paymentRequest['success']) {
                // Create payment record
                $paymentData = [
                    'booking_id' => $bookingId,
                    'patient_id' => null, // Will be updated when we get booking details
                    'amount' => $amount,
                    'payment_method' => 'instamojo',
                    'payment_status' => PAYMENT_PENDING,
                    'transaction_id' => $paymentRequest['payment_request']['id'],
                    'gateway_response' => json_encode($paymentRequest)
                ];

                // Get patient ID from booking
                $booking = new Booking();
                $bookingDetails = $booking->getById($bookingId);
                if ($bookingDetails) {
                    $paymentData['patient_id'] = $bookingDetails['patient_id'];
                }

                $paymentId = $this->create($paymentData);

                if ($paymentId) {
                    Logger::payment('Instamojo payment initiated', [
                        'payment_id' => $paymentId,
                        'booking_id' => $bookingId,
                        'request_id' => $paymentRequest['payment_request']['id']
                    ]);

                    return [
                        'success' => true,
                        'payment_id' => $paymentId,
                        'payment_url' => $paymentRequest['payment_request']['longurl'],
                        'request_id' => $paymentRequest['payment_request']['id']
                    ];
                }
            } else {
                Logger::error("Instamojo payment request failed: " . json_encode($paymentRequest));
                return ['success' => false, 'message' => 'Payment request failed'];
            }
        } catch (Exception $e) {
            Logger::error("Failed to initiate Instamojo payment: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify Instamojo payment
     */
    public function verifyInstamojo($paymentId, $paymentRequestId) {
        try {
            require_once __DIR__ . '/../lib/instamojo/Instamojo.php';
            
            $api = new Instamojo(INSTAMOJO_API_KEY, INSTAMOJO_AUTH_TOKEN, INSTAMOJO_ENDPOINT);
            
            $paymentDetails = $api->getPaymentRequestDetails($paymentRequestId);
            
            if ($paymentDetails['success']) {
                $status = $paymentDetails['payment_request']['status'];
                $payments = $paymentDetails['payment_request']['payments'];
                
                if ($status === 'Completed' && !empty($payments)) {
                    $payment = $payments[0]; // Get first payment
                    
                    // Update payment status
                    $this->updateStatus(
                        $paymentId,
                        PAYMENT_PAID,
                        $paymentDetails,
                        $payment['created_at']
                    );
                    
                    Logger::payment('Instamojo payment verified and completed', [
                        'payment_id' => $paymentId,
                        'instamojo_payment_id' => $payment['payment_id'],
                        'amount' => $payment['amount']
                    ]);
                    
                    return ['success' => true, 'status' => 'paid'];
                } else {
                    Logger::payment('Instamojo payment verification - not completed', [
                        'payment_id' => $paymentId,
                        'status' => $status
                    ]);
                    
                    return ['success' => true, 'status' => 'pending'];
                }
            } else {
                Logger::error("Instamojo payment verification failed: " . json_encode($paymentDetails));
                return ['success' => false, 'message' => 'Payment verification failed'];
            }
        } catch (Exception $e) {
            Logger::error("Failed to verify Instamojo payment: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle Instamojo webhook
     */
    public function handleInstamojokWebhook($payload) {
        try {
            $paymentRequestId = $payload['payment_request_id'] ?? null;
            $paymentId = $payload['payment_id'] ?? null;
            $status = $payload['status'] ?? null;
            
            if (!$paymentRequestId || !$status) {
                Logger::error("Invalid webhook payload: " . json_encode($payload));
                return false;
            }

            // Find payment record by transaction_id (which stores payment_request_id)
            $payment = $this->getByTransactionId($paymentRequestId);
            
            if (!$payment) {
                Logger::error("Payment not found for webhook: " . $paymentRequestId);
                return false;
            }

            if ($status === 'Credit') {
                // Payment successful
                $this->updateStatus(
                    $payment['id'],
                    PAYMENT_PAID,
                    $payload,
                    date('Y-m-d H:i:s')
                );
                
                Logger::payment('Webhook processed - payment confirmed', [
                    'payment_id' => $payment['id'],
                    'instamojo_payment_id' => $paymentId,
                    'booking_id' => $payment['booking_id']
                ]);
                
                return true;
            } else {
                Logger::payment('Webhook processed - payment not credited', [
                    'payment_id' => $payment['id'],
                    'status' => $status
                ]);
                
                return false;
            }
        } catch (Exception $e) {
            Logger::error("Failed to handle Instamojo webhook: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payments with pagination and filters
     */
    public function getPayments($filters = [], $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $where = ['1=1'];
            $params = [];

            // Add filters
            if (!empty($filters['patient_id'])) {
                $where[] = 'p.patient_id = ?';
                $params[] = $filters['patient_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = 'p.payment_status = ?';
                $params[] = $filters['status'];
            }

            if (!empty($filters['method'])) {
                $where[] = 'p.payment_method = ?';
                $params[] = $filters['method'];
            }

            if (!empty($filters['date_from'])) {
                $where[] = 'DATE(p.payment_date) >= ?';
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where[] = 'DATE(p.payment_date) <= ?';
                $params[] = $filters['date_to'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(p.transaction_id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $whereClause = implode(' AND ', $where);

            // Get total count
            $countSql = "
                SELECT COUNT(*) 
                FROM payments p
                JOIN users u ON p.patient_id = u.id
                WHERE $whereClause
            ";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get payments
            $sql = "
                SELECT p.*, 
                       u.name as patient_name, u.email as patient_email,
                       b.booking_date, t.test_name
                FROM payments p
                JOIN users u ON p.patient_id = u.id
                JOIN bookings b ON p.booking_id = b.id
                JOIN tests t ON b.test_id = t.id
                WHERE $whereClause 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $payments = $stmt->fetchAll();

            return [
                'payments' => $payments,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            Logger::error("Failed to get payments: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get patient payments
     */
    public function getPatientPayments($patientId, $page = 1, $limit = 10) {
        return $this->getPayments(['patient_id' => $patientId], $page, $limit);
    }

    /**
     * Get payment statistics
     */
    public function getStatistics($dateFrom = null, $dateTo = null) {
        try {
            $where = ['1=1'];
            $params = [];

            if ($dateFrom) {
                $where[] = 'DATE(payment_date) >= ?';
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $where[] = 'DATE(payment_date) <= ?';
                $params[] = $dateTo;
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) as pending_payments,
                    SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) as paid_payments,
                    SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) as failed_payments,
                    SUM(CASE WHEN payment_status = ? THEN amount ELSE 0 END) as total_revenue,
                    AVG(CASE WHEN payment_status = ? THEN amount ELSE NULL END) as average_payment
                FROM payments 
                WHERE $whereClause
            ");
            
            $queryParams = array_merge([
                PAYMENT_PENDING,
                PAYMENT_PAID,
                PAYMENT_FAILED,
                PAYMENT_PAID,
                PAYMENT_PAID
            ], $params);
            
            $stmt->execute($queryParams);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get payment statistics: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Refund payment
     */
    public function refund($paymentId, $reason = '') {
        try {
            $payment = $this->getById($paymentId);
            
            if (!$payment || $payment['payment_status'] !== PAYMENT_PAID) {
                return ['success' => false, 'message' => 'Payment cannot be refunded'];
            }

            // For Instamojo, you would typically call their refund API here
            // For now, we'll just update the status locally
            
            $stmt = $this->db->prepare("
                UPDATE payments 
                SET payment_status = ?, refund_reason = ?, refund_date = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([PAYMENT_REFUNDED, $reason, $paymentId]);
            
            if ($result) {
                Logger::payment('Payment refunded', [
                    'payment_id' => $paymentId,
                    'booking_id' => $payment['booking_id'],
                    'amount' => $payment['amount'],
                    'reason' => $reason
                ]);
                
                return ['success' => true, 'message' => 'Payment refunded successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to process refund'];
        } catch (Exception $e) {
            Logger::error("Failed to refund payment: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
