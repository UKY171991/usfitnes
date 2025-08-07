/**
 * PathLab Pro - Common JavaScript Utilities
 * 
 * This file contains common utility functions that can be used across the entire application.
 */

// Global utility functions
// Standardized alert function
function showAlert(type, message, containerId = 'alertContainer') {
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' || type === 'danger' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'fas fa-check' : 
                type === 'error' || type === 'danger' ? 'fas fa-times' : 
                type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${icon}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = alert;
        // Auto-dismiss after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(() => {
                const alertElement = container.querySelector('.alert');
                if (alertElement) {
                    $(alertElement).alert('close');
                }
            }, 5000);
        }
    } else {
        // Fallback to console if container not found
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// HTML escape function for security
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Calculate age from date of birth
function calculateAge(dateOfBirth) {
    if (!dateOfBirth) return 'N/A';
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

// Format phone number
function formatPhone(phone) {
    if (!phone) return '';
    const cleaned = phone.replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return phone;
}

// Initialize common form components
function initializeFormComponents() {
  // Initialize Select2
  if ($.fn.select2) {
    $('.select2').select2({
      theme: 'bootstrap4',
      width: '100%'
    });
  }
  
  // Initialize InputMask for phone numbers
  if ($.fn.inputmask) {
    $('input[type="tel"]').inputmask({
      mask: "(999) 999-9999",
      showMaskOnHover: false,
      showMaskOnFocus: true
    });
    
    // Date masks
    $('.date-mask').inputmask('yyyy-mm-dd', {
      placeholder: 'YYYY-MM-DD',
      showMaskOnHover: false
    });
    
    // Currency masks
    $('.currency-mask').inputmask('currency', {
      prefix: '$',
      digits: 2,
      digitsOptional: false,
      rightAlign: false
    });
  }
  
  // Initialize Datepicker
  if ($.fn.daterangepicker) {
    $('.datepicker').daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      minYear: 1900,
      maxYear: parseInt(moment().format('YYYY'), 10) + 10,
      locale: {
        format: 'YYYY-MM-DD'
      }
    });
  }
  
  // Initialize Summernote WYSIWYG editor
  if ($.fn.summernote) {
    $('.summernote').summernote({
      height: 200,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'underline', 'clear']],
        ['color', ['color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['view', ['fullscreen', 'codeview', 'help']]
      ]
    });
  }
  
  // Initialize form validation
  if ($.fn.validate) {
    $('.needs-validation').validate({
      errorElement: 'span',
      errorPlacement: function(error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
      },
      highlight: function(element, errorClass, validClass) {
        $(element).addClass('is-invalid').removeClass('is-valid');
      },
      unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass('is-invalid').addClass('is-valid');
      }
    });
  }
}

// Format date for display
function formatDate(dateString) {
  if (!dateString) return 'N/A';
  return moment(dateString).format('MMM DD, YYYY');
}

// Format datetime for display
function formatDateTime(dateTimeString) {
  if (!dateTimeString) return 'N/A';
  return moment(dateTimeString).format('MMM DD, YYYY h:mm A');
}

// Format currency
function formatCurrency(amount) {
  if (amount === null || amount === undefined) return '$0.00';
  return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Show loading overlay
function showLoading(message = 'Loading...') {
  Swal.fire({
    title: message,
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });
}

// Hide loading overlay
function hideLoading() {
  Swal.close();
}

// Show success toast notification
function showSuccessToast(message) {
  Swal.fire({
    icon: 'success',
    title: 'Success',
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
}

// Show error toast notification
function showErrorToast(message) {
  Swal.fire({
    icon: 'error',
    title: 'Error',
    text: message,
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
  });
}

// Show confirmation dialog
function showConfirmDialog(title, message, confirmCallback, cancelCallback) {
  Swal.fire({
    title: title,
    text: message,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
      if (typeof confirmCallback === 'function') {
        confirmCallback();
      }
    } else if (typeof cancelCallback === 'function') {
      cancelCallback();
    }
  });
}

// AJAX helper function with error handling
function ajaxRequest(url, method, data, successCallback, errorCallback) {
  $.ajax({
    url: url,
    type: method,
    data: data,
    dataType: 'json',
    beforeSend: function() {
      showLoading();
    },
    success: function(response) {
      hideLoading();
      if (response.success) {
        if (typeof successCallback === 'function') {
          successCallback(response);
        }
      } else {
        showErrorToast(response.message || 'An error occurred');
        if (typeof errorCallback === 'function') {
          errorCallback(response);
        }
      }
    },
    error: function(xhr, status, error) {
      hideLoading();
      showErrorToast('An error occurred: ' + error);
      if (typeof errorCallback === 'function') {
        errorCallback({
          success: false,
          message: 'An error occurred: ' + error,
          xhr: xhr
        });
      }
    }
  });
}

// Initialize DataTable with standard options
function initializeDataTable(tableId, options = {}) {
  const defaultOptions = {
    responsive: true,
    lengthChange: true,
    autoWidth: false,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
  };
  
  const mergedOptions = {...defaultOptions, ...options};
  
  return $('#' + tableId).DataTable(mergedOptions);
}

// Contact form handling
function initializeContactForm() {
    const contactForm = document.getElementById('contactForm');
    if (!contactForm) return;

    // Form validation
    const validateForm = () => {
        let isValid = true;
        const formData = new FormData(contactForm);
        
        // Clear previous validation
        contactForm.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('is-invalid', 'is-valid');
            const feedback = input.parentNode.querySelector('.invalid-feedback, .valid-feedback');
            if (feedback) feedback.remove();
        });

        // Required fields validation
        const requiredFields = [
            { name: 'firstName', label: 'First Name' },
            { name: 'lastName', label: 'Last Name' },
            { name: 'email', label: 'Email Address' },
            { name: 'subject', label: 'Subject' },
            { name: 'message', label: 'Message' }
        ];

        requiredFields.forEach(field => {
            const input = contactForm.querySelector(`[name="${field.name}"]`);
            const value = formData.get(field.name);
            
            if (!value || value.trim() === '') {
                showFieldError(input, `${field.label} is required`);
                isValid = false;
            } else if (field.name === 'email' && !isValidEmail(value)) {
                showFieldError(input, 'Please enter a valid email address');
                isValid = false;
            } else {
                showFieldSuccess(input);
            }
        });

        return isValid;
    };

    const showFieldError = (input, message) => {
        input.classList.add('is-invalid');
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        input.parentNode.appendChild(feedback);
    };

    const showFieldSuccess = (input) => {
        input.classList.add('is-valid');
    };

    const isValidEmail = (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    };

    // Real-time validation
    contactForm.addEventListener('input', (e) => {
        const input = e.target;
        if (input.classList.contains('form-control')) {
            // Clear previous validation state
            input.classList.remove('is-invalid', 'is-valid');
            const feedback = input.parentNode.querySelector('.invalid-feedback, .valid-feedback');
            if (feedback) feedback.remove();

            // Validate on input
            const value = input.value.trim();
            if (input.required && value === '') {
                // Don't show error while typing
                return;
            } else if (input.type === 'email' && value && !isValidEmail(value)) {
                showFieldError(input, 'Please enter a valid email address');
            } else if (value) {
                showFieldSuccess(input);
            }
        }
    });

    // Form submission
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!validateForm()) {
            showAlert('error', 'Please correct the errors in the form before submitting.');
            return;
        }

        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

        try {
            // Submit form data
            const formData = new FormData(contactForm);
            const response = await fetch('contact_handler.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                showAlert('success', result.message);
                
                // Reset form
                contactForm.reset();
                contactForm.querySelectorAll('.form-control').forEach(input => {
                    input.classList.remove('is-invalid', 'is-valid');
                });
                
                // Scroll to top of contact section
                document.getElementById('contact').scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            } else {
                showAlert('error', result.message);
            }
            
        } catch (error) {
            showAlert('error', 'Sorry, there was an error sending your message. Please try again or contact us directly.');
            console.error('Contact form error:', error);
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Smooth scrolling for contact section links
function initializeContactScrolling() {
    // Handle navbar contact link
    document.addEventListener('click', (e) => {
        const target = e.target.closest('a[href="#contact"]');
        if (target) {
            e.preventDefault();
            const contactSection = document.getElementById('contact');
            if (contactSection) {
                contactSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
}

// Initialize contact form animations
function initializeContactAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);

    // Observe contact section elements
    document.querySelectorAll('#contact .fade-in-up').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
}

// Initialize on document ready
$(document).ready(function() {
  // Initialize form components
  initializeFormComponents();
  
  // Initialize contact form
  initializeContactForm();
  
  // Initialize smooth scrolling for contact section
  initializeContactScrolling();
  
  // Initialize contact form animations
  initializeContactAnimations();
  
  // Add global AJAX error handling
  $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    if (jqxhr.status === 401) {
      Swal.fire({
        icon: 'error',
        title: 'Session Expired',
        text: 'Your session has expired. Please log in again.',
        allowOutsideClick: false
      }).then(() => {
        window.location.href = 'index.php';
      });
    }
  });
});

// CRUD Operations Class
class CrudOperations {
    constructor(apiEndpoint, entityName) {
        this.apiEndpoint = apiEndpoint;
        this.entityName = entityName;
    }

    // Create or Update record
    save(formData, isUpdate = false) {
        const action = isUpdate ? 'update' : 'create';
        const method = isUpdate ? 'PUT' : 'POST';
        
        return this.makeRequest(action, method, formData);
    }

    // Get single record
    get(id) {
        return this.makeRequest('read', 'GET', null, `/${id}`);
    }

    // Delete record
    delete(id) {
        return this.makeRequest('delete', 'DELETE', null, `/${id}`);
    }

    // Get all records with pagination
    list(params = {}) {
        return this.makeRequest('list', 'GET', params);
    }

    // Make AJAX request
    makeRequest(action, method, data = null, urlSuffix = '') {
        return new Promise((resolve, reject) => {
            const ajaxOptions = {
                url: this.apiEndpoint + urlSuffix,
                type: method,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        resolve(response);
                    } else {
                        reject(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`${action} failed:`, error);
                    reject({
                        success: false,
                        message: 'Request failed: ' + error
                    });
                }
            };

            // Add data based on method
            if (method === 'GET') {
                ajaxOptions.data = data;
            } else {
                ajaxOptions.data = data instanceof FormData ? data : JSON.stringify(data);
                if (!(data instanceof FormData)) {
                    ajaxOptions.contentType = 'application/json';
                }
            }

            // Add action parameter
            if (ajaxOptions.data instanceof FormData) {
                ajaxOptions.data.append('action', action);
            } else if (typeof ajaxOptions.data === 'object') {
                ajaxOptions.data.action = action;
            }

            $.ajax(ajaxOptions);
        });
    }
}

// Form Handler Class
class FormHandler {
    constructor(formSelector, apiEndpoint, options = {}) {
        this.formSelector = formSelector;
        this.apiEndpoint = apiEndpoint;
        this.options = {
            onSuccess: options.onSuccess || function() {},
            onError: options.onError || function() {},
            onValidationError: options.onValidationError || function() {},
            resetFormAfterSuccess: options.resetFormAfterSuccess !== false,
            showLoader: options.showLoader !== false
        };
        
        this.init();
    }

    init() {
        const self = this;
        $(this.formSelector).on('submit', function(e) {
            e.preventDefault();
            self.handleSubmit();
        });
    }

    handleSubmit() {
        const form = $(this.formSelector);
        const formData = new FormData(form[0]);
        const submitButton = form.find('button[type="submit"]');
        
        // Validate form
        if (!this.validateForm()) {
            return;
        }

        // Show loader
        if (this.options.showLoader) {
            this.showLoader(submitButton);
        }

        // Make AJAX request
        $.ajax({
            url: this.apiEndpoint,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: (response) => {
                this.hideLoader(submitButton);
                
                if (response.success) {
                    if (this.options.resetFormAfterSuccess) {
                        form[0].reset();
                    }
                    this.options.onSuccess(response);
                    showToast('success', response.message);
                } else {
                    if (response.errors) {
                        this.showValidationErrors(response.errors);
                        this.options.onValidationError(response.errors);
                    } else {
                        this.options.onError(response);
                        showToast('error', response.message || 'An error occurred');
                    }
                }
            },
            error: (xhr, status, error) => {
                this.hideLoader(submitButton);
                console.error('Form submission error:', error);
                const errorMessage = xhr.responseJSON?.message || 'Request failed: ' + error;
                this.options.onError({ message: errorMessage });
                showToast('error', errorMessage);
            }
        });
    }

    validateForm() {
        const form = $(this.formSelector);
        let isValid = true;
        
        // Clear previous validation errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();
        
        // HTML5 validation
        if (!form[0].checkValidity()) {
            isValid = false;
            form[0].reportValidity();
        }
        
        return isValid;
    }

    showValidationErrors(errors) {
        const form = $(this.formSelector);
        
        Object.keys(errors).forEach(field => {
            const input = form.find(`[name="${field}"]`);
            if (input.length) {
                input.addClass('is-invalid');
                const errorDiv = $(`<div class="invalid-feedback">${errors[field]}</div>`);
                input.after(errorDiv);
            }
        });
    }

    showLoader(button) {
        button.prop('disabled', true);
        const originalText = button.html();
        button.data('original-text', originalText);
        button.html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');
    }

    hideLoader(button) {
        button.prop('disabled', false);
        const originalText = button.data('original-text');
        if (originalText) {
            button.html(originalText);
        }
    }
}

// Toast notification helper
function showToast(type, message, title = '') {
    const options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: type === 'error' ? 8000 : 5000
    };
    
    switch(type) {
        case 'success':
            toastr.success(message, title || 'Success', options);
            break;
        case 'error':
            toastr.error(message, title || 'Error', options);
            break;
        case 'warning':
            toastr.warning(message, title || 'Warning', options);
            break;
        case 'info':
            toastr.info(message, title || 'Info', options);
            break;
    }
}
