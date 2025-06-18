<?php
$title = 'Patient Login - US Fitness Lab';
$additionalCSS = ['/assets/css/auth.css'];
$additionalJS = ['/assets/js/auth.js'];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Patient Login
                    </h3>
                    <p class="mb-0 mt-2">Access your account and book tests</p>
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

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <form id="loginForm" action="/patient/authenticate" method="POST" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= old('email') ?>" required autofocus>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me for 30 days
                                </label>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="/patient/forgot-password" class="text-decoration-none">
                                <i class="fas fa-key me-1"></i>
                                Forgot your password?
                            </a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-2">Don't have an account?</p>
                        <a href="/patient/register" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus me-2"></i>
                            Create Account
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-2 text-muted">Quick Actions</p>
                        <div class="d-grid gap-2">
                            <a href="/tests" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-search me-2"></i>
                                Browse Available Tests
                            </a>
                            <a href="/branches" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Find Nearest Branch
                            </a>
                        </div>
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

.btn-outline-primary:hover,
.btn-outline-info:hover,
.btn-outline-success:hover {
    transform: translateY(-1px);
}

.alert {
    border-radius: 8px;
}

.input-group .btn {
    border-color: #dee2e6;
}

hr {
    opacity: 0.1;
}
</style>
