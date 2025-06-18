<?php
$title = 'My Reports - US Fitness Lab';
$additionalCSS = ['/assets/css/reports.css'];
$additionalJS = ['/assets/js/patient-reports.js'];
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/patient/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Reports</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 text-primary mb-2">
                        <i class="fas fa-file-medical me-3"></i>
                        My Lab Reports
                    </h1>
                    <p class="lead text-muted">View and download your test reports</p>
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
                                <label for="status_filter" class="form-label">Report Status</label>
                                <select class="form-select" id="status_filter" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="ready" <?= $filters['status'] === 'ready' ? 'selected' : '' ?>>Ready</option>
                                    <option value="processing" <?= $filters['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="test_category" class="form-label">Test Category</label>
                                <select class="form-select" id="test_category" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= $filters['category'] == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?= $filters['date_from'] ?? '' ?>">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?= $filters['date_to'] ?? '' ?>">
                            </div>
                            <div class="col-md-2 mb-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                    <a href="/patient/reports" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2 text-primary"></i>
                                Test Reports
                                <span class="badge bg-primary ms-2"><?= $pagination['total'] ?></span>
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success" id="downloadAllReports" 
                                        <?= empty($ready_reports) ? 'disabled' : '' ?>>
                                    <i class="fas fa-download me-2"></i>
                                    Download All Ready Reports
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($reports)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No reports found</h5>
                            <p class="text-muted mb-3">
                                <?php if (!empty($filters['status']) || !empty($filters['date_from'])): ?>
                                    Try adjusting your filters or clearing them to see more results.
                                <?php else: ?>
                                    Complete your first test to get your lab reports here.
                                <?php endif; ?>
                            </p>
                            <a href="/patient/book-test" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Book Your First Test
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="p-4">
                            <div class="row">
                                <?php foreach ($reports as $report): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card h-100 report-card border border-light">
                                            <div class="card-header bg-light border-bottom-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 text-primary"><?= htmlspecialchars($report['test_name']) ?></h6>
                                                        <small class="text-muted">Report #<?= $report['id'] ?></small>
                                                    </div>
                                                    <span class="badge bg-<?= getReportStatusColor($report['status']) ?>">
                                                        <?= ucfirst($report['status']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <div class="row text-sm">
                                                        <div class="col-6">
                                                            <i class="fas fa-calendar text-muted me-2"></i>
                                                            <strong>Test Date:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <?= date('M d, Y', strtotime($report['test_date'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row text-sm">
                                                        <div class="col-6">
                                                            <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                                            <strong>Branch:</strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <?= htmlspecialchars($report['branch_name']) ?>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($report['category_name'])): ?>
                                                        <div class="row text-sm">
                                                            <div class="col-6">
                                                                <i class="fas fa-tag text-muted me-2"></i>
                                                                <strong>Category:</strong>
                                                            </div>
                                                            <div class="col-6">
                                                                <?= htmlspecialchars($report['category_name']) ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($report['status'] === 'ready' && !empty($report['generated_at'])): ?>
                                                        <div class="row text-sm">
                                                            <div class="col-6">
                                                                <i class="fas fa-clock text-muted me-2"></i>
                                                                <strong>Generated:</strong>
                                                            </div>
                                                            <div class="col-6">
                                                                <?= date('M d, Y h:i A', strtotime($report['generated_at'])) ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Status specific content -->
                                                <?php if ($report['status'] === 'ready'): ?>
                                                    <div class="alert alert-success alert-sm mb-3">
                                                        <i class="fas fa-check-circle me-2"></i>
                                                        Report is ready for download
                                                    </div>
                                                <?php elseif ($report['status'] === 'processing'): ?>
                                                    <div class="alert alert-info alert-sm mb-3">
                                                        <i class="fas fa-spinner fa-spin me-2"></i>
                                                        Report is being processed
                                                    </div>
                                                <?php elseif ($report['status'] === 'pending'): ?>
                                                    <div class="alert alert-warning alert-sm mb-3">
                                                        <i class="fas fa-clock me-2"></i>
                                                        Waiting for test completion
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Report parameters preview -->
                                                <?php if (!empty($report['parameters']) && $report['status'] === 'ready'): ?>
                                                    <div class="mb-3">
                                                        <h6 class="text-muted mb-2">Test Parameters:</h6>
                                                        <div class="bg-light p-2 rounded">
                                                            <?php
                                                            $parameters = json_decode($report['parameters'], true);
                                                            $paramCount = count($parameters);
                                                            $showCount = min(3, $paramCount);
                                                            ?>
                                                            <?php for ($i = 0; $i < $showCount; $i++): ?>
                                                                <small class="d-block">
                                                                    <strong><?= htmlspecialchars($parameters[$i]['name']) ?>:</strong>
                                                                    <?= htmlspecialchars($parameters[$i]['value']) ?>
                                                                    <?= htmlspecialchars($parameters[$i]['unit']) ?>
                                                                </small>
                                                            <?php endfor; ?>
                                                            <?php if ($paramCount > 3): ?>
                                                                <small class="text-muted">... and <?= $paramCount - 3 ?> more</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="d-grid gap-2">
                                                    <?php if ($report['status'] === 'ready'): ?>
                                                        <a href="/patient/report/<?= $report['id'] ?>/view" 
                                                           class="btn btn-primary btn-sm" target="_blank">
                                                            <i class="fas fa-eye me-2"></i>
                                                            View Report
                                                        </a>
                                                        <a href="/patient/report/<?= $report['id'] ?>/download" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="fas fa-download me-2"></i>
                                                            Download PDF
                                                        </a>
                                                        <button class="btn btn-outline-info btn-sm" 
                                                                onclick="shareReport(<?= $report['id'] ?>)">
                                                            <i class="fas fa-share me-2"></i>
                                                            Share Report
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="/patient/booking/<?= $report['booking_id'] ?>" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-eye me-2"></i>
                                                            View Booking Details
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent border-top-0 text-center">
                                                <small class="text-muted">
                                                    Booking #<?= $report['booking_id'] ?> • 
                                                    <?= date('M d, Y', strtotime($report['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="card-footer bg-white border-top">
                        <nav aria-label="Reports pagination">
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

<!-- Share Report Modal -->
<div class="modal fade" id="shareReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Share Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="shareEmail" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="shareEmail" placeholder="Enter email address">
                </div>
                <div class="mb-3">
                    <label for="shareMessage" class="form-label">Message (Optional)</label>
                    <textarea class="form-control" id="shareMessage" rows="3" 
                              placeholder="Add a personal message..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendReport()">
                    <i class="fas fa-share me-2"></i>
                    Share Report
                </button>
            </div>
        </div>
    </div>
</div>

<?php
function getReportStatusColor($status) {
    switch (strtolower($status)) {
        case 'ready': return 'success';
        case 'processing': return 'info';
        case 'pending': return 'warning';
        case 'error': return 'danger';
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
.report-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.report-card:hover {
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

.page-link {
    border-radius: 0.375rem;
    margin: 0 0.125rem;
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
