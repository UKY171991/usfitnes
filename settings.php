<?php
$page_title = 'Settings';
$breadcrumbs = [
    ['title' => 'Home', 'url' => 'dashboard.php'],
    ['title' => 'Settings']
];
$additional_css = ['css/settings.css'];
$additional_js = ['js/settings.js'];

ob_start();
?>

<div class="row">
    <div class="col-md-4">
        <!-- Profile Settings Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user mr-2"></i>
                    Profile Settings
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <h5 class="mt-2"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></h5>
                    <p class="text-muted"><?php echo ucfirst($_SESSION['user_type'] ?? 'user'); ?></p>
                </div>
                
                <button type="button" class="btn btn-primary btn-block" onclick="showProfileModal()">
                    <i class="fas fa-edit mr-1"></i>
                    Edit Profile
                </button>
                
                <button type="button" class="btn btn-warning btn-block mt-2" onclick="showPasswordModal()">
                    <i class="fas fa-key mr-1"></i>
                    Change Password
                </button>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-2"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-info btn-block" onclick="exportUserData()">
                    <i class="fas fa-download mr-1"></i>
                    Export My Data
                </button>
                
                <button type="button" class="btn btn-secondary btn-block mt-2" onclick="showActivityLog()">
                    <i class="fas fa-history mr-1"></i>
                    Activity Log
                </button>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- System Settings Card -->
        <?php if ($_SESSION['user_type'] === 'admin'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i>
                    System Settings
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="setting-item">
                            <h6><i class="fas fa-building mr-2"></i>Lab Information</h6>
                            <p class="text-muted">Update laboratory details and contact information</p>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showLabInfoModal()">
                                Configure
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="setting-item">
                            <h6><i class="fas fa-envelope mr-2"></i>Email Settings</h6>
                            <p class="text-muted">Configure SMTP and email notifications</p>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showEmailSettingsModal()">
                                Configure
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="setting-item">
                            <h6><i class="fas fa-bell mr-2"></i>Notifications</h6>
                            <p class="text-muted">Manage system notifications and alerts</p>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showNotificationSettings()">
                                Configure
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="setting-item">
                            <h6><i class="fas fa-shield-alt mr-2"></i>Security</h6>
                            <p class="text-muted">Security settings and access control</p>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showSecuritySettings()">
                                Configure
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Preferences Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sliders-h mr-2"></i>
                    Preferences
                </h3>
            </div>
            <div class="card-body">
                <form id="preferencesForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Theme</label>
                                <select class="form-control" name="theme" id="themeSelect">
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Language</label>
                                <select class="form-control" name="language">
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Format</label>
                                <select class="form-control" name="date_format">
                                    <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                    <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                    <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Timezone</label>
                                <select class="form-control" name="timezone">
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">Eastern Time</option>
                                    <option value="America/Chicago">Central Time</option>
                                    <option value="America/Los_Angeles">Pacific Time</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="emailNotifications" name="email_notifications">
                            <label class="custom-control-label" for="emailNotifications">
                                Enable email notifications
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="autoSave" name="auto_save">
                            <label class="custom-control-label" for="autoSave">
                                Enable auto-save
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Save Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Profile</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="profileForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="fullName">Full Name *</label>
                        <input type="text" class="form-control" id="fullName" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" readonly>
                        <small class="form-text text-muted">Username cannot be changed</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Password Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Change Password</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="passwordForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="currentPassword">Current Password *</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="newPassword">New Password *</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="6">
                        <small class="form-text text-muted">Password must be at least 6 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key mr-1"></i>
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Activity Log Modal -->
<div class="modal fade" id="activityModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Activity Log</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="activityLogContent">
                    <!-- Activity log will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'includes/adminlte3_template.php';
?>
