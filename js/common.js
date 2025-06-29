/**
 * PathLab Pro - Common JavaScript Utilities
 * 
 * This file contains common utility functions that can be used across the entire application.
 */

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

// Initialize on document ready
$(document).ready(function() {
  // Initialize form components
  initializeFormComponents();
  
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
