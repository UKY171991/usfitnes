<?php
/**
 * Booking Model Class
 * Handles test booking operations
 */

class Booking {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create new booking
     */
    public function create($bookingData) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO bookings (patient_id, test_id, branch_id, booking_date, 
                                    appointment_date, appointment_time, amount, discount, 
                                    final_amount, payment_status, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $bookingData['patient_id'],
                $bookingData['test_id'],
                $bookingData['branch_id'],
                $bookingData['booking_date'] ?? date('Y-m-d H:i:s'),
                $bookingData['appointment_date'] ?? null,
                $bookingData['appointment_time'] ?? null,
                $bookingData['amount'],
                $bookingData['discount'] ?? 0,
                $bookingData['final_amount'],
                $bookingData['payment_status'] ?? PAYMENT_PENDING,
                $bookingData['notes'] ?? null,
                $bookingData['status'] ?? BOOKING_PENDING
            ]);

            if ($result) {
                $bookingId = $this->db->lastInsertId();
                
                // Create initial report entry
                $this->createReportEntry($bookingId, $bookingData['patient_id']);
                
                $this->db->commit();
                Logger::activity($bookingData['patient_id'], 'Booking Created', ['booking_id' => $bookingId]);
                return $bookingId;
            }
            
            $this->db->rollback();
            return false;
        } catch (Exception $e) {
            $this->db->rollback();
            Logger::error("Failed to create booking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get booking by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT b.*, 
                       u.name as patient_name, u.email as patient_email, u.phone as patient_phone,
                       t.test_name, t.description as test_description,
                       br.branch_name, br.address as branch_address,
                       p.payment_status as payment_status, p.payment_id as payment_id
                FROM bookings b
                JOIN users u ON b.patient_id = u.id
                JOIN tests t ON b.test_id = t.id
                JOIN branches br ON b.branch_id = br.id
                LEFT JOIN payments p ON b.id = p.booking_id
                WHERE b.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get booking by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update booking
     */
    public function update($id, $bookingData) {
        try {
            $setParts = [];
            $params = [];

            foreach ($bookingData as $field => $value) {
                if ($field !== 'id') {
                    $setParts[] = "$field = ?";
                    $params[] = $value;
                }
            }

            $params[] = $id;
            
            $sql = "UPDATE bookings SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                Logger::activity(null, 'Booking Updated', ['booking_id' => $id, 'data' => $bookingData]);
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update booking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel booking
     */
    public function cancel($id, $reason = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE bookings 
                SET status = ?, notes = CONCAT(IFNULL(notes, ''), ?, ?), updated_at = NOW() 
                WHERE id = ?
            ");
            $cancelNote = "\nCancelled on " . date('Y-m-d H:i:s');
            if ($reason) {
                $cancelNote .= " - Reason: $reason";
            }
            
            $result = $stmt->execute([BOOKING_CANCELLED, $cancelNote, '', $id]);
            
            if ($result) {
                Logger::activity(null, 'Booking Cancelled', ['booking_id' => $id, 'reason' => $reason]);
            }
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to cancel booking: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get bookings with pagination and filters
     */
    public function getBookings($filters = [], $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $where = ['1=1'];
            $params = [];

            // Add filters
            if (!empty($filters['patient_id'])) {
                $where[] = 'b.patient_id = ?';
                $params[] = $filters['patient_id'];
            }

            if (!empty($filters['branch_id'])) {
                $where[] = 'b.branch_id = ?';
                $params[] = $filters['branch_id'];
            }

            if (!empty($filters['status'])) {
                $where[] = 'b.status = ?';
                $params[] = $filters['status'];
            }

            if (!empty($filters['payment_status'])) {
                $where[] = 'b.payment_status = ?';
                $params[] = $filters['payment_status'];
            }

            if (!empty($filters['date_from'])) {
                $where[] = 'DATE(b.booking_date) >= ?';
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $where[] = 'DATE(b.booking_date) <= ?';
                $params[] = $filters['date_to'];
            }

            if (!empty($filters['search'])) {
                $where[] = '(u.name LIKE ? OR u.email LIKE ? OR t.test_name LIKE ?)';
                $search = '%' . $filters['search'] . '%';
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }

            $whereClause = implode(' AND ', $where);

            // Get total count
            $countSql = "
                SELECT COUNT(*) 
                FROM bookings b
                JOIN users u ON b.patient_id = u.id
                JOIN tests t ON b.test_id = t.id
                WHERE $whereClause
            ";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            // Get bookings
            $sql = "
                SELECT b.*, 
                       u.name as patient_name, u.email as patient_email, u.phone as patient_phone,
                       t.test_name, t.price as test_price,
                       br.branch_name,
                       r.report_status, r.pdf_path
                FROM bookings b
                JOIN users u ON b.patient_id = u.id
                JOIN tests t ON b.test_id = t.id
                JOIN branches br ON b.branch_id = br.id
                LEFT JOIN reports r ON b.id = r.booking_id
                WHERE $whereClause 
                ORDER BY b.created_at DESC 
                LIMIT ? OFFSET ?
            ";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $bookings = $stmt->fetchAll();

            return [
                'bookings' => $bookings,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            Logger::error("Failed to get bookings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get patient bookings
     */
    public function getPatientBookings($patientId, $page = 1, $limit = 10) {
        return $this->getBookings(['patient_id' => $patientId], $page, $limit);
    }

    /**
     * Get branch bookings
     */
    public function getBranchBookings($branchId, $page = 1, $limit = 10) {
        return $this->getBookings(['branch_id' => $branchId], $page, $limit);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($bookingId, $status, $paymentId = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE bookings 
                SET payment_status = ?, payment_id = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$status, $paymentId, $bookingId]);
            
            if ($result && $status === PAYMENT_PAID) {
                // Update booking status to confirmed
                $this->update($bookingId, ['status' => BOOKING_CONFIRMED]);
            }
            
            Logger::payment("Booking payment status updated", [
                'booking_id' => $bookingId,
                'status' => $status,
                'payment_id' => $paymentId
            ]);
            
            return $result;
        } catch (Exception $e) {
            Logger::error("Failed to update payment status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get booking statistics
     */
    public function getStatistics($branchId = null, $dateFrom = null, $dateTo = null) {
        try {
            $where = ['1=1'];
            $params = [];

            if ($branchId) {
                $where[] = 'branch_id = ?';
                $params[] = $branchId;
            }

            if ($dateFrom) {
                $where[] = 'DATE(booking_date) >= ?';
                $params[] = $dateFrom;
            }

            if ($dateTo) {
                $where[] = 'DATE(booking_date) <= ?';
                $params[] = $dateTo;
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as confirmed_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_bookings,
                    SUM(CASE WHEN payment_status = ? THEN final_amount ELSE 0 END) as total_revenue,
                    AVG(final_amount) as average_booking_value
                FROM bookings 
                WHERE $whereClause
            ");
            
            $queryParams = array_merge([
                BOOKING_PENDING,
                BOOKING_CONFIRMED,
                BOOKING_COMPLETED,
                BOOKING_CANCELLED,
                PAYMENT_PAID
            ], $params);
            
            $stmt->execute($queryParams);
            return $stmt->fetch();
        } catch (Exception $e) {
            Logger::error("Failed to get booking statistics: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create initial report entry for booking
     */
    private function createReportEntry($bookingId, $patientId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO reports (booking_id, patient_id, report_status, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            return $stmt->execute([$bookingId, $patientId, REPORT_PENDING]);
        } catch (Exception $e) {
            Logger::error("Failed to create report entry: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcomingAppointments($branchId = null, $days = 7) {
        try {
            $where = ['appointment_date IS NOT NULL', 'appointment_date >= CURDATE()'];
            $params = [];

            if ($branchId) {
                $where[] = 'b.branch_id = ?';
                $params[] = $branchId;
            }

            $where[] = 'appointment_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)';
            $params[] = $days;

            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT b.*, 
                       u.name as patient_name, u.phone as patient_phone,
                       t.test_name,
                       br.branch_name
                FROM bookings b
                JOIN users u ON b.patient_id = u.id
                JOIN tests t ON b.test_id = t.id
                JOIN branches br ON b.branch_id = br.id
                WHERE $whereClause
                ORDER BY appointment_date, appointment_time
            ");
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            Logger::error("Failed to get upcoming appointments: " . $e->getMessage());
            return false;
        }
    }
}
?>
