/**
 * PathLab Pro Home Page JavaScript
 * Handles animations, scroll effects, and interactive features
 */

// jQuery easing function
$.easing.easeInOutQuart = function (x, t, b, c, d) {
  if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
  return -c/2 * ((t-=2)*t*t*t - 2) + b;
};

$(document).ready(function() {
  // Initialize all features
  initScrollAnimations();
  initCounterAnimations();
  initParticleAnimations();
  initNavbarEffects();
  initButtonEffects();
  initSmoothScrolling();
  
  // Initialize contact form functionality
  initializeContactForm();
  initializeContactScrolling();
  initializeContactAnimations();
  
  // Force navbar visibility on page load
  forceNavbarVisibility();
});

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScrolling() {
  $('a[href*="#"]').on('click', function (e) {
    e.preventDefault();
    const target = $($(this).attr('href'));
    if (target.length) {
      $('html, body').animate({
        scrollTop: target.offset().top - 80
      }, 800, 'easeInOutQuart');
    }
  });
}

/**
 * Initialize navbar scroll effects and mobile collapse
 */
function initNavbarEffects() {
  // Enhanced navbar on scroll
  $(window).scroll(function() {
    const scrollTop = $(this).scrollTop();
    const navbar = $('.navbar-custom');
    
    if (scrollTop > 50) {
      navbar.addClass('scrolled');
    } else {
      navbar.removeClass('scrolled');
    }
    
    // Parallax effect for hero section
    const heroSection = $('.hero-section');
    const parallaxSpeed = 0.3;
    heroSection.css('transform', `translateY(${scrollTop * parallaxSpeed}px)`);
  });

  // Auto-collapse navbar on mobile after clicking a link
  $('.navbar-nav .nav-link').on('click', function() {
    if ($(window).width() < 992) {
      $('.navbar-collapse').collapse('hide');
    }
  });
}

/**
 * Initialize scroll animations using Intersection Observer
 */
function initScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('animated');
      }
    });
  }, observerOptions);

  document.querySelectorAll('.fade-in-up').forEach(el => {
    observer.observe(el);
  });
}

/**
 * Initialize counter animations for stats section
 */
function initCounterAnimations() {
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
        entry.target.classList.add('counted');
        animateCounter(entry.target);
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.counter').forEach(counter => {
    counterObserver.observe(counter);
  });
}

/**
 * Animate counter numbers
 * @param {Element} element - The counter element
 */
function animateCounter(element) {
  const target = parseInt(element.dataset.target);
  const duration = 2000;
  const step = target / (duration / 16);
  let current = 0;

  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      current = target;
      clearInterval(timer);
    }
    element.textContent = Math.floor(current) + '+';
  }, 16);
}

/**
 * Initialize floating particle animations
 */
function initParticleAnimations() {
  const particles = document.querySelectorAll('.particle');
  particles.forEach((particle, index) => {
    // Random size and animation duration
    const size = Math.random() * 10 + 5;
    const duration = Math.random() * 3 + 3;
    const delay = Math.random() * 2;
    
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';
    particle.style.animationDuration = duration + 's';
    particle.style.animationDelay = delay + 's';
    
    // Random horizontal position
    particle.style.left = Math.random() * 100 + '%';
  });
}

/**
 * Initialize button hover effects
 */
function initButtonEffects() {
  // Feature card hover effects
  $('.feature-card').hover(
    function() {
      $(this).find('.feature-icon').addClass('animate__animated animate__pulse');
    },
    function() {
      $(this).find('.feature-icon').removeClass('animate__animated animate__pulse');
    }
  );

  // Enhanced button interactions
  $('.btn-primary-custom, .btn-outline-custom').hover(
    function() {
      $(this).addClass('animate__animated animate__pulse');
    },
    function() {
      $(this).removeClass('animate__animated animate__pulse');
    }
  );
}

/**
 * Force navbar visibility to ensure all links are shown
 */
function forceNavbarVisibility() {
  // Wait for DOM to be fully ready
  setTimeout(function() {
    // Ensure navbar collapse is properly shown on desktop
    const navbar = document.querySelector('.navbar-custom');
    const navbarCollapse = document.querySelector('#navbarNav');
    const navbarNav = document.querySelector('.navbar-nav');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    if (navbar) {
      navbar.style.display = 'block';
      navbar.style.visibility = 'visible';
    }
    
    if (navbarCollapse) {
      // Force show collapse on desktop
      if (window.innerWidth >= 992) {
        navbarCollapse.classList.add('show');
        navbarCollapse.style.display = 'flex';
        navbarCollapse.style.visibility = 'visible';
        navbarCollapse.style.opacity = '1';
      }
    }
    
    if (navbarNav) {
      navbarNav.style.display = 'flex';
      navbarNav.style.visibility = 'visible';
      navbarNav.style.opacity = '1';
    }
    
    navLinks.forEach(function(link) {
      link.style.display = 'flex';
      link.style.visibility = 'visible';
      link.style.opacity = '1';
      link.style.color = '#2c5aa0';
    });
    
    console.log('Navbar visibility forced - Width:', window.innerWidth);
  }, 100);
  
  // Also handle window resize
  window.addEventListener('resize', function() {
    const navbarCollapse = document.querySelector('#navbarNav');
    if (navbarCollapse && window.innerWidth >= 992) {
      navbarCollapse.classList.add('show');
      navbarCollapse.style.display = 'flex';
    }
  });
}

/**
 * Create dynamic mouse particles
 */
function createMouseParticle(x, y) {
  const particle = document.createElement('div');
  particle.style.position = 'fixed';
  particle.style.left = x + 'px';
  particle.style.top = y + 'px';
  particle.style.width = '4px';
  particle.style.height = '4px';
  particle.style.background = 'rgba(102, 126, 234, 0.6)';
  particle.style.borderRadius = '50%';
  particle.style.pointerEvents = 'none';
  particle.style.zIndex = '9999';
  particle.style.animation = 'particleFade 1s ease-out forwards';
  
  document.body.appendChild(particle);
  
  setTimeout(() => {
    particle.remove();
  }, 1000);
}

/**
 * Typing effect for text (optional feature)
 * @param {Element} element - The element to apply typing effect to
 * @param {string} text - The text to type
 * @param {number} speed - Typing speed in milliseconds
 */
function typeWriter(element, text, speed = 100) {
  let i = 0;
  element.innerHTML = '';
  
  function type() {
    if (i < text.length) {
      element.innerHTML += text.charAt(i);
      i++;
      setTimeout(type, speed);
    }
  }
  
  setTimeout(type, 1000); // Start after 1 second
}

// Add dynamic particles on mouse move
document.addEventListener('mousemove', (e) => {
  if (Math.random() > 0.98) { // Only create particles occasionally
    createMouseParticle(e.clientX, e.clientY);
  }
});

// Uncomment to enable typing effect for hero title
// const heroTitle = document.querySelector('.hero-title');
// if (heroTitle) typeWriter(heroTitle, 'PathLab Pro');

/**
 * Initialize contact form functionality
 */
function initializeContactForm() {
  // Handle form submission with AJAX
  $('#contactForm').on('submit', function(e) {
    e.preventDefault();
    
    // Validate form before submission
    if (!validateForm()) {
      showAlert('warning', 'Please correct the errors in the form before submitting.');
      return;
    }
    
    // Get form data
    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    
    // Disable submit button and show loading state
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending...');
    
    // Clear previous alerts
    $('#alertContainer').empty();
    
    // Send AJAX request
    $.ajax({
      url: 'contact_handler.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(response) {
        if (response.success) {
          // Show success message
          showAlert('success', response.message);
          // Reset form
          $('#contactForm')[0].reset();
          // Reset form field styling
          $('#contactForm input, #contactForm select, #contactForm textarea').css('border-color', '#e9ecef');
        } else {
          // Show error message
          showAlert('danger', response.message);
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', xhr.responseText);
        let errorMessage = 'Sorry, there was an error sending your message. Please try again later.';
        
        // Try to parse error response
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.message) {
            errorMessage = response.message;
          }
        } catch (e) {
          // Use default message
        }
        
        showAlert('danger', errorMessage);
      },
      complete: function() {
        // Re-enable submit button
        submitBtn.prop('disabled', false).html(originalBtnText);
      }
    });
  });
  
  // Form field validation and styling
  $('#contactForm input, #contactForm select, #contactForm textarea').on('focus', function() {
    $(this).css('border-color', '#667eea');
  }).on('blur', function() {
    validateField($(this));
  }).on('input change', function() {
    validateField($(this));
  });
  
  // Real-time validation
  function validateField($field) {
    const value = $field.val().trim();
    const fieldName = $field.attr('name');
    let isValid = true;
    
    // Reset previous validation state
    $field.removeClass('is-valid is-invalid');
    $field.siblings('.invalid-feedback').remove();
    
    // Required field validation
    if ($field.prop('required') && value === '') {
      isValid = false;
      showFieldError($field, 'This field is required');
    }
    
    // Email validation
    else if (fieldName === 'email' && value !== '') {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        showFieldError($field, 'Please enter a valid email address');
      }
    }
    
    // Phone validation (optional but if provided should be valid)
    else if (fieldName === 'phone' && value !== '') {
      const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
      if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
        isValid = false;
        showFieldError($field, 'Please enter a valid phone number');
      }
    }
    
    if (isValid && value !== '') {
      $field.addClass('is-valid').css('border-color', '#28a745');
    } else if (!isValid) {
      $field.addClass('is-invalid').css('border-color', '#dc3545');
    } else {
      $field.css('border-color', '#e9ecef');
    }
    
    return isValid;
  }
  
  function showFieldError($field, message) {
    $field.after(`<div class="invalid-feedback" style="display: block;">${message}</div>`);
  }
  
  // Form submission validation
  function validateForm() {
    let isValid = true;
    $('#contactForm input[required], #contactForm select[required], #contactForm textarea[required]').each(function() {
      if (!validateField($(this))) {
        isValid = false;
      }
    });
    return isValid;
  }
}

/**
 * Show alert message
 */
function showAlert(type, message) {
  const iconMap = {
    'success': 'check-circle',
    'danger': 'exclamation-triangle',
    'warning': 'exclamation-circle',
    'info': 'info-circle'
  };
  
  const alertHtml = `
    <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="border-radius: 10px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
      <i class="fas fa-${iconMap[type] || 'info-circle'} mr-2"></i>
      ${message}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  `;
  
  $('#alertContainer').html(alertHtml);
  
  // Scroll to alert if not visible
  const alertContainer = $('#alertContainer');
  if (alertContainer.length) {
    $('html, body').animate({
      scrollTop: alertContainer.offset().top - 100
    }, 500);
  }
  
  // Auto-hide success messages after 5 seconds
  if (type === 'success') {
    setTimeout(() => {
      $('#alertContainer .alert').fadeOut(500, function() {
        $(this).remove();
      });
    }, 5000);
  }
}

/**
 * Initialize contact scrolling behavior
 */
function initializeContactScrolling() {
  // Smooth scroll to contact section
  $('#scrollToContact').on('click', function(e) {
    e.preventDefault();
    const target = $('#contactSection');
    if (target.length) {
      $('html, body').animate({
        scrollTop: target.offset().top - 80
      }, 800, 'easeInOutQuart');
    }
  });
}

/**
 * Initialize contact form animations
 */
function initializeContactAnimations() {
  // Fade in contact form on page load
  $('#contactSection').css('opacity', '0');
  $('#contactSection').animate({ opacity: 1 }, 1000, 'easeInOutQuart');
}
