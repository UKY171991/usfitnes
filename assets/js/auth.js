/**
 * Patient Authentication JavaScript
 * Handles login, registration, and form validation
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize authentication forms
    initAuthForms();
});

/**
 * Initialize authentication forms
 */
function initAuthForms() {
    // Registration form
    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        initRegistrationForm(registrationForm);
    }
    
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        initLoginForm(loginForm);
    }
    
    // Password toggle functionality
    initPasswordToggle();
}

/**
 * Initialize registration form
 */
function initRegistrationForm(form) {
    // Real-time validation
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => clearFieldError(input));
    });
    
    // Password confirmation validation
    const password = form.querySelector('#password');
    const passwordConfirmation = form.querySelector('#password_confirmation');
    
    if (password && passwordConfirmation) {
        passwordConfirmation.addEventListener('input', () => {
            validatePasswordConfirmation(password, passwordConfirmation);
        });
    }
    
    // Form submission
    form.addEventListener('submit', handleRegistrationSubmit);
}

/**
 * Initialize login form
 */
function initLoginForm(form) {
    // Real-time validation
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => clearFieldError(input));
    });
    
    // Form submission
    form.addEventListener('submit', handleLoginSubmit);
}

/**
 * Initialize password toggle functionality
 */
function initPasswordToggle() {
    const toggleButtons = document.querySelectorAll('#togglePassword');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const passwordInput = this.parentElement.querySelector('input[type="password"], input[type="text"]');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

/**
 * Handle registration form submission
 */
function handleRegistrationSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validate all fields
    if (!validateRegistrationForm(form)) {
        showError('Please correct the errors below.');
        return;
    }
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
    submitButton.disabled = true;
    
    // Submit form
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Registration successful! Redirecting to dashboard...');
            setTimeout(() => {
                window.location.href = data.redirect || '/patient/dashboard';
            }, 1500);
        } else {
            showError(data.message || 'Registration failed. Please try again.');
            if (data.errors) {
                displayFieldErrors(data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showError('Registration failed. Please try again.');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

/**
 * Handle login form submission
 */
function handleLoginSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validate fields
    if (!validateLoginForm(form)) {
        showError('Please provide valid email and password.');
        return;
    }
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
    submitButton.disabled = true;
    
    // Submit form
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Login successful! Redirecting...');
            setTimeout(() => {
                window.location.href = data.redirect || '/patient/dashboard';
            }, 1000);
        } else {
            showError(data.message || 'Invalid email or password.');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showError('Login failed. Please try again.');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

/**
 * Validate registration form
 */
function validateRegistrationForm(form) {
    let isValid = true;
    
    // Required fields
    const requiredFields = ['first_name', 'last_name', 'email', 'phone', 'date_of_birth', 'gender', 'password'];
    
    requiredFields.forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field && !validateField(field)) {
            isValid = false;
        }
    });
    
    // Password confirmation
    const password = form.querySelector('#password');
    const passwordConfirmation = form.querySelector('#password_confirmation');
    if (password && passwordConfirmation) {
        if (!validatePasswordConfirmation(password, passwordConfirmation)) {
            isValid = false;
        }
    }
    
    // Terms acceptance
    const terms = form.querySelector('#terms');
    if (terms && !terms.checked) {
        showFieldError(terms, 'You must agree to the Terms of Service and Privacy Policy.');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate login form
 */
function validateLoginForm(form) {
    let isValid = true;
    
    const email = form.querySelector('#email');
    const password = form.querySelector('#password');
    
    if (!validateField(email)) isValid = false;
    if (!validateField(password)) isValid = false;
    
    return isValid;
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const fieldName = field.name;
    let isValid = true;
    let errorMessage = '';
    
    // Clear previous errors
    clearFieldError(field);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        errorMessage = 'This field is required.';
        isValid = false;
    }
    
    // Specific field validations
    if (value && isValid) {
        switch (fieldName) {
            case 'email':
                if (!isValidEmail(value)) {
                    errorMessage = 'Please enter a valid email address.';
                    isValid = false;
                }
                break;
                
            case 'phone':
                if (!isValidPhone(value)) {
                    errorMessage = 'Please enter a valid 10-digit phone number.';
                    isValid = false;
                }
                break;
                
            case 'password':
                if (value.length < 8) {
                    errorMessage = 'Password must be at least 8 characters long.';
                    isValid = false;
                } else if (!isValidPassword(value)) {
                    errorMessage = 'Password must contain uppercase, lowercase, and number.';
                    isValid = false;
                }
                break;
                
            case 'date_of_birth':
                if (!isValidDate(value)) {
                    errorMessage = 'Please enter a valid date of birth.';
                    isValid = false;
                } else if (!isValidAge(value)) {
                    errorMessage = 'You must be at least 18 years old.';
                    isValid = false;
                }
                break;
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    }
    
    return isValid;
}

/**
 * Validate password confirmation
 */
function validatePasswordConfirmation(passwordField, confirmationField) {
    const password = passwordField.value;
    const confirmation = confirmationField.value;
    
    clearFieldError(confirmationField);
    
    if (confirmation && password !== confirmation) {
        showFieldError(confirmationField, 'Password confirmation does not match.');
        return false;
    }
    
    return true;
}

/**
 * Show field error
 */
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    
    let feedbackElement = field.parentElement.querySelector('.invalid-feedback');
    if (feedbackElement) {
        feedbackElement.textContent = message;
    }
}

/**
 * Clear field error
 */
function clearFieldError(field) {
    field.classList.remove('is-invalid');
    
    let feedbackElement = field.parentElement.querySelector('.invalid-feedback');
    if (feedbackElement) {
        feedbackElement.textContent = '';
    }
}

/**
 * Display multiple field errors
 */
function displayFieldErrors(errors) {
    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            showFieldError(field, errors[fieldName]);
        }
    });
}

/**
 * Validation helper functions
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[0-9]{10}$/;
    return phoneRegex.test(phone);
}

function isValidPassword(password) {
    // At least one uppercase, one lowercase, and one number
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/;
    return passwordRegex.test(password);
}

function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

function isValidAge(dateString) {
    const birthDate = new Date(dateString);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age >= 18;
}

/**
 * Utility functions
 */
function showSuccess(message) {
    showAlert(message, 'success');
}

function showError(message) {
    showAlert(message, 'danger');
}

function showAlert(message, type) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of form
    const form = document.querySelector('form');
    if (form) {
        form.insertBefore(alert, form.firstChild);
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
