<?php
$title = 'Patient Registration - US Fitness Lab';
$additionalCSS = ['/assets/css/auth.css'];
$additionalJS = ['/assets/js/auth.js'];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Patient Registration
                    </h3>
                    <p class="mb-0 mt-2">Create your account to book tests</p>
                </div>
                <div class="card-body p-5">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form id="registrationForm" action="/patient/register" method="POST" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name *
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= old('first_name') ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Last Name *
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= old('last_name') ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= old('email') ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Phone Number *
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= old('phone') ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Date of Birth *
                                </label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?= old('date_of_birth') ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">
                                    <i class="fas fa-venus-mars me-1"></i>Gender *
                                </label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Address
                            </label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= old('address') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">
                                    <i class="fas fa-city me-1"></i>City
                                </label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= old('city') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">
                                    <i class="fas fa-map-pin me-1"></i>Pincode
                                </label>
                                <input type="text" class="form-control" id="pincode" name="pincode" 
                                       value="<?= old('pincode') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Password must be at least 8 characters long and contain uppercase, lowercase, and number.
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-lock me-1"></i>Confirm Password *
                            </label>
                            <input type="password" class="form-control" id="password_confirmation" 
                                   name="password_confirmation" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="/terms" target="_blank">Terms of Service</a> 
                                    and <a href="/privacy" target="_blank">Privacy Policy</a> *
                                </label>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>
                                Register Account
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Already have an account?</p>
                        <a href="/patient/login" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Login Here
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 15px;
}

.card-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    border-radius: 8px;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-1px);
}

.alert {
    border-radius: 8px;
}

.input-group .btn {
    border-color: #dee2e6;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}
</style>
