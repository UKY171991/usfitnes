<?php
$title = 'My Bookings - US Fitness Lab';
$additionalCSS = ['/assets/css/bookings.css'];
$additionalJS = ['/assets/js/patient-bookings.js'];
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/patient/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Bookings</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 text-primary mb-2">
                        <i class="fas fa-calendar-alt me-3"></i>
                        My Bookings
                    </h1>
                    <p class="lead text-muted">View and manage all your test bookings</p>
                </div>
                <div>
                    <a href="/patient/book-test" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>
                        Book New Test
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="filterForm" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-3">
                                <label for="status_filter" class="form-label">Status</label>
                                <select class="form-select" id="status_filter" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $filters['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?= $filters['date_from'] ?? '' ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?= $filters['date_to'] ?? '' ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-2"></i>
                                        Filter
                                    </button>
                                    <a href="/patient/bookings" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>
                                        Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2 text-primary"></i>
                                Booking History
                                <span class="badge bg-primary ms-2"><?= $pagination['total'] ?></span>
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="listView">
                                    <i class="fas fa-list"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary active" id="cardView">
                                    <i class="fas fa-th"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($bookings)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No bookings found</h5>
                            <p class="text-muted mb-3">
                                <?php if (!empty($filters['status']) || !empty($filters['date_from'])): ?>
                                    Try adjusting your filters or clearing them to see more results.
                                <?php else: ?>
                                    You haven't made any test bookings yet. Start by booking your first test!
                                <?php endif; ?>
                            </p>
                            <a href="/patient/book-test" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Book Your First Test
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Card View -->
                        <div id="cardViewContainer" class="p-4">
                            <div class="row">
                                <?php foreach ($bookings as $booking): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card h-100 booking-card border border-light">
                                            <div class="card-header bg-light border-bottom-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 text-primary"><?= htmlspecialchars($booking['test_name']) ?></h6>
                                                        <small class="text-muted">Booking #<?= $booking['id'] ?></small>
                                                    </div>
                                                    <span class="badge bg-<?= getStatusColor($booking['status']) ?>">
                                                        <?= ucfirst($booking['status']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="row text-sm">
                                                        <div class="col-6">
                                                            <i class="fas fa-calendar text-muted me-2"></i>
                                                            <strong>Date:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <?= date('M d, Y', strtotime($booking['appointment_date'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row text-sm">
                                                        <div class="col-6">
                                                            <i class="fas fa-clock text-muted me-2"></i>
                                                            <strong>Time:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <?= date('h:i A', strtotime($booking['appointment_time'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row text-sm">
                                                        <div class="col-6">
                                                            <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                            <strong>Branch:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <?= htmlspecialchars($booking['branch_name']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row text-sm">
                                                        <div class="col-6">
                                                            <i class="fas fa-rupee-sign text-muted me-2"></i>
                                                            <strong>Amount:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            ₹<?= number_format($booking['final_amount'], 2) ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if ($booking['payment_status'] === 'paid'): ?>
                                                    <div class="alert alert-success alert-sm mb-3">
                                                        <i class="fas fa-check-circle me-2"></i>
                                                        Payment Completed
                                                    </div>
                                                <?php elseif ($booking['payment_status'] === 'pending'): ?>
                                                    <div class="alert alert-warning alert-sm mb-3">
                                                        <i class="fas fa-clock me-2"></i>
                                                        Payment Pending
                                                    </div>
                                                <?php endif; ?>

                                                <div class="d-grid gap-2">
                                                    <a href="/patient/booking/<?= $booking['id'] ?>" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye me-2"></i>
                                                        View Details
                                                    </a>
                                                    
                                                    <?php if ($booking['status'] === 'completed' && $booking['report_status'] === 'ready'): ?>
                                                        <a href="/patient/report/<?= $booking['id'] ?>/download" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="fas fa-download me-2"></i>
                                                            Download Report
                                                        </a>
                                                    <?php elseif ($booking['status'] === 'completed' && $booking['report_status'] === 'processing'): ?>
                                                        <button class="btn btn-outline-info btn-sm" disabled>
                                                            <i class="fas fa-spinner me-2"></i>
                                                            Report Processing
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($booking['payment_status'] === 'pending' && $booking['status'] !== 'cancelled'): ?>
                                                        <a href="/patient/payment/<?= $booking['id'] ?>" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="fas fa-credit-card me-2"></i>
                                                            Pay Now
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                        <button class="btn btn-outline-danger btn-sm" 
                                                                onclick="cancelBooking(<?= $booking['id'] ?>)">
                                                            <i class="fas fa-times me-2"></i>
                                                            Cancel
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent border-top-0 text-center">
                                                <small class="text-muted">
                                                    Booked on <?= date('M d, Y', strtotime($booking['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- List View -->
                        <div id="listViewContainer" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Test Name</th>
                                            <th>Appointment</th>
                                            <th>Branch</th>
                                            <th>Amount</th>
                                            <th>Payment</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?= $booking['id'] ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= date('M d, Y', strtotime($booking['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold"><?= htmlspecialchars($booking['test_name']) ?></div>
                                                    <?php if (!empty($booking['test_category'])): ?>
                                                        <small class="text-muted"><?= htmlspecialchars($booking['test_category']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div><?= date('M d, Y', strtotime($booking['appointment_date'])) ?></div>
                                                    <small class="text-muted"><?= date('h:i A', strtotime($booking['appointment_time'])) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($booking['branch_name']) ?></td>
                                                <td>
                                                    <div class="fw-semibold">₹<?= number_format($booking['final_amount'], 2) ?></div>
                                                    <?php if ($booking['discount'] > 0): ?>
                                                        <small class="text-success">Discount: ₹<?= number_format($booking['discount'], 2) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= getPaymentStatusColor($booking['payment_status']) ?>">
                                                        <?= ucfirst($booking['payment_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= getStatusColor($booking['status']) ?>">
                                                        <?= ucfirst($booking['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/patient/booking/<?= $booking['id'] ?>" 
                                                           class="btn btn-outline-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($booking['status'] === 'completed' && $booking['report_status'] === 'ready'): ?>
                                                            <a href="/patient/report/<?= $booking['id'] ?>/download" 
                                                               class="btn btn-outline-success" title="Download Report">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($booking['payment_status'] === 'pending' && $booking['status'] !== 'cancelled'): ?>
                                                            <a href="/patient/payment/<?= $booking['id'] ?>" 
                                                               class="btn btn-outline-warning" title="Pay Now">
                                                                <i class="fas fa-credit-card"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="card-footer bg-white border-top">
                        <nav aria-label="Bookings pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?><?= buildQueryString($filters) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= buildQueryString($filters) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?><?= buildQueryString($filters) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'completed': return 'success';
        case 'confirmed': return 'info';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getPaymentStatusColor($status) {
    switch (strtolower($status)) {
        case 'paid': return 'success';
        case 'pending': return 'warning';
        case 'failed': return 'danger';
        case 'refunded': return 'info';
        default: return 'secondary';
    }
}

function buildQueryString($filters) {
    $params = [];
    foreach ($filters as $key => $value) {
        if (!empty($value)) {
            $params[] = $key . '=' . urlencode($value);
        }
    }
    return empty($params) ? '' : '&' . implode('&', $params);
}
?>

<style>
.booking-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.booking-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.text-sm {
    font-size: 0.875rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.page-link {
    border-radius: 0.375rem;
    margin: 0 0.125rem;
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>
