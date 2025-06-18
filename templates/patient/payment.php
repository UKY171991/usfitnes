<?php
$title = 'Payment - US Fitness Lab';
$additionalCSS = ['/assets/css/payment.css'];
?>

<div class="container my-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/bookings">Bookings</a></li>
                    <li class="breadcrumb-item active">Payment</li>
                </ol>
            </nav>
            <h1 class="display-6 text-primary mb-2">
                <i class="fas fa-credit-card me-3"></i>
                Complete Payment
            </h1>
            <p class="lead text-muted">Secure payment for your lab test booking.</p>
        </div>
    </div>

    <div class="row">
        <!-- Payment Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>
                        Secure Payment
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Booking Details -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Booking Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Booking ID:</strong> #<?= $booking['id'] ?></p>
                                <p class="mb-1"><strong>Test:</strong> <?= htmlspecialchars($booking['test_name']) ?></p>
                                <p class="mb-1"><strong>Patient:</strong> <?= htmlspecialchars($booking['patient_name']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Branch:</strong> <?= htmlspecialchars($booking['branch_name']) ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?= date('d M Y', strtotime($booking['booking_date'])) ?></p>
                                <p class="mb-1"><strong>Amount:</strong> <span class="text-success fw-bold">₹<?= number_format($booking['final_amount'], 2) ?></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <form id="paymentForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                        
                        <h6 class="mb-3">
                            <i class="fas fa-wallet me-2"></i>
                            Select Payment Method
                        </h6>
                        
                        <!-- Instamojo Payment -->
                        <div class="payment-method mb-3">
                            <div class="card border-2">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="instamojo" value="instamojo" checked>
                                        <label class="form-check-label fw-bold" for="instamojo">
                                            <i class="fas fa-globe text-primary me-2"></i>
                                            Online Payment (Instamojo)
                                        </label>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0">
                                        Pay securely using Credit Card, Debit Card, Net Banking, UPI, or Wallets
                                    </p>
                                    <div class="payment-icons mt-2">
                                        <i class="fab fa-cc-visa text-primary fs-5 me-2"></i>
                                        <i class="fab fa-cc-mastercard text-warning fs-5 me-2"></i>
                                        <i class="fas fa-university text-success fs-5 me-2"></i>
                                        <i class="fas fa-mobile-alt text-info fs-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pay at Branch Option (Future) -->
                        <div class="payment-method mb-4">
                            <div class="card border-2 opacity-50">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="branch_payment" value="branch" disabled>
                                        <label class="form-check-label" for="branch_payment">
                                            <i class="fas fa-building text-secondary me-2"></i>
                                            Pay at Branch <span class="badge bg-secondary ms-2">Coming Soon</span>
                                        </label>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0">
                                        Pay directly at the branch during your visit
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" id="payBtn">
                                <i class="fas fa-lock me-2"></i>
                                Pay ₹<?= number_format($booking['final_amount'], 2) ?> Securely
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Features -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-shield-alt me-2"></i>
                        Your Payment is Secure
                    </h6>
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-lock fa-2x text-primary mb-2"></i>
                            <h6 class="small">SSL Encrypted</h6>
                            <p class="text-muted small">256-bit encryption</p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                            <h6 class="small">PCI Compliant</h6>
                            <p class="text-muted small">Industry standards</p>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <i class="fas fa-undo fa-2x text-info mb-2"></i>
                            <h6 class="small">Refund Policy</h6>
                            <p class="text-muted small">Easy cancellation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Payment Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="summary-item d-flex justify-content-between py-2">
                        <span>Test Amount:</span>
                        <span>₹<?= number_format($booking['amount'], 2) ?></span>
                    </div>
                    <?php if ($booking['discount'] > 0): ?>
                    <div class="summary-item d-flex justify-content-between py-2 text-success">
                        <span>Discount:</span>
                        <span>-₹<?= number_format($booking['discount'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-item d-flex justify-content-between py-2 border-top">
                        <span class="fw-bold">Total Amount:</span>
                        <span class="fw-bold text-success">₹<?= number_format($booking['final_amount'], 2) ?></span>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="small text-muted mb-2">Payment includes:</h6>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-check text-success me-2"></i>Test processing</li>
                            <li><i class="fas fa-check text-success me-2"></i>Digital report</li>
                            <li><i class="fas fa-check text-success me-2"></i>Expert consultation</li>
                            <li><i class="fas fa-check text-success me-2"></i>Quality assurance</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body text-center">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h6>Need Help?</h6>
                    <p class="text-muted small">Our support team is here to help</p>
                    <div class="d-grid gap-2">
                        <a href="tel:+1234567890" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-2"></i>Call Support
                        </a>
                        <a href="/contact" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-envelope me-2"></i>Email Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-method .card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method .card:hover {
    border-color: #0d6efd !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.payment-method input[type="radio"]:checked + label + p + .payment-icons {
    opacity: 1;
}

.payment-icons {
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.summary-item {
    border-bottom: 1px solid #eee;
}

.summary-item:last-child {
    border-bottom: none;
}

.sticky-top {
    z-index: 1020;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paymentForm');
    const payBtn = document.getElementById('payBtn');
    
    // Handle payment method selection
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            // Update UI based on selection
            document.querySelectorAll('.payment-method .card').forEach(card => {
                card.classList.remove('border-primary');
            });
            
            this.closest('.payment-method').querySelector('.card').classList.add('border-primary');
        });
    });
    
    // Set initial selection
    document.querySelector('input[name="payment_method"]:checked').dispatchEvent(new Event('change'));
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        processPayment();
    });
    
    function processPayment() {
        const originalText = payBtn.innerHTML;
        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        
        const formData = new FormData(form);
        
        fetch('/payment/process-instamojo', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': window.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to payment gateway
                window.location.href = data.payment_url;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Payment Failed',
                    text: data.message || 'Unable to initiate payment. Please try again.'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred. Please try again.'
            });
        })
        .finally(() => {
            payBtn.disabled = false;
            payBtn.innerHTML = originalText;
        });
    }
});
</script>
