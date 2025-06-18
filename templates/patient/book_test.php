<?php
$title = 'Book Test - US Fitness Lab';
$additionalCSS = ['/assets/css/booking.css'];
$additionalJS = ['/assets/js/booking.js'];
?>

<div class="container my-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/tests">Tests</a></li>
                    <li class="breadcrumb-item active">Book Test</li>
                </ol>
            </nav>
            <h1 class="display-6 text-primary mb-2">
                <i class="fas fa-calendar-plus me-3"></i>
                Book a Test
            </h1>
            <p class="lead text-muted">Schedule your lab test appointment at your preferred location and time.</p>
        </div>
    </div>

    <!-- Booking Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Test Booking Form
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="bookingForm" action="/booking/store" method="POST">
                        <?= csrf_field() ?>
                        
                        <!-- Test Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="test_category" class="form-label">
                                    <i class="fas fa-list me-1"></i>Test Category
                                </label>
                                <select class="form-select" id="test_category" name="test_category">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="test_id" class="form-label">
                                    <i class="fas fa-vial me-1"></i>Select Test <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="test_id" name="test_id" required>
                                    <option value="">Choose a test</option>
                                    <?php foreach ($tests as $test): ?>
                                        <option value="<?= $test['id'] ?>" data-price="<?= $test['price'] ?>" data-description="<?= htmlspecialchars($test['description']) ?>">
                                            <?= htmlspecialchars($test['test_name']) ?> - ₹<?= number_format($test['price'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Or <a href="#" id="searchTestLink" data-bs-toggle="modal" data-bs-target="#testSearchModal">search for a specific test</a>
                                </div>
                            </div>
                        </div>

                        <!-- Test Details Display -->
                        <div id="testDetails" class="alert alert-info d-none mb-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Test Information</h6>
                            <div id="testInfo"></div>
                        </div>

                        <!-- Branch Selection -->
                        <div class="mb-4">
                            <label for="branch_id" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Select Branch <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="branch_id" name="branch_id" required>
                                <option value="">Choose a branch</option>
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?= $branch['id'] ?>" data-address="<?= htmlspecialchars($branch['address']) ?>">
                                        <?= htmlspecialchars($branch['branch_name']) ?> - <?= htmlspecialchars($branch['city']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="branchDetails" class="form-text d-none">
                                <i class="fas fa-location-dot me-1"></i>
                                <span id="branchAddress"></span>
                            </div>
                        </div>

                        <!-- Appointment Date & Time -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Preferred Date
                                </label>
                                <input type="date" class="form-control" id="appointment_date" name="appointment_date" min="<?= date('Y-m-d') ?>">
                                <div class="form-text">Leave blank for flexible scheduling</div>
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_time" class="form-label">
                                    <i class="fas fa-clock me-1"></i>Preferred Time
                                </label>
                                <select class="form-select" id="appointment_time" name="appointment_time">
                                    <option value="">Any time</option>
                                    <option value="06:00">6:00 AM</option>
                                    <option value="07:00">7:00 AM</option>
                                    <option value="08:00">8:00 AM</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                    <option value="19:00">7:00 PM</option>
                                    <option value="20:00">8:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Special Instructions (Optional)
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Any special instructions or requirements..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-calendar-plus me-2"></i>
                                Book Test
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Booking Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        Booking Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div id="bookingSummary">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <p>Select a test and branch to see booking summary</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Quick Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Book 24 hours in advance for guaranteed slots</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning me-2"></i>
                            <small>Morning slots (6-10 AM) recommended for fasting tests</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-id-card text-info me-2"></i>
                            <small>Carry a valid ID proof for verification</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <small>Call us for home collection services</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Search Modal -->
<div class="modal fade" id="testSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-search me-2"></i>
                    Search Tests
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="testSearchInput" 
                           placeholder="Search for tests by name or keyword...">
                </div>
                <div id="testSearchResults" class="list-group">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>Start typing to search for tests</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sticky-top {
    z-index: 1020;
}

#bookingSummary .summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

#bookingSummary .summary-item:last-child {
    border-bottom: none;
}

.test-search-item {
    cursor: pointer;
    transition: background-color 0.2s;
}

.test-search-item:hover {
    background-color: #f8f9fa;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('bookingForm');
    const testSelect = document.getElementById('test_id');
    const branchSelect = document.getElementById('branch_id');
    const categorySelect = document.getElementById('test_category');
    const testSearchInput = document.getElementById('testSearchInput');
    
    // Handle category change
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        if (categoryId) {
            loadTestsByCategory(categoryId);
        } else {
            // Reset to all tests
            testSelect.innerHTML = '<option value="">Choose a test</option>';
            <?php foreach ($tests as $test): ?>
                testSelect.innerHTML += '<option value="<?= $test['id'] ?>" data-price="<?= $test['price'] ?>" data-description="<?= htmlspecialchars($test['description']) ?>"><?= htmlspecialchars($test['test_name']) ?> - ₹<?= number_format($test['price'], 2) ?></option>';
            <?php endforeach; ?>
        }
    });
    
    // Handle test selection
    testSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            showTestDetails(selectedOption);
            updateBookingSummary();
        } else {
            hideTestDetails();
            updateBookingSummary();
        }
    });
    
    // Handle branch selection
    branchSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            showBranchDetails(selectedOption);
            updateBookingSummary();
        } else {
            hideBranchDetails();
            updateBookingSummary();
        }
    });
    
    // Test search functionality
    let searchTimeout;
    testSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => searchTests(query), 300);
        } else {
            document.getElementById('testSearchResults').innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Start typing to search for tests</p>
                </div>
            `;
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitBooking();
    });
    
    function loadTestsByCategory(categoryId) {
        fetch(`/api/tests/category/${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    testSelect.innerHTML = '<option value="">Choose a test</option>';
                    data.tests.forEach(test => {
                        testSelect.innerHTML += `<option value="${test.id}" data-price="${test.price}" data-description="${test.description}">${test.test_name} - ₹${parseFloat(test.price).toFixed(2)}</option>`;
                    });
                }
            })
            .catch(error => console.error('Error loading tests:', error));
    }
    
    function showTestDetails(option) {
        const price = option.dataset.price;
        const description = option.dataset.description;
        
        document.getElementById('testInfo').innerHTML = `
            <div class="row">
                <div class="col-md-8">
                    <strong>${option.text.split(' - ')[0]}</strong>
                    ${description ? `<p class="mb-1 mt-2">${description}</p>` : ''}
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-success fs-6">₹${parseFloat(price).toFixed(2)}</span>
                </div>
            </div>
        `;
        document.getElementById('testDetails').classList.remove('d-none');
    }
    
    function hideTestDetails() {
        document.getElementById('testDetails').classList.add('d-none');
    }
    
    function showBranchDetails(option) {
        const address = option.dataset.address;
        document.getElementById('branchAddress').textContent = address;
        document.getElementById('branchDetails').classList.remove('d-none');
    }
    
    function hideBranchDetails() {
        document.getElementById('branchDetails').classList.add('d-none');
    }
    
    function updateBookingSummary() {
        const testOption = testSelect.options[testSelect.selectedIndex];
        const branchOption = branchSelect.options[branchSelect.selectedIndex];
        const appointmentDate = document.getElementById('appointment_date').value;
        const appointmentTime = document.getElementById('appointment_time').value;
        
        if (testOption.value && branchOption.value) {
            const price = parseFloat(testOption.dataset.price);
            const testName = testOption.text.split(' - ')[0];
            const branchName = branchOption.text;
            
            document.getElementById('bookingSummary').innerHTML = `
                <div class="summary-item">
                    <span><i class="fas fa-vial me-2"></i>Test:</span>
                    <span>${testName}</span>
                </div>
                <div class="summary-item">
                    <span><i class="fas fa-map-marker-alt me-2"></i>Branch:</span>
                    <span>${branchName}</span>
                </div>
                ${appointmentDate ? `
                <div class="summary-item">
                    <span><i class="fas fa-calendar me-2"></i>Date:</span>
                    <span>${new Date(appointmentDate).toLocaleDateString()}</span>
                </div>` : ''}
                ${appointmentTime ? `
                <div class="summary-item">
                    <span><i class="fas fa-clock me-2"></i>Time:</span>
                    <span>${appointmentTime}</span>
                </div>` : ''}
                <div class="summary-item border-top pt-2 mt-2">
                    <span class="fw-bold"><i class="fas fa-rupee-sign me-2"></i>Total Amount:</span>
                    <span class="fw-bold text-success">₹${price.toFixed(2)}</span>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Payment can be made online after booking confirmation
                    </small>
                </div>
            `;
        } else {
            document.getElementById('bookingSummary').innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                    <p>Select a test and branch to see booking summary</p>
                </div>
            `;
        }
    }
    
    function searchTests(query) {
        fetch(`/api/tests/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const results = document.getElementById('testSearchResults');
                    if (data.tests.length > 0) {
                        results.innerHTML = data.tests.map(test => `
                            <div class="list-group-item test-search-item" data-test-id="${test.id}" data-test-price="${test.price}" data-test-description="${test.description}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">${test.test_name}</h6>
                                        <p class="mb-1 text-muted small">${test.description || ''}</p>
                                        <small class="text-primary">${test.category_name || ''}</small>
                                    </div>
                                    <span class="badge bg-success">₹${parseFloat(test.price).toFixed(2)}</span>
                                </div>
                            </div>
                        `).join('');
                        
                        // Add click handlers
                        results.querySelectorAll('.test-search-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const testId = this.dataset.testId;
                                const testPrice = this.dataset.testPrice;
                                const testDescription = this.dataset.testDescription;
                                const testName = this.querySelector('h6').textContent;
                                
                                // Update main form
                                testSelect.innerHTML = `<option value="${testId}" data-price="${testPrice}" data-description="${testDescription}" selected>${testName} - ₹${parseFloat(testPrice).toFixed(2)}</option>`;
                                testSelect.value = testId;
                                testSelect.dispatchEvent(new Event('change'));
                                
                                // Close modal
                                bootstrap.Modal.getInstance(document.getElementById('testSearchModal')).hide();
                            });
                        });
                    } else {
                        results.innerHTML = `
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <p>No tests found for "${query}"</p>
                            </div>
                        `;
                    }
                }
            })
            .catch(error => {
                console.error('Error searching tests:', error);
                document.getElementById('testSearchResults').innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Error searching tests. Please try again.</p>
                    </div>
                `;
            });
    }
    
    function submitBooking() {
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': window.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Booking Successful!',
                    text: data.message,
                    confirmButtonText: 'Proceed to Payment'
                }).then(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Booking Failed',
                    text: data.message || 'An error occurred while processing your booking.'
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
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }
});
</script>
