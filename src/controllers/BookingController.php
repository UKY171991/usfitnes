<?php
/**
 * Booking Controller
 * Handles test booking operations
 */

class BookingController extends BaseController {

    /**
     * Display booking form
     */
    public function create() {
        $this->requireAuth();
        
        $testModel = new Test();
        $branchModel = new Branch();
        
        $data = [
            'tests' => $testModel->getActiveTests(),
            'branches' => $branchModel->getActiveBranches(),
            'categories' => $testModel->getCategories()
        ];
        
        $this->render('patient/book_test', $data);
    }

    /**
     * Process booking creation
     */
    public function store() {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $bookingData = [
                'patient_id' => $_SESSION['user_id'],
                'test_id' => Sanitizer::integer($_POST['test_id']),
                'branch_id' => Sanitizer::integer($_POST['branch_id']),
                'appointment_date' => Sanitizer::string($_POST['appointment_date']),
                'appointment_time' => Sanitizer::string($_POST['appointment_time']),
                'notes' => Sanitizer::string($_POST['notes'] ?? '')
            ];

            // Validate required fields
            if (!$bookingData['test_id'] || !$bookingData['branch_id']) {
                $this->jsonError('Test and branch are required');
                return;
            }

            // Get test details and calculate amount
            $testModel = new Test();
            $branchModel = new Branch();
            
            $test = $testModel->getById($bookingData['test_id']);
            $branchTests = $branchModel->getBranchTests($bookingData['branch_id']);
            
            if (!$test) {
                $this->jsonError('Invalid test selected');
                return;
            }

            // Find test price for this branch
            $testPrice = $test['price']; // Default price
            foreach ($branchTests as $branchTest) {
                if ($branchTest['test_id'] == $bookingData['test_id']) {
                    $testPrice = $branchTest['price'];
                    break;
                }
            }

            $bookingData['amount'] = $testPrice;
            $bookingData['final_amount'] = $testPrice; // No discount for now
            $bookingData['booking_date'] = date('Y-m-d H:i:s');

            $bookingModel = new Booking();
            $bookingId = $bookingModel->create($bookingData);

            if ($bookingId) {
                Logger::activity($_SESSION['user_id'], 'Booking Created', [
                    'booking_id' => $bookingId,
                    'test_id' => $bookingData['test_id'],
                    'amount' => $bookingData['final_amount']
                ]);

                $this->jsonSuccess([
                    'booking_id' => $bookingId,
                    'message' => 'Booking created successfully',
                    'redirect' => '/booking/payment/' . $bookingId
                ]);
            } else {
                $this->jsonError('Failed to create booking');
            }
        } catch (Exception $e) {
            Logger::error("Booking creation failed: " . $e->getMessage());
            $this->jsonError('An error occurred while creating booking');
        }
    }

    /**
     * Display booking details
     */
    public function show($bookingId) {
        $this->requireAuth();
        
        $bookingModel = new Booking();
        $booking = $bookingModel->getById($bookingId);
        
        if (!$booking) {
            $this->show404();
            return;
        }

        // Check if user can view this booking
        if ($_SESSION['role'] === 'patient' && $booking['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        $this->render('patient/booking_details', ['booking' => $booking]);
    }

    /**
     * Display user's bookings
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

        $bookingModel = new Booking();
        $result = $bookingModel->getBookings($filters, $page, 10);
        
        $data = [
            'bookings' => $result['bookings'],
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
        
        $this->render('patient/bookings', $data);
    }

    /**
     * Cancel booking
     */
    public function cancel($bookingId) {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
            $bookingModel = new Booking();
            $booking = $bookingModel->getById($bookingId);
            
            if (!$booking) {
                $this->jsonError('Booking not found');
                return;
            }

            // Check if user can cancel this booking
            if ($_SESSION['role'] === 'patient' && $booking['patient_id'] != $_SESSION['user_id']) {
                $this->jsonError('Unauthorized');
                return;
            }

            // Check if booking can be cancelled
            if ($booking['status'] === BOOKING_COMPLETED || $booking['status'] === BOOKING_CANCELLED) {
                $this->jsonError('Booking cannot be cancelled');
                return;
            }

            $reason = Sanitizer::string($_POST['reason'] ?? 'Cancelled by user');
            $result = $bookingModel->cancel($bookingId, $reason);

            if ($result) {
                Logger::activity($_SESSION['user_id'], 'Booking Cancelled', [
                    'booking_id' => $bookingId,
                    'reason' => $reason
                ]);

                $this->jsonSuccess(['message' => 'Booking cancelled successfully']);
            } else {
                $this->jsonError('Failed to cancel booking');
            }
        } catch (Exception $e) {
            Logger::error("Booking cancellation failed: " . $e->getMessage());
            $this->jsonError('An error occurred while cancelling booking');
        }
    }

    /**
     * Display payment page
     */
    public function payment($bookingId) {
        $this->requireAuth();
        
        $bookingModel = new Booking();
        $booking = $bookingModel->getById($bookingId);
        
        if (!$booking) {
            $this->show404();
            return;
        }

        // Check if user can pay for this booking
        if ($_SESSION['role'] === 'patient' && $booking['patient_id'] != $_SESSION['user_id']) {
            $this->show403();
            return;
        }

        // Check if payment is already completed
        if ($booking['payment_status'] === PAYMENT_PAID) {
            $this->redirect('/booking/' . $bookingId);
            return;
        }

        $this->render('patient/payment', ['booking' => $booking]);
    }

    /**
     * Process payment
     */
    public function processPayment($bookingId) {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();
            
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

            $paymentMethod = Sanitizer::string($_POST['payment_method']);
            
            if ($paymentMethod === 'instamojo') {
                $paymentModel = new Payment();
                $result = $paymentModel->initiateInstamojo(
                    $bookingId,
                    $booking['final_amount'],
                    'Lab Test - ' . $booking['test_name'],
                    $booking['patient_name'],
                    $booking['patient_email'],
                    $booking['patient_phone']
                );

                if ($result['success']) {
                    Logger::activity($_SESSION['user_id'], 'Payment Initiated', [
                        'booking_id' => $bookingId,
                        'payment_method' => 'instamojo',
                        'amount' => $booking['final_amount']
                    ]);

                    $this->jsonSuccess([
                        'payment_url' => $result['payment_url'],
                        'message' => 'Redirecting to payment gateway...'
                    ]);
                } else {
                    $this->jsonError($result['message'] ?? 'Payment initiation failed');
                }
            } else {
                $this->jsonError('Invalid payment method');
            }
        } catch (Exception $e) {
            Logger::error("Payment processing failed: " . $e->getMessage());
            $this->jsonError('An error occurred while processing payment');
        }
    }

    /**
     * Handle payment success callback
     */
    public function paymentSuccess() {
        $paymentId = Sanitizer::string($_GET['payment_id'] ?? '');
        $paymentRequestId = Sanitizer::string($_GET['payment_request_id'] ?? '');
        
        if (!$paymentId || !$paymentRequestId) {
            $this->show404();
            return;
        }

        $paymentModel = new Payment();
        $payment = $paymentModel->getByTransactionId($paymentRequestId);
        
        if (!$payment) {
            $this->show404();
            return;
        }

        // Verify payment with Instamojo
        $verificationResult = $paymentModel->verifyInstamojo($payment['id'], $paymentRequestId);
        
        $data = [
            'payment' => $payment,
            'verification' => $verificationResult,
            'booking_id' => $payment['booking_id']
        ];
        
        $this->render('patient/payment_success', $data);
    }

    /**
     * Handle payment failure callback
     */
    public function paymentFailure() {
        $paymentRequestId = Sanitizer::string($_GET['payment_request_id'] ?? '');
        
        $data = [
            'payment_request_id' => $paymentRequestId,
            'message' => 'Payment was not completed. Please try again.'
        ];
        
        $this->render('patient/payment_failure', $data);
    }

    /**
     * Get tests by category (AJAX)
     */
    public function getTestsByCategory() {
        $this->requireAuth();
        
        $categoryId = Sanitizer::integer($_GET['category_id'] ?? 0);
        
        if (!$categoryId) {
            $this->jsonError('Category ID is required');
            return;
        }

        $testModel = new Test();
        $tests = $testModel->getTestsByCategory($categoryId);
        
        $this->jsonSuccess(['tests' => $tests]);
    }

    /**
     * Get test details (AJAX)
     */
    public function getTestDetails() {
        $this->requireAuth();
        
        $testId = Sanitizer::integer($_GET['test_id'] ?? 0);
        $branchId = Sanitizer::integer($_GET['branch_id'] ?? 0);
        
        if (!$testId) {
            $this->jsonError('Test ID is required');
            return;
        }

        $testModel = new Test();
        $test = $testModel->getById($testId);
        
        if (!$test) {
            $this->jsonError('Test not found');
            return;
        }

        // Get branch-specific price if branch is selected
        if ($branchId) {
            $branchModel = new Branch();
            $branchTests = $branchModel->getBranchTests($branchId);
            
            foreach ($branchTests as $branchTest) {
                if ($branchTest['test_id'] == $testId) {
                    $test['price'] = $branchTest['price'];
                    break;
                }
            }
        }
        
        $this->jsonSuccess(['test' => $test]);
    }

    /**
     * Admin: View all bookings
     */
    public function adminIndex() {
        $this->requireRole(['admin', 'branch_admin']);
        
        $page = Sanitizer::integer($_GET['page'] ?? 1);
        $search = Sanitizer::string($_GET['search'] ?? '');
        $status = Sanitizer::string($_GET['status'] ?? '');
        $branchId = Sanitizer::integer($_GET['branch_id'] ?? 0);
        
        $filters = [];
        
        if ($search) {
            $filters['search'] = $search;
        }
        
        if ($status) {
            $filters['status'] = $status;
        }

        // Branch admin can only see their branch bookings
        if ($_SESSION['role'] === 'branch_admin' && isset($_SESSION['branch_id'])) {
            $filters['branch_id'] = $_SESSION['branch_id'];
        } elseif ($branchId) {
            $filters['branch_id'] = $branchId;
        }

        $bookingModel = new Booking();
        $result = $bookingModel->getBookings($filters, $page, 20);
        
        $branchModel = new Branch();
        $branches = $branchModel->getActiveBranches();
        
        $data = [
            'bookings' => $result['bookings'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total' => $result['total']
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'branch_id' => $branchId
            ],
            'branches' => $branches
        ];
        
        $template = ($_SESSION['role'] === 'admin') ? 'admin/bookings' : 'branch_admin/bookings';
        $this->render($template, $data);
    }

    /**
     * Admin: Update booking status
     */
    public function updateStatus($bookingId) {
        $this->requireRole(['admin', 'branch_admin']);
        
        try {
            $this->validateCsrf();
            
            $status = Sanitizer::string($_POST['status']);
            $notes = Sanitizer::string($_POST['notes'] ?? '');
            
            if (!in_array($status, [BOOKING_PENDING, BOOKING_CONFIRMED, BOOKING_COMPLETED, BOOKING_CANCELLED])) {
                $this->jsonError('Invalid status');
                return;
            }

            $bookingModel = new Booking();
            $booking = $bookingModel->getById($bookingId);
            
            if (!$booking) {
                $this->jsonError('Booking not found');
                return;
            }

            // Branch admin can only update their branch bookings
            if ($_SESSION['role'] === 'branch_admin' && 
                isset($_SESSION['branch_id']) && 
                $booking['branch_id'] != $_SESSION['branch_id']) {
                $this->jsonError('Unauthorized');
                return;
            }

            $updateData = ['status' => $status];
            if ($notes) {
                $updateData['notes'] = $booking['notes'] . "\n" . date('Y-m-d H:i:s') . " - " . $notes;
            }

            $result = $bookingModel->update($bookingId, $updateData);

            if ($result) {
                Logger::activity($_SESSION['user_id'], 'Booking Status Updated', [
                    'booking_id' => $bookingId,
                    'old_status' => $booking['status'],
                    'new_status' => $status
                ]);

                $this->jsonSuccess(['message' => 'Booking status updated successfully']);
            } else {
                $this->jsonError('Failed to update booking status');
            }
        } catch (Exception $e) {
            Logger::error("Booking status update failed: " . $e->getMessage());
            $this->jsonError('An error occurred while updating booking status');
        }
    }
}
?>
