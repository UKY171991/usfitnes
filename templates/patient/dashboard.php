<?php
$title = 'Patient Dashboard - US Fitness Lab';
$additionalCSS = ['/assets/css/dashboard.css'];
$additionalJS = ['/assets/js/patient-dashboard.js'];
?>

<div class="container-fluid py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white border-0">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="display-6 mb-2">
                                <i class="fas fa-user-circle me-3"></i>
                                Welcome back, <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>!
                            </h1>
                            <p class="lead mb-0">
                                Manage your test bookings, view reports, and schedule new appointments.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="/patient/book-test" class="btn btn-light btn-lg">
                                <i class="fas fa-plus me-2"></i>
                                Book New Test
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="fas fa-calendar-check fa-3x"></i>
                    </div>
                    <h3 class="text-primary mb-1"><?= $stats['total_bookings'] ?></h3>
                    <p class="text-muted mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="fas fa-file-medical fa-3x"></i>
                    </div>
                    <h3 class="text-success mb-1"><?= $stats['completed_tests'] ?></h3>
                    <p class="text-muted mb-0">Completed Tests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="fas fa-clock fa-3x"></i>
                    </div>
                    <h3 class="text-warning mb-1"><?= $stats['pending_tests'] ?></h3>
                    <p class="text-muted mb-0">Pending Tests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-3">
                        <i class="fas fa-download fa-3x"></i>
                    </div>
                    <h3 class="text-info mb-1"><?= $stats['available_reports'] ?></h3>
                    <p class="text-muted mb-0">Available Reports</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Recent Bookings -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                Recent Bookings
                            </h5>
                        </div>
                        <div class="col-auto">
                            <a href="/patient/bookings" class="btn btn-outline-primary btn-sm">
                                View All
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_bookings)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No bookings found</h6>
                            <p class="text-muted mb-3">Start by booking your first test</p>
                            <a href="/patient/book-test" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Book Test Now
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Test Name</th>
                                        <th>Appointment</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= htmlspecialchars($booking['test_name']) ?></div>
                                                <small class="text-muted">Booking #<?= $booking['id'] ?></small>
                                            </td>
                                            <td>
                                                <div><?= date('M d, Y', strtotime($booking['appointment_date'])) ?></div>
                                                <small class="text-muted"><?= date('h:i A', strtotime($booking['appointment_time'])) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($booking['branch_name']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getStatusColor($booking['status']) ?>">
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
                                            </td>
                                            <td>₹<?= number_format($booking['final_amount'], 2) ?></td>
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
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Profile -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/patient/book-test" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Book New Test
                        </a>
                        <a href="/patient/reports" class="btn btn-outline-success">
                            <i class="fas fa-file-medical me-2"></i>
                            View Reports
                        </a>
                        <a href="/patient/bookings" class="btn btn-outline-info">
                            <i class="fas fa-history me-2"></i>
                            Booking History
                        </a>
                        <a href="/patient/profile" class="btn btn-outline-secondary">
                            <i class="fas fa-user-edit me-2"></i>
                            Update Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Summary -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2 text-info"></i>
                        Profile Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td><?= htmlspecialchars($patient['email']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td><?= htmlspecialchars($patient['phone']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Age:</td>
                            <td><?= calculateAge($patient['date_of_birth']) ?> years</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Gender:</td>
                            <td><?= ucfirst($patient['gender']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member Since:</td>
                            <td><?= date('M Y', strtotime($patient['created_at'])) ?></td>
                        </tr>
                    </table>
                    <div class="d-grid">
                        <a href="/patient/profile" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-2"></i>
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    <?php if (!empty($upcoming_appointments)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-day me-2 text-success"></i>
                            Upcoming Appointments
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border border-success">
                                        <div class="card-body">
                                            <h6 class="card-title text-success"><?= htmlspecialchars($appointment['test_name']) ?></h6>
                                            <p class="card-text">
                                                <i class="fas fa-calendar me-2"></i>
                                                <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>
                                                <br>
                                                <i class="fas fa-clock me-2"></i>
                                                <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                                                <br>
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                <?= htmlspecialchars($appointment['branch_name']) ?>
                                            </p>
                                            <a href="/patient/booking/<?= $appointment['id'] ?>" class="btn btn-sm btn-outline-success">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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

function calculateAge($birthdate) {
    return date_diff(date_create($birthdate), date_create('today'))->y;
}
?>

<style>
.card {
    border-radius: 12px;
}

.btn {
    border-radius: 8px;
}

.badge {
    font-size: 0.75rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}
</style>
