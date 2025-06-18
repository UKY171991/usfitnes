<?php
$title = 'My Profile - US Fitness Lab';
$additionalCSS = ['/assets/css/profile.css'];
$additionalJS = ['/assets/js/patient-profile.js'];
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/patient/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Profile</li>
                </ol>
            </nav>
            <h1 class="display-6 text-primary mb-2">
                <i class="fas fa-user-edit me-3"></i>
                My Profile
            </h1>
            <p class="lead text-muted">Manage your personal information and account settings</p>
        </div>
    </div>

    <div class="row">
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Personal Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form id="profileForm" action="/patient/profile/update" method="POST" novalidate>
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>First Name *
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($patient['first_name']) ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Last Name *
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($patient['last_name']) ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address *
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($patient['email']) ?>" required>
                            <div class="form-text">
                                Email is used for login and receiving important notifications.
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Phone Number *
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($patient['phone']) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Date of Birth *
                                </label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?= htmlspecialchars($patient['date_of_birth']) ?>" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">
                                    <i class="fas fa-venus-mars me-1"></i>Gender *
                                </label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= $patient['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= $patient['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= $patient['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Address
                            </label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">
                                    <i class="fas fa-city me-1"></i>City
                                </label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= htmlspecialchars($patient['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">
                                    <i class="fas fa-map-pin me-1"></i>Pincode
                                </label>
                                <input type="text" class="form-control" id="pincode" name="pincode" 
                                       value="<?= htmlspecialchars($patient['pincode'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Update Profile
                            </button>
                            <a href="/patient/dashboard" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-phone-alt me-2"></i>
                        Emergency Contact
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="emergencyContactForm" action="/patient/emergency-contact/update" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_name" class="form-label">Contact Name</label>
                                <input type="text" class="form-control" id="emergency_name" name="emergency_name" 
                                       value="<?= htmlspecialchars($patient['emergency_contact_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_phone" class="form-label">Contact Phone</label>
                                <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone" 
                                       value="<?= htmlspecialchars($patient['emergency_contact_phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="emergency_relationship" class="form-label">Relationship</label>
                            <select class="form-select" id="emergency_relationship" name="emergency_relationship">
                                <option value="">Select Relationship</option>
                                <option value="spouse" <?= ($patient['emergency_contact_relationship'] ?? '') === 'spouse' ? 'selected' : '' ?>>Spouse</option>
                                <option value="parent" <?= ($patient['emergency_contact_relationship'] ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
                                <option value="sibling" <?= ($patient['emergency_contact_relationship'] ?? '') === 'sibling' ? 'selected' : '' ?>>Sibling</option>
                                <option value="child" <?= ($patient['emergency_contact_relationship'] ?? '') === 'child' ? 'selected' : '' ?>>Child</option>
                                <option value="friend" <?= ($patient['emergency_contact_relationship'] ?? '') === 'friend' ? 'selected' : '' ?>>Friend</option>
                                <option value="other" <?= ($patient['emergency_contact_relationship'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>
                            Update Emergency Contact
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Account Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Patient ID:</td>
                            <td><strong><?= $patient['id'] ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member Since:</td>
                            <td><?= date('M d, Y', strtotime($patient['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Updated:</td>
                            <td><?= date('M d, Y', strtotime($patient['updated_at'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email Verified:</td>
                            <td>
                                <?php if ($patient['email_verified']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Verified
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                                    <br>
                                    <a href="/patient/verify-email" class="btn btn-sm btn-outline-warning mt-2">
                                        Send Verification
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Status:</td>
                            <td>
                                <span class="badge bg-<?= $patient['status'] === 'active' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($patient['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>
                        Security Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/patient/change-password" class="btn btn-outline-danger">
                            <i class="fas fa-key me-2"></i>
                            Change Password
                        </a>
                        <a href="/patient/login-history" class="btn btn-outline-info">
                            <i class="fas fa-history me-2"></i>
                            Login History
                        </a>
                        <button class="btn btn-outline-warning" onclick="enableTwoFactor()">
                            <i class="fas fa-shield-alt me-2"></i>
                            Enable 2FA
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preferences -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <form id="preferencesForm" action="/patient/preferences/update" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" 
                                       name="email_notifications" 
                                       <?= ($patient['preferences']['email_notifications'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="emailNotifications">
                                    Email Notifications
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="smsNotifications" 
                                       name="sms_notifications" 
                                       <?= ($patient['preferences']['sms_notifications'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="smsNotifications">
                                    SMS Notifications
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="marketingEmails" 
                                       name="marketing_emails" 
                                       <?= ($patient['preferences']['marketing_emails'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="marketingEmails">
                                    Marketing Emails
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reminderTime" class="form-label">Appointment Reminder</label>
                            <select class="form-select form-select-sm" id="reminderTime" name="reminder_time">
                                <option value="24" <?= ($patient['preferences']['reminder_hours'] ?? 24) == 24 ? 'selected' : '' ?>>24 hours before</option>
                                <option value="12" <?= ($patient['preferences']['reminder_hours'] ?? 24) == 12 ? 'selected' : '' ?>>12 hours before</option>
                                <option value="6" <?= ($patient['preferences']['reminder_hours'] ?? 24) == 6 ? 'selected' : '' ?>>6 hours before</option>
                                <option value="2" <?= ($patient['preferences']['reminder_hours'] ?? 24) == 2 ? 'selected' : '' ?>>2 hours before</option>
                                <option value="0" <?= ($patient['preferences']['reminder_hours'] ?? 24) == 0 ? 'selected' : '' ?>>No reminder</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-save me-2"></i>
                            Save Preferences
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 12px;
}

.card-header {
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn {
    border-radius: 8px;
}

.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.badge {
    font-size: 0.75rem;
}

.table-sm td {
    padding: 0.5rem 0.25rem;
    vertical-align: top;
}

.alert {
    border-radius: 8px;
}
</style>
